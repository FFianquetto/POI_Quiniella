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
        Schema::create('tournament_quinielas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained('world_cup_tournaments')->onDelete('cascade');
            $table->unsignedSmallInteger('round_index')->default(0);
            $table->string('round_name');
            $table->enum('estado', ['activa', 'cerrada', 'finalizada'])->default('activa');
            $table->timestamp('fecha_limite')->nullable();
            $table->timestamps();

            $table->unique(['tournament_id', 'round_index']);
            $table->index(['tournament_id', 'estado']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tournament_quinielas');
    }
};
