<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crypto_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('crypto_settings', 'activation_enabled')) {
                $table->boolean('activation_enabled')->default(true);
            }
            if (!Schema::hasColumn('crypto_settings', 'activation_title')) {
                $table->string('activation_title')->default('Activate Your Account');
            }
            if (!Schema::hasColumn('crypto_settings', 'bonus_enabled')) {
                $table->boolean('bonus_enabled')->default(true);
            }
            if (!Schema::hasColumn('crypto_settings', 'referral_commission_amount')) {
                $table->decimal('referral_commission_amount', 8, 2)->default(1.00);
            }
        });
    }

    public function down(): void
    {
        Schema::table('crypto_settings', function (Blueprint $table) {
            $table->dropColumn([
                'activation_enabled', 'activation_title', 'bonus_enabled', 'referral_commission_amount'
            ]);
        });
    }
};
