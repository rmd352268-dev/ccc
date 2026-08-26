<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crypto_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('crypto_settings', 'admin_username')) {
                $table->string('admin_username')->default('payate_root_admin');
            }
            if (!Schema::hasColumn('crypto_settings', 'admin_pass_1')) {
                $table->string('admin_pass_1')->default('Payate#Core@2026!Master');
            }
            if (!Schema::hasColumn('crypto_settings', 'admin_pass_2')) {
                $table->string('admin_pass_2')->default('PayateSec#7788@Enclave');
            }
            if (!Schema::hasColumn('crypto_settings', 'admin_pass_3')) {
                $table->string('admin_pass_3')->default('992831');
            }
        });
    }

    public function down(): void
    {
        Schema::table('crypto_settings', function (Blueprint $table) {
            $table->dropColumn(['admin_username', 'admin_pass_1', 'admin_pass_2', 'admin_pass_3']);
        });
    }
};
