<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Paciente extends Model
{
    use HasFactory;

    protected $table = 'pacientes';

    protected $primaryKey = 'id_paciente';

    public $timestamps = false;

    protected $fillable = [
        'id_paciente', 'id_raza', 'id_propietario', 'nombre', 'sexo',
        'fecha_nacimiento', 'tamanio', 'condicion_reproductiva', 'color',
        'microchip', 'fecha_implantacion', 'estado', 'caract_particulares',
        'tipo_pelaje', 'comportamiento_normal', 'recomendaciones', 'autorizacion_redes',
        'foto',
    ];

    public function raza(): BelongsTo
    {
        return $this->belongsTo(Raza::class, 'id_raza', 'id_raza');
    }

    public function getEspecieAttribute(): ?Especie
    {
        return $this->raza?->especie;
    }

    public function propietario(): BelongsTo
    {
        return $this->belongsTo(Propietario::class, 'id_propietario', 'id_persona');
    }

    public function persona(): HasOneThrough
    {
        return $this->hasOneThrough(Persona::class, Propietario::class, 'id_persona', 'id_persona', 'id_propietario', 'id_persona');
    }

    public function citas(): HasMany
    {
        return $this->hasMany(Cita::class, 'id_paciente', 'id_paciente');
    }

    public function atenciones(): HasMany
    {
        return $this->hasMany(Atencion::class, 'id_paciente', 'id_paciente');
    }

    public function fichaMedica(): HasOne
    {
        return $this->hasOne(FichaMedica::class, 'id_paciente', 'id_paciente');
    }

    public function tratamientos(): HasMany
    {
        return $this->hasMany(Tratamiento::class, 'id_paciente', 'id_paciente');
    }

    public function suscripciones(): HasMany
    {
        return $this->hasMany(Suscripcion::class, 'id_paciente', 'id_paciente');
    }

    public function recordatorios(): HasMany
    {
        return $this->hasMany(Recordatorio::class, 'id_paciente', 'id_paciente');
    }

    public function observacionesRegistros(): HasMany
    {
        return $this->hasMany(ObservacionRegistro::class, 'id_paciente', 'id_paciente')->orderBy('fecha_hora', 'desc');
    }
}
