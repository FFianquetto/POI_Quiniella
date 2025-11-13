<?php

namespace App\Http\Controllers;

use App\Models\Quiniela;
use App\Models\Partido;
use App\Models\ParticipanteQuiniela;
use App\Models\WorldCupBet;
use App\Models\WorldCupTournament;
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
        $latestTournament = Schema::hasTable('world_cup_tournaments')
            ? WorldCupTournament::latest()->first()
            : null;

        if ($latestTournament) {
            $worldCupMatches = $this->extractMatchesFromTournament($latestTournament);
        } else {
            $worldCupTeams = $this->getWorldCupTeams();
            $worldCupMatches = $this->generateWorldCupMatches($worldCupTeams);
        }

        $usuarioBets = collect();
        if ($usuarioId && Schema::hasTable('world_cup_bets')) {
            $usuarioBets = \App\Models\WorldCupBet::where('registro_id', $usuarioId)->get()->keyBy('match_key');
        }

        $quinielas = Quiniela::with(['partido.equipoLocal', 'partido.equipoVisitante', 'participantes'])
            ->where('estado', 'activa')
            ->orderBy('fecha_limite')
            ->paginate(10);
        
        return view('quiniela.index', compact('quinielas', 'usuarioId', 'worldCupMatches', 'usuarioBets'));
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

    public function apostarMundial(Request $request): RedirectResponse
    {
        if (!Schema::hasTable('world_cup_bets')) {
            return back()->with('error', 'Las apuestas mundialistas no están disponibles. Ejecuta la migración correspondiente.');
        }

        $usuarioId = session('registro_id');

        if (!$usuarioId) {
            return back()->with('error', 'Debes iniciar sesión para registrar tus apuestas.');
        }

        $betsInput = $request->input('bets', []);

        if (!is_array($betsInput) || empty($betsInput)) {
            return back()->with('error', 'Debes seleccionar al menos un enfrentamiento.');
        }

        $worldCupTeams = $this->getWorldCupTeams();
        $matches = $this->generateWorldCupMatches($worldCupTeams)->keyBy('match_key');

        foreach ($betsInput as $matchKey => $betData) {
            if (!$matches->has($matchKey) || !is_array($betData)) {
                continue;
            }

            $chosenTeam = $betData['team'] ?? null;
            $scoreA = $betData['score_a'] ?? null;
            $scoreB = $betData['score_b'] ?? null;

            $match = $matches->get($matchKey);
            $validSelections = [
                $match['team_a']['code'],
                $match['team_b']['code'],
            ];

            if (!$chosenTeam || !in_array($chosenTeam, $validSelections, true)) {
                continue;
            }

            $scoreA = is_numeric($scoreA) ? max(0, min(20, (int) $scoreA)) : null;
            $scoreB = is_numeric($scoreB) ? max(0, min(20, (int) $scoreB)) : null;

            WorldCupBet::updateOrCreate(
                [
                    'registro_id' => $usuarioId,
                    'match_key' => $matchKey,
                ],
                [
                    'stage' => $match['stage'],
                    'team_a_code' => $match['team_a']['code'],
                    'team_b_code' => $match['team_b']['code'],
                    'selected_code' => $chosenTeam,
                    'score_a' => $scoreA,
                    'score_b' => $scoreB,
                ]
            );
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

    protected function extractMatchesFromTournament(WorldCupTournament $tournament): Collection
    {
        $rounds = $tournament->rounds ?? [];
        $firstRound = collect($rounds)->first() ?? [];
        $matches = collect($firstRound['matches'] ?? []);

        return $matches->map(function ($match) {
            return [
                'match_key' => $match['id'] ?? $match['match_key'] ?? Str::uuid()->toString(),
                'team_a' => $this->normalizeStoredTeam($match['team1'] ?? $match['team_a'] ?? null),
                'team_b' => $this->normalizeStoredTeam($match['team2'] ?? $match['team_b'] ?? null),
            ];
        })->filter(fn ($match) => $match['team_a'] && $match['team_b'])->values();
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
}
