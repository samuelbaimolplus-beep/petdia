<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistroSignosVital extends Model
{
    use HasFactory;

    protected $table = 'registro_signos_vitales';

    protected $primaryKey = 'id_registro_signos';

    public $timestamps = false;

    protected $fillable = [
        'id_registro_signos', 'id_atencion', 'fecha_hora', 'peso', 'temperatura',
        'frecuencia_cardiaca', 'frecuencia_respiratoria',
    ];

    public function atencionVeterinaria(): BelongsTo
    {
        return $this->belongsTo(AtencionVeterinaria::class, 'id_atencion', 'id_atencion');
    }
}
