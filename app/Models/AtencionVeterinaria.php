<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AtencionVeterinaria extends Model
{
    use HasFactory;

    protected $table = 'atencion_veterinarias';

    protected $primaryKey = 'id_atencion';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'id_atencion', 'motivo_consulta', 'tipo', 'anamnesis', 'sintomas',
        'examen_fisico', 'conclusion', 'fecha_reconsulta', 'observaciones_clinicas',
    ];

    public function atencion(): BelongsTo
    {
        return $this->belongsTo(Atencion::class, 'id_atencion', 'id_atencion');
    }

    public function tratamientos(): BelongsToMany
    {
        return $this->belongsToMany(
            Tratamiento::class,
            'atencion_tratamiento',
            'id_atencion',
            'id_tratamiento'
        );
    }

    public function registrosSignosVitales(): HasMany
    {
        return $this->hasMany(RegistroSignosVital::class, 'id_atencion', 'id_atencion');
    }

    public function diagnosticos(): HasMany
    {
        return $this->hasMany(Diagnostico::class, 'id_atencion', 'id_atencion');
    }

    public function procedimientos(): HasMany
    {
        return $this->hasMany(Procedimiento::class, 'id_atencion', 'id_atencion');
    }

    public function internacion(): HasOne
    {
        return $this->hasOne(Internacion::class, 'id_atencion_ingreso', 'id_atencion');
    }

    public function recetas(): HasMany
    {
        return $this->hasMany(Receta::class, 'id_atencion', 'id_atencion');
    }
}
