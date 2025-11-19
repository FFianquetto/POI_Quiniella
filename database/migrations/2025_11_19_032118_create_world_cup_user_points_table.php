<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabla para guardar los puntos acumulados por usuario en cada ronda del torneo
     */
    public function up(): void
    {
        Schema::create('world_cup_user_points', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('registro_id'); // Usuario
            $table->foreignId('tournament_id')->constrained('world_cup_tournaments')->onDelete('cascade');
            $table->unsignedSmallInteger('round_index'); // Índice de la ronda (0, 1, 2, 3, 4)
            $table->string('round_name')->nullable(); // Nombre de la ronda (ej: "Dieciseisavos de Final")
            $table->unsignedInteger('puntos_totales')->default(0); // Puntos totales obtenidos en esta ronda
            $table->unsignedInteger('apuestas_totales')->default(0); // Total de apuestas realizadas
            $table->unsignedInteger('apuestas_acertadas')->default(0); // Total de apuestas acertadas
            $table->timestamp('fecha_calculo')->nullable(); // Fecha en que se calcularon los puntos
            
            $table->timestamps();

            // Índice único para evitar duplicados: un usuario solo puede tener un registro de puntos por ronda
            $table->unique(['registro_id', 'tournament_id', 'round_index'], 'user_tournament_round_unique');
            
            // Índices para consultas rápidas
            $table->index(['tournament_id', 'round_index']);
            $table->index(['registro_id', 'tournament_id']);
            $table->index('puntos_totales'); // Para rankings
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('world_cup_user_points');
    }
};
