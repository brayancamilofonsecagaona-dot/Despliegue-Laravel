<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tarea extends Model
{
    use HasFactory;

    public const ESTADOS = [
        'pendiente'   => 'Pendiente',
        'en_progreso' => 'En progreso',
        'completada'  => 'Completada',
    ];

    // user_id no es fillable a propósito: siempre se asigna desde el usuario autenticado
    protected $fillable = ['titulo', 'descripcion', 'estado', 'fecha_limite'];

    protected function casts(): array
    {
        return ['fecha_limite' => 'date'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}