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
        Schema::create('chat_grupo_tareas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_id')->constrained('chats')->cascadeOnDelete();
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->foreignId('creado_por')->constrained('registros')->cascadeOnDelete();
            $table->foreignId('asignado_a')->nullable()->constrained('registros')->nullOnDelete();
            $table->string('estado')->default('pendiente');
            $table->foreignId('completado_por')->nullable()->constrained('registros')->nullOnDelete();
            $table->timestamp('completado_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_grupo_tareas');
    }
};

