<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prediccion extends Model
{
    protected $table = 'predicciones';
    
    protected $fillable = [
        'quiniela_id',
        'registro_id',
        'partido_id',
        'prediccion',
        'puntos_obtenidos',
        'acierto'
    ];

    protected $casts = [
        'acierto' => 'boolean',
    ];

    /**
     * Quiniela a la que pertenece
     */
    public function quiniela()
    {
        return $this->belongsTo(Quiniela::class);
    }

    /**
     * Usuario que hizo la predicción
     */
    public function usuario()
    {
        return $this->belongsTo(Registro::class, 'registro_id');
    }

    /**
     * Partido de la predicción
     */
    public function partido()
    {
        return $this->belongsTo(Partido::class);
    }

    /**
     * Calcular puntos basado en el resultado real
     */
    public function calcularPuntos()
    {
        if (!$this->partido->isFinalizado()) {
            return 0;
        }

        $resultadoReal = $this->partido->resultado;
        
        if ($this->prediccion === $resultadoReal) {
            $this->acierto = true;
            $this->puntos_obtenidos = 3; // 3 puntos por acierto
        } else {
            $this->acierto = false;
            $this->puntos_obtenidos = 0;
        }

        $this->save();
        return $this->puntos_obtenidos;
    }
}
