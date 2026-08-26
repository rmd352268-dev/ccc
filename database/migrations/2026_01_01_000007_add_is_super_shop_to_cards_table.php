<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('cards')) {
            Schema::table('cards', function (Blueprint $table) {
                if (!Schema::hasColumn('cards', 'is_super_shop')) {
                    $table->boolean('is_super_shop')->default(false)->after('status');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('cards') && Schema::hasColumn('cards', 'is_super_shop')) {
            Schema::table('cards', function (Blueprint $table) {
                $table->dropColumn('is_super_shop');
            });
        }
    }
};
