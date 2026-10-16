<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HorarioProfesional extends Model
{
    use HasFactory;

    protected $table = 'horario_profesionales';

    protected $primaryKey = 'id_horario';

    public $timestamps = false;

    protected $fillable = [
        'id_horario', 'id_profesional', 'dia_semana', 'hora_inicio', 'hora_fin', 'estado',
    ];

    public function profesional(): BelongsTo
    {
        return $this->belongsTo(Profesional::class, 'id_profesional', 'id_persona');
    }

    public function getNombreDiaAttribute(): string
    {
        return match ((int) $this->dia_semana) {
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado',
            7 => 'Domingo',
            default => 'Día '.$this->dia_semana,
        };
    }
}
