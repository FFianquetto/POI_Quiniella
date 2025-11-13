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
        if (Schema::hasTable('world_cup_bets')) {
            Schema::table('world_cup_bets', function (Blueprint $table) {
                if (!Schema::hasColumn('world_cup_bets', 'score_a')) {
                    $table->unsignedTinyInteger('score_a')->nullable()->after('selected_code');
                }
                if (!Schema::hasColumn('world_cup_bets', 'score_b')) {
                    $table->unsignedTinyInteger('score_b')->nullable()->after('score_a');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('world_cup_bets')) {
            Schema::table('world_cup_bets', function (Blueprint $table) {
                if (Schema::hasColumn('world_cup_bets', 'score_a')) {
                    $table->dropColumn('score_a');
                }
                if (Schema::hasColumn('world_cup_bets', 'score_b')) {
                    $table->dropColumn('score_b');
                }
            });
        }
    }
};

