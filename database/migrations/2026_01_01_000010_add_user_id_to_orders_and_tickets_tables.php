<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'user_id')) {
                $table->foreignId('user_id')->nullable()->index();
            }
            if (!Schema::hasColumn('orders', 'username')) {
                $table->string('username')->nullable()->index();
            }
        });

        Schema::table('tickets', function (Blueprint $table) {
            if (!Schema::hasColumn('tickets', 'user_id')) {
                $table->foreignId('user_id')->nullable()->index();
            }
            if (!Schema::hasColumn('tickets', 'username')) {
                $table->string('username')->nullable()->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['user_id', 'username']);
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['user_id', 'username']);
        });
    }
};
