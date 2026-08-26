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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_activated')->default(false)->after('status');
        });

        // Automatically activate any existing users who have completed deposits or total_recharge > 0 or balance > 0
        \App\Models\User::all()->each(function ($u) {
            $hasDeposit = \App\Models\Deposit::where('username', $u->username)->where('status', 'completed')->exists();
            if ($hasDeposit || $u->total_recharge > 0 || $u->balance > 0 || $u->role === 'admin') {
                $u->is_activated = 1;
                $u->save();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_activated');
        });
    }
};
