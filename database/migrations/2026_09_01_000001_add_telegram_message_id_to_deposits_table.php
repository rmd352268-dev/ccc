<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('deposits')) {
            Schema::table('deposits', function (Blueprint $table) {
                if (!Schema::hasColumn('deposits', 'telegram_message_id')) {
                    $table->string('telegram_message_id')->nullable()->after('telegram_username');
                }
                if (!Schema::hasColumn('deposits', 'telegram_chat_id')) {
                    $table->string('telegram_chat_id')->nullable()->after('telegram_message_id');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('deposits')) {
            Schema::table('deposits', function (Blueprint $table) {
                if (Schema::hasColumn('deposits', 'telegram_chat_id')) {
                    $table->dropColumn('telegram_chat_id');
                }
                if (Schema::hasColumn('deposits', 'telegram_message_id')) {
                    $table->dropColumn('telegram_message_id');
                }
            });
        }
    }
};
