<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Registro;

/**
 * Class Comentario
 *
 * @property int $id
 * @property int $registro_id_emisor
 * @property int $registro_id_receptor
 * @property string $mensaje
 * @property string $link
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property Registro $emisor
 * @property Registro $receptor
 */
class Comentario extends Model
{
    protected $perPage = 20;

    protected $fillable = ['registro_id_emisor', 'registro_id_receptor', 'mensaje', 'link'];

    /**
     * Emisor del comentario
     */
    public function emisor()
    {
        return $this->belongsTo(Registro::class, 'registro_id_emisor', 'id');
    }

    /**
     * Receptor del comentario
     */
    public function receptor()
    {
        return $this->belongsTo(Registro::class, 'registro_id_receptor', 'id');
    }
}
