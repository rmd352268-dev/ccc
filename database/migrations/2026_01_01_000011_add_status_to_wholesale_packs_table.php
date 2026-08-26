<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wholesale_packs', function (Blueprint $table) {
            if (!Schema::hasColumn('wholesale_packs', 'status')) {
                $table->string('status', 20)->default('available')->index();
            }
            if (!Schema::hasColumn('wholesale_packs', 'buyer_username')) {
                $table->string('buyer_username', 50)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('wholesale_packs', function (Blueprint $table) {
            $table->dropColumn(['status', 'buyer_username']);
        });
    }
};
