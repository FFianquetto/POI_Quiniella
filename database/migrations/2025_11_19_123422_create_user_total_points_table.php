<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabla para guardar los puntos acumulados globales de cada usuario
     * Suma todos los puntos ganados en todos los torneos desde que empezó
     */
    public function up(): void
    {
        Schema::create('user_total_points', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('registro_id')->unique(); // Usuario (único por usuario)
            $table->unsignedInteger('puntos_totales')->default(0); // Puntos acumulados globales
            $table->unsignedInteger('partidos_acertados')->default(0); // Total de partidos acertados
            $table->unsignedInteger('torneos_participados')->default(0); // Total de torneos en los que participó
            $table->timestamp('ultima_actualizacion')->nullable(); // Fecha de última actualización
            $table->timestamps();

            // Foreign key a registros
            $table->foreign('registro_id')->references('id')->on('registros')->onDelete('cascade');
            
            // Índices para consultas rápidas
            $table->index('puntos_totales'); // Para rankings globales
            $table->index('ultima_actualizacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_total_points');
    }
};
