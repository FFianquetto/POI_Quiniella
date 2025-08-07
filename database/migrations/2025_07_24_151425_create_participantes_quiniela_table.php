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
        Schema::create('participantes_quiniela', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiniela_id')->constrained('quinielas')->onDelete('cascade');
            $table->foreignId('registro_id')->constrained('registros')->onDelete('cascade');
            $table->integer('puntos_totales')->default(0);
            $table->integer('posicion')->nullable();
            $table->boolean('pagado')->default(false);
            $table->timestamps();
            
            // Un usuario solo puede participar una vez en cada quiniela
            $table->unique(['quiniela_id', 'registro_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('participantes_quiniela');
    }
};
