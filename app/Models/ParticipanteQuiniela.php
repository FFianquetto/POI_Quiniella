<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParticipanteQuiniela extends Model
{
    protected $table = 'participantes_quiniela';

    protected $fillable = [
        'quiniela_id',
        'registro_id',
        'puntos_totales',
        'posicion',
        'pagado'
    ];

    protected $casts = [
        'pagado' => 'boolean',
    ];

    /**
     * Quiniela a la que pertenece
     */
    public function quiniela()
    {
        return $this->belongsTo(Quiniela::class);
    }

    /**
     * Usuario participante
     */
    public function usuario()
    {
        return $this->belongsTo(Registro::class, 'registro_id');
    }

    /**
     * Predicciones del participante
     */
    public function predicciones()
    {
        return $this->hasMany(Prediccion::class, 'registro_id', 'registro_id')
                    ->where('quiniela_id', $this->quiniela_id);
    }

    /**
     * Calcular puntos totales
     */
    public function calcularPuntosTotales()
    {
        $puntos = $this->predicciones()->sum('puntos_obtenidos');
        $this->puntos_totales = $puntos;
        $this->save();
        return $puntos;
    }

    /**
     * Actualizar posición en la quiniela
     */
    public function actualizarPosicion()
    {
        $participantes = $this->quiniela->participantes()
            ->orderBy('puntos_totales', 'desc')
            ->orderBy('created_at', 'asc')
            ->get();

        $posicion = $participantes->search(function ($participante) {
            return $participante->id === $this->id;
        });

        if ($posicion !== false) {
            $this->posicion = $posicion + 1;
            $this->save();
        }

        return $this->posicion;
    }
}
