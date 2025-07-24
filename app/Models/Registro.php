<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Comentario;

/**
 * Class Registro
 *
 * @property int $id
 * @property string $nombre
 * @property string $correo
 * @property string $contrasena
 * @property int $edad
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * @property Comentario[] $comentariosEnviados
 * @property Comentario[] $comentariosRecibidos
 */
class Registro extends Model
{
    protected $perPage = 20;

    protected $fillable = ['nombre', 'correo', 'contrasena', 'edad'];

    /**
     * Comentarios enviados por este usuario (emisor)
     */
    public function comentariosEnviados()
    {
        return $this->hasMany(Comentario::class, 'registro_id_emisor', 'id');
    }

    /**
     * Comentarios recibidos por este usuario (receptor)
     */
    public function comentariosRecibidos()
    {
        return $this->hasMany(Comentario::class, 'registro_id_receptor', 'id'); 
    }
}
