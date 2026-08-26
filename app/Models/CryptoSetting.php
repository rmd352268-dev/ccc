<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CryptoSetting extends Model
{
    use HasFactory;

    protected $guarded = [];

    public static function getSettings()
    {
        return self::firstOrCreate(['id' => 1], [
            'btc_address' => 'bc1q54tlpkne0oqdgczcej0jwy6dd8gx4w4p48w6wu',
            'btc_rate' => '69,525.00',
            'ltc_address' => 'ltc1qguspwq09kw86d07u64w7ezyy9d39stpdstcec',
            'ltc_rate' => '46.33',
            'usdt_address' => 'TP3vFabnm17eSNhYJRtg3gGSX3hLzjRVjf',
            'min_deposit' => '10.00',
            'admin_username' => 'payate_root_admin',
            'admin_pass_1' => 'Payate#Core@2026!Master',
            'admin_pass_2' => 'PayateSec#7788@Enclave',
            'admin_pass_3' => '992831',
            'activation_enabled' => true,
            'activation_title' => 'Activate Your Account',
            'activation_subtitle' => 'The marketplace is reserved for verified members. Make a one-time minimum deposit of $10.00 to unlock the vault — funds stay yours, ready to spend.',
            'bonus_enabled' => true,
            'referral_commission_percent' => 50.00,
        ]);
    }

    public function getBonusTiers()
    {
        if (!empty($this->bonus_tiers_json)) {
            $decoded = json_decode($this->bonus_tiers_json, true);
            if (is_array($decoded) && !empty($decoded)) {
                return $decoded;
            }
        }

        // Default 10 Balanced Tiers
        return [
            ['icon' => '🥉', 'deposit' => 10, 'bonus' => 1.00, 'total' => 11.00],
            ['icon' => '🥈', 'deposit' => 20, 'bonus' => 2.00, 'total' => 22.00],
            ['icon' => '🥇', 'deposit' => 30, 'bonus' => 3.50, 'total' => 33.50],
            ['icon' => '💎', 'deposit' => 50, 'bonus' => 6.00, 'total' => 56.00],
            ['icon' => '🚀', 'deposit' => 75, 'bonus' => 10.00, 'total' => 85.00],
            ['icon' => '⭐', 'deposit' => 100, 'bonus' => 15.00, 'total' => 115.00],
            ['icon' => '👑', 'deposit' => 150, 'bonus' => 25.00, 'total' => 175.00],
            ['icon' => '🔥', 'deposit' => 200, 'bonus' => 35.00, 'total' => 235.00],
            ['icon' => '⚡', 'deposit' => 300, 'bonus' => 55.00, 'total' => 355.00],
            ['icon' => '🏆', 'deposit' => 500, 'bonus' => 100.00, 'total' => 600.00],
        ];
    }

    public function getPerks()
    {
        if (!empty($this->perks_data)) {
            $decoded = json_decode($this->perks_data, true);
            if (is_array($decoded) && !empty($decoded)) {
                return $decoded;
            }
        }

        return [
            ['icon' => 'fa-shield-halved', 'color' => '#38BDF8', 'title' => 'Verified Status', 'desc' => 'Anti-fraud protection for community'],
            ['icon' => 'fa-bolt', 'color' => '#FBBF24', 'title' => 'Instant Access', 'desc' => 'Unlocks entire live inventory immediately'],
            ['icon' => 'fa-wallet', 'color' => '#34D399', 'title' => 'Wallet Credit', 'desc' => 'Every cent goes to balance — spend anytime'],
            ['icon' => 'fa-gem', 'color' => '#A78BFA', 'title' => 'Premium Perks', 'desc' => 'Auto-replacement, priority member support'],
        ];
    }

    public function getTelegramButtons()

    {
        if (!empty($this->telegram_custom_buttons)) {
            $decoded = json_decode($this->telegram_custom_buttons, true);
            if (is_array($decoded) && !empty($decoded)) {
                return $decoded;
            }
        }

        return [
            ["🚀 /start", "📊 Live Status"],
            ["💰 Pending Deposits", "👥 User Management"],
            ["💳 Cards Vault", "🎫 Support Desk"],
            ["📢 News Feed", "⚙️ Wallets & Config"],
            ["📦 Wholesale Packs", "📋 Orders & Sales"],
        ];
    }
}

