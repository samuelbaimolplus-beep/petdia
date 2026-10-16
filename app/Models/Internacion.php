<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Internacion extends Model
{
    use HasFactory;

    protected $table = 'internaciones';

    protected $primaryKey = 'id_internacion';

    public $timestamps = false;

    protected $fillable = [
        'id_internacion', 'id_atencion_ingreso', 'fecha_hora_ingreso',
        'fecha_hora_alta', 'motivo', 'estado', 'jaula_numero', 'observaciones',
    ];

    public function atencionVeterinaria(): BelongsTo
    {
        return $this->belongsTo(AtencionVeterinaria::class, 'id_atencion_ingreso', 'id_atencion');
    }

    public function atencion(): BelongsTo
    {
        return $this->belongsTo(Atencion::class, 'id_atencion_ingreso', 'id_atencion');
    }

    public function seguimientos(): HasMany
    {
        return $this->hasMany(SeguimientoInternacion::class, 'id_internacion', 'id_internacion')->orderByDesc('fecha_hora');
    }
}
