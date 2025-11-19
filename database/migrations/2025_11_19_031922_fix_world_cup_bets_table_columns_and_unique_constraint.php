<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Agrega las columnas score_a y score_b si no existen
     * Modifica la restricción única para incluir tournament_id y round_index
     * para evitar que se pueda contestar la misma fase dos veces
     */
    public function up(): void
    {
        if (!Schema::hasTable('world_cup_bets')) {
            return;
        }

        Schema::table('world_cup_bets', function (Blueprint $table) {
            // Agregar columnas score_a y score_b si no existen
            if (!Schema::hasColumn('world_cup_bets', 'score_a')) {
                $table->unsignedTinyInteger('score_a')->nullable()->after('selected_code');
            }
            if (!Schema::hasColumn('world_cup_bets', 'score_b')) {
                $table->unsignedTinyInteger('score_b')->nullable()->after('score_a');
            }
        });

        // Eliminar la restricción única antigua ['registro_id', 'match_key']
        // Primero verificar si existe el índice único
        $indexes = DB::select("SHOW INDEX FROM `world_cup_bets` WHERE Key_name = 'world_cup_bets_registro_id_match_key_unique'");
        if (!empty($indexes)) {
            Schema::table('world_cup_bets', function (Blueprint $table) {
                $table->dropUnique(['registro_id', 'match_key']);
            });
        }

        // Crear nueva restricción única que incluya tournament_id y round_index
        // Esto evita que se pueda contestar la misma fase dos veces
        // Nota: Los campos nullable en índices únicos pueden causar problemas,
        // pero MySQL/MariaDB permite múltiples NULLs, así que funcionará
        Schema::table('world_cup_bets', function (Blueprint $table) {
            $table->unique(['registro_id', 'tournament_id', 'round_index', 'match_key'], 
                          'world_cup_bets_user_tournament_round_match_unique');
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

        // Eliminar la nueva restricción única
        Schema::table('world_cup_bets', function (Blueprint $table) {
            $table->dropUnique('world_cup_bets_user_tournament_round_match_unique');
        });

        // Restaurar la restricción única antigua
        Schema::table('world_cup_bets', function (Blueprint $table) {
            $table->unique(['registro_id', 'match_key']);
        });

        // Eliminar columnas score_a y score_b
        Schema::table('world_cup_bets', function (Blueprint $table) {
            if (Schema::hasColumn('world_cup_bets', 'score_b')) {
                $table->dropColumn('score_b');
            }
            if (Schema::hasColumn('world_cup_bets', 'score_a')) {
                $table->dropColumn('score_a');
            }
        });
    }
};
