<?php

namespace App\Http\Controllers;

use App\Models\Quiniela;
use App\Models\Partido;
use App\Models\ParticipanteQuiniela;
use App\Models\WorldCupBet;
use App\Models\WorldCupTournament;
use App\Models\WorldCupMatchResult;
use App\Models\WorldCupTeam;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class QuinielaController extends Controller
{
    public function index(): View
    {
        $usuarioId = session('registro_id');
        $latestTournament = null;
        $tournamentStatus = null;
        $isTournamentClosed = false;

        if (Schema::hasTable('world_cup_tournaments')) {
            $hasStatusColumn = Schema::hasColumn('world_cup_tournaments', 'status');
            
            if ($hasStatusColumn) {
                $latestTournament = WorldCupTournament::where(function($query) {
                    $query->where('status', '!=', 'archived')
                          ->orWhereNull('status');
                })
                ->latest()
                ->first();
            } else {
                $latestTournament = WorldCupTournament::latest()->first();
            }

            if ($latestTournament) {
                $tournamentStatus = $latestTournament->status ?? 'in_progress';
                $isTournamentClosed = in_array($tournamentStatus, ['completed', 'archived']);
            }
        }

        // Obtener partidos del torneo si existe y no está cerrado
        // Determinar la ronda activa actual
        $activeRoundIndex = $this->getActiveRoundIndex($latestTournament);
        $activeRoundName = null;
        $activeRoundNumber = null;
        
        // Obtener información de la ronda activa
        if ($latestTournament && Schema::hasTable('tournament_quinielas')) {
            $activeQuiniela = \App\Models\TournamentQuiniela::where('tournament_id', $latestTournament->id)
                ->where('round_index', $activeRoundIndex)
                ->first();
            
            if ($activeQuiniela) {
                $activeRoundName = $activeQuiniela->round_name;
                $activeRoundNumber = $activeRoundIndex + 1; // Fase 1, 2, 3, etc.
            } else {
                // Si no hay quiniela, obtener el nombre desde los rounds del torneo
                $rounds = $latestTournament->rounds ?? [];
                $roundNames = [
                    'Dieciseisavos de Final',
                    'Octavos de Final',
                    'Cuartos de Final',
                    'Semifinales',
                    'Final'
                ];
                $activeRoundName = $rounds[$activeRoundIndex]['name'] ?? $roundNames[$activeRoundIndex] ?? "Ronda " . ($activeRoundIndex + 1);
                $activeRoundNumber = $activeRoundIndex + 1;
            }
        }
        
        if ($latestTournament && !$isTournamentClosed) {
            $worldCupMatches = $this->extractMatchesFromTournament($latestTournament, $activeRoundIndex);
        } else {
            $worldCupMatches = collect();
        }
        
        // Si el torneo está en progreso pero no tiene partidos aún, mostrar mensaje
        $tournamentHasNoMatches = $latestTournament && !$isTournamentClosed && $worldCupMatches->isEmpty();

        $usuarioBets = collect();
        $hasAllBetsForActiveRound = false;
        if ($usuarioId && Schema::hasTable('world_cup_bets')) {
            // Solo obtener las apuestas de la ronda activa si existe
            if ($latestTournament && !$isTournamentClosed && $activeRoundIndex !== null && $worldCupMatches->isNotEmpty()) {
                // Obtener solo las apuestas de la ronda activa actual
                $usuarioBets = \App\Models\WorldCupBet::where('registro_id', $usuarioId)
                    ->where('tournament_id', $latestTournament->id)
                    ->where('round_index', $activeRoundIndex)
                    ->get()
                    ->keyBy('match_key');
                
                $matchesInActiveRound = $worldCupMatches->count();
                $betsInActiveRound = $usuarioBets->count();
                
                $hasAllBetsForActiveRound = ($betsInActiveRound >= $matchesInActiveRound && $matchesInActiveRound > 0);
            } else {
                // Si no hay ronda activa, obtener todas las apuestas (fallback)
                $usuarioBets = \App\Models\WorldCupBet::where('registro_id', $usuarioId)->get()->keyBy('match_key');
            }
        }

        $quinielas = Quiniela::with(['partido.equipoLocal', 'partido.equipoVisitante', 'participantes'])
            ->where('estado', 'activa')
            ->orderBy('fecha_limite')
            ->paginate(10);
        
        // Obtener puntos por fase si hay un torneo activo
        $puntosPorFase = [];
        $puntosTotales = 0;
        if ($latestTournament && $usuarioId && Schema::hasTable('world_cup_bets')) {
            // Obtener todas las rondas del torneo
            $rounds = $latestTournament->rounds ?? [];
            $roundNames = [
                'Dieciseisavos de Final',
                'Octavos de Final',
                'Cuartos de Final',
                'Semifinales',
                'Final'
            ];
            
            foreach ($rounds as $roundIndex => $round) {
                $puntosRonda = WorldCupBet::puntosRonda(
                    $usuarioId,
                    $latestTournament->id,
                    $roundIndex
                );
                
                if ($puntosRonda > 0 || $this->rondaTieneResultados($latestTournament->id, $roundIndex)) {
                    $roundName = $round['name'] ?? $roundNames[$roundIndex] ?? "Ronda " . ($roundIndex + 1);
                    $puntosPorFase[] = [
                        'fase' => $roundIndex + 1,
                        'nombre' => $roundName,
                        'puntos' => $puntosRonda,
                        'round_index' => $roundIndex,
                    ];
                    $puntosTotales += $puntosRonda;
                }
            }
        }
        
        return view('quiniela.index', compact(
            'quinielas', 
            'usuarioId', 
            'worldCupMatches', 
            'usuarioBets', 
            'isTournamentClosed', 
            'tournamentStatus', 
            'tournamentHasNoMatches',
            'activeRoundName',
            'activeRoundNumber',
            'puntosPorFase',
            'puntosTotales',
            'latestTournament',
            'hasAllBetsForActiveRound'
        ));
    }

    public function show(Quiniela $quiniela): View
    {
        $quiniela->load(['partido.equipoLocal', 'partido.equipoVisitante', 'participantes.usuario', 'predicciones.usuario']);
        
        $usuarioId = session('registro_id');
        $participante = $quiniela->participantes()->where('registro_id', $usuarioId)->first();
        $miPrediccion = null;
        
        if ($participante) {
            $miPrediccion = $quiniela->predicciones()->where('registro_id', $usuarioId)->first();
        }
        
        $estadisticas = $quiniela->getEstadisticasApuestas();

        return view('quiniela.show', compact('quiniela', 'participante', 'miPrediccion', 'estadisticas', 'usuarioId'));
    }

    public function participar(Request $request, Quiniela $quiniela): RedirectResponse
    {
        $usuarioId = session('registro_id');
        
        if (!$usuarioId) {
            return back()->with('error', 'Debes iniciar sesión para participar.');
        }
        
        if (!$quiniela->puedeParticipar($usuarioId)) {
            return back()->with('error', 'No puedes participar en esta quiniela.');
        }

        ParticipanteQuiniela::create([
            'quiniela_id' => $quiniela->id,
            'registro_id' => $usuarioId,
            'puntos_totales' => 0,
            'pagado' => true,
        ]);

        return redirect()->route('quinielas.show', $quiniela)
            ->with('success', 'Te has unido a la quiniela exitosamente.');
    }

    public function hacerPrediccion(Request $request, Quiniela $quiniela): RedirectResponse
    {
        $request->validate([
            'prediccion' => 'required|in:local,visitante,empate',
        ]);

        $usuarioId = session('registro_id');
        
        if (!$usuarioId) {
            return back()->with('error', 'Debes iniciar sesión para hacer predicciones.');
        }
        
        // Verificar que el usuario participe en la quiniela
        $participante = $quiniela->participantes()->where('registro_id', $usuarioId)->first();
        if (!$participante) {
            return back()->with('error', 'Debes participar en la quiniela para hacer predicciones.');
        }

        // Verificar que no haya hecho ya una predicción
        $prediccionExistente = $quiniela->predicciones()
            ->where('registro_id', $usuarioId)
            ->first();

        if ($prediccionExistente) {
            $prediccionExistente->update(['prediccion' => $request->prediccion]);
            $mensaje = 'Predicción actualizada exitosamente.';
        } else {
            $quiniela->predicciones()->create([
                'registro_id' => $usuarioId,
                'partido_id' => $quiniela->partido_id,
                'prediccion' => $request->prediccion,
            ]);
            $mensaje = 'Predicción guardada exitosamente.';
        }

        return back()->with('success', $mensaje);
    }

    /**
     * Registrar apuestas del Mundial para la ronda activa
     * IMPORTANTE: Los usuarios pueden participar en cualquier fase activa, 
     * independientemente de si participaron o no en fases anteriores.
     * No hay restricciones que impidan participar en fases posteriores.
     */
    public function apostarMundial(Request $request): RedirectResponse
    {
        if (!Schema::hasTable('world_cup_bets')) {
            return back()->with('error', 'Las apuestas mundialistas no están disponibles. Ejecuta la migración correspondiente.');
        }

        // Verificar si el torneo está cerrado
        $latestTournament = null;
        if (Schema::hasTable('world_cup_tournaments')) {
            $hasStatusColumn = Schema::hasColumn('world_cup_tournaments', 'status');
            
            if ($hasStatusColumn) {
                $latestTournament = WorldCupTournament::where(function($query) {
                    $query->where('status', '!=', 'archived')
                          ->orWhereNull('status');
                })
                ->latest()
                ->first();
            } else {
                $latestTournament = WorldCupTournament::latest()->first();
            }

            if ($latestTournament) {
                $tournamentStatus = $latestTournament->status ?? 'in_progress';
                if (in_array($tournamentStatus, ['completed', 'archived'])) {
                    return back()->with('error', 'El torneo ha finalizado. Las quinielas están cerradas.');
                }
            }
        }

        $usuarioId = session('registro_id');

        if (!$usuarioId) {
            return back()->with('error', 'Debes iniciar sesión para registrar tus apuestas.');
        }

        // Validar que el usuario existe en la tabla registros
        if (!Schema::hasTable('registros') || !\App\Models\Registro::find($usuarioId)) {
            return back()->with('error', 'Usuario no válido. Por favor, inicia sesión nuevamente.');
        }

        $betsInput = $request->input('bets', []);

        if (!is_array($betsInput) || empty($betsInput)) {
            return back()->with('error', 'Debes seleccionar al menos un enfrentamiento.');
        }

        // Obtener el torneo activo para asociar las apuestas
        $activeTournament = null;
        if (Schema::hasTable('world_cup_tournaments')) {
            $hasStatusColumn = Schema::hasColumn('world_cup_tournaments', 'status');
            
            if ($hasStatusColumn) {
                $activeTournament = WorldCupTournament::where(function($query) {
                    $query->where('status', '!=', 'archived')
                          ->orWhereNull('status');
                })
                ->latest()
                ->first();
            } else {
                $activeTournament = WorldCupTournament::latest()->first();
            }
        }

        // Validar que exista un torneo activo
        if (!$activeTournament) {
            return back()->with('error', 'No hay un torneo activo. Debes generar un torneo primero.');
        }

        // Obtener el round_index de la ronda activa actual
        $activeRoundIndex = $this->getActiveRoundIndex($activeTournament);
        if ($activeRoundIndex === null) {
            $activeRoundIndex = 0; // Por defecto primera ronda si no se puede determinar
        }

        // Obtener los partidos de la ronda activa del torneo (igual que en index())
        $matches = $this->extractMatchesFromTournament($activeTournament, $activeRoundIndex)->keyBy('match_key');
        
        // Validar que haya partidos en la ronda activa
        if ($matches->isEmpty()) {
            return back()->with('error', 'No hay partidos disponibles en la ronda activa. El torneo aún no tiene enfrentamientos definidos.');
        }

        $expectedMatches = $matches->count();
        $pendingMatches = [];
        $validatedBets = [];

        foreach ($matches as $matchKey => $match) {
            $betData = $betsInput[$matchKey] ?? null;
            $chosenTeam = $betData['team'] ?? null;
            $scoreA = $betData['score_a'] ?? null;
            $scoreB = $betData['score_b'] ?? null;

            $matchLabel = sprintf('%s vs %s', $match['team_a']['code'], $match['team_b']['code']);

            // Validar que haya seleccionado un ganador y que los marcadores sean numéricos (0 es válido)
            if (!$betData || $chosenTeam === null) {
                $pendingMatches[] = $matchLabel;
                continue;
            }
            
            // Los marcadores pueden ser 0, pero deben ser numéricos
            if ($scoreA === null || $scoreA === '' || !is_numeric($scoreA)) {
                $scoreA = 0;
            }
            if ($scoreB === null || $scoreB === '' || !is_numeric($scoreB)) {
                $scoreB = 0;
            }

            $validSelections = [
                $match['team_a']['code'],
                $match['team_b']['code'],
            ];

            if (!in_array($chosenTeam, $validSelections, true)) {
                $pendingMatches[] = $matchLabel;
                continue;
            }

            if (!is_numeric($scoreA) || !is_numeric($scoreB)) {
                $pendingMatches[] = $matchLabel;
                continue;
            }

            $scoreA = max(0, min(20, (int) $scoreA));
            $scoreB = max(0, min(20, (int) $scoreB));

            $validatedBets[$matchKey] = [
                'match' => $match,
                'selected_code' => $chosenTeam,
                'score_a' => $scoreA,
                'score_b' => $scoreB,
            ];
        }

        if (count($validatedBets) !== $expectedMatches) {
            $message = 'Debes registrar un ganador y marcador para todos los partidos.';
            if (!empty($pendingMatches)) {
                $message .= ' Pendientes: ' . implode(', ', $pendingMatches);
            }

            return back()
                ->withInput()
                ->with('error', $message);
        }

        foreach ($validatedBets as $matchKey => $data) {
            $match = $data['match'];
            
            // Usar el round_index de la ronda activa para todas las apuestas
            // Si el partido ya existe en match_results, usar su round_index
            $roundIndex = $activeRoundIndex;
            if ($activeTournament && Schema::hasTable('world_cup_match_results')) {
                $matchResult = WorldCupMatchResult::where('tournament_id', $activeTournament->id)
                    ->where('match_key', $matchKey)
                    ->first();
                if ($matchResult) {
                    $roundIndex = $matchResult->round_index;
                }
            }

            // Buscar apuesta existente considerando también tournament_id y round_index
            // para evitar conflictos entre diferentes torneos o rondas
            $bet = WorldCupBet::where('registro_id', $usuarioId)
                ->where('match_key', $matchKey)
                ->where('round_index', $roundIndex)
                ->when($activeTournament, function($query) use ($activeTournament) {
                    return $query->where('tournament_id', $activeTournament->id);
                })
                ->first();

            if ($bet) {
                // Actualizar apuesta existente (solo si pertenece al mismo torneo y ronda)
                if ($bet->tournament_id == $activeTournament->id && $bet->round_index == $roundIndex) {
                    $bet->update([
                        'tournament_id' => $activeTournament->id,
                        'round_index' => $roundIndex,
                        'stage' => $match['stage'] ?? 'Fase Eliminatoria',
                        'team_a_code' => $match['team_a']['code'],
                        'team_b_code' => $match['team_b']['code'],
                        'selected_code' => $data['selected_code'],
                        'score_a' => $data['score_a'],
                        'score_b' => $data['score_b'],
                    ]);
                }
            } else {
                // Crear nueva apuesta (siempre debe tener tournament_id y round_index)
                WorldCupBet::create([
                    'registro_id' => $usuarioId,
                    'tournament_id' => $activeTournament->id,
                    'round_index' => $roundIndex,
                    'match_key' => $matchKey,
                    'stage' => $match['stage'] ?? 'Fase Eliminatoria',
                    'team_a_code' => $match['team_a']['code'],
                    'team_b_code' => $match['team_b']['code'],
                    'selected_code' => $data['selected_code'],
                    'score_a' => $data['score_a'],
                    'score_b' => $data['score_b'],
                ]);
            }
        }

        return back()->with('success', 'Tus apuestas del Mundial fueron registradas correctamente.');
    }

    protected function getWorldCupTeams(): Collection
    {
        if (Schema::hasTable('world_cup_teams')) {
            return WorldCupTeam::orderByDesc('fifa_ranking')->get();
        }

        return $this->fallbackWorldCupTeams();
    }

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

    protected function generateWorldCupMatches(Collection $teams): Collection
    {
        $matches = collect();
        $stage = 'Dieciseisavos de Final';
        $teams = $teams->values();

        for ($i = 0; $i < $teams->count(); $i += 2) {
            if (!isset($teams[$i + 1])) {
                break;
            }

            $teamA = $this->normalizeTeam($teams[$i]);
            $teamB = $this->normalizeTeam($teams[$i + 1]);

            $matchKey = sprintf('%s_vs_%s', $teamA['code'], $teamB['code']);

            $matches->push([
                'match_key' => $matchKey,
                'stage' => $stage,
                'team_a' => $teamA,
                'team_b' => $teamB,
            ]);
        }

        return $matches;
    }

    protected function normalizeTeam($team): array
    {
        return [
            'name' => $team->name ?? $team['name'],
            'code' => $team->code ?? $team['code'],
            'ranking' => $team->fifa_ranking ?? $team['fifa_ranking'],
            'continent' => $team->continent ?? $team['continent'],
            'flag_url' => $team->flag_url ?? $team['flag_url'] ?? null,
        ];
    }

    /**
     * Obtener el índice de la ronda activa actual
     * Basado en los resultados del torneo y las quinielas
     */
    protected function getActiveRoundIndex(?WorldCupTournament $tournament): ?int
    {
        if (!$tournament) {
            return 0; // Por defecto primera ronda
        }

        // Primero, intentar determinar la ronda activa basándose en world_cup_match_results (más actualizado)
        if (Schema::hasTable('world_cup_match_results')) {
            // Obtener todas las rondas con partidos
            $roundsWithMatches = WorldCupMatchResult::where('tournament_id', $tournament->id)
                ->select('round_index')
                ->distinct()
                ->orderBy('round_index')
                ->pluck('round_index')
                ->toArray();
            
            if (!empty($roundsWithMatches)) {
                // Buscar la primera ronda que tenga partidos no jugados
                foreach ($roundsWithMatches as $roundIndex) {
                    $totalMatches = WorldCupMatchResult::where('tournament_id', $tournament->id)
                        ->where('round_index', $roundIndex)
                        ->count();
                    
                    $playedMatches = WorldCupMatchResult::where('tournament_id', $tournament->id)
                        ->where('round_index', $roundIndex)
                        ->where('played', true)
                        ->count();
                    
                    // Si no todos los partidos están jugados, esta es la ronda activa
                    if ($totalMatches > 0 && $playedMatches < $totalMatches) {
                        return $roundIndex;
                    }
                }
                
                // Si todas las rondas están completadas, devolver la última
                return max($roundsWithMatches);
            }
        }

        // Si no hay resultados en MySQL, intentar desde el JSON rounds del torneo
        $rounds = $tournament->rounds ?? [];
        
        if (!empty($rounds)) {
            foreach ($rounds as $roundIndex => $round) {
                $matches = $round['matches'] ?? [];
                
                if (empty($matches)) {
                    continue; // Saltar rondas sin partidos
                }
                
                // Verificar si todos los partidos están jugados
                $allPlayed = collect($matches)->every(function ($match) {
                    return !empty($match['played']);
                });
                
                // Si no todos están jugados, esta es la ronda activa
                if (!$allPlayed) {
                    return $roundIndex;
                }
            }
            
            // Si todas las rondas están completadas, buscar la última ronda
            $lastRoundIndex = count($rounds) - 1;
            if ($lastRoundIndex >= 0) {
                return $lastRoundIndex;
            }
        }

        // Si no hay rounds en el JSON, intentar desde las quinielas
        if (Schema::hasTable('tournament_quinielas')) {
            // Buscar la quiniela activa
            $activeQuiniela = \App\Models\TournamentQuiniela::where('tournament_id', $tournament->id)
                ->where('estado', 'activa')
                ->first();

            if ($activeQuiniela) {
                return $activeQuiniela->round_index;
            }

            // Si no hay quiniela activa, buscar la primera ronda que no esté finalizada
            $nextQuiniela = \App\Models\TournamentQuiniela::where('tournament_id', $tournament->id)
                ->where('estado', '!=', 'finalizada')
                ->orderBy('round_index')
                ->first();

            if ($nextQuiniela) {
                return $nextQuiniela->round_index;
            }
        }

        // Por defecto, primera ronda
        return 0;
    }

    protected function extractMatchesFromTournament(WorldCupTournament $tournament, ?int $roundIndex = null): Collection
    {
        $matches = collect();
        
        // Si no se especifica roundIndex, usar la ronda activa
        if ($roundIndex === null) {
            $roundIndex = $this->getActiveRoundIndex($tournament);
        }

        // Primero intentar obtener desde world_cup_match_results (MySQL) - más actualizado
        if (Schema::hasTable('world_cup_match_results')) {
            $matchResults = WorldCupMatchResult::where('tournament_id', $tournament->id)
                ->where('round_index', $roundIndex)
                ->orderBy('order')
                ->get();

            if ($matchResults->isNotEmpty()) {
                // Obtener información de continentes desde world_cup_teams
                $teamCodes = $matchResults->pluck('team1_code')
                    ->merge($matchResults->pluck('team2_code'))
                    ->filter()
                    ->unique();

                $teamsData = [];
                if (Schema::hasTable('world_cup_teams') && $teamCodes->isNotEmpty()) {
                    $teamsData = WorldCupTeam::whereIn('code', $teamCodes)
                        ->get()
                        ->keyBy('code');
                }

                // Obtener nombre de la ronda para el stage
                $roundNames = [
                    'Dieciseisavos de Final',
                    'Octavos de Final',
                    'Cuartos de Final',
                    'Semifinales',
                    'Final'
                ];
                $roundName = $roundNames[$roundIndex] ?? 'Fase Eliminatoria';

                $matches = $matchResults->map(function ($matchResult) use ($teamsData, $roundName) {
                    $team1Data = $teamsData->get($matchResult->team1_code);
                    $team2Data = $teamsData->get($matchResult->team2_code);

                    return [
                        'match_key' => $matchResult->match_key,
                        'stage' => $roundName,
                        'team_a' => [
                            'name' => $matchResult->team1_name ?? $team1Data->name ?? null,
                            'code' => $matchResult->team1_code,
                            'ranking' => $team1Data->fifa_ranking ?? null,
                            'continent' => $team1Data->continent ?? null,
                            'flag_url' => $team1Data->flag_url ?? null,
                        ],
                        'team_b' => [
                            'name' => $matchResult->team2_name ?? $team2Data->name ?? null,
                            'code' => $matchResult->team2_code,
                            'ranking' => $team2Data->fifa_ranking ?? null,
                            'continent' => $team2Data->continent ?? null,
                            'flag_url' => $team2Data->flag_url ?? null,
                        ],
                    ];
                })->filter(fn ($match) => $match['team_a']['code'] && $match['team_b']['code'])->values();
            }
        }

        // Si no hay resultados en MySQL, obtener desde el JSON rounds del torneo
        if ($matches->isEmpty()) {
            $rounds = $tournament->rounds ?? [];
            $targetRound = collect($rounds)->get($roundIndex) ?? [];
            $roundMatches = collect($targetRound['matches'] ?? []);

            if ($roundMatches->isNotEmpty()) {
                // Obtener información de equipos desde el JSON teams del torneo
                $tournamentTeams = collect($tournament->teams ?? [])->keyBy('code');

                // Obtener nombre de la ronda para el stage
                $roundNames = [
                    'Dieciseisavos de Final',
                    'Octavos de Final',
                    'Cuartos de Final',
                    'Semifinales',
                    'Final'
                ];
                $roundName = $targetRound['name'] ?? $roundNames[$roundIndex] ?? 'Fase Eliminatoria';

                $matches = $roundMatches->map(function ($match, $index) use ($tournamentTeams, $roundIndex, $roundName) {
                    $team1 = $match['team1'] ?? $match['team_a'] ?? null;
                    $team2 = $match['team2'] ?? $match['team_b'] ?? null;

                    if (!$team1 || !$team2) {
                        return null;
                    }

                    $team1Code = $team1['code'] ?? null;
                    $team2Code = $team2['code'] ?? null;

                    // Buscar información completa del equipo desde tournament teams o usar la del match
                    $team1Data = $tournamentTeams->get($team1Code) ?? $team1;
                    $team2Data = $tournamentTeams->get($team2Code) ?? $team2;

                    $matchKey = $match['id'] ?? $match['match_key'] ?? sprintf('round-%d-match-%d', $roundIndex, $index);

                    return [
                        'match_key' => $matchKey,
                        'stage' => $roundName,
                        'team_a' => [
                            'name' => $team1Data['name'] ?? $team1['name'] ?? null,
                            'code' => $team1Code,
                            'ranking' => $team1Data['ranking'] ?? $team1['ranking'] ?? null,
                            'continent' => $team1Data['continent'] ?? $team1['continent'] ?? null,
                            'flag_url' => $team1Data['flag_url'] ?? $team1['flag_url'] ?? null,
                        ],
                        'team_b' => [
                            'name' => $team2Data['name'] ?? $team2['name'] ?? null,
                            'code' => $team2Code,
                            'ranking' => $team2Data['ranking'] ?? $team2['ranking'] ?? null,
                            'continent' => $team2Data['continent'] ?? $team2['continent'] ?? null,
                            'flag_url' => $team2Data['flag_url'] ?? $team2['flag_url'] ?? null,
                        ],
                    ];
                })->filter()->values();
            }
        }

        return $matches;
    }

    protected function normalizeStoredTeam($team): ?array
    {
        if (!$team) {
            return null;
        }

        return [
            'name' => $team['name'] ?? null,
            'code' => $team['code'] ?? null,
            'ranking' => $team['ranking'] ?? null,
            'continent' => $team['continent'] ?? null,
        ];
    }

    /**
     * Mostrar resultados del torneo por ronda y totales
     */
    public function resultadosTorneo()
    {
        $usuarioId = session('registro_id');
        
        if (!$usuarioId) {
            return redirect()->route('quinielas.index')
                ->with('error', 'Debes iniciar sesión para ver tus resultados.');
        }

        $latestTournament = null;
        if (Schema::hasTable('world_cup_tournaments')) {
            $hasStatusColumn = Schema::hasColumn('world_cup_tournaments', 'status');
            
            if ($hasStatusColumn) {
                $latestTournament = WorldCupTournament::where(function($query) {
                    $query->where('status', '!=', 'archived')
                          ->orWhereNull('status');
                })
                ->latest()
                ->first();
            } else {
                $latestTournament = WorldCupTournament::latest()->first();
            }
        }

        if (!$latestTournament) {
            return redirect()->route('quinielas.index')
                ->with('error', 'No hay torneo activo.');
        }

        // Obtener quinielas del torneo
        $quinielas = collect();
        if (Schema::hasTable('tournament_quinielas')) {
            $quinielas = \App\Models\TournamentQuiniela::where('tournament_id', $latestTournament->id)
                ->orderBy('round_index')
                ->get();
        }

        // Calcular puntos por ronda y totales
        $resultadosPorRonda = [];
        $puntosTotales = 0;

        foreach ($quinielas as $quiniela) {
            $puntosRonda = \App\Models\WorldCupBet::puntosRonda(
                $usuarioId,
                $latestTournament->id,
                $quiniela->round_index
            );

            $resultadosPorRonda[] = [
                'ronda' => $quiniela->round_name,
                'round_index' => $quiniela->round_index,
                'estado' => $quiniela->estado,
                'puntos' => $puntosRonda,
            ];

            $puntosTotales += $puntosRonda;
        }

        // Si no hay quinielas creadas aún, calcular desde las apuestas directamente
        if ($quinielas->isEmpty() && Schema::hasTable('world_cup_bets')) {
            $puntosTotales = \App\Models\WorldCupBet::puntosTotales($usuarioId, $latestTournament->id);
        }

        return view('quiniela.resultados-torneo', compact(
            'latestTournament',
            'resultadosPorRonda',
            'puntosTotales',
            'quinielas'
        ));
    }

    /**
     * Verificar si una ronda tiene resultados
     */
    protected function rondaTieneResultados(int $tournamentId, int $roundIndex): bool
    {
        if (!Schema::hasTable('world_cup_match_results')) {
            return false;
        }
        
        return WorldCupMatchResult::where('tournament_id', $tournamentId)
            ->where('round_index', $roundIndex)
            ->where('played', true)
            ->exists();
    }
}
