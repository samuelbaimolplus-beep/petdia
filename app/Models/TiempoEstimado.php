<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TiempoEstimado extends Model
{
    use HasFactory;

    protected $table = 'tiempos_estimados';

    protected $primaryKey = 'id_tiempo_estimado';

    public $timestamps = false;

    protected $fillable = [
        'id_tiempo_estimado', 'id_servicio', 'id_especie', 'peso_min',
        'peso_max', 'duracion_minutos',
    ];

    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class, 'id_servicio', 'id_servicio');
    }

    public function especie(): BelongsTo
    {
        return $this->belongsTo(Especie::class, 'id_especie', 'id_especie');
    }

    public function ajustesTiempoRaza(): HasMany
    {
        return $this->hasMany(AjusteTiempoRaza::class, 'id_tiempo_estimado', 'id_tiempo_estimado');
    }
}
