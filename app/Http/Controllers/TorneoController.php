<?php

namespace App\Http\Controllers;

use App\Models\WorldCupMatchResult;
use App\Models\WorldCupTeam;
use App\Models\WorldCupTournament;
use App\Models\TournamentQuiniela;
use App\Models\UserTournamentFavorite;
use App\Models\UserTotalPoint;
use App\Models\WorldCupUserPoint;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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

        $latestTournament = null;
        if (Schema::hasTable('world_cup_tournaments')) {
            $hasStatusColumn = Schema::hasColumn('world_cup_tournaments', 'status');
            
            if ($hasStatusColumn) {
                // Buscar torneo activo: que no esté archivado ni completado
                $latestTournament = WorldCupTournament::where(function($query) {
                    $query->where(function($q) {
                        $q->whereNull('status')
                          ->orWhere('status', 'in_progress')
                          ->orWhere('status', '!=', 'completed')
                          ->orWhere('status', '!=', 'archived');
                    });
                })
                ->latest()
                ->first();
            } else {
                // Si no existe la columna status, obtener el último torneo sin filtrar
                $latestTournament = WorldCupTournament::latest()->first();
            }
        }

        $serializedTournament = $this->serializeTournamentForFrontend($latestTournament);
        
        // Determinar si el torneo está completado para mostrar/ocultar botón "Volver a Selección"
        $isTournamentCompleted = false;
        if ($latestTournament) {
            $hasStatusColumn = Schema::hasColumn('world_cup_tournaments', 'status');
            if ($hasStatusColumn) {
                $tournamentStatus = $latestTournament->status ?? 'in_progress';
                $isTournamentCompleted = in_array($tournamentStatus, ['completed', 'archived']);
            } else {
                $isTournamentCompleted = !is_null($latestTournament->completed_at);
            }
        }

        return view('torneo.index', compact('worldCupTeams', 'worldCupTeamsDataset', 'serializedTournament', 'isTournamentCompleted'));
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

        // Log para depuración
        \Log::info('Store tournament request', [
            'has_rounds' => $request->has('rounds'),
            'rounds_type' => gettype($request->input('rounds')),
            'rounds_value' => $request->input('rounds'),
            'teams_count' => is_array($request->input('teams')) ? count($request->input('teams')) : 0,
        ]);

        $data = $request->validate([
            'favorite_team' => 'nullable|string|max:5',
            'teams' => 'required|array',
            'rounds' => 'nullable|array', // Cambiado a nullable para permitir guardar sin rounds inicialmente
            'results' => 'nullable|array',
        ]);

        $hasStatusColumn = Schema::hasColumn('world_cup_tournaments', 'status');
        
        // Buscar el último torneo y verificar si está activo
        // Un torneo está activo si NO está completado ni archivado
        $latestTournament = WorldCupTournament::latest()->first();
        
        if ($latestTournament) {
            $tournamentStatus = $latestTournament->status ?? null;
            
            // Un torneo está activo si NO está completado ni archivado
            $isActive = false;
            
            if ($hasStatusColumn) {
                // Si tiene columna status, verificar que NO esté completado ni archivado
                $isActive = !in_array($tournamentStatus, ['completed', 'archived']);
            } else {
                // Sin columna status, verificar por completed_at
                $isActive = is_null($latestTournament->completed_at);
            }
            
            if ($isActive) {
                \Log::info('Torneo activo encontrado, bloqueando creación', [
                    'uuid' => $latestTournament->uuid,
                    'status' => $tournamentStatus,
                    'id' => $latestTournament->id,
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe un torneo en progreso. Debes finalizarlo antes de generar uno nuevo.',
                    'uuid' => $latestTournament->uuid,
                    'status' => $tournamentStatus ?? 'in_progress',
                ], 409);
            }
        }

        try {
            $tournament = WorldCupTournament::create([
                'uuid' => Str::uuid()->toString(),
                'favorite_team' => $data['favorite_team'] ?? null,
                'total_teams' => is_countable($data['teams']) ? count($data['teams']) : 32,
                'teams' => $data['teams'],
                'rounds' => $data['rounds'] ?? [], // Permitir rounds vacíos
                'results' => $data['results'] ?? null,
                'status' => 'in_progress',
                'completed_at' => null,
            ]);

            $tournament->refresh();
            
            \Log::info('Tournament created successfully', [
                'uuid' => $tournament->uuid,
                'id' => $tournament->id,
                'rounds_count' => is_array($tournament->rounds) ? count($tournament->rounds) : 0,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error creating tournament', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el torneo: ' . $e->getMessage(),
            ], 500);
        }
        
        // Sincronizar resultados de partidos solo si hay rounds con matches
        if (!empty($data['rounds'])) {
            $this->syncMatchResults($tournament);
        }

        // Crear quinielas automáticamente para cada ronda del torneo
        $this->createTournamentQuinielas($tournament);

        return response()->json([
            'success' => true,
            'uuid' => $tournament->uuid,
            'id' => $tournament->id,
            'status' => $tournament->status ?? 'in_progress',
            'tournament' => $this->serializeTournamentForFrontend($tournament),
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
            'status' => 'nullable|string|in:in_progress,completed',
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
            $newResults = $payload['results'] ?? [];
            // Asegurar que rewards se guarde correctamente
            if (isset($newResults['rewards'])) {
                $existingResults['rewards'] = $newResults['rewards'];
            }
            if (isset($newResults['third_place'])) {
                $existingResults['third_place'] = $newResults['third_place'];
            }
            $updates['results'] = $existingResults;
        }

        if (array_key_exists('status', $payload)) {
            $updates['status'] = $payload['status'];
            $updates['completed_at'] = $payload['status'] === 'completed' ? Carbon::now() : null;
        }

        if (!empty($updates)) {
            $tournament->update($updates);
        }

        $tournament->refresh();

        // Detectar si se completó una ronda y actualizar quinielas
        if (!empty($payload['rounds'])) {
            // Asegurar que todas las rondas tengan quinielas creadas
            $this->createTournamentQuinielas($tournament);
            // Verificar y actualizar estado de quinielas cuando se completan rondas
            $this->checkAndUpdateCompletedRounds($tournament, $payload['rounds']);
        }

        // Verificar si el torneo se está marcando como completado
        $isBeingCompleted = ($payload['status'] ?? null) === 'completed' 
            || (($tournament->status ?? 'in_progress') !== 'completed' && $this->tournamentHasFinished($tournament));
        
        if ($isBeingCompleted) {
            $tournament->update([
                'status' => 'completed',
                'completed_at' => Carbon::now(),
            ]);
            $tournament->refresh();
            
            // Calcular puntos adicionales por favorita ganadora
            // Esto se ejecuta cuando el torneo se completa
            $this->calcularPuntosFavoritaGanadora($tournament);
            
            // Finalizar todas las quinielas del torneo
            $this->finalizeAllTournamentQuinielas($tournament);
        }

        // Sincronizar resultados de partidos después de cada actualización
        $this->syncMatchResults($tournament);

        return response()->json([
            'success' => true,
            'status' => $tournament->status ?? 'in_progress',
            'completed_at' => optional($tournament->completed_at)->toIso8601String(),
            'tournament' => $this->serializeTournamentForFrontend($tournament),
        ]);
    }

    /**
     * Finalizar un torneo completado para permitir crear uno nuevo
     */
    public function finalize(Request $request)
    {
        $request->validate([
            'tournament_uuid' => 'required|string',
        ]);

        if (!Schema::hasTable('world_cup_tournaments')) {
            return redirect()->route('torneo.index')
                ->with('error', 'Tabla de torneos no disponible.');
        }

        $tournament = WorldCupTournament::where('uuid', $request->tournament_uuid)->first();

        if (!$tournament) {
            return redirect()->route('torneo.index')
                ->with('error', 'Torneo no encontrado.');
        }

        // Verificar si existe la columna status
        $hasStatusColumn = Schema::hasColumn('world_cup_tournaments', 'status');
        
        if ($hasStatusColumn) {
            // Verificar que el torneo esté completado
            if (($tournament->status ?? 'in_progress') !== 'completed') {
                return redirect()->route('torneo.index')
                    ->with('error', 'El torneo aún no ha finalizado. Completa todos los partidos primero.');
            }

            // Marcar el torneo como archivado
            $tournament->update([
                'status' => 'archived',
            ]);
        } else {
            // Si no existe la columna status, eliminar el torneo directamente
            $tournament->delete();
        }

        return redirect()->route('torneo.index')
            ->with('success', 'Torneo finalizado exitosamente. Ya puedes generar un nuevo torneo.');
    }

    protected function serializeTournamentForFrontend(?WorldCupTournament $tournament): ?array
    {
        if (!$tournament) {
            return null;
        }

        // Verificar si existe la columna status antes de usarla
        $hasStatusColumn = Schema::hasColumn('world_cup_tournaments', 'status');
        
        // No serializar torneos archivados (solo si existe la columna)
        if ($hasStatusColumn && $tournament->status === 'archived') {
            return null;
        }

        return [
            'uuid' => $tournament->uuid,
            'status' => ($hasStatusColumn && isset($tournament->status)) ? $tournament->status : 'in_progress',
            'favorite_team' => $tournament->favorite_team,
            'teams' => $tournament->teams ?? [],
            'rounds' => $tournament->rounds ?? [],
            'results' => $tournament->results ?? null,
            'completed_at' => ($hasStatusColumn && $tournament->completed_at) ? $tournament->completed_at->toIso8601String() : null,
        ];
    }

    protected function tournamentHasFinished(WorldCupTournament $tournament): bool
    {
        $rounds = collect($tournament->rounds ?? []);

        if ($rounds->isEmpty()) {
            return false;
        }

        $finalRound = $rounds->last();
        $finalMatches = collect($finalRound['matches'] ?? []);
        $finalized = $finalMatches->isNotEmpty() && $finalMatches->every(fn ($match) => !empty($match['played']));

        if (!$finalized) {
            return false;
        }

        $thirdPlace = data_get($tournament->results, 'third_place');

        if ($thirdPlace) {
            return (bool) data_get($thirdPlace, 'played', false);
        }

        return true;
    }

    protected function syncMatchResults(WorldCupTournament $tournament): void
    {
        if (!Schema::hasTable('world_cup_match_results')) {
            return;
        }

        $rounds = collect($tournament->rounds ?? []);
        $records = [];

        foreach ($rounds as $roundIndex => $round) {
            $roundName = $round['name'] ?? $round['round_name'] ?? $round['stage'] ?? sprintf('Ronda %d', $roundIndex + 1);

            foreach ($round['matches'] ?? [] as $matchIndex => $match) {
                // Sincronizar todos los matches, incluso si no tienen resultados aún
                $records[] = $this->buildMatchResultPayload($tournament, (int) $roundIndex, $roundName, (int) $matchIndex, $match);
            }
        }

        if ($thirdPlace = data_get($tournament->results, 'third_place')) {
            $records[] = $this->buildMatchResultPayload($tournament, 4, 'Tercer Lugar', 0, $thirdPlace, true);
        }

        if (empty($records)) {
            return;
        }

        $matchKeys = collect($records)->pluck('match_key')->all();

        WorldCupMatchResult::upsert(
            $records,
            ['tournament_id', 'match_key'],
            [
                'round_index',
                'round_name',
                'order',
                'team1_code',
                'team1_name',
                'team2_code',
                'team2_name',
                'score1',
                'score2',
                'winner_code',
                'winner_name',
                'decided_by_penalties',
                'penalty_score',
                'played',
                'played_at',
                'updated_at',
            ]
        );

        WorldCupMatchResult::where('tournament_id', $tournament->id)
            ->whereNotIn('match_key', $matchKeys)
            ->delete();
    }

    protected function buildMatchResultPayload(
        WorldCupTournament $tournament,
        int $roundIndex,
        ?string $roundName,
        int $order,
        array $match,
        bool $isThirdPlace = false
    ): array {
        $matchKey = $match['match_key'] ?? $match['id'] ?? ($isThirdPlace ? 'third-place' : sprintf('round-%d-match-%d', $roundIndex, $order));
        $team1 = $match['team1'] ?? $match['team_a'] ?? null;
        $team2 = $match['team2'] ?? $match['team_b'] ?? null;
        $played = (bool) ($match['played'] ?? false);
        $decidedByPenalties = (bool) ($match['decidedByPenalties'] ?? $match['decided_by_penalties'] ?? false);

        return [
            'tournament_id' => $tournament->id,
            'match_key' => $matchKey,
            'round_index' => $isThirdPlace ? 4 : $roundIndex,
            'round_name' => $roundName,
            'order' => $order,
            'team1_code' => $team1['code'] ?? null,
            'team1_name' => $team1['name'] ?? null,
            'team2_code' => $team2['code'] ?? null,
            'team2_name' => $team2['name'] ?? null,
            'score1' => $match['score1'] ?? null,
            'score2' => $match['score2'] ?? null,
            'winner_code' => data_get($match, 'winner.code'),
            'winner_name' => data_get($match, 'winner.name'),
            'decided_by_penalties' => $decidedByPenalties,
            'penalty_score' => $match['penaltyScore'] ?? $match['penalty_score'] ?? null,
            'played' => $played,
            'played_at' => $played ? Carbon::now() : null,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }

    /**
     * Crear quinielas automáticamente para cada ronda del torneo
     */
    protected function createTournamentQuinielas(WorldCupTournament $tournament)
    {
        if (!Schema::hasTable('tournament_quinielas')) {
            return;
        }

        $rounds = $tournament->rounds ?? [];
        
        // Crear quiniela para cada ronda que tenga partidos
        foreach ($rounds as $roundIndex => $round) {
            $roundName = $round['name'] ?? $this->getRoundName($roundIndex);
            $matches = $round['matches'] ?? [];
            
            // Solo crear quiniela si la ronda tiene partidos
            if (!empty($matches)) {
                $quiniela = TournamentQuiniela::firstOrCreate(
                    [
                        'tournament_id' => $tournament->id,
                        'round_index' => $roundIndex,
                    ],
                    [
                        'round_name' => $roundName,
                        'estado' => $roundIndex === 0 ? 'activa' : 'cerrada', // Solo la primera ronda está activa inicialmente
                        'fecha_limite' => null,
                    ]
                );
                
                // Si la quiniela ya existía, actualizar el nombre por si cambió
                if (!$quiniela->wasRecentlyCreated && $quiniela->round_name !== $roundName) {
                    $quiniela->round_name = $roundName;
                    $quiniela->save();
                }
            }
        }
    }

    /**
     * Actualizar estado de quinielas cuando se completa una ronda
     */
    protected function updateQuinielasOnRoundComplete(WorldCupTournament $tournament, int $completedRoundIndex)
    {
        if (!Schema::hasTable('tournament_quinielas')) {
            return;
        }

        \Log::info('Actualizando quinielas para ronda completada', [
            'tournament_id' => $tournament->id,
            'completed_round_index' => $completedRoundIndex,
        ]);

        // Finalizar la quiniela de la ronda completada
        $completedQuiniela = TournamentQuiniela::where('tournament_id', $tournament->id)
            ->where('round_index', $completedRoundIndex)
            ->first();

        if ($completedQuiniela && $completedQuiniela->estado !== 'finalizada') {
            $completedQuiniela->finalizar();
            \Log::info('Quiniela finalizada', [
                'quiniela_id' => $completedQuiniela->id,
                'round_index' => $completedRoundIndex,
            ]);
            
            // Calcular puntos de todas las apuestas de esta ronda
            $this->calcularPuntosRonda($tournament->id, $completedRoundIndex);
        }

        // Activar la siguiente ronda si existe
        $nextRoundIndex = $completedRoundIndex + 1;
        $nextQuiniela = TournamentQuiniela::where('tournament_id', $tournament->id)
            ->where('round_index', $nextRoundIndex)
            ->first();

        if ($nextQuiniela) {
            if ($nextQuiniela->estado === 'cerrada') {
                $nextQuiniela->estado = 'activa';
                $nextQuiniela->save();
                \Log::info('Quiniela activada', [
                    'quiniela_id' => $nextQuiniela->id,
                    'round_index' => $nextRoundIndex,
                ]);
            }
        } else {
            // Si no existe la quiniela para la siguiente ronda, crearla
            $rounds = $tournament->rounds ?? [];
            if (isset($rounds[$nextRoundIndex])) {
                $round = $rounds[$nextRoundIndex];
                $roundName = $round['name'] ?? $this->getRoundName($nextRoundIndex);
                $matches = $round['matches'] ?? [];
                
                if (!empty($matches)) {
                    $newQuiniela = TournamentQuiniela::create([
                        'tournament_id' => $tournament->id,
                        'round_index' => $nextRoundIndex,
                        'round_name' => $roundName,
                        'estado' => 'activa',
                        'fecha_limite' => null,
                    ]);
                    \Log::info('Quiniela creada y activada', [
                        'quiniela_id' => $newQuiniela->id,
                        'round_index' => $nextRoundIndex,
                    ]);
                }
            }
        }
    }

    /**
     * Calcular puntos de todas las apuestas de una ronda completada
     * Compara las respuestas del usuario con los resultados reales y guarda los puntos
     */
    protected function calcularPuntosRonda(int $tournamentId, int $roundIndex)
    {
        if (!Schema::hasTable('world_cup_bets')) {
            return;
        }

        \Log::info('Calculando puntos para ronda completada', [
            'tournament_id' => $tournamentId,
            'round_index' => $roundIndex,
        ]);

        // Obtener el torneo para obtener el nombre de la ronda
        $tournament = WorldCupTournament::find($tournamentId);
        if (!$tournament) {
            return;
        }

        $roundNames = [
            'Dieciseisavos de Final',
            'Octavos de Final',
            'Cuartos de Final',
            'Semifinales',
            'Final'
        ];
        $roundName = $roundNames[$roundIndex] ?? "Ronda " . ($roundIndex + 1);
        
        // Obtener el nombre de la ronda desde el torneo si está disponible
        $rounds = $tournament->rounds ?? [];
        if (isset($rounds[$roundIndex]['name'])) {
            $roundName = $rounds[$roundIndex]['name'];
        }

        // Obtener todas las apuestas de esta ronda agrupadas por usuario
        // IMPORTANTE: 
        // - Solo se procesan usuarios que REALMENTE tienen apuestas en esta ronda
        // - Si un usuario no participó en esta fase, NO se le sumarán puntos (no aparecerá en $bets)
        // - Los usuarios pueden participar en fases posteriores aunque no hayan participado en anteriores
        // - Cada usuario solo suma puntos de las fases en las que realmente participó
        $bets = \App\Models\WorldCupBet::where('tournament_id', $tournamentId)
            ->where('round_index', $roundIndex)
            ->whereNotNull('registro_id') // Asegurar que solo se procesen apuestas con usuario
            ->get()
            ->groupBy('registro_id')
            ->filter(function ($usuarioBets, $usuarioId) {
                // Validar que el usuario existe antes de procesar sus apuestas
                return Schema::hasTable('registros') && \App\Models\Registro::find($usuarioId) !== null;
            });

        $totalCalculated = 0;
        $usersProcessed = 0;

        // Procesar solo usuarios que tienen apuestas en esta ronda
        // Si un usuario no participó en esta fase, no estará en este loop y no se le sumarán puntos
        foreach ($bets as $usuarioId => $usuarioBets) {
            $puntosTotales = 0;
            $apuestasTotales = $usuarioBets->count();
            $apuestasAcertadas = 0;

            // Calcular puntos para cada apuesta del usuario
            // Solo se calculan puntos si el usuario REALMENTE hizo apuestas en esta ronda
            foreach ($usuarioBets as $bet) {
                $puntos = $bet->calcularPuntos();
                $puntosTotales += $puntos;
                
                if ($puntos > 0) {
                    $apuestasAcertadas++;
                }
            }

            // Guardar o actualizar los puntos del usuario en la tabla world_cup_user_points
            // Solo guardar si el usuario es válido
            if ($usuarioId && Schema::hasTable('world_cup_user_points')) {
                // Validar nuevamente que el usuario existe antes de guardar sus puntos
                if (Schema::hasTable('registros') && \App\Models\Registro::find($usuarioId)) {
                    // IMPORTANTE: Verificar ANTES de guardar si los puntos de esta ronda ya se calcularon
                    // para evitar sumar puntos duplicados en la tabla de puntos acumulados
                    $existingPoint = \App\Models\WorldCupUserPoint::where('registro_id', $usuarioId)
                        ->where('tournament_id', $tournamentId)
                        ->where('round_index', $roundIndex)
                        ->whereNotNull('fecha_calculo')
                        ->first();
                    
                    $esPrimeraVez = !$existingPoint;
                    
                    // Guardar o actualizar los puntos de la ronda
                    \App\Models\WorldCupUserPoint::updateOrCreate(
                        [
                            'registro_id' => $usuarioId,
                            'tournament_id' => $tournamentId,
                            'round_index' => $roundIndex,
                        ],
                        [
                            'round_name' => $roundName,
                            'puntos_totales' => $puntosTotales,
                            'apuestas_totales' => $apuestasTotales,
                            'apuestas_acertadas' => $apuestasAcertadas,
                            'fecha_calculo' => \Carbon\Carbon::now(),
                        ]
                    );
                    
                    // Actualizar puntos acumulados globales del usuario
                    // SOLO si es la primera vez que se calculan los puntos para esta ronda
                    // IMPORTANTE: Solo se suman puntos si el usuario REALMENTE participó en esta fase
                    // Si un usuario no hizo apuestas en esta fase, no estará en este loop y no se le sumarán puntos
                    // Los usuarios pueden participar en fases posteriores aunque no hayan participado en anteriores
                    if ($esPrimeraVez && Schema::hasTable('user_total_points') && $puntosTotales > 0) {
                        \App\Models\UserTotalPoint::actualizarPuntosAcumulados(
                            $usuarioId,
                            $puntosTotales,
                            $apuestasAcertadas
                        );
                    }
                    
                    $usersProcessed++;
                }
            }

            if ($puntosTotales > 0) {
                $totalCalculated++;
            }
        }

        \Log::info('Puntos calculados para ronda', [
            'tournament_id' => $tournamentId,
            'round_index' => $roundIndex,
            'round_name' => $roundName,
            'total_users' => $bets->count(),
            'users_processed' => $usersProcessed,
            'users_with_points' => $totalCalculated,
        ]);
    }

    /**
     * Obtener el nombre de una ronda por su índice
     */
    protected function getRoundName(int $roundIndex): string
    {
        $roundNames = [
            'Dieciseisavos de Final',
            'Octavos de Final',
            'Cuartos de Final',
            'Semifinales',
            'Final'
        ];
        
        return $roundNames[$roundIndex] ?? "Ronda " . ($roundIndex + 1);
    }

    /**
     * Verificar y actualizar quinielas cuando se completan rondas
     */
    protected function checkAndUpdateCompletedRounds(WorldCupTournament $tournament, array $rounds)
    {
        foreach ($rounds as $roundIndex => $round) {
            $matches = $round['matches'] ?? [];
            
            // Verificar si todos los partidos de la ronda están jugados
            $allPlayed = !empty($matches) && collect($matches)->every(function ($match) {
                return !empty($match['played']);
            });
            
            if ($allPlayed) {
                $this->updateQuinielasOnRoundComplete($tournament, $roundIndex);
            }
        }
    }

    /**
     * Finalizar todas las quinielas del torneo cuando se completa
     */
    protected function finalizeAllTournamentQuinielas(WorldCupTournament $tournament)
    {
        if (!Schema::hasTable('tournament_quinielas')) {
            return;
        }

        $quinielas = TournamentQuiniela::where('tournament_id', $tournament->id)
            ->where('estado', '!=', 'finalizada')
            ->get();

        foreach ($quinielas as $quiniela) {
            $quiniela->finalizar();
        }
    }

    /**
     * Calcular puntos adicionales (10 puntos) para usuarios cuya selección favorita ganó el torneo
     * Método estático para poder llamarlo desde otros controladores
     */
    public static function calcularPuntosFavoritaGanadoraStatic(WorldCupTournament $tournament)
    {
        $instance = new static();
        return $instance->calcularPuntosFavoritaGanadora($tournament);
    }

    /**
     * Calcular puntos adicionales (10 puntos) para usuarios cuya selección favorita ganó el torneo
     */
    protected function calcularPuntosFavoritaGanadora(WorldCupTournament $tournament)
    {
        if (!Schema::hasTable('user_tournament_favorites')) {
            \Log::info('Tabla user_tournament_favorites no existe');
            return;
        }

        // Refrescar el torneo para obtener los datos más recientes
        $tournament->refresh();
        
        // Obtener el ganador del torneo desde results
        $results = $tournament->results ?? [];
        $champion = $results['rewards']['champion'] ?? $results['champion'] ?? null;
        
        if (!$champion) {
            \Log::warning('No se encontró el campeón del torneo', [
                'tournament_id' => $tournament->id,
                'results' => $results,
                'results_keys' => is_array($results) ? array_keys($results) : 'not_array',
            ]);
            return;
        }

        // El champion puede venir como objeto o array
        $championCode = null;
        if (is_array($champion)) {
            $championCode = $champion['code'] ?? null;
        } elseif (is_object($champion)) {
            $championCode = $champion->code ?? null;
        }
        
        if (!$championCode) {
            \Log::warning('No se pudo obtener el código del campeón', [
                'tournament_id' => $tournament->id,
                'champion' => $champion,
                'champion_type' => gettype($champion),
            ]);
            return;
        }

        // Obtener todos los usuarios que seleccionaron esta favorita
        $favorites = UserTournamentFavorite::where('tournament_id', $tournament->id)
            ->where('favorite_team_code', $championCode)
            ->get();

        \Log::info('Calculando puntos adicionales por favorita ganadora', [
            'tournament_id' => $tournament->id,
            'champion_code' => $championCode,
            'users_count' => $favorites->count(),
        ]);

        foreach ($favorites as $favorite) {
            $usuarioId = $favorite->registro_id;
            
            // Validar que el usuario existe
            if (!Schema::hasTable('registros') || !\App\Models\Registro::find($usuarioId)) {
                continue;
            }

            // Verificar si ya se otorgaron los puntos adicionales (evitar duplicados)
            // Usamos 999 como identificador especial para puntos de favorita ganadora (no podemos usar -1 porque round_index es UNSIGNED)
            $existingBonus = WorldCupUserPoint::where('registro_id', $usuarioId)
                ->where('tournament_id', $tournament->id)
                ->where('round_index', 999) // Usamos 999 para identificar puntos de favorita ganadora
                ->first();

            if ($existingBonus) {
                continue; // Ya se otorgaron los puntos
            }

            // Guardar los 10 puntos adicionales en world_cup_user_points
            if (Schema::hasTable('world_cup_user_points')) {
                WorldCupUserPoint::create([
                    'registro_id' => $usuarioId,
                    'tournament_id' => $tournament->id,
                    'round_index' => 999, // Identificador especial para puntos de favorita ganadora (999 porque round_index es UNSIGNED)
                    'round_name' => 'Bonus: Favorita Ganadora',
                    'puntos_totales' => 10,
                    'apuestas_totales' => 0,
                    'apuestas_acertadas' => 0,
                    'fecha_calculo' => Carbon::now(),
                ]);
            }

            // Actualizar puntos acumulados globales
            if (Schema::hasTable('user_total_points')) {
                UserTotalPoint::actualizarPuntosAcumulados(
                    $usuarioId,
                    10, // 10 puntos adicionales
                    0   // No cuenta como partido acertado
                );
            }

            \Log::info('Puntos adicionales otorgados por favorita ganadora', [
                'usuario_id' => $usuarioId,
                'tournament_id' => $tournament->id,
                'favorite_team_code' => $championCode,
                'puntos' => 10,
            ]);
        }
    }
}
