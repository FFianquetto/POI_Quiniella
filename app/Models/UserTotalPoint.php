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
            ->orderByDesc('puntos_totales')
            ->limit($limit)
            ->get();
    }
}
