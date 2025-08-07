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
        Schema::create('predicciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiniela_id')->constrained('quinielas')->onDelete('cascade');
            $table->foreignId('registro_id')->constrained('registros')->onDelete('cascade');
            $table->foreignId('partido_id')->constrained('partidos')->onDelete('cascade');
            $table->enum('prediccion', ['local', 'visitante', 'empate']);
            $table->integer('puntos_obtenidos')->default(0);
            $table->boolean('acierto')->default(false);
            $table->timestamps();
            
            // Un usuario solo puede tener una predicción por partido en cada quiniela
            $table->unique(['quiniela_id', 'registro_id', 'partido_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('predicciones');
    }
};
