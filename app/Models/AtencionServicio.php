<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AtencionServicio extends Model
{
    use HasFactory;

    protected $table = 'atencion_servicios';

    protected $primaryKey = 'id_atencion_servicio';

    public $timestamps = false;

    protected $fillable = [
        'id_atencion_servicio', 'id_atencion', 'id_servicio', 'cantidad',
        'precio_unitario', 'descuento',
    ];

    public function atencion(): BelongsTo
    {
        return $this->belongsTo(Atencion::class, 'id_atencion', 'id_atencion');
    }

    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class, 'id_servicio', 'id_servicio');
    }
}
