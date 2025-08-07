<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Partido extends Model
{
    protected $table = 'partidos';
    
    protected $fillable = [
        'equipo_local_id',
        'equipo_visitante_id',
        'fecha_hora',
        'estadio',
        'liga',
        'estado',
        'goles_local',
        'goles_visitante'
    ];

    protected $casts = [
        'fecha_hora' => 'datetime',
    ];

    /**
     * Equipo local
     */
    public function equipoLocal()
    {
        return $this->belongsTo(Equipo::class, 'equipo_local_id');
    }

    /**
     * Equipo visitante
     */
    public function equipoVisitante()
    {
        return $this->belongsTo(Equipo::class, 'equipo_visitante_id');
    }

    /**
     * Predicciones para este partido
     */
    public function predicciones()
    {
        return $this->hasMany(Prediccion::class);
    }

    /**
     * Obtener el resultado del partido
     */
    public function getResultadoAttribute()
    {
        if ($this->goles_local === null || $this->goles_visitante === null) {
            return null;
        }

        if ($this->goles_local > $this->goles_visitante) {
            return 'local';
        } elseif ($this->goles_visitante > $this->goles_local) {
            return 'visitante';
        } else {
            return 'empate';
        }
    }

    /**
     * Verificar si el partido ya finalizó
     */
    public function isFinalizado()
    {
        return $this->estado === 'finalizado';
    }
}
