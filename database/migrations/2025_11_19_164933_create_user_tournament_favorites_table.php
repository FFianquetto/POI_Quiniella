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
        Schema::create('user_tournament_favorites', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('registro_id');
            $table->unsignedBigInteger('tournament_id');
            $table->string('favorite_team_code', 3);
            $table->timestamps();
            
            $table->unique(['registro_id', 'tournament_id']);
            $table->foreign('registro_id')->references('id')->on('registros')->onDelete('cascade');
            $table->foreign('tournament_id')->references('id')->on('world_cup_tournaments')->onDelete('cascade');
            $table->index('favorite_team_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_tournament_favorites');
    }
};
