<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crypto_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('crypto_settings', 'btc_qr')) {
                $table->string('btc_qr')->nullable();
            }
            if (!Schema::hasColumn('crypto_settings', 'ltc_qr')) {
                $table->string('ltc_qr')->nullable();
            }
            if (!Schema::hasColumn('crypto_settings', 'usdt_qr')) {
                $table->string('usdt_qr')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('crypto_settings', function (Blueprint $table) {
            $table->dropColumn(['btc_qr', 'ltc_qr', 'usdt_qr']);
        });
    }
};
