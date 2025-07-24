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
        Schema::create('publicaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registro_id')->constrained('registros')->onDelete('cascade');
            $table->string('titulo');
            $table->text('contenido');

            // Relación opcional a una conversación iniciada con el autor
            $table->foreignId('conversacion_id')
                ->nullable()
                ->constrained('conversaciones')
                ->nullOnDelete(); // Si se borra la conversación, se pone null

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('publicaciones');
    }
};
