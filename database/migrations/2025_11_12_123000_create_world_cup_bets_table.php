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
        Schema::create('world_cup_bets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('registro_id')->nullable();
            $table->string('match_key');
            $table->string('stage')->default('Dieciseisavos de Final');
            $table->string('team_a_code', 3);
            $table->string('team_b_code', 3);
            $table->string('selected_code', 3);
            $table->timestamps();

            $table->unique(['registro_id', 'match_key']);
            $table->index('match_key');
            $table->index('selected_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('world_cup_bets');
    }
};

