<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleCita extends Model
{
    use HasFactory;

    protected $table = 'detalle_citas';

    protected $primaryKey = 'id_detalle_cita';

    public $timestamps = false;

    protected $fillable = [
        'id_detalle_cita', 'id_cita', 'id_servicio', 'costo_estimado', 'tiempo_estimado_minutos',
    ];

    public function cita(): BelongsTo
    {
        return $this->belongsTo(Cita::class, 'id_cita', 'id_cita');
    }

    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class, 'id_servicio', 'id_servicio');
    }
}
