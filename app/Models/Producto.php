<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos';

    protected $primaryKey = 'id_producto';

    public $timestamps = false;

    protected $fillable = [
        'id_producto', 'nombre', 'tipo', 'categoria', 'marca', 'unidad_medida',
        'descripcion', 'precio_venta', 'stock_minimo', 'control_lotes', 'estado',
        'codigo_barra',
    ];

    public function medicamento(): HasOne
    {
        return $this->hasOne(Medicamento::class, 'id_producto', 'id_producto');
    }

    public function compras(): BelongsToMany
    {
        return $this->belongsToMany(Compra::class, 'detalle_compras', 'id_producto', 'id_compra');
    }

    public function ventas(): BelongsToMany
    {
        return $this->belongsToMany(Venta::class, 'detalle_ventas', 'id_producto', 'id_venta');
    }

    public function lotes(): HasMany
    {
        return $this->hasMany(Lote::class, 'id_producto', 'id_producto');
    }

    public function usosProductoAtencion(): HasMany
    {
        return $this->hasMany(UsoProductoAtencion::class, 'id_producto', 'id_producto');
    }

    public function detalleCompras(): HasMany
    {
        return $this->hasMany(DetalleCompra::class, 'id_producto', 'id_producto');
    }

    public function detalleVentas(): HasMany
    {
        return $this->hasMany(DetalleVenta::class, 'id_producto', 'id_producto');
    }

    public function movimientoInventarios(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class, 'id_producto', 'id_producto');
    }
}
