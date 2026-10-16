<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AdministracionMedicamento extends Model
{
    use HasFactory;

    protected $table = 'administracion_medicamentos';

    protected $primaryKey = 'id_uso';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'id_uso', 'fecha_hora', 'dosis', 'unidad_dosis', 'via_administracion', 'observaciones_clinicas',
    ];

    public function usoProductoAtencion(): BelongsTo
    {
        return $this->belongsTo(UsoProductoAtencion::class, 'id_uso', 'id_uso');
    }

    public function vacunacion(): HasOne
    {
        return $this->hasOne(Vacunacion::class, 'id_uso', 'id_uso');
    }

    public function desparasitacion(): HasOne
    {
        return $this->hasOne(Desparasitacion::class, 'id_uso', 'id_uso');
    }
}
