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
        Schema::table('quinielas', function (Blueprint $table) {
            // Eliminar columnas que ya no necesitamos
            $table->dropForeign(['registro_id']);
            $table->dropColumn(['registro_id', 'precio_entrada', 'max_participantes']);
            
            // Agregar nuevas columnas
            $table->foreignId('partido_id')->constrained('partidos')->onDelete('cascade');
            $table->enum('resultado_final', ['local', 'visitante', 'empate'])->nullable();
            $table->integer('puntos_ganador')->default(10);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quinielas', function (Blueprint $table) {
            $table->dropForeign(['partido_id']);
            $table->dropColumn(['partido_id', 'resultado_final', 'puntos_ganador']);
            
            $table->foreignId('registro_id')->constrained('registros')->onDelete('cascade');
            $table->decimal('precio_entrada', 8, 2)->default(0);
            $table->integer('max_participantes')->nullable();
        });
    }
};
