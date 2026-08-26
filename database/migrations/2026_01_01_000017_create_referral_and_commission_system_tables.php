<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'referred_by')) {
                $table->string('referred_by')->nullable()->after('role');
            }
            if (!Schema::hasColumn('users', 'referral_code')) {
                $table->string('referral_code')->nullable()->after('referred_by');
            }
            if (!Schema::hasColumn('users', 'commission_balance')) {
                $table->decimal('commission_balance', 10, 2)->default(0.00)->after('referral_code');
            }
        });

        if (!Schema::hasTable('commissions')) {
            Schema::create('commissions', function (Blueprint $table) {
                $table->id();
                $table->string('referrer_username');
                $table->string('referred_username');
                $table->string('deposit_trx_id')->nullable();
                $table->decimal('deposit_amount', 10, 2)->default(0.00);
                $table->decimal('commission_rate', 5, 2)->default(50.00);
                $table->decimal('commission_amount', 10, 2)->default(0.00);
                $table->string('status')->default('credited'); // credited, transferred
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['referred_by', 'referral_code', 'commission_balance']);
        });
        Schema::dropIfExists('commissions');
    }
};
