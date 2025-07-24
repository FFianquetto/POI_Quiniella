<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Publicacione
 *
 * @property $id
 * @property $registro_id
 * @property $titulo
 * @property $contenido
 * @property $conversacion_id
 * @property $created_at
 * @property $updated_at
 *
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Publicacione extends Model
{
    
    protected $perPage = 20;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['registro_id', 'titulo', 'contenido', 'conversacion_id'];

    /**
     * Relación con el autor de la publicación
     */
    public function autor()
    {
        return $this->belongsTo(Registro::class, 'registro_id', 'id');
    }
}
