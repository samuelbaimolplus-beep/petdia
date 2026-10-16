<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cargo extends Model
{
    use HasFactory;

    protected $table = 'cargos';

    protected $primaryKey = 'id_cargo';

    public $timestamps = false;

    protected $fillable = [
        'id_cargo', 'id_cuenta', 'id_tratamiento', 'fecha_hora', 'concepto',
        'monto', 'descuento', 'estado', 'observaciones',
    ];

    public function cuenta(): BelongsTo
    {
        return $this->belongsTo(Cuenta::class, 'id_cuenta', 'id_cuenta');
    }

    public function tratamiento(): BelongsTo
    {
        return $this->belongsTo(Tratamiento::class, 'id_tratamiento', 'id_tratamiento');
    }

    public function aplicacionesPago(): HasMany
    {
        return $this->hasMany(AplicacionPago::class, 'id_cargo', 'id_cargo');
    }
}
