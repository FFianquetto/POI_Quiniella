<?php

namespace App\Http\Controllers;

use App\Models\WorldCupTeam;
use App\Models\WorldCupTournament;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class TorneoController extends Controller
{
    /**
     * Mostrar la vista del simulador de torneo
     */
    public function index()
    {
        $worldCupTeams = Schema::hasTable('world_cup_teams')
            ? WorldCupTeam::orderByDesc('fifa_ranking')->get()
            : $this->fallbackWorldCupTeams();

        $worldCupTeamsDataset = $worldCupTeams->map(function ($team, $index) {
            return [
                'id' => $team->id ?? $index + 1,
                'name' => $team->name,
                'code' => $team->code,
                'ranking' => $team->fifa_ranking,
                'continent' => $team->continent,
                'flag_url' => $team->flag_url,
            ];
        })->values()->toJson();

        return view('torneo.index', compact('worldCupTeams', 'worldCupTeamsDataset'));
    }

    /**
     * Datos por defecto en caso de que la tabla aún no exista.
     */
    protected function fallbackWorldCupTeams(): Collection
    {
        $teams = [
            ['name' => 'Argentina', 'code' => 'ARG', 'fifa_ranking' => 98, 'continent' => 'CONMEBOL', 'flag_url' => 'https://flagcdn.com/w40/ar.png'],
            ['name' => 'Brasil', 'code' => 'BRA', 'fifa_ranking' => 97, 'continent' => 'CONMEBOL', 'flag_url' => 'https://flagcdn.com/w40/br.png'],
            ['name' => 'Francia', 'code' => 'FRA', 'fifa_ranking' => 96, 'continent' => 'UEFA', 'flag_url' => 'https://flagcdn.com/w40/fr.png'],
            ['name' => 'Inglaterra', 'code' => 'ENG', 'fifa_ranking' => 94, 'continent' => 'UEFA', 'flag_url' => 'https://flagcdn.com/w40/gb.png'],
            ['name' => 'España', 'code' => 'ESP', 'fifa_ranking' => 93, 'continent' => 'UEFA', 'flag_url' => 'https://flagcdn.com/w40/es.png'],
            ['name' => 'Alemania', 'code' => 'GER', 'fifa_ranking' => 92, 'continent' => 'UEFA', 'flag_url' => 'https://flagcdn.com/w40/de.png'],
            ['name' => 'Portugal', 'code' => 'POR', 'fifa_ranking' => 91, 'continent' => 'UEFA', 'flag_url' => 'https://flagcdn.com/w40/pt.png'],
            ['name' => 'Bélgica', 'code' => 'BEL', 'fifa_ranking' => 90, 'continent' => 'UEFA', 'flag_url' => 'https://flagcdn.com/w40/be.png'],
            ['name' => 'Países Bajos', 'code' => 'NED', 'fifa_ranking' => 89, 'continent' => 'UEFA', 'flag_url' => 'https://flagcdn.com/w40/nl.png'],
            ['name' => 'Italia', 'code' => 'ITA', 'fifa_ranking' => 88, 'continent' => 'UEFA', 'flag_url' => 'https://flagcdn.com/w40/it.png'],
            ['name' => 'Croacia', 'code' => 'CRO', 'fifa_ranking' => 86, 'continent' => 'UEFA', 'flag_url' => 'https://flagcdn.com/w40/hr.png'],
            ['name' => 'Uruguay', 'code' => 'URU', 'fifa_ranking' => 85, 'continent' => 'CONMEBOL', 'flag_url' => 'https://flagcdn.com/w40/uy.png'],
            ['name' => 'México', 'code' => 'MEX', 'fifa_ranking' => 84, 'continent' => 'CONCACAF', 'flag_url' => 'https://flagcdn.com/w40/mx.png'],
            ['name' => 'Colombia', 'code' => 'COL', 'fifa_ranking' => 83, 'continent' => 'CONMEBOL', 'flag_url' => 'https://flagcdn.com/w40/co.png'],
            ['name' => 'Estados Unidos', 'code' => 'USA', 'fifa_ranking' => 82, 'continent' => 'CONCACAF', 'flag_url' => 'https://flagcdn.com/w40/us.png'],
            ['name' => 'Dinamarca', 'code' => 'DEN', 'fifa_ranking' => 81, 'continent' => 'UEFA', 'flag_url' => 'https://flagcdn.com/w40/dk.png'],
            ['name' => 'Marruecos', 'code' => 'MAR', 'fifa_ranking' => 80, 'continent' => 'CAF', 'flag_url' => 'https://flagcdn.com/w40/ma.png'],
            ['name' => 'Suiza', 'code' => 'SUI', 'fifa_ranking' => 79, 'continent' => 'UEFA', 'flag_url' => 'https://flagcdn.com/w40/ch.png'],
            ['name' => 'Japón', 'code' => 'JPN', 'fifa_ranking' => 78, 'continent' => 'AFC', 'flag_url' => 'https://flagcdn.com/w40/jp.png'],
            ['name' => 'Senegal', 'code' => 'SEN', 'fifa_ranking' => 77, 'continent' => 'CAF', 'flag_url' => 'https://flagcdn.com/w40/sn.png'],
            ['name' => 'Serbia', 'code' => 'SRB', 'fifa_ranking' => 76, 'continent' => 'UEFA', 'flag_url' => 'https://flagcdn.com/w40/rs.png'],
            ['name' => 'Polonia', 'code' => 'POL', 'fifa_ranking' => 75, 'continent' => 'UEFA', 'flag_url' => 'https://flagcdn.com/w40/pl.png'],
            ['name' => 'Australia', 'code' => 'AUS', 'fifa_ranking' => 74, 'continent' => 'AFC', 'flag_url' => 'https://flagcdn.com/w40/au.png'],
            ['name' => 'Corea del Sur', 'code' => 'KOR', 'fifa_ranking' => 73, 'continent' => 'AFC', 'flag_url' => 'https://flagcdn.com/w40/kr.png'],
            ['name' => 'Nigeria', 'code' => 'NGA', 'fifa_ranking' => 72, 'continent' => 'CAF', 'flag_url' => 'https://flagcdn.com/w40/ng.png'],
            ['name' => 'Ecuador', 'code' => 'ECU', 'fifa_ranking' => 71, 'continent' => 'CONMEBOL', 'flag_url' => 'https://flagcdn.com/w40/ec.png'],
            ['name' => 'Costa Rica', 'code' => 'CRC', 'fifa_ranking' => 68, 'continent' => 'CONCACAF', 'flag_url' => 'https://flagcdn.com/w40/cr.png'],
            ['name' => 'Camerún', 'code' => 'CMR', 'fifa_ranking' => 67, 'continent' => 'CAF', 'flag_url' => 'https://flagcdn.com/w40/cm.png'],
            ['name' => 'Arabia Saudita', 'code' => 'KSA', 'fifa_ranking' => 65, 'continent' => 'AFC', 'flag_url' => 'https://flagcdn.com/w40/sa.png'],
            ['name' => 'Panamá', 'code' => 'PAN', 'fifa_ranking' => 58, 'continent' => 'CONCACAF', 'flag_url' => 'https://flagcdn.com/w40/pa.png'],
            ['name' => 'Nueva Zelanda', 'code' => 'NZL', 'fifa_ranking' => 52, 'continent' => 'OFC', 'flag_url' => 'https://flagcdn.com/w40/nz.png'],
            ['name' => 'Catar', 'code' => 'QAT', 'fifa_ranking' => 50, 'continent' => 'AFC', 'flag_url' => 'https://flagcdn.com/w40/qa.png'],
        ];

        return collect($teams)->map(fn ($team) => (object) $team);
    }

    /**
     * Generar un nuevo torneo con equipos específicos
     */
    public function generate(Request $request)
    {
        $request->validate([
            'teams' => 'required|array|size:16',
            'teams.*' => 'required|string|max:30|min:1'
        ]);

        // Mezclar equipos aleatoriamente para el sorteo
        $teams = $request->teams;
        shuffle($teams);

        $tournamentData = [
            'teams' => array_map(function($team, $index) {
                return [
                    'id' => $index + 1,
                    'name' => $team
                ];
            }, $teams, array_keys($teams)),
            'rounds' => [],
            'currentRound' => 0,
            'currentMatch' => 0
        ];

        return response()->json([
            'success' => true,
            'tournament' => $tournamentData
        ]);
    }

    /**
     * Simular un partido específico
     */
    public function simulateMatch(Request $request)
    {
        $request->validate([
            'team1' => 'required|string',
            'team2' => 'required|string'
        ]);

        // Simular resultado del partido
        $score1 = rand(0, 5);
        $score2 = rand(0, 5);

        // Asegurar que no haya empate
        if ($score1 === $score2) {
            $score1 += rand(0, 1);
        }

        $winner = $score1 > $score2 ? $request->team1 : $request->team2;

        return response()->json([
            'success' => true,
            'result' => [
                'team1' => $request->team1,
                'team2' => $request->team2,
                'score1' => $score1,
                'score2' => $score2,
                'winner' => $winner
            ]
        ]);
    }

    /**
     * Obtener estadísticas del torneo
     */
    public function getStats()
    {
        $stats = [
            'total_teams' => 16,
            'total_rounds' => 4,
            'total_matches' => 15,
            'rounds' => [
                'Octavos de Final' => 8,
                'Cuartos de Final' => 4,
                'Semifinales' => 2,
                'Final' => 1
            ]
        ];

        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }

    public function store(Request $request)
    {
        if (!Schema::hasTable('world_cup_tournaments')) {
            return response()->json(['success' => false, 'message' => 'Tabla de torneos no disponible. Ejecuta las migraciones.'], 500);
        }

        $data = $request->validate([
            'favorite_team' => 'nullable|string|max:5',
            'teams' => 'required|array',
            'rounds' => 'required|array',
            'results' => 'nullable|array',
        ]);

        $tournament = WorldCupTournament::create([
            'uuid' => Str::uuid()->toString(),
            'favorite_team' => $data['favorite_team'] ?? null,
            'total_teams' => is_countable($data['teams']) ? count($data['teams']) : 32,
            'teams' => $data['teams'],
            'rounds' => $data['rounds'],
            'results' => $data['results'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'uuid' => $tournament->uuid,
            'id' => $tournament->id,
        ]);
    }

    public function update(Request $request, string $uuid)
    {
        if (!Schema::hasTable('world_cup_tournaments')) {
            return response()->json(['success' => false, 'message' => 'Tabla de torneos no disponible. Ejecuta las migraciones.'], 500);
        }

        $tournament = WorldCupTournament::where('uuid', $uuid)->first();

        if (!$tournament) {
            return response()->json(['success' => false, 'message' => 'Torneo no encontrado.'], 404);
        }

        $payload = $request->validate([
            'favorite_team' => 'nullable|string|max:5',
            'rounds' => 'nullable|array',
            'results' => 'nullable|array',
        ]);

        $updates = [];

        if (array_key_exists('favorite_team', $payload)) {
            $updates['favorite_team'] = $payload['favorite_team'];
        }

        if (!empty($payload['rounds'])) {
            $updates['rounds'] = $payload['rounds'];
        }

        if (array_key_exists('results', $payload)) {
            $existingResults = $tournament->results ?? [];
            $updates['results'] = array_merge($existingResults, $payload['results'] ?? []);
        }

        if (!empty($updates)) {
            $tournament->update($updates);
        }

        return response()->json(['success' => true]);
    }
}
