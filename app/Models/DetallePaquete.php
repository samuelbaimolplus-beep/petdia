<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetallePaquete extends Model
{
    use HasFactory;

    protected $table = 'detalle_paquetes';

    protected $primaryKey = 'id_detalle_paquete';

    public $timestamps = false;

    protected $fillable = [
        'id_detalle_paquete', 'id_paquete', 'id_servicio', 'cantidad_incluida',
    ];

    public function paquete(): BelongsTo
    {
        return $this->belongsTo(Paquete::class, 'id_paquete', 'id_paquete');
    }

    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class, 'id_servicio', 'id_servicio');
    }
}
