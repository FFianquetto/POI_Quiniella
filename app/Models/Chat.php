<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    protected $table = 'chats';
    
    protected $fillable = [
        'nombre',
        'tipo'
    ];

    /**
     * Usuarios que participan en este chat
     */
    public function usuarios()
    {
        return $this->belongsToMany(Registro::class, 'chat_usuario', 'chat_id', 'registro_id')
                    ->withPivot('ultima_lectura')
                    ->withTimestamps();
    }

    /**
     * Mensajes del chat
     */
    public function mensajes()
    {
        return $this->hasMany(Mensaje::class)->orderBy('created_at', 'asc');
    }

    /**
     * Último mensaje del chat
     */
    public function ultimoMensaje()
    {
        return $this->hasOne(Mensaje::class)->latest();
    }

    /**
     * Crear o encontrar chat individual entre dos usuarios
     */
    public static function encontrarOcrearChat($usuario1Id, $usuario2Id)
    {
        $chat = self::whereHas('usuarios', function ($query) use ($usuario1Id) {
            $query->where('registro_id', $usuario1Id);
        })->whereHas('usuarios', function ($query) use ($usuario2Id) {
            $query->where('registro_id', $usuario2Id);
        })->where('tipo', 'individual')->first();

        if (!$chat) {
            $chat = self::create(['tipo' => 'individual']);
            $chat->usuarios()->attach([$usuario1Id, $usuario2Id]);
        }

        return $chat;
    }

    /**
     * Verificar si un usuario está en este chat
     */
    public function tieneUsuario($usuarioId)
    {
        return $this->usuarios()->where('registro_id', $usuarioId)->exists();
    }

    /**
     * Obtener el otro usuario en un chat individual
     */
    public function otroUsuario($usuarioId)
    {
        if ($this->tipo === 'individual') {
            return $this->usuarios()->where('registro_id', '!=', $usuarioId)->first();
        }
        return null;
    }
}
