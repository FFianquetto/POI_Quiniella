<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatGrupoTarea extends Model
{
    protected $table = 'chat_grupo_tareas';

    protected $fillable = [
        'chat_id',
        'titulo',
        'descripcion',
        'creado_por',
        'asignado_a',
        'estado',
        'completado_por',
        'completado_at',
    ];

    protected $casts = [
        'completado_at' => 'datetime',
    ];

    public const ESTADO_PENDIENTE = 'pendiente';
    public const ESTADO_COMPLETADA = 'completada';

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    public function creador()
    {
        return $this->belongsTo(Registro::class, 'creado_por');
    }

    public function asignadoA()
    {
        return $this->belongsTo(Registro::class, 'asignado_a');
    }

    public function completadoPor()
    {
        return $this->belongsTo(Registro::class, 'completado_por');
    }

    public function estaCompletada(): bool
    {
        return $this->estado === self::ESTADO_COMPLETADA;
    }

    public function marcarComoCompletada(int $usuarioId): void
    {
        $this->estado = self::ESTADO_COMPLETADA;
        $this->completado_por = $usuarioId;
        $this->completado_at = now();
        $this->save();
    }
}

