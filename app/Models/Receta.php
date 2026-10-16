<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Receta extends Model
{
    use HasFactory;

    protected $table = 'recetas';

    protected $primaryKey = 'id_receta';

    public $timestamps = false;

    protected $fillable = [
        'id_receta', 'id_atencion', 'fecha_hora', 'indicaciones_generales',
    ];

    public function atencionVeterinaria(): BelongsTo
    {
        return $this->belongsTo(AtencionVeterinaria::class, 'id_atencion', 'id_atencion');
    }

    public function detalleRecetas(): HasMany
    {
        return $this->hasMany(DetalleReceta::class, 'id_receta', 'id_receta');
    }
}
