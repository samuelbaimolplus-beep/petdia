<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Servicio extends Model
{
    use HasFactory;

    protected $table = 'servicios';

    protected $primaryKey = 'id_servicio';

    public $timestamps = false;

    protected $fillable = [
        'id_servicio', 'nombre', 'categoria', 'descripcion', 'observaciones',
        'precio_base', 'disponible_domicilio', 'estado',
    ];

    public function profesionales(): BelongsToMany
    {
        return $this->belongsToMany(
            Profesional::class,
            'profesional_servicio',
            'id_servicio',
            'id_profesional',
            null,
            'id_persona'
        );
    }

    public function paquetes(): BelongsToMany
    {
        return $this->belongsToMany(Paquete::class, 'detalle_paquetes', 'id_servicio', 'id_paquete');
    }

    public function tiemposEstimados(): HasMany
    {
        return $this->hasMany(TiempoEstimado::class, 'id_servicio', 'id_servicio');
    }

    public function detalleCitas(): HasMany
    {
        return $this->hasMany(DetalleCita::class, 'id_servicio', 'id_servicio');
    }

    public function atencionServicios(): HasMany
    {
        return $this->hasMany(AtencionServicio::class, 'id_servicio', 'id_servicio');
    }

    public function detallePaquetes(): HasMany
    {
        return $this->hasMany(DetallePaquete::class, 'id_servicio', 'id_servicio');
    }

    public function recordatorios(): HasMany
    {
        return $this->hasMany(Recordatorio::class, 'id_servicio', 'id_servicio');
    }
}
