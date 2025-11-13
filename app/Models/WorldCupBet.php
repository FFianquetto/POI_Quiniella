<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorldCupBet extends Model
{
    protected $fillable = [
        'registro_id',
        'match_key',
        'stage',
        'team_a_code',
        'team_b_code',
        'selected_code',
        'score_a',
        'score_b',
    ];
}

