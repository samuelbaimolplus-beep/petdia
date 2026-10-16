<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lote extends Model
{
    use HasFactory;

    protected $table = 'lotes';

    protected $primaryKey = 'id_lote';

    public $timestamps = false;

    protected $fillable = [
        'id_lote', 'id_producto', 'id_detalle_compra', 'numero_lote',
        'fecha_vencimiento', 'estado',
    ];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }

    public function detalleCompra(): BelongsTo
    {
        return $this->belongsTo(DetalleCompra::class, 'id_detalle_compra', 'id_detalle_compra');
    }

    public function movimientoInventarios(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class, 'id_lote', 'id_lote');
    }
}
