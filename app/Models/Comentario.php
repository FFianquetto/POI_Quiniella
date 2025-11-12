<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Comentario extends Model
{
    protected $perPage = 20;
    protected $fillable = ['registro_id_emisor', 'registro_id_receptor', 'mensaje', 'link'];

    public function emisor()
    {
        return $this->belongsTo(Registro::class, 'registro_id_emisor', 'id');
    }

    public function receptor()
    {
        return $this->belongsTo(Registro::class, 'registro_id_receptor', 'id');
    }
}
