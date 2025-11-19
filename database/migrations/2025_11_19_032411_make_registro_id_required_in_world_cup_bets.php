<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Asegura que registro_id sea requerido (NOT NULL) en world_cup_bets
     * para garantizar que todas las apuestas estén asociadas a un usuario
     */
    public function up(): void
    {
        if (!Schema::hasTable('world_cup_bets')) {
            return;
        }

        // Primero eliminar cualquier apuesta sin usuario (datos inconsistentes)
        DB::table('world_cup_bets')->whereNull('registro_id')->delete();

        // Hacer que registro_id sea NOT NULL
        Schema::table('world_cup_bets', function (Blueprint $table) {
            // Cambiar registro_id de nullable a NOT NULL
            $table->unsignedBigInteger('registro_id')->nullable(false)->change();
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
            // Revertir a nullable (aunque no es recomendado)
            $table->unsignedBigInteger('registro_id')->nullable()->change();
        });
    }
};
