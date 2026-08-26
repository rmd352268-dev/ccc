<?php

namespace App\Services;

use App\Models\Deposit;
use App\Models\User;
use App\Models\CryptoSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramNotificationService
{
    /**
     * Get configured HTTP client with Tor SOCKS5 proxy support if available
     */
    protected static function getHttpClient()
    {
        $options = [
            'timeout' => 10,
            'connect_timeout' => 6,
        ];

        // If Tor SOCKS proxy is listening on 127.0.0.1:9050, route requests through it
        $connection = @fsockopen('127.0.0.1', 9050, $errno, $errstr, 0.2);
        if (is_resource($connection)) {
            fclose($connection);
            $options['proxy'] = 'socks5h://127.0.0.1:9050';
        }

        return Http::withOptions($options);
    }

    /**
     * Send Instant Telegram Alert to Admin for newly submitted deposits
     */
    public static function sendDepositAlert(Deposit $deposit): bool
    {
        try {
            $settings = CryptoSetting::firstOrCreate(['id' => 1]);

            if (empty($settings->security_secret_token)) {
                $settings->security_secret_token = bin2hex(random_bytes(16));
                $settings->save();
            }

            $botToken = trim($settings->telegram_bot_token ?? '8615399993:AAEwJGBH7EMQK88sNQzmF1ExNp_tQU1sMVs');
            $chatId = trim($settings->telegram_chat_id ?? '8814743492');

            if (empty($botToken) || empty($chatId)) {
                return false;
            }

            $user = User::where('username', $deposit->username)->first();
            $userBalance = $user ? number_format($user->balance, 2) : '0.00';
            $secret = $settings->security_secret_token;

            $baseUrl = config('app.url', url('/'));
            $approveUrl = "{$baseUrl}/api/telegram/approve-deposit/{$deposit->id}/{$secret}";
            $rejectUrl = "{$baseUrl}/api/telegram/reject-deposit/{$deposit->id}/{$secret}";

            $text = "💰 <b>[Payate CC] NEW DEPOSIT SUBMITTED!</b>\n\n"
                  . "👤 <b>User:</b> @{$deposit->username}\n"
                  . "💵 <b>Amount:</b> <b>\${$deposit->amount} USD</b>\n"
                  . "💎 <b>Gateway:</b> {$deposit->currency}\n"
                  . "🏷️ <b>Ref ID:</b> <code>{$deposit->trx_id}</code>\n"
                  . "🏦 <b>Address:</b> <code>{$deposit->address}</code>\n"
                  . "💳 <b>Current Balance:</b> \${$userBalance}\n"
                  . "📅 <b>Time:</b> " . date('Y-m-d H:i:s') . " UTC\n\n"
                  . "⚡ <i>Click below to approve or reject instantly:</i>";

            $inlineKeyboard = [
                'inline_keyboard' => [
                    [
                        [
                            'text' => "✅ Approve (\${$deposit->amount})",
                            'callback_data' => "approve_deposit:{$deposit->id}"
                        ],
                        [
                            'text' => "❌ Reject",
                            'callback_data' => "reject_deposit:{$deposit->id}"
                        ]
                    ],
                    [
                        [
                            'text' => "🌐 Direct Web Link",
                            'url' => $approveUrl
                        ]
                    ]
                ]
            ];

            $response = self::getHttpClient()->post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'HTML',
                'reply_markup' => json_encode($inlineKeyboard),
                'disable_web_page_preview' => true,
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error("Telegram Deposit Alert Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Handle Direct Telegram Approval by Admin
     */
    public static function processApproval($id, $secret = null): array
    {
        $settings = CryptoSetting::firstOrCreate(['id' => 1]);

        if ($secret !== null && (empty($settings->security_secret_token) || $settings->security_secret_token !== $secret)) {
            return ['success' => false, 'message' => 'Invalid security token.'];
        }

        $deposit = Deposit::findOrFail($id);

        if ($deposit->status === 'completed') {
            return ['success' => true, 'already' => true, 'message' => "Deposit #{$deposit->trx_id} was already approved and credited."];
        }

        // Credit user balance
        $user = User::where('username', $deposit->username)->first();
        if ($user) {
            $user->balance += (float)$deposit->amount;
            $user->total_recharge += (float)$deposit->amount;
            $user->save();

            // Referral Commission
            if (!empty($user->referred_by)) {
                $referrer = User::where('username', $user->referred_by)
                    ->orWhere('referral_code', $user->referred_by)
                    ->first();
                if ($referrer) {
                    $commissionPercent = (float)($settings->referral_commission_percent ?? 50.00);
                    $commissionAmount = round(($deposit->amount * $commissionPercent) / 100.0, 2);

                    if ($commissionAmount > 0) {
                        $referrer->commission_balance = (float)$referrer->commission_balance + $commissionAmount;
                        $referrer->save();

                        \App\Models\Commission::create([
                            'referrer_username' => $referrer->username,
                            'referred_username' => $user->username,
                            'deposit_trx_id' => $deposit->trx_id,
                            'deposit_amount' => $deposit->amount,
                            'commission_rate' => $commissionPercent,
                            'commission_amount' => $commissionAmount,
                            'status' => 'credited',
                        ]);
                    }
                }
            }
        }

        $deposit->status = 'completed';
        $deposit->admin_notes = 'Approved via Telegram 1-Click Action';
        $deposit->save();

        self::sendSimpleMessage("✅ <b>[Payate CC] DEPOSIT APPROVED & CREDITED!</b>\n\n"
            . "Deposit <code>{$deposit->trx_id}</code> (\${$deposit->amount}) for <b>@{$deposit->username}</b> has been credited.\n"
            . "User New Balance: <b>\$" . ($user ? number_format($user->balance, 2) : '0.00') . "</b>");

        return [
            'success' => true,
            'deposit' => $deposit,
            'user' => $user,
            'message' => "Deposit #{$deposit->trx_id} approved! \${$deposit->amount} credited to @{$deposit->username}."
        ];
    }

    /**
     * Handle Direct Telegram Rejection by Admin
     */
    public static function processRejection($id, $secret = null): array
    {
        $settings = CryptoSetting::firstOrCreate(['id' => 1]);

        if ($secret !== null && (empty($settings->security_secret_token) || $settings->security_secret_token !== $secret)) {
            return ['success' => false, 'message' => 'Invalid security token.'];
        }

        $deposit = Deposit::findOrFail($id);
        $deposit->status = 'rejected';
        $deposit->admin_notes = 'Rejected via Telegram Admin Action';
        $deposit->save();

        self::sendSimpleMessage("❌ <b>[Payate CC] DEPOSIT REJECTED</b>\n\nDeposit <code>{$deposit->trx_id}</code> (\${$deposit->amount}) for @{$deposit->username} was rejected.");

        return [
            'success' => true,
            'deposit' => $deposit,
            'message' => "Deposit #{$deposit->trx_id} has been marked as rejected."
        ];
    }

    /**
     * Send Security Intrusion Alert to Admin Telegram
     */
    public static function sendSecurityAlert(string $alertType, string $details = '', $request = null): bool
    {
        try {
            $ip = $request ? $request->ip() : 'Tor/Internal';
            $uri = $request ? $request->fullUrl() : 'N/A';
            $method = $request ? $request->method() : 'N/A';
            $userAgent = $request ? substr($request->userAgent() ?? 'Unknown', 0, 120) : 'N/A';
            $time = date('Y-m-d H:i:s') . ' UTC';

            $text = "🚨 <b>[SECURITY ALERT] INTRUSION BLOCKED!</b>\n\n"
                  . "⚠️ <b>Type:</b> {$alertType}\n"
                  . "📝 <b>Details:</b> <code>{$details}</code>\n"
                  . "🌐 <b>Target:</b> <code>{$method} {$uri}</code>\n"
                  . "🕵️ <b>User-Agent:</b> <code>{$userAgent}</code>\n"
                  . "📅 <b>Time:</b> {$time}\n\n"
                  . "🛡️ <i>The malicious request was safely intercepted and blocked.</i>";

            return self::sendSimpleMessage($text);
        } catch (\Exception $e) {
            Log::error("Security Alert Telegram Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send New Order Alert
     */
    public static function sendOrderAlert($order, $itemCount = 1): bool
    {
        try {
            $text = "🛒 <b>[Payate CC] NEW ORDER PURCHASED!</b>\n\n"
                  . "👤 <b>Buyer:</b> @{$order->username}\n"
                  . "📦 <b>Order ID:</b> #{$order->id}\n"
                  . "💵 <b>Total Paid:</b> \${$order->total_price}\n"
                  . "🏷️ <b>Items Count:</b> {$itemCount} item(s)\n"
                  . "📅 <b>Time:</b> " . date('Y-m-d H:i:s') . " UTC";

            return self::sendSimpleMessage($text);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Send New User Registered Alert
     */
    public static function sendUserRegisteredAlert(User $user): bool
    {
        try {
            $ref = $user->referred_by ? " (Ref by: @{$user->referred_by})" : "";
            $text = "👤 <b>[Payate CC] NEW USER REGISTRATION</b>\n\n"
                  . "👤 <b>Username:</b> @{$user->username}{$ref}\n"
                  . "🆔 <b>User ID:</b> #{$user->id}\n"
                  . "📅 <b>Registered At:</b> " . date('Y-m-d H:i:s') . " UTC";

            return self::sendSimpleMessage($text);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Send Settings Update Alert to Admin Telegram
     */
    public static function sendSettingsUpdateAlert(string $title, string $details = ''): bool
    {
        try {
            $time = date('Y-m-d H:i:s') . ' UTC';
            $text = "⚙️ <b>[ADMIN PANEL SYNC] SETTINGS UPDATED</b>\n"
                  . "━━━━━━━━━━━━━━━━━━━━\n"
                  . "🛠️ <b>Category:</b> {$title}\n"
                  . (!empty($details) ? "📝 <b>Details:</b>\n<code>{$details}</code>\n" : "")
                  . "━━━━━━━━━━━━━━━━━━━━\n"
                  . "🔄 <i>Telegram Bot and Database are now synchronized.</i>\n"
                  . "⏰ <b>Time:</b> {$time}";

            return self::sendSimpleMessage($text);
        } catch (\Exception $e) {
            Log::error("Settings Update Telegram Alert Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send Admin Action Alert (e.g. news, wholesale, bulk clean)
     */
    public static function sendAdminActionAlert(string $title, string $details = ''): bool
    {
        try {
            $time = date('Y-m-d H:i:s') . ' UTC';
            $text = "⚡ <b>[ADMIN ACTION PERFORMED]</b>\n"
                  . "━━━━━━━━━━━━━━━━━━━━\n"
                  . "📌 <b>Action:</b> {$title}\n"
                  . (!empty($details) ? "📝 <b>Details:</b>\n{$details}\n" : "")
                  . "━━━━━━━━━━━━━━━━━━━━\n"
                  . "⏰ <b>Time:</b> {$time}";

            return self::sendSimpleMessage($text);
        } catch (\Exception $e) {
            Log::error("Admin Action Telegram Alert Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Trigger Background Git Sync
     */
    public static function triggerGitSync(): void
    {
        try {
            $script = base_path('auto_git_sync.py');
            if (file_exists($script)) {
                $cmd = "start /B python \"{$script}\"";
                pclose(popen($cmd, "r"));
            }
        } catch (\Exception $e) {
            Log::error("Git Sync Trigger Error: " . $e->getMessage());
        }
    }

    /**
     * Send simple Telegram message
     */
    public static function sendSimpleMessage(string $text): bool
    {
        try {
            $settings = CryptoSetting::firstOrCreate(['id' => 1]);
            $botToken = trim($settings->telegram_bot_token ?? '8615399993:AAEwJGBH7EMQK88sNQzmF1ExNp_tQU1sMVs');
            $chatId = trim($settings->telegram_chat_id ?? '8814743492');

            if (empty($botToken) || empty($chatId)) return false;

            $response = self::getHttpClient()->post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => true,
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error("Telegram sendSimpleMessage Error: " . $e->getMessage());
            return false;
        }
    }
}

