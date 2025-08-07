<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Equipo extends Model
{
    protected $table = 'equipos';
    
    protected $fillable = [
        'nombre',
        'abreviacion',
        'logo',
        'ciudad',
        'liga'
    ];

    /**
     * Partidos como equipo local
     */
    public function partidosLocal()
    {
        return $this->hasMany(Partido::class, 'equipo_local_id');
    }

    /**
     * Partidos como equipo visitante
     */
    public function partidosVisitante()
    {
        return $this->hasMany(Partido::class, 'equipo_visitante_id');
    }

    /**
     * Todos los partidos del equipo
     */
    public function partidos()
    {
        return $this->partidosLocal->merge($this->partidosVisitante);
    }
}
