<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServicioDomicilio extends Model
{
    use HasFactory;

    protected $table = 'servicio_domicilios';

    protected $primaryKey = 'id_servicio_domicilio';

    public $timestamps = false;

    protected $fillable = [
        'id_servicio_domicilio', 'id_cita', 'direccion', 'referencia',
        'distancia_estimada', 'costo_domicilio', 'estado',
    ];

    public function cita(): BelongsTo
    {
        return $this->belongsTo(Cita::class, 'id_cita', 'id_cita');
    }
}
