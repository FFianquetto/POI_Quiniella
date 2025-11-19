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
        if (!Schema::hasTable('world_cup_tournaments')) {
            return;
        }

        Schema::table('world_cup_tournaments', function (Blueprint $table) {
            if (Schema::hasColumn('world_cup_tournaments', 'rounds')) {
                $table->json('rounds')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('world_cup_tournaments')) {
            return;
        }

        Schema::table('world_cup_tournaments', function (Blueprint $table) {
            if (Schema::hasColumn('world_cup_tournaments', 'rounds')) {
                $table->json('rounds')->nullable(false)->change();
            }
        });
    }
};
