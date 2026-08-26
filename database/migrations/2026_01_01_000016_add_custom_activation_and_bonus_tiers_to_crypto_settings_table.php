<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crypto_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('crypto_settings', 'activation_subtitle')) {
                $table->text('activation_subtitle')->nullable();
            }
            if (!Schema::hasColumn('crypto_settings', 'perks_data')) {
                $table->text('perks_data')->nullable();
            }
            if (!Schema::hasColumn('crypto_settings', 'bonus_tiers_json')) {
                $table->text('bonus_tiers_json')->nullable();
            }
            if (!Schema::hasColumn('crypto_settings', 'referral_commission_percent')) {
                $table->decimal('referral_commission_percent', 5, 2)->default(50.00);
            }
        });
    }

    public function down(): void
    {
        Schema::table('crypto_settings', function (Blueprint $table) {
            $table->dropColumn([
                'activation_subtitle', 'perks_data', 'bonus_tiers_json', 'referral_commission_percent'
            ]);
        });
    }
};
