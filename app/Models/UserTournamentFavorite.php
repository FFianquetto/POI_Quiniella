<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserTournamentFavorite extends Model
{
    protected $fillable = [
        'registro_id',
        'tournament_id',
        'favorite_team_code',
    ];

    /**
     * Usuario que seleccionó la favorita
     */
    public function usuario()
    {
        return $this->belongsTo(Registro::class, 'registro_id');
    }

    /**
     * Torneo al que pertenece la selección
     */
    public function tournament()
    {
        return $this->belongsTo(WorldCupTournament::class, 'tournament_id');
    }
}
