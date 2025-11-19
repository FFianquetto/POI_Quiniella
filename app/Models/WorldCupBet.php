<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorldCupBet extends Model
{
    protected $fillable = [
        'registro_id',
        'tournament_id',
        'round_index',
        'match_key',
        'stage',
        'team_a_code',
        'team_b_code',
        'selected_code',
        'score_a',
        'score_b',
        'puntos_obtenidos',
        'acierto_ganador',
        'acierto_marcador',
    ];

    protected $casts = [
        'acierto_ganador' => 'boolean',
        'acierto_marcador' => 'boolean',
        'puntos_obtenidos' => 'integer',
    ];

    /**
     * Torneo al que pertenece
     */
    public function tournament()
    {
        return $this->belongsTo(WorldCupTournament::class, 'tournament_id');
    }

    /**
     * Usuario que hizo la apuesta
     */
    public function usuario()
    {
        return $this->belongsTo(Registro::class, 'registro_id');
    }

    /**
     * Resultado del partido
     */
    public function matchResult()
    {
        return $this->hasOne(WorldCupMatchResult::class, 'match_key', 'match_key')
            ->where('tournament_id', $this->tournament_id);
    }

    /**
     * Calcular puntos basado en el resultado real del partido
     * Solo se otorgan puntos por acertar el ganador (1 punto por partido acertado)
     * El marcador es indiferente
     */
    public function calcularPuntos()
    {
        $matchResult = $this->matchResult()->first();
        
        if (!$matchResult || !$matchResult->played) {
            $this->puntos_obtenidos = 0;
            $this->acierto_ganador = false;
            $this->acierto_marcador = false;
            $this->save();
            return 0;
        }

        $puntos = 0;
        $aciertoGanador = false;

        // Verificar acierto de ganador (1 punto por partido acertado) - el marcador es indiferente
        if ($matchResult->winner_code === $this->selected_code) {
            $puntos = 1;
            $aciertoGanador = true;
        }

        $this->puntos_obtenidos = $puntos;
        $this->acierto_ganador = $aciertoGanador;
        $this->acierto_marcador = false; // Ya no se usa, pero mantenemos el campo
        $this->save();

        return $puntos;
    }

    /**
     * Obtener puntos totales del usuario en una ronda específica
     */
    public static function puntosRonda($usuarioId, $tournamentId, $roundIndex)
    {
        return static::where('registro_id', $usuarioId)
            ->where('tournament_id', $tournamentId)
            ->where('round_index', $roundIndex)
            ->sum('puntos_obtenidos');
    }

    /**
     * Obtener puntos totales del usuario en todo el torneo
     */
    public static function puntosTotales($usuarioId, $tournamentId)
    {
        return static::where('registro_id', $usuarioId)
            ->where('tournament_id', $tournamentId)
            ->sum('puntos_obtenidos');
    }
}

