<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medicamento extends Model
{
    use HasFactory;

    protected $table = 'medicamentos';

    protected $primaryKey = 'id_producto';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'id_producto', 'principio_activo', 'presentacion', 'concentracion', 'unidad_concentracion',
    ];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }

    public function detalleRecetas(): HasMany
    {
        return $this->hasMany(DetalleReceta::class, 'id_producto', 'id_producto');
    }
}
