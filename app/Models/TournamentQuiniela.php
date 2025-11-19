<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TournamentQuiniela extends Model
{
    protected $fillable = [
        'tournament_id',
        'round_index',
        'round_name',
        'estado',
        'fecha_limite',
    ];

    protected $casts = [
        'fecha_limite' => 'datetime',
    ];

    /**
     * Torneo al que pertenece
     */
    public function tournament()
    {
        return $this->belongsTo(WorldCupTournament::class, 'tournament_id');
    }

    /**
     * Apuestas de esta quiniela
     */
    public function bets()
    {
        return $this->hasMany(WorldCupBet::class, 'tournament_id', 'tournament_id')
            ->where('round_index', $this->round_index);
    }

    /**
     * Verificar si la quiniela está activa
     */
    public function isActiva()
    {
        return $this->estado === 'activa';
    }

    /**
     * Cerrar la quiniela
     */
    public function cerrar()
    {
        $this->estado = 'cerrada';
        $this->save();
    }

    /**
     * Finalizar la quiniela y calcular puntos
     */
    public function finalizar()
    {
        $this->estado = 'finalizada';
        $this->save();
        
        // Calcular puntos para todas las apuestas de esta ronda
        $this->calcularPuntosRonda();
    }

    /**
     * Calcular puntos para todas las apuestas de esta ronda
     */
    public function calcularPuntosRonda()
    {
        $bets = $this->bets()->get();
        
        foreach ($bets as $bet) {
            $bet->calcularPuntos();
        }
    }
}
