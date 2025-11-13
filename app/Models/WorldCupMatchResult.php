<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorldCupMatchResult extends Model
{
    protected $fillable = [
        'tournament_id',
        'match_key',
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
    ];

    protected $casts = [
        'decided_by_penalties' => 'bool',
        'played' => 'bool',
        'played_at' => 'datetime',
    ];

    public function tournament()
    {
        return $this->belongsTo(WorldCupTournament::class, 'tournament_id');
    }
}

