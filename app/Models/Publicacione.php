<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Publicacione extends Model
{
    protected $perPage = 20;
    protected $fillable = ['registro_id', 'titulo', 'contenido'];

    public function autor()
    {
        return $this->belongsTo(Registro::class, 'registro_id', 'id');
    }
}
