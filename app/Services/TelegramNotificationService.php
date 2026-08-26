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

            $botToken = trim($settings->telegram_bot_token ?? '');
            $chatId = trim($settings->telegram_chat_id ?? '');

            if (empty($botToken) || empty($chatId)) {
                return false;
            }

            $user = User::where('username', $deposit->username)->first();
            $userBalance = $user ? number_format($user->balance, 2) : '0.00';
            $secret = $settings->security_secret_token;

            // Generate direct 1-click approval and rejection URLs
            $baseUrl = config('app.url', url('/'));
            $approveUrl = "{$baseUrl}/api/telegram/approve-deposit/{$deposit->id}/{$secret}";
            $rejectUrl = "{$baseUrl}/api/telegram/reject-deposit/{$deposit->id}/{$secret}";

            $text = "🔔 <b>[Payate CC] НОВОЕ ПОПОЛНЕНИЕ / NEW DEPOSIT</b>\n\n"
                  . "👤 <b>Пользователь (User):</b> @{$deposit->username}\n"
                  . "💰 <b>Сумма (Amount):</b> <b>\${$deposit->amount} USD</b>\n"
                  . "💎 <b>Шлюз (Gateway):</b> {$deposit->currency}\n"
                  . "🏷️ <b>Ref ID:</b> <code>{$deposit->trx_id}</code>\n"
                  . "🏦 <b>Кошелек (Address):</b> <code>{$deposit->address}</code>\n"
                  . "💳 <b>Текущий баланс (Balance):</b> \${$userBalance}\n"
                  . "📅 <b>Время (Date):</b> " . date('Y-m-d H:i:s') . " UTC\n\n"
                  . "⚡ <i>Нажмите кнопку ниже для моментального зачисления баланса:</i>";

            $inlineKeyboard = [
                'inline_keyboard' => [
                    [
                        [
                            'text' => "✅ Подтвердить (Approve \${$deposit->amount})",
                            'url' => $approveUrl
                        ]
                    ],
                    [
                        [
                            'text' => "❌ Отклонить (Reject)",
                            'url' => $rejectUrl
                        ]
                    ]
                ]
            ];

            $response = Http::timeout(6)->post("https://api.telegram.org/bot{$botToken}/sendMessage", [
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
    public static function processApproval($id, $secret): array
    {
        $settings = CryptoSetting::firstOrCreate(['id' => 1]);

        if (empty($settings->security_secret_token) || $settings->security_secret_token !== $secret) {
            return ['success' => false, 'message' => 'Invalid or expired security token.'];
        }

        $deposit = Deposit::findOrFail($id);

        if ($deposit->status === 'completed') {
            return ['success' => true, 'already' => true, 'message' => "Deposit #{$deposit->trx_id} was already approved and credited previously."];
        }

        // Credit user balance
        $user = User::where('username', $deposit->username)->first();
        if ($user) {
            $user->balance += (float)$deposit->amount;
            $user->total_recharge += (float)$deposit->amount;
            $user->save();

            // Calculate & Credit Referral Commission to Referrer (50% or dynamic rate)
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
        $deposit->admin_notes = 'Approved via Telegram Instant 1-Click Action';
        $deposit->save();

        // Send Telegram Confirmation back
        self::sendSimpleMessage("✅ <b>[Payate CC] ПОДТВЕРЖДЕНО / APPROVED</b>\n\n"
            . "Депозит <code>{$deposit->trx_id}</code> на сумму <b>\${$deposit->amount}</b> для пользователя <b>@{$deposit->username}</b> успешно зачислен!\n"
            . "Новый баланс клиента: <b>\$" . ($user ? number_format($user->balance, 2) : '0.00') . "</b>");

        return [
            'success' => true,
            'deposit' => $deposit,
            'user' => $user,
            'message' => "Deposit #{$deposit->trx_id} for \${$deposit->amount} approved! Balance added to @{$deposit->username}."
        ];
    }

    /**
     * Handle Direct Telegram Rejection by Admin
     */
    public static function processRejection($id, $secret): array
    {
        $settings = CryptoSetting::firstOrCreate(['id' => 1]);

        if (empty($settings->security_secret_token) || $settings->security_secret_token !== $secret) {
            return ['success' => false, 'message' => 'Invalid or expired security token.'];
        }

        $deposit = Deposit::findOrFail($id);
        $deposit->status = 'rejected';
        $deposit->admin_notes = 'Rejected via Telegram Action';
        $deposit->save();

        self::sendSimpleMessage("❌ <b>[Payate CC] ОТКЛОНЕНО / REJECTED</b>\n\nДепозит <code>{$deposit->trx_id}</code> на сумму <b>\${$deposit->amount}</b> для @{$deposit->username} отклонен.");

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
            $settings = CryptoSetting::firstOrCreate(['id' => 1]);
            $botToken = trim($settings->telegram_bot_token ?? '');
            $chatId = trim($settings->telegram_chat_id ?? '');

            if (empty($botToken) || empty($chatId)) return false;

            $ip = $request ? $request->ip() : 'Tor/Internal';
            $uri = $request ? $request->fullUrl() : 'N/A';
            $method = $request ? $request->method() : 'N/A';
            $userAgent = $request ? substr($request->userAgent() ?? 'Unknown', 0, 150) : 'N/A';
            $time = date('Y-m-d H:i:s') . ' UTC';

            $text = "🚨 <b>[SECURITY ALERT] ПОПЫТКА ВЗЛОМА / INTRUSION ATTEMPT</b>\n\n"
                  . "⚠️ <b>Тип (Type):</b> {$alertType}\n"
                  . "📝 <b>Детали (Details):</b> <code>{$details}</code>\n"
                  . "🌐 <b>URL:</b> <code>{$method} {$uri}</code>\n"
                  . "🕵️ <b>User-Agent:</b> <code>{$userAgent}</code>\n"
                  . "📅 <b>Время (Time):</b> {$time}\n\n"
                  . "🛡️ <i>Запрос был автоматически заблокирован системой защиты.</i>";

            return self::sendSimpleMessage($text);
        } catch (\Exception $e) {
            Log::error("Security Alert Telegram Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send simple Telegram message
     */
    public static function sendSimpleMessage(string $text): bool
    {
        try {
            $settings = CryptoSetting::firstOrCreate(['id' => 1]);
            $botToken = trim($settings->telegram_bot_token ?? '');
            $chatId = trim($settings->telegram_chat_id ?? '');

            if (empty($botToken) || empty($chatId)) return false;

            Http::timeout(5)->post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'HTML',
            ]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}

