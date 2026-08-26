<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->unique()->nullable()->after('id');
            $table->decimal('balance', 10, 2)->default(10.00)->after('password');
            $table->decimal('total_recharge', 10, 2)->default(0.00)->after('balance');
            $table->string('telegram')->nullable()->after('total_recharge');
            $table->string('jabber')->nullable()->after('telegram');
            $table->string('phone')->nullable()->after('jabber');
            $table->string('country')->default('US')->after('phone');
            $table->string('timezone')->default('America/Los_Angeles')->after('country');
            $table->string('tier')->default('Verified VIP Member')->after('timezone');
            $table->string('status')->default('active')->after('tier'); // active, banned, suspended
            $table->string('role')->default('user')->after('status'); // user, admin
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'username', 'balance', 'total_recharge', 'telegram', 'jabber',
                'phone', 'country', 'timezone', 'tier', 'status', 'role'
            ]);
        });
    }
};
