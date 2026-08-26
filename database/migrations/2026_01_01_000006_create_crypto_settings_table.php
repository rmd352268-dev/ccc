<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crypto_settings', function (Blueprint $table) {
            $table->id();
            $table->string('btc_address')->default('bc1q54tlpkne0oqdgczcej0jwy6dd8gx4w4p48w6wu');
            $table->string('btc_rate')->default('69,525.00');
            $table->string('ltc_address')->default('ltc1qguspwq09kw86d07u64w7ezyy9d39stpdstcec');
            $table->string('ltc_rate')->default('46.33');
            $table->string('usdt_address')->default('TP3vFabnm17eSNhYJRtg3gGSX3hLzjRVjf');
            $table->string('min_deposit')->default('10.00');
            $table->timestamps();
        });

        // Ensure deposits table has all verification columns
        if (Schema::hasTable('deposits')) {
            Schema::table('deposits', function (Blueprint $table) {
                if (!Schema::hasColumn('deposits', 'username')) {
                    $table->string('username')->default('asadulislam17p')->after('id');
                }
                if (!Schema::hasColumn('deposits', 'txid')) {
                    $table->string('txid')->nullable()->after('amount');
                }
                if (!Schema::hasColumn('deposits', 'admin_notes')) {
                    $table->text('admin_notes')->nullable()->after('status');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('crypto_settings');
    }
};
