<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AjusteTiempoRaza extends Model
{
    use HasFactory;

    protected $table = 'ajustes_tiempo_razas';

    protected $primaryKey = 'id_ajuste';

    public $timestamps = false;

    protected $fillable = [
        'id_ajuste', 'id_tiempo_estimado', 'id_raza', 'duracion_minutos',
    ];

    public function tiempoEstimado(): BelongsTo
    {
        return $this->belongsTo(TiempoEstimado::class, 'id_tiempo_estimado', 'id_tiempo_estimado');
    }

    public function raza(): BelongsTo
    {
        return $this->belongsTo(Raza::class, 'id_raza', 'id_raza');
    }
}
