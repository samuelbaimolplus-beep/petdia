<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetallePago extends Model
{
    use HasFactory;

    protected $table = 'detalle_pagos';

    protected $primaryKey = 'id_detalle_pago';

    public $timestamps = false;

    protected $fillable = [
        'id_detalle_pago', 'id_pago', 'id_metodo_pago', 'monto', 'referencia',
    ];

    public function pago(): BelongsTo
    {
        return $this->belongsTo(Pago::class, 'id_pago', 'id_pago');
    }

    public function metodoPago(): BelongsTo
    {
        return $this->belongsTo(MetodoPago::class, 'id_metodo_pago', 'id_metodo_pago');
    }
}
