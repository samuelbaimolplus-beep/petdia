<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeguimientoInternacion extends Model
{
    use HasFactory;

    protected $table = 'seguimiento_internaciones';

    protected $primaryKey = 'id_seguimiento';

    public $timestamps = false;

    protected $fillable = [
        'id_seguimiento', 'id_internacion', 'id_profesional', 'fecha_hora',
        'temperatura', 'frecuencia_cardiaca', 'frecuencia_respiratoria', 'peso',
        'estado_animo', 'alimentacion', 'hidratacion', 'deposiciones', 'orina',
        'cuidados', 'observaciones',
    ];

    public function internacion(): BelongsTo
    {
        return $this->belongsTo(Internacion::class, 'id_internacion', 'id_internacion');
    }

    public function profesional(): BelongsTo
    {
        return $this->belongsTo(Profesional::class, 'id_profesional', 'id_persona');
    }
}
