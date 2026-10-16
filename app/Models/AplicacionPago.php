<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AplicacionPago extends Model
{
    use HasFactory;

    protected $table = 'aplicacion_pagos';

    protected $primaryKey = 'id_aplicacion';

    public $timestamps = false;

    protected $fillable = [
        'id_aplicacion', 'id_pago', 'id_cargo', 'monto_aplicado',
    ];

    public function pago(): BelongsTo
    {
        return $this->belongsTo(Pago::class, 'id_pago', 'id_pago');
    }

    public function cargo(): BelongsTo
    {
        return $this->belongsTo(Cargo::class, 'id_cargo', 'id_cargo');
    }
}
