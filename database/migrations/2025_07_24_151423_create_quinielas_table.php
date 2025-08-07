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
        Schema::create('quinielas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->foreignId('registro_id')->constrained('registros')->onDelete('cascade');
            $table->decimal('precio_entrada', 8, 2)->default(0);
            $table->integer('max_participantes')->nullable();
            $table->dateTime('fecha_limite');
            $table->enum('estado', ['activa', 'cerrada', 'finalizada'])->default('activa');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quinielas');
    }
};
