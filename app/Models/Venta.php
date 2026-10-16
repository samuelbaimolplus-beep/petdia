<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venta extends Model
{
    use HasFactory;

    protected $table = 'ventas';

    protected $primaryKey = 'id_venta';

    public $timestamps = false;

    protected $fillable = [
        'id_venta', 'id_propietario', 'id_usuario', 'fecha_hora', 'estado', 'total', 'observaciones',
    ];

    public function propietario(): BelongsTo
    {
        return $this->belongsTo(Propietario::class, 'id_propietario', 'id_persona');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function productos(): BelongsToMany
    {
        return $this->belongsToMany(Producto::class, 'detalle_ventas', 'id_venta', 'id_producto');
    }

    public function detalleVentas(): HasMany
    {
        return $this->hasMany(DetalleVenta::class, 'id_venta', 'id_venta');
    }

    public function pagoVentas(): HasMany
    {
        return $this->hasMany(PagoVenta::class, 'id_venta', 'id_venta');
    }
}
