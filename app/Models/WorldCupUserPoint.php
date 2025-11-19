<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorldCupUserPoint extends Model
{
    protected $fillable = [
        'registro_id',
        'tournament_id',
        'round_index',
        'round_name',
        'puntos_totales',
        'apuestas_totales',
        'apuestas_acertadas',
        'fecha_calculo',
    ];

    protected $casts = [
        'puntos_totales' => 'integer',
        'apuestas_totales' => 'integer',
        'apuestas_acertadas' => 'integer',
        'fecha_calculo' => 'datetime',
    ];

    /**
     * Usuario al que pertenecen estos puntos
     */
    public function usuario()
    {
        return $this->belongsTo(Registro::class, 'registro_id');
    }

    /**
     * Torneo al que pertenecen estos puntos
     */
    public function tournament()
    {
        return $this->belongsTo(WorldCupTournament::class, 'tournament_id');
    }

    /**
     * Obtener puntos totales de un usuario en todo el torneo
     */
    public static function puntosTotalesTorneo($usuarioId, $tournamentId)
    {
        return static::where('registro_id', $usuarioId)
            ->where('tournament_id', $tournamentId)
            ->sum('puntos_totales');
    }

    /**
     * Obtener puntos de un usuario en una ronda específica
     */
    public static function puntosRonda($usuarioId, $tournamentId, $roundIndex)
    {
        $point = static::where('registro_id', $usuarioId)
            ->where('tournament_id', $tournamentId)
            ->where('round_index', $roundIndex)
            ->first();
            
        return $point ? $point->puntos_totales : 0;
    }

    /**
     * Obtener ranking de usuarios en un torneo
     */
    public static function rankingTorneo($tournamentId, $limit = 10)
    {
        return static::selectRaw('registro_id, SUM(puntos_totales) as total_puntos')
            ->where('tournament_id', $tournamentId)
            ->groupBy('registro_id')
            ->orderByDesc('total_puntos')
            ->limit($limit)
            ->get();
    }
}
