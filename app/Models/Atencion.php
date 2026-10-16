<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Atencion extends Model
{
    use HasFactory;

    protected $table = 'atenciones';

    protected $primaryKey = 'id_atencion';

    public $timestamps = false;

    protected $fillable = [
        'id_atencion', 'id_paciente', 'id_cita', 'fecha_hora_inicio',
        'fecha_hora_fin', 'estado', 'observaciones', 'recomendaciones', 'total_final',
    ];

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class, 'id_paciente', 'id_paciente');
    }

    public function cita(): BelongsTo
    {
        return $this->belongsTo(Cita::class, 'id_cita', 'id_cita');
    }

    public function atencionVeterinaria(): HasOne
    {
        return $this->hasOne(AtencionVeterinaria::class, 'id_atencion', 'id_atencion');
    }

    public function atencionEstetica(): HasOne
    {
        return $this->hasOne(AtencionEstetica::class, 'id_atencion', 'id_atencion');
    }

    public function participaciones(): HasMany
    {
        return $this->hasMany(ParticipacionAtencion::class, 'id_atencion', 'id_atencion');
    }

    public function profesionales(): BelongsToMany
    {
        return $this->belongsToMany(Profesional::class, 'participacion_atencions', 'id_atencion', 'id_profesional');
    }

    public function getProfesionalAttribute(): ?Profesional
    {
        return $this->profesionales->first() ?? $this->cita?->profesional;
    }

    public function atencionServicios(): HasMany
    {
        return $this->hasMany(AtencionServicio::class, 'id_atencion', 'id_atencion');
    }

    public function usosProductoAtencion(): HasMany
    {
        return $this->hasMany(UsoProductoAtencion::class, 'id_atencion', 'id_atencion');
    }

    public function pagoAtencions(): HasMany
    {
        return $this->hasMany(PagoAtencion::class, 'id_atencion', 'id_atencion');
    }

    public function recordatorios(): HasMany
    {
        return $this->hasMany(Recordatorio::class, 'id_atencion_origen', 'id_atencion');
    }

    public function observacionesRegistros(): HasMany
    {
        return $this->hasMany(ObservacionRegistro::class, 'id_atencion', 'id_atencion')->orderBy('fecha_hora', 'desc');
    }

    public function getTotalPagadoAttribute(): float
    {
        if ($this->relationLoaded('pagoAtencions')) {
            return (float) $this->pagoAtencions->sum('monto_aplicado');
        }

        return (float) $this->pagoAtencions()->sum('monto_aplicado');
    }

    public function getSaldoPendienteAttribute(): float
    {
        $total = (float) ($this->total_final ?? 0);
        $pagado = $this->total_pagado;

        return max(0, $total - $pagado);
    }

    public function getEstaPagadaAttribute(): bool
    {
        return $this->total_final > 0 && $this->saldo_pendiente <= 0.01;
    }
}
