<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PagoAtencion extends Model
{
    use HasFactory;

    protected $table = 'pago_atencions';

    protected $primaryKey = 'id_pago_atencion';

    public $timestamps = false;

    protected $fillable = [
        'id_pago_atencion', 'id_pago', 'id_atencion', 'monto_aplicado',
    ];

    public function pago(): BelongsTo
    {
        return $this->belongsTo(Pago::class, 'id_pago', 'id_pago');
    }

    public function atencion(): BelongsTo
    {
        return $this->belongsTo(Atencion::class, 'id_atencion', 'id_atencion');
    }
}
