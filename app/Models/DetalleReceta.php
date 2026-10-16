<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleReceta extends Model
{
    use HasFactory;

    protected $table = 'detalle_recetas';

    protected $primaryKey = 'id_detalle_receta';

    public $timestamps = false;

    protected $fillable = [
        'id_detalle_receta', 'id_receta', 'id_producto', 'dosis', 'unidad_dosis',
        'frecuencia', 'duracion', 'via_administracion', 'indicaciones',
    ];

    public function receta(): BelongsTo
    {
        return $this->belongsTo(Receta::class, 'id_receta', 'id_receta');
    }

    public function medicamento(): BelongsTo
    {
        return $this->belongsTo(Medicamento::class, 'id_producto', 'id_producto');
    }
}
