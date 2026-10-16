<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class UsoProductoAtencion extends Model
{
    use HasFactory;

    protected $table = 'uso_producto_atencions';

    protected $primaryKey = 'id_uso';

    public $timestamps = false;

    protected $fillable = [
        'id_uso', 'id_atencion', 'id_producto', 'id_lote', 'cantidad', 'observaciones',
    ];

    public function atencion(): BelongsTo
    {
        return $this->belongsTo(Atencion::class, 'id_atencion', 'id_atencion');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }

    public function administracionMedicamento(): HasOne
    {
        return $this->hasOne(AdministracionMedicamento::class, 'id_uso', 'id_uso');
    }

    public function movimientoInventarios(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class, 'id_uso', 'id_uso');
    }
}
