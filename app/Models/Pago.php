<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pago extends Model
{
    use HasFactory;

    protected $table = 'pagos';

    protected $primaryKey = 'id_pago';

    public $timestamps = false;

    protected $fillable = [
        'id_pago', 'id_cuenta', 'id_cierre', 'fecha_hora', 'estado', 'observaciones',
    ];

    public function cuenta(): BelongsTo
    {
        return $this->belongsTo(Cuenta::class, 'id_cuenta', 'id_cuenta');
    }

    public function cierreCaja(): BelongsTo
    {
        return $this->belongsTo(CierreCaja::class, 'id_cierre', 'id_cierre');
    }

    public function detallePagos(): HasMany
    {
        return $this->hasMany(DetallePago::class, 'id_pago', 'id_pago');
    }

    public function aplicaciones(): HasMany
    {
        return $this->hasMany(AplicacionPago::class, 'id_pago', 'id_pago');
    }

    public function pagoAtencions(): HasMany
    {
        return $this->hasMany(PagoAtencion::class, 'id_pago', 'id_pago');
    }

    public function pagoVentas(): HasMany
    {
        return $this->hasMany(PagoVenta::class, 'id_pago', 'id_pago');
    }

    public function devoluciones(): HasMany
    {
        return $this->hasMany(DevolucionPago::class, 'id_pago', 'id_pago');
    }

    public function getMontoTotalAttribute(): float
    {
        if ($this->relationLoaded('detallePagos')) {
            return (float) $this->detallePagos->sum('monto');
        }

        return (float) $this->detallePagos()->sum('monto');
    }

    public function getMetodosTextoAttribute(): string
    {
        if ($this->relationLoaded('detallePagos')) {
            return $this->detallePagos->map(fn ($d) => $d->metodoPago?->nombre ?? 'N/A')->unique()->implode(', ');
        }

        return $this->detallePagos()->with('metodoPago')->get()->map(fn ($d) => $d->metodoPago?->nombre ?? 'N/A')->unique()->implode(', ');
    }
}
