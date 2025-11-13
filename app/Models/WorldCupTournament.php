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
    ];

    protected $casts = [
        'teams' => 'array',
        'rounds' => 'array',
        'results' => 'array',
    ];
}
