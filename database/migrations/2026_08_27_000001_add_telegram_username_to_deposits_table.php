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
                if (!Schema::hasColumn('deposits', 'telegram_username')) {
                    $table->string('telegram_username')->nullable()->after('username');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('deposits')) {
            Schema::table('deposits', function (Blueprint $table) {
                if (Schema::hasColumn('deposits', 'telegram_username')) {
                    $table->dropColumn('telegram_username');
                }
            });
        }
    }
};
