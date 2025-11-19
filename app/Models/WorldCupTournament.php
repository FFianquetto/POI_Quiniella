<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorldCupTournament extends Model
{
    protected $fillable = [
        'uuid',
        'favorite_team',
        'total_teams',
        'teams',
        'rounds',
        'results',
        'status',
        'completed_at',
    ];

    protected $casts = [
        'teams' => 'array',
        'rounds' => 'array',
        'results' => 'array',
        'completed_at' => 'datetime',
    ];

    public function matchResults()
    {
        return $this->hasMany(WorldCupMatchResult::class, 'tournament_id');
    }

    public function tournamentQuinielas()
    {
        return $this->hasMany(TournamentQuiniela::class, 'tournament_id');
    }

    public function bets()
    {
        return $this->hasMany(WorldCupBet::class, 'tournament_id');
    }
}
