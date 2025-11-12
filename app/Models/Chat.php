<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    protected $table = 'chats';
    
    protected $fillable = [
        'nombre',
        'tipo',
        'descripcion',
        'creador_id',
        'imagen_grupo'
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
     * Tareas asociadas al chat grupal
     */
    public function tareas()
    {
        return $this->hasMany(ChatGrupoTarea::class, 'chat_id');
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

    /**
     * Usuario que creó el grupo
     */
    public function creador()
    {
        return $this->belongsTo(Registro::class, 'creador_id');
    }

    /**
     * Administradores del grupo
     */
    public function administradores()
    {
        return $this->belongsToMany(Registro::class, 'chat_grupo_administradores', 'chat_id', 'registro_id')
                    ->withTimestamps();
    }

    /**
     * Verificar si un usuario es administrador del grupo
     */
    public function esAdministrador($usuarioId)
    {
        return $this->administradores()->where('registro_id', $usuarioId)->exists();
    }

    /**
     * Verificar si es un chat grupal
     */
    public function esGrupal()
    {
        return $this->tipo === 'grupal';
    }

    /**
     * Crear un nuevo chat grupal
     */
    public static function crearGrupo($nombre, $descripcion, $creadorId, $miembrosIds)
    {
        // Validar que hay al menos 3 miembros (incluyendo el creador)
        $totalMiembros = count($miembrosIds) + 1; // +1 por el creador
        if ($totalMiembros < 3) {
            throw new \Exception('Un grupo debe tener al menos 3 miembros');
        }

        // Crear el chat grupal
        $chat = self::create([
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'tipo' => 'grupal',
            'creador_id' => $creadorId
        ]);

        // Agregar el creador como miembro y administrador
        $chat->usuarios()->attach($creadorId);
        $chat->administradores()->attach($creadorId);

        // Agregar los demás miembros
        if (!empty($miembrosIds)) {
            $chat->usuarios()->attach($miembrosIds);
        }

        return $chat;
    }

    /**
     * Agregar miembros al grupo
     */
    public function agregarMiembros($miembrosIds)
    {
        if (!$this->esGrupal()) {
            throw new \Exception('Solo se pueden agregar miembros a chats grupales');
        }

        $this->usuarios()->attach($miembrosIds);
    }

    /**
     * Remover miembros del grupo
     */
    public function removerMiembros($miembrosIds)
    {
        if (!$this->esGrupal()) {
            throw new \Exception('Solo se pueden remover miembros de chats grupales');
        }

        // No permitir remover al creador
        $miembrosIds = array_filter($miembrosIds, function($id) {
            return $id != $this->creador_id;
        });

        if (!empty($miembrosIds)) {
            $this->usuarios()->detach($miembrosIds);
            $this->administradores()->detach($miembrosIds);
        }
    }

    /**
     * Promover usuario a administrador
     */
    public function promoverAdministrador($usuarioId)
    {
        if (!$this->esGrupal()) {
            throw new \Exception('Solo se pueden promover administradores en chats grupales');
        }

        if (!$this->tieneUsuario($usuarioId)) {
            throw new \Exception('El usuario no es miembro del grupo');
        }

        $this->administradores()->syncWithoutDetaching([$usuarioId]);
    }

    /**
     * Degradar administrador a miembro común
     */
    public function degradarAdministrador($usuarioId)
    {
        if (!$this->esGrupal()) {
            throw new \Exception('Solo se pueden degradar administradores en chats grupales');
        }

        // No permitir degradar al creador
        if ($usuarioId == $this->creador_id) {
            throw new \Exception('No se puede degradar al creador del grupo');
        }

        $this->administradores()->detach($usuarioId);
    }

    /**
     * Obtener el número de miembros del grupo
     */
    public function contarMiembros()
    {
        return $this->usuarios()->count();
    }
}
