<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasEncryption;

class Comentario extends Model
{
    use HasEncryption;
    
    protected $perPage = 20;
    protected $fillable = ['registro_id_emisor', 'registro_id_receptor', 'mensaje', 'link'];

    /**
     * Boot del modelo para manejar encriptación automática
     */
    protected static function boot()
    {
        parent::boot();

        // Encriptar antes de guardar
        static::saving(function ($comentario) {
            if ($comentario->isEncryptionEnabled('comments')) {
                $comentario->mensaje = $comentario->encryptValue($comentario->mensaje, 'comments');
            }
        });
    }

    /**
     * Accessor para desencriptar mensaje automáticamente
     */
    public function getMensajeAttribute($value)
    {
        if ($this->isEncryptionEnabled('comments')) {
            return $this->decryptValue($value, 'comments');
        }
        return $value;
    }

    public function emisor()
    {
        return $this->belongsTo(Registro::class, 'registro_id_emisor', 'id');
    }

    public function receptor()
    {
        return $this->belongsTo(Registro::class, 'registro_id_receptor', 'id');
    }
}
