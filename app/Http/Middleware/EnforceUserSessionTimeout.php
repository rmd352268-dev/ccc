<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceUserSessionTimeout
{
    /**
     * Handle an incoming request: enforce strict 1-hour session timeout & instant suspension lock for regular users ONLY.
     * Admin panel and admin sessions are strictly isolated and 100% exempt from user bans.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. NEVER block or interfere with Admin Panel routes
        if ($request->is('admin*') || $request->is('admin/*')) {
            return $next($request);
        }

        if (session('user_logged_in') === true) {
            $userId = session('user_id');
            $user = $userId ? User::find($userId) : null;
            if (!$user && session()->has('user_username')) {
                $user = User::where('username', session('user_username'))->first();
            }

            // 2. Instant Suspension Check: Only for regular client accounts
            if ($user && $user->role !== 'admin' && ($user->status === 'suspended' || $user->status === 'banned')) {
                // Clear ONLY client user session keys — NEVER touch admin session keys
                session()->forget([
                    'user_logged_in', 'user_id', 'user_username', 'user_balance', 
                    'total_recharge', 'user_profile', 'user_login_timestamp',
                    'pending_login_user_id', 'pending_login_username', 'commission_balance'
                ]);

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'error' => 'Account suspended/banned by system administration.'
                    ], 403);
                }

                return response()->view('auth.suspended', [], 403);
            }

            // Sync live database stats into session so all views and controllers are 100% up-to-date
            if ($user) {
                session()->put('user_id', $user->id);
                session()->put('user_username', $user->username);
                session()->put('user_balance', (float)$user->balance);
                session()->put('total_recharge', (float)$user->total_recharge);
                session()->put('commission_balance', (float)$user->commission_balance);
            }

            // 3. 1-Hour Session Timeout Check (3600 seconds) for client users
            $loginTime = session('user_login_timestamp');
            $maxLifetime = 3600; // 1 Hour

            if ($loginTime && (time() - $loginTime > $maxLifetime)) {
                session()->forget([
                    'user_logged_in', 'user_id', 'user_username', 'user_balance', 
                    'total_recharge', 'user_profile', 'user_login_timestamp',
                    'pending_login_user_id', 'pending_login_username', 'commission_balance'
                ]);

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'error' => 'Security timeout: 1-hour session expired. Please log in again.'
                    ], 401);
                }

                return redirect()->route('login')->with('error', 'Security Timeout: Your 1-hour session has expired. Please log in again.');
            }
        }

        return $next($request);
    }
}
