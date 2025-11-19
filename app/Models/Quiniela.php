<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quiniela extends Model
{
    protected $table = 'quinielas';
    
    protected $fillable = [
        'nombre',
        'descripcion',
        'partido_id',
        'fecha_limite',
        'estado',
        'resultado_final',
        'puntos_ganador'
    ];

    protected $casts = [
        'fecha_limite' => 'datetime',
        'puntos_ganador' => 'integer',
    ];

    /**
     * Partido asociado a esta quiniela
     */
    public function partido()
    {
        return $this->belongsTo(Partido::class);
    }

    /**
     * Participantes de la quiniela
     */
    public function participantes()
    {
        return $this->hasMany(ParticipanteQuiniela::class);
    }

    /**
     * Predicciones de esta quiniela
     */
    public function predicciones()
    {
        return $this->hasMany(Prediccion::class);
    }

    /**
     * Verificar si la quiniela está activa
     */
    public function isActiva()
    {
        return $this->estado === 'activa' && $this->fecha_limite > now();
    }

    /**
     * Verificar si un usuario puede participar
     */
    public function puedeParticipar($usuarioId)
    {
        if (!$this->isActiva()) {
            return false;
        }

        return !$this->participantes()->where('registro_id', $usuarioId)->exists();
    }

    /**
     * Obtener estadísticas de las apuestas
     */
    public function getEstadisticasApuestas()
    {
        $totalApuestas = $this->predicciones()->count();
        
        $apuestasLocal = $this->predicciones()->where('prediccion', 'local')->count();
        $apuestasVisitante = $this->predicciones()->where('prediccion', 'visitante')->count();
        $apuestasEmpate = $this->predicciones()->where('prediccion', 'empate')->count();

        return [
            'total' => $totalApuestas,
            'local' => $apuestasLocal,
            'visitante' => $apuestasVisitante,
            'empate' => $apuestasEmpate,
            'porcentaje_local' => $totalApuestas > 0 ? round(($apuestasLocal / $totalApuestas) * 100, 1) : 0,
            'porcentaje_visitante' => $totalApuestas > 0 ? round(($apuestasVisitante / $totalApuestas) * 100, 1) : 0,
            'porcentaje_empate' => $totalApuestas > 0 ? round(($apuestasEmpate / $totalApuestas) * 100, 1) : 0,
        ];
    }

    /**
     * Calcular ganadores cuando se define el resultado final
     */
    public function calcularGanadores()
    {
        if (!$this->resultado_final) {
            return null;
        }

        $ganadores = $this->predicciones()
            ->where('prediccion', $this->resultado_final)
            ->with('usuario')
            ->get();

        // Asignar puntos a los ganadores
        foreach ($ganadores as $prediccion) {
            $puntosAnteriores = $prediccion->puntos_obtenidos ?? 0;
            $prediccion->puntos_obtenidos = $this->puntos_ganador;
            $prediccion->acierto = true;
            $prediccion->save();

            // Actualizar puntos totales del participante
            $participante = $this->participantes()->where('registro_id', $prediccion->registro_id)->first();
            if ($participante) {
                $participante->calcularPuntosTotales();
                $participante->actualizarPosicion();
            }
            
            // Actualizar puntos acumulados globales en user_total_points
            // Solo sumar la diferencia si es la primera vez que se asignan puntos
            if ($puntosAnteriores == 0 && $this->puntos_ganador > 0) {
                if (\Illuminate\Support\Facades\Schema::hasTable('user_total_points')) {
                    \App\Models\UserTotalPoint::actualizarPuntosAcumulados(
                        $prediccion->registro_id,
                        $this->puntos_ganador,
                        1 // 1 partido acertado
                    );
                }
            }
        }

        return $ganadores;
    }
}
