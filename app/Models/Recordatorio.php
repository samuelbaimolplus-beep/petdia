<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recordatorio extends Model
{
    use HasFactory;

    protected $table = 'recordatorios';

    protected $primaryKey = 'id_recordatorio';

    public $timestamps = false;

    protected $fillable = [
        'id_recordatorio', 'id_paciente', 'id_servicio', 'id_atencion_origen',
        'id_cita', 'id_profesional', 'tipo', 'fecha_hora_programada', 'fecha_hora_envio',
        'medio_envio', 'mensaje', 'estado',
    ];

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class, 'id_paciente', 'id_paciente');
    }

    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class, 'id_servicio', 'id_servicio');
    }

    public function profesional(): BelongsTo
    {
        return $this->belongsTo(Profesional::class, 'id_profesional', 'id_persona');
    }

    public function atencionOrigen(): BelongsTo
    {
        return $this->belongsTo(Atencion::class, 'id_atencion_origen', 'id_atencion');
    }

    public function cita(): BelongsTo
    {
        return $this->belongsTo(Cita::class, 'id_cita', 'id_cita');
    }

    /**
     * Resuelve el profesional/veterinario responsable del recordatorio.
     */
    public function getProfesionalResponsableAttribute(): ?Profesional
    {
        if ($this->profesional) {
            return $this->profesional;
        }

        if ($this->cita && $this->cita->profesional) {
            return $this->cita->profesional;
        }

        return null;
    }
}
