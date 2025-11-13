<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorldCupTeam extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'fifa_ranking',
        'continent',
        'flag_url',
    ];
}

