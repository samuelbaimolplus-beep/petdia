<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DevolucionPago extends Model
{
    use HasFactory;

    protected $table = 'devolucion_pagos';

    protected $primaryKey = 'id_devolucion';

    public $timestamps = false;

    protected $fillable = [
        'id_devolucion', 'id_pago', 'id_usuario', 'fecha_hora', 'monto', 'motivo', 'estado',
    ];

    public function pago(): BelongsTo
    {
        return $this->belongsTo(Pago::class, 'id_pago', 'id_pago');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }
}
