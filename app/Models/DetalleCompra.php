<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DetalleCompra extends Model
{
    use HasFactory;

    protected $table = 'detalle_compras';

    protected $primaryKey = 'id_detalle_compra';

    public $timestamps = false;

    protected $fillable = [
        'id_detalle_compra', 'id_compra', 'id_producto', 'cantidad', 'precio_unitario',
    ];

    public function compra(): BelongsTo
    {
        return $this->belongsTo(Compra::class, 'id_compra', 'id_compra');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }

    public function lotes(): HasMany
    {
        return $this->hasMany(Lote::class, 'id_detalle_compra', 'id_detalle_compra');
    }

    public function movimientoInventarios(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class, 'id_detalle_compra', 'id_detalle_compra');
    }
}
