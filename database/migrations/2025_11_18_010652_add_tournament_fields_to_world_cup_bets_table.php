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
        if (!Schema::hasTable('world_cup_bets')) {
            return;
        }

        Schema::table('world_cup_bets', function (Blueprint $table) {
            if (!Schema::hasColumn('world_cup_bets', 'tournament_id')) {
                $table->foreignId('tournament_id')->nullable()->after('registro_id')
                    ->constrained('world_cup_tournaments')->onDelete('cascade');
            }
            
            if (!Schema::hasColumn('world_cup_bets', 'round_index')) {
                $table->unsignedSmallInteger('round_index')->nullable()->after('tournament_id');
            }
            
            if (!Schema::hasColumn('world_cup_bets', 'puntos_obtenidos')) {
                // Intentar agregar después de score_b, si no existe, agregar al final
                if (Schema::hasColumn('world_cup_bets', 'score_b')) {
                    $table->unsignedInteger('puntos_obtenidos')->default(0)->after('score_b');
                } else {
                    $table->unsignedInteger('puntos_obtenidos')->default(0);
                }
            }
            
            if (!Schema::hasColumn('world_cup_bets', 'acierto_ganador')) {
                $table->boolean('acierto_ganador')->default(false)->after('puntos_obtenidos');
            }
            
            if (!Schema::hasColumn('world_cup_bets', 'acierto_marcador')) {
                $table->boolean('acierto_marcador')->default(false)->after('acierto_ganador');
            }

            $table->index(['tournament_id', 'round_index']);
            $table->index(['registro_id', 'tournament_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('world_cup_bets')) {
            return;
        }

        Schema::table('world_cup_bets', function (Blueprint $table) {
            if (Schema::hasColumn('world_cup_bets', 'acierto_marcador')) {
                $table->dropColumn('acierto_marcador');
            }
            
            if (Schema::hasColumn('world_cup_bets', 'acierto_ganador')) {
                $table->dropColumn('acierto_ganador');
            }
            
            if (Schema::hasColumn('world_cup_bets', 'puntos_obtenidos')) {
                $table->dropColumn('puntos_obtenidos');
            }
            
            if (Schema::hasColumn('world_cup_bets', 'round_index')) {
                $table->dropColumn('round_index');
            }
            
            if (Schema::hasColumn('world_cup_bets', 'tournament_id')) {
                $table->dropForeign(['tournament_id']);
                $table->dropColumn('tournament_id');
            }
        });
    }
};
