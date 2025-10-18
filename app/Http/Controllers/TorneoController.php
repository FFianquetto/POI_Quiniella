<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TorneoController extends Controller
{
    /**
     * Mostrar la vista del simulador de torneo
     */
    public function index()
    {
        return view('torneo.index');
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
}
