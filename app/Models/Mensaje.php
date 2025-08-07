<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mensaje extends Model
{
    protected $table = 'mensajes';
    
    protected $fillable = [
        'chat_id',
        'registro_id_emisor',
        'contenido',
        'tipo',
        'archivo_url',
        'archivo_nombre',
        'leido'
    ];

    protected $casts = [
        'leido' => 'boolean',
    ];

    /**
     * Chat al que pertenece el mensaje
     */
    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    /**
     * Usuario que envió el mensaje
     */
    public function emisor()
    {
        return $this->belongsTo(Registro::class, 'registro_id_emisor');
    }

    /**
     * Marcar mensaje como leído
     */
    public function marcarComoLeido()
    {
        $this->leido = true;
        $this->save();
    }

    /**
     * Verificar si el mensaje es de un archivo
     */
    public function esArchivo()
    {
        return in_array($this->tipo, ['imagen', 'video', 'audio', 'archivo']);
    }

    /**
     * Obtener la extensión del archivo
     */
    public function getExtension()
    {
        if ($this->archivo_nombre) {
            return pathinfo($this->archivo_nombre, PATHINFO_EXTENSION);
        }
        return null;
    }

    /**
     * Obtener el nombre del archivo sin extensión
     */
    public function getNombreSinExtension()
    {
        if ($this->archivo_nombre) {
            return pathinfo($this->archivo_nombre, PATHINFO_FILENAME);
        }
        return null;
    }
}
