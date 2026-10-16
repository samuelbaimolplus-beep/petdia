<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Paquete extends Model
{
    use HasFactory;

    protected $table = 'paquetes';

    protected $primaryKey = 'id_paquete';

    public $timestamps = false;

    protected $fillable = [
        'id_paquete', 'nombre', 'descripcion', 'precio', 'estado',
    ];

    public function servicios(): BelongsToMany
    {
        return $this->belongsToMany(Servicio::class, 'detalle_paquetes', 'id_paquete', 'id_servicio');
    }

    public function detallePaquetes(): HasMany
    {
        return $this->hasMany(DetallePaquete::class, 'id_paquete', 'id_paquete');
    }

    public function suscripciones(): HasMany
    {
        return $this->hasMany(Suscripcion::class, 'id_paquete', 'id_paquete');
    }
}
