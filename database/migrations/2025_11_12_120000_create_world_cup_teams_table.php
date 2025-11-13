<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('world_cup_teams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 3)->unique();
            $table->unsignedTinyInteger('fifa_ranking');
            $table->string('continent', 20);
            $table->string('flag_url')->nullable();
            $table->timestamps();
        });

        $now = now();

        $teams = [
            ['name' => 'Argentina', 'code' => 'ARG', 'iso2' => 'ar', 'ranking' => 98, 'continent' => 'CONMEBOL'],
            ['name' => 'Brasil', 'code' => 'BRA', 'iso2' => 'br', 'ranking' => 97, 'continent' => 'CONMEBOL'],
            ['name' => 'Francia', 'code' => 'FRA', 'iso2' => 'fr', 'ranking' => 96, 'continent' => 'UEFA'],
            ['name' => 'Inglaterra', 'code' => 'ENG', 'iso2' => 'gb', 'ranking' => 94, 'continent' => 'UEFA'],
            ['name' => 'España', 'code' => 'ESP', 'iso2' => 'es', 'ranking' => 93, 'continent' => 'UEFA'],
            ['name' => 'Alemania', 'code' => 'GER', 'iso2' => 'de', 'ranking' => 92, 'continent' => 'UEFA'],
            ['name' => 'Portugal', 'code' => 'POR', 'iso2' => 'pt', 'ranking' => 91, 'continent' => 'UEFA'],
            ['name' => 'Bélgica', 'code' => 'BEL', 'iso2' => 'be', 'ranking' => 90, 'continent' => 'UEFA'],
            ['name' => 'Países Bajos', 'code' => 'NED', 'iso2' => 'nl', 'ranking' => 89, 'continent' => 'UEFA'],
            ['name' => 'Italia', 'code' => 'ITA', 'iso2' => 'it', 'ranking' => 88, 'continent' => 'UEFA'],
            ['name' => 'Croacia', 'code' => 'CRO', 'iso2' => 'hr', 'ranking' => 86, 'continent' => 'UEFA'],
            ['name' => 'Uruguay', 'code' => 'URU', 'iso2' => 'uy', 'ranking' => 85, 'continent' => 'CONMEBOL'],
            ['name' => 'México', 'code' => 'MEX', 'iso2' => 'mx', 'ranking' => 84, 'continent' => 'CONCACAF'],
            ['name' => 'Colombia', 'code' => 'COL', 'iso2' => 'co', 'ranking' => 83, 'continent' => 'CONMEBOL'],
            ['name' => 'Estados Unidos', 'code' => 'USA', 'iso2' => 'us', 'ranking' => 82, 'continent' => 'CONCACAF'],
            ['name' => 'Dinamarca', 'code' => 'DEN', 'iso2' => 'dk', 'ranking' => 81, 'continent' => 'UEFA'],
            ['name' => 'Marruecos', 'code' => 'MAR', 'iso2' => 'ma', 'ranking' => 80, 'continent' => 'CAF'],
            ['name' => 'Suiza', 'code' => 'SUI', 'iso2' => 'ch', 'ranking' => 79, 'continent' => 'UEFA'],
            ['name' => 'Japón', 'code' => 'JPN', 'iso2' => 'jp', 'ranking' => 78, 'continent' => 'AFC'],
            ['name' => 'Senegal', 'code' => 'SEN', 'iso2' => 'sn', 'ranking' => 77, 'continent' => 'CAF'],
            ['name' => 'Serbia', 'code' => 'SRB', 'iso2' => 'rs', 'ranking' => 76, 'continent' => 'UEFA'],
            ['name' => 'Polonia', 'code' => 'POL', 'iso2' => 'pl', 'ranking' => 75, 'continent' => 'UEFA'],
            ['name' => 'Australia', 'code' => 'AUS', 'iso2' => 'au', 'ranking' => 74, 'continent' => 'AFC'],
            ['name' => 'Corea del Sur', 'code' => 'KOR', 'iso2' => 'kr', 'ranking' => 73, 'continent' => 'AFC'],
            ['name' => 'Nigeria', 'code' => 'NGA', 'iso2' => 'ng', 'ranking' => 72, 'continent' => 'CAF'],
            ['name' => 'Ecuador', 'code' => 'ECU', 'iso2' => 'ec', 'ranking' => 71, 'continent' => 'CONMEBOL'],
            ['name' => 'Costa Rica', 'code' => 'CRC', 'iso2' => 'cr', 'ranking' => 68, 'continent' => 'CONCACAF'],
            ['name' => 'Camerún', 'code' => 'CMR', 'iso2' => 'cm', 'ranking' => 67, 'continent' => 'CAF'],
            ['name' => 'Arabia Saudita', 'code' => 'KSA', 'iso2' => 'sa', 'ranking' => 65, 'continent' => 'AFC'],
            ['name' => 'Panamá', 'code' => 'PAN', 'iso2' => 'pa', 'ranking' => 58, 'continent' => 'CONCACAF'],
            ['name' => 'Nueva Zelanda', 'code' => 'NZL', 'iso2' => 'nz', 'ranking' => 52, 'continent' => 'OFC'],
            ['name' => 'Catar', 'code' => 'QAT', 'iso2' => 'qa', 'ranking' => 50, 'continent' => 'AFC'],
        ];

        DB::table('world_cup_teams')->insert(array_map(function ($team) use ($now) {
            return [
                'name' => $team['name'],
                'code' => $team['code'],
                'fifa_ranking' => $team['ranking'],
                'continent' => $team['continent'],
                'flag_url' => "https://flagcdn.com/w40/{$team['iso2']}.png",
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }, $teams));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('world_cup_teams');
    }
};

