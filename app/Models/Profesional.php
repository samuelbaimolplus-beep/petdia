<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Profesional extends Model
{
    use HasFactory;

    protected $table = 'profesionales';

    protected $primaryKey = 'id_persona';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'id_persona', 'especialidad', 'registro_profesional', 'estado',
    ];

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'id_persona', 'id_persona');
    }

    public function usuario(): HasOne
    {
        return $this->hasOne(Usuario::class, 'id_persona', 'id_persona');
    }

    public function servicios(): BelongsToMany
    {
        return $this->belongsToMany(
            Servicio::class,
            'profesional_servicio',
            'id_profesional',
            'id_servicio',
            'id_persona'
        );
    }

    public function citas(): HasMany
    {
        return $this->hasMany(Cita::class, 'id_profesional', 'id_persona');
    }

    public function horarios(): HasMany
    {
        return $this->hasMany(HorarioProfesional::class, 'id_profesional', 'id_persona');
    }

    public function bloqueosAgenda(): HasMany
    {
        return $this->hasMany(BloqueoAgenda::class, 'id_profesional', 'id_persona');
    }

    public function participacionesAtencion(): HasMany
    {
        return $this->hasMany(ParticipacionAtencion::class, 'id_profesional', 'id_persona');
    }

    public function seguimientosInternacion(): HasMany
    {
        return $this->hasMany(SeguimientoInternacion::class, 'id_profesional', 'id_persona');
    }
}
