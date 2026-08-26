<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crypto_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('crypto_settings', 'telegram_bot_token')) {
                $table->string('telegram_bot_token')->nullable();
            }
            if (!Schema::hasColumn('crypto_settings', 'telegram_chat_id')) {
                $table->string('telegram_chat_id')->nullable();
            }
            if (!Schema::hasColumn('crypto_settings', 'telegram_notify_enabled')) {
                $table->boolean('telegram_notify_enabled')->default(true);
            }
            if (!Schema::hasColumn('crypto_settings', 'security_secret_token')) {
                $table->string('security_secret_token', 64)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('crypto_settings', function (Blueprint $table) {
            $table->dropColumn(['telegram_bot_token', 'telegram_chat_id', 'telegram_notify_enabled', 'security_secret_token']);
        });
    }
};
