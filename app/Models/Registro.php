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
 * @property Quiniela[] $quinielasCreadas
 * @property ParticipanteQuiniela[] $participaciones
 * @property Prediccion[] $predicciones
 */
class Registro extends Model
{
    protected $perPage = 20;

    protected $fillable = ['nombre', 'correo', 'contrasena', 'edad'];

    /**
     * Chats en los que participa este usuario
     */
    public function chats()
    {
        return $this->belongsToMany(Chat::class, 'chat_usuario', 'registro_id', 'chat_id')
                    ->withPivot('ultima_lectura')
                    ->withTimestamps();
    }

    /**
     * Mensajes enviados por este usuario
     */
    public function mensajesEnviados()
    {
        return $this->hasMany(Mensaje::class, 'registro_id_emisor');
    }

    /**
     * Quinielas creadas por este usuario
     */
    public function quinielasCreadas()
    {
        return $this->hasMany(Quiniela::class, 'registro_id');
    }

    /**
     * Participaciones en quinielas
     */
    public function participaciones()
    {
        return $this->hasMany(ParticipanteQuiniela::class, 'registro_id');
    }

    /**
     * Predicciones realizadas
     */
    public function predicciones()
    {
        return $this->hasMany(Prediccion::class, 'registro_id');
    }

    /**
     * Obtener quinielas activas donde participa
     */
    public function quinielasActivas()
    {
        return $this->participaciones()
            ->whereHas('quiniela', function ($query) {
                $query->where('estado', 'activa');
            })
            ->with('quiniela');
    }
}
