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
        Schema::create('chat_usuario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_id')->constrained('chats')->onDelete('cascade');
            $table->foreignId('registro_id')->constrained('registros')->onDelete('cascade');
            $table->timestamp('ultima_lectura')->nullable();
            $table->timestamps();
            
            // Un usuario solo puede estar una vez en cada chat
            $table->unique(['chat_id', 'registro_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_usuario');
    }
};
