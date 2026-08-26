<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class TorSecurityMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Intrusion Detection System (IDS): Inspect for attack signatures
        if ($attackInfo = $this->detectAttack($request)) {
            $this->reportAndLogAttack($attackInfo, $request);
            return response('', 404, ['Content-Type' => 'text/plain']);
        }

        $torEnforced = config('security.tor_only_enforce', false);

        // If Tor-only enforcement is active, perform access validation
        if ($torEnforced && !$this->isTorAuthorized($request)) {
            return $this->handleBlockedResponse($request);
        }

        /** @var Response $response */
        $response = $next($request);

        // Apply strict anti-leak and privacy security headers to the response
        $this->applySecurityHeaders($response, $request);

        return $response;
    }

    /**
     * Inspect request URI, query parameters, and body for attack patterns.
     */
    protected function detectAttack(Request $request): ?string
    {
        $uri = strtolower($request->getRequestUri());
        
        // A. Scanner Probes & Sensitive Path Hunting
        $badPaths = [
            '.env', '.git', 'wp-admin', 'wp-login', 'phpmyadmin', 'eval-stdin',
            'phpinfo', 'telescope', 'debugbar', '_ignition', 'storage/logs',
            'etc/passwd', 'win.ini', 'boot.ini', 'proc/self', 'select%20', 'union%20select'
        ];
        foreach ($badPaths as $bad) {
            if (str_contains($uri, $bad)) {
                return "Malicious URL Scanner Probe: '{$bad}'";
            }
        }

        // B. Query String & Input Attack Inspection (SQL Injection / XSS / RCE)
        $allInputs = json_encode($request->all());
        $attackSignatures = [
            '/union\s+(all\s+)?select/i' => 'SQL Injection (UNION SELECT)',
            '/information_schema/i' => 'SQL Injection (information_schema scan)',
            '/(sleep\(|benchmark\(|waitfor\s+delay)/i' => 'SQL Injection Time-Based Blind',
            '/<script[\s\S]*?>[\s\S]*?<\/script>/i' => 'Cross-Site Scripting (XSS Script Tag)',
            '/(cmd\.exe|\/bin\/sh|\/bin\/bash)/i' => 'Remote Command Execution (RCE)',
            '/(\.\.\/|\.\.\\\\)/i' => 'Directory Path Traversal (../)',
        ];

        foreach ($attackSignatures as $pattern => $attackName) {
            if (preg_match($pattern, $allInputs) || preg_match($pattern, $uri)) {
                return $attackName;
            }
        }

        return null;
    }

    /**
     * Report attack to Telegram Admin with rate limiting
     */
    protected function reportAndLogAttack(string $attackName, Request $request): void
    {
        $cacheKey = 'sec_alert_' . md5($attackName . $request->ip() . $request->path());
        
        // Alert at most once per 60 seconds for the same attack type
        if (!Cache::has($cacheKey)) {
            Cache::put($cacheKey, true, 60);
            
            try {
                if (class_exists(\App\Services\TelegramNotificationService::class)) {
                    \App\Services\TelegramNotificationService::sendSecurityAlert(
                        $attackName,
                        'Target: ' . $request->path(),
                        $request
                    );
                }
            } catch (\Throwable $e) {
                // Fail safe silently
            }
        }
    }

    /**
     * Determine if the incoming request is authorized under Tor-only rules.
     */
    protected function isTorAuthorized(Request $request): bool
    {
        // 1. Check for emergency secret bypass token (URL param, HTTP header, or session)
        if ($this->hasBypassAccess($request)) {
            return true;
        }

        // 2. Allow local / private network access if enabled (for dev/local setup)
        if (config('security.tor_allow_local', true) && $this->isLocalOrPrivateIp($request->ip())) {
            return true;
        }

        // 3. Direct access via Tor Hidden Service (.onion domain)
        if ($this->isTorOnionDomain($request)) {
            return true;
        }

        // 4. Real-time Tor Exit Node verification via DNSBL (dnsel.torproject.org)
        if ($this->isVerifiedTorExitNode($request->ip())) {
            return true;
        }

        return false;
    }

    /**
     * Check if the host requested is an official .onion address.
     */
    protected function isTorOnionDomain(Request $request): bool
    {
        $host = strtolower($request->getHost());
        return str_ends_with($host, '.onion');
    }

    /**
     * Check if client IP is localhost or a private LAN subnet.
     */
    protected function isLocalOrPrivateIp(?string $ip): bool
    {
        if (empty($ip)) {
            return false;
        }

        if (in_array($ip, ['127.0.0.1', '::1', 'localhost'], true)) {
            return true;
        }

        // Private network ranges (10.0.0.0/8, 172.16.0.0/12, 192.168.0.0/16)
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return true;
        }

        return false;
    }

    /**
     * Check for administrator bypass credentials.
     */
    protected function hasBypassAccess(Request $request): bool
    {
        $bypassKey = config('security.tor_bypass_key');
        if (empty($bypassKey)) {
            return false;
        }

        // Check query string: ?bypass_tor=YOUR_KEY
        if ($request->query('bypass_tor') === $bypassKey) {
            if ($request->hasSession()) {
                $request->session()->put('tor_bypass_active', true);
            }
            return true;
        }

        // Check custom header: X-Tor-Bypass: YOUR_KEY
        if ($request->header('X-Tor-Bypass') === $bypassKey) {
            return true;
        }

        // Check active session
        if ($request->hasSession() && $request->session()->get('tor_bypass_active') === true) {
            return true;
        }

        return false;
    }

    /**
     * Check if client IP address is a verified Tor Exit Node using Tor Project DNSBL.
     */
    protected function isVerifiedTorExitNode(?string $clientIp): bool
    {
        if (empty($clientIp) || !filter_var($clientIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return false;
        }

        $cacheTtl = (int) config('security.tor_cache_ttl', 3600);
        $cacheKey = 'tor_node_check_' . md5($clientIp);

        return (bool) Cache::remember($cacheKey, $cacheTtl, function () use ($clientIp) {
            return $this->queryTorDnsbl($clientIp);
        });
    }

    /**
     * Query Tor Project DNSBL zone.
     */
    protected function queryTorDnsbl(string $clientIp): bool
    {
        try {
            $reversedIp = implode('.', array_reverse(explode('.', $clientIp)));
            $dnsblZone = config('security.tor_dnsbl_zone', 'dnsel.torproject.org');
            $query = "{$reversedIp}.{$dnsblZone}";

            // Set socket timeout for fast lookup
            $result = @gethostbyname($query);

            // Tor DNSEL returns '127.0.0.2' when IP is an active Tor exit node
            return ($result === '127.0.0.2');
        } catch (\Throwable $e) {
            // Fail safely if DNS lookup fails
            return false;
        }
    }

    /**
     * Return blocked response based on config.
     */
    protected function handleBlockedResponse(Request $request): Response
    {
        $action = config('security.tor_block_action', 'blank');

        switch ($action) {
            case '403':
                return response('403 Forbidden', 403, [
                    'Content-Type' => 'text/plain',
                ]);

            case 'fake_error':
                $html = '<!DOCTYPE html><html><head><title>404 Not Found</title></head><body>'
                      . '<h1>Not Found</h1><p>The requested URL was not found on this server.</p>'
                      . '<hr><address>Apache Server at ' . htmlspecialchars($request->getHost()) . ' Port 80</address>'
                      . '</body></html>';
                return response($html, 404, ['Content-Type' => 'text/html']);

            case 'tor_notice':
                $html = '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8">'
                      . '<meta name="viewport" content="width=device-width, initial-scale=1">'
                      . '<title>Access Denied - Tor Browser Required</title>'
                      . '<style>'
                      . 'body{background:#0d1117;color:#c9d1d9;font-family:system-ui,-apple-system,sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;padding:20px;box-sizing:border-box;}'
                      . '.card{background:#161b22;border:1px solid #30363d;border-radius:12px;max-width:500px;width:100%;padding:32px;text-align:center;box-shadow:0 10px 30px rgba(0,0,0,0.5);}'
                      . '.icon{font-size:48px;margin-bottom:16px;}'
                      . 'h1{color:#f85149;font-size:22px;margin:0 0 12px;font-weight:700;}'
                      . 'p{color:#8b949e;font-size:14px;line-height:1.6;margin:0 0 20px;}'
                      . '.badge{display:inline-block;background:#238636;color:#fff;padding:8px 16px;border-radius:6px;font-size:13px;font-weight:600;text-decoration:none;}'
                      . '</style></head><body>'
                      . '<div class="card">'
                      . '<div class="icon">🔒</div>'
                      . '<h1>Security Restriction</h1>'
                      . '<p>Access to this service is strictly restricted. For privacy and anonymity, standard browsers are blocked. Please access this website using <strong>Tor Browser</strong> or the official Tor Network.</p>'
                      . '<a href="https://www.torproject.org/download/" target="_blank" rel="noopener noreferrer" class="badge">Download Tor Browser</a>'
                      . '</div></body></html>';
                return response($html, 403, ['Content-Type' => 'text/html']);

            case 'blank':
            default:
                // Stealth Blank 404
                return response('', 404, [
                    'Content-Type' => 'text/plain',
                ]);
        }
    }

    /**
     * Apply maximum privacy, anti-leak, and security headers.
     */
    protected function applySecurityHeaders(Response $response, Request $request): void
    {
        // 1. Completely disable browser tracking, camera, microphone, GPS/location APIs
        $response->headers->set(
            'Permissions-Policy',
            'geolocation=(), camera=(), microphone=(), interest-cohort=(), payment=(), usb=(), sync-xhr=(), display-capture=(), accelerometer=(), gyroscope=(), magnetometer=()'
        );

        // 2. Prevent URL / IP leaking to third parties via referrer
        $response->headers->set('Referrer-Policy', 'no-referrer');

        // 3. Prevent Clickjacking & Iframe sniffing
        $response->headers->set('X-Frame-Options', 'DENY');

        // 4. Prevent MIME-sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // 5. Cross-Origin Protection
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');
        $response->headers->set('Cross-Origin-Resource-Policy', 'same-origin');

        // 6. XSS Filter
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // 7. Tor Onion-Location Header (prompts Tor Browser users to use .onion address)
        $onionAddress = config('security.tor_onion_address');
        if (!empty($onionAddress) && !$this->isTorOnionDomain($request)) {
            $cleanOnion = rtrim(str_replace(['http://', 'https://'], '', $onionAddress), '/');
            $requestUri = $request->getRequestUri();
            $response->headers->set('Onion-Location', "http://{$cleanOnion}{$requestUri}");
        }

        // 8. Remove fingerprinting headers that expose PHP / server stack
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');
        if (function_exists('header_remove')) {
            @header_remove('X-Powered-By');
            @header_remove('Server');
        }
    }
}
