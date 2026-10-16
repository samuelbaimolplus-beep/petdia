<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cirugia extends Model
{
    use HasFactory;

    protected $table = 'cirugias';

    protected $primaryKey = 'id_procedimiento';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'id_procedimiento',
        'tipo_cirugia',
        'diagnostico_preoperatorio',
        'complicaciones',
        'indicaciones_postoperatorias',
        'resultado',
        'estado',
        'riesgo_asa',
        'tipo_anestesia',
        'ayuno_solidos_horas',
        'ayuno_liquidos_horas',
        'fecha_programada',
        'id_cirujano',
        'id_anestesista',
        'autorizacion_firmada',
        'firma_propietario_nombre',
        'firma_propietario_documento',
        'observaciones_prequirurgicas',
    ];

    public function procedimiento(): BelongsTo
    {
        return $this->belongsTo(Procedimiento::class, 'id_procedimiento', 'id_procedimiento');
    }

    public function cirujano(): BelongsTo
    {
        return $this->belongsTo(Profesional::class, 'id_cirujano', 'id_persona');
    }

    public function anestesista(): BelongsTo
    {
        return $this->belongsTo(Profesional::class, 'id_anestesista', 'id_persona');
    }

    public function controles(): HasMany
    {
        return $this->hasMany(ControlCirugia::class, 'id_cirugia', 'id_procedimiento')->orderBy('fecha_control', 'desc');
    }

    public function getPacienteAttribute(): ?Paciente
    {
        return $this->procedimiento?->atencionVeterinaria?->atencion?->paciente;
    }
}
