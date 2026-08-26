<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('crypto_settings') && !Schema::hasColumn('crypto_settings', 'telegram_custom_buttons')) {
            Schema::table('crypto_settings', function (Blueprint $table) {
                $table->text('telegram_custom_buttons')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('crypto_settings') && Schema::hasColumn('crypto_settings', 'telegram_custom_buttons')) {
            Schema::table('crypto_settings', function (Blueprint $table) {
                $table->dropColumn('telegram_custom_buttons');
            });
        }
    }
};
