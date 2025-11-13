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
        if (!Schema::hasTable('world_cup_tournaments')) {
            return;
        }

        Schema::create('world_cup_match_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')
                ->constrained('world_cup_tournaments')
                ->cascadeOnDelete();
            $table->string('match_key');
            $table->unsignedSmallInteger('round_index')->default(0);
            $table->string('round_name')->nullable();
            $table->unsignedSmallInteger('order')->default(0);
            $table->string('team1_code', 5)->nullable();
            $table->string('team2_code', 5)->nullable();
            $table->string('team1_name')->nullable();
            $table->string('team2_name')->nullable();
            $table->unsignedTinyInteger('score1')->nullable();
            $table->unsignedTinyInteger('score2')->nullable();
            $table->string('winner_code', 5)->nullable();
            $table->string('winner_name')->nullable();
            $table->boolean('decided_by_penalties')->default(false);
            $table->string('penalty_score')->nullable();
            $table->boolean('played')->default(false);
            $table->timestamp('played_at')->nullable();
            $table->timestamps();

            $table->unique(['tournament_id', 'match_key']);
            $table->index(['round_index', 'match_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('world_cup_match_results');
    }
};

