<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class UserTotalPoint extends Model
{
    protected $table = 'user_total_points';

    protected $fillable = [
        'registro_id',
        'puntos_totales',
        'partidos_acertados',
        'torneos_participados',
        'ultima_actualizacion',
    ];

    protected $casts = [
        'puntos_totales' => 'integer',
        'partidos_acertados' => 'integer',
        'torneos_participados' => 'integer',
        'ultima_actualizacion' => 'datetime',
    ];

    /**
     * Usuario al que pertenecen estos puntos
     */
    public function usuario()
    {
        return $this->belongsTo(Registro::class, 'registro_id');
    }

    /**
     * Actualizar o crear los puntos acumulados de un usuario
     * Suma los puntos ganados en una ronda a los puntos totales
     * IMPORTANTE: Este método asegura que cada usuario solo suma SUS propios puntos
     */
    public static function actualizarPuntosAcumulados($usuarioId, $puntosGanados, $partidosAcertados)
    {
        if (!Schema::hasTable('user_total_points')) {
            return;
        }

        // Validar que el usuarioId es válido y numérico
        if (!$usuarioId || !is_numeric($usuarioId) || $usuarioId <= 0) {
            \Log::warning('Intento de actualizar puntos con usuarioId inválido', ['usuarioId' => $usuarioId]);
            return;
        }

        // Validar que el usuario existe en la tabla registros
        if (!Schema::hasTable('registros') || !\App\Models\Registro::find($usuarioId)) {
            \Log::warning('Intento de actualizar puntos para usuario inexistente', ['usuarioId' => $usuarioId]);
            return;
        }

        // Asegurar que los valores sean numéricos y no negativos
        $puntosGanados = max(0, (int) $puntosGanados);
        $partidosAcertados = max(0, (int) $partidosAcertados);

        // firstOrNew asegura que solo hay UN registro por usuario (gracias a unique en registro_id)
        $userPoint = static::firstOrNew(['registro_id' => $usuarioId]);
        
        // Sumar los nuevos puntos a los existentes (o inicializar en 0 si es nuevo)
        $userPoint->puntos_totales = ($userPoint->puntos_totales ?? 0) + $puntosGanados;
        $userPoint->partidos_acertados = ($userPoint->partidos_acertados ?? 0) + $partidosAcertados;
        $userPoint->ultima_actualizacion = now();
        
        $userPoint->save();
    }

    /**
     * Obtener puntos totales acumulados de un usuario
     */
    public static function puntosTotalesUsuario($usuarioId)
    {
        if (!Schema::hasTable('user_total_points')) {
            return 0;
        }

        $userPoint = static::where('registro_id', $usuarioId)->first();
        return $userPoint ? $userPoint->puntos_totales : 0;
    }

    /**
     * Obtener ranking global de usuarios por puntos acumulados
     */
    public static function rankingGlobal($limit = 10)
    {
        if (!Schema::hasTable('user_total_points')) {
            return collect();
        }

        return static::with('usuario')
            ->whereHas('usuario') // Solo incluir usuarios que existen
            ->where('puntos_totales', '>', 0) // Solo usuarios con puntos
            ->orderByDesc('puntos_totales')
            ->orderBy('ultima_actualizacion', 'desc') // En caso de empate, el más reciente primero
            ->limit($limit)
            ->get();
    }

    /**
     * Calcular el rango/medalla del usuario basado en sus puntos totales
     * Rangos: Bronce (0), Plata (20), Oro (40), Platino (60), Diamante (80), Leyenda (100+)
     */
    public static function calcularRango($puntosTotales)
    {
        $puntos = (int) $puntosTotales;
        
        if ($puntos >= 100) {
            return [
                'nombre' => 'Leyenda',
                'nivel' => 6,
                'puntos_requeridos' => 100,
                'puntos_siguiente' => null,
                'color' => '#9b59b6',
                'icono' => 'fa-crown',
                'descripcion' => 'El máximo nivel de conocimiento futbolístico'
            ];
        } elseif ($puntos >= 80) {
            return [
                'nombre' => 'Diamante',
                'nivel' => 5,
                'puntos_requeridos' => 80,
                'puntos_siguiente' => 100,
                'color' => '#3498db',
                'icono' => 'fa-gem',
                'descripcion' => 'Experto en predicciones futbolísticas'
            ];
        } elseif ($puntos >= 60) {
            return [
                'nombre' => 'Platino',
                'nivel' => 4,
                'puntos_requeridos' => 60,
                'puntos_siguiente' => 80,
                'color' => '#95a5a6',
                'icono' => 'fa-medal',
                'descripcion' => 'Gran conocedor del fútbol'
            ];
        } elseif ($puntos >= 40) {
            return [
                'nombre' => 'Oro',
                'nivel' => 3,
                'puntos_requeridos' => 40,
                'puntos_siguiente' => 60,
                'color' => '#f39c12',
                'icono' => 'fa-medal',
                'descripcion' => 'Buen nivel de conocimiento'
            ];
        } elseif ($puntos >= 20) {
            return [
                'nombre' => 'Plata',
                'nivel' => 2,
                'puntos_requeridos' => 20,
                'puntos_siguiente' => 40,
                'color' => '#95a5a6',
                'icono' => 'fa-medal',
                'descripcion' => 'Conocimiento básico del fútbol'
            ];
        } else {
            return [
                'nombre' => 'Bronce',
                'nivel' => 1,
                'puntos_requeridos' => 0,
                'puntos_siguiente' => 20,
                'color' => '#cd7f32',
                'icono' => 'fa-medal',
                'descripcion' => 'Iniciando en las quinielas'
            ];
        }
    }
}
