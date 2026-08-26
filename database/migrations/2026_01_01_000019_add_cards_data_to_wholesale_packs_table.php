<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wholesale_packs', function (Blueprint $table) {
            if (!Schema::hasColumn('wholesale_packs', 'cards_data')) {
                $table->longText('cards_data')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('wholesale_packs', function (Blueprint $table) {
            if (Schema::hasColumn('wholesale_packs', 'cards_data')) {
                $table->dropColumn('cards_data');
            }
        });
    }
};
