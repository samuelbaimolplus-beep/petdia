<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Cita extends Model
{
    use HasFactory;

    protected $table = 'citas';

    protected $primaryKey = 'id_cita';

    public $timestamps = false;

    protected $fillable = [
        'id_cita', 'id_paciente', 'id_profesional', 'fecha_hora_inicio',
        'fecha_hora_fin', 'estado', 'motivo_cancelacion', 'origen',
    ];

    protected function casts(): array
    {
        return [
            'fecha_hora_inicio' => 'datetime',
            'fecha_hora_fin' => 'datetime',
        ];
    }

    public function getFechaHoraAttribute()
    {
        return $this->fecha_hora_inicio;
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class, 'id_paciente', 'id_paciente');
    }

    public function profesional(): BelongsTo
    {
        return $this->belongsTo(Profesional::class, 'id_profesional', 'id_persona');
    }

    public function detalleCitas(): HasMany
    {
        return $this->hasMany(DetalleCita::class, 'id_cita', 'id_cita');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleCita::class, 'id_cita', 'id_cita');
    }

    public function servicioDomicilio(): HasOne
    {
        return $this->hasOne(ServicioDomicilio::class, 'id_cita', 'id_cita');
    }

    public function atenciones(): HasMany
    {
        return $this->hasMany(Atencion::class, 'id_cita', 'id_cita');
    }

    public function recordatorios(): HasMany
    {
        return $this->hasMany(Recordatorio::class, 'id_cita', 'id_cita');
    }
}
