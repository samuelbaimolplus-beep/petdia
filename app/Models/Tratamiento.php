<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tratamiento extends Model
{
    use HasFactory;

    protected $table = 'tratamientos';

    protected $primaryKey = 'id_tratamiento';

    public $timestamps = false;

    protected $fillable = [
        'id_tratamiento', 'id_paciente', 'descripcion', 'indicaciones',
        'fecha_inicio', 'fecha_fin', 'estado', 'observaciones',
    ];

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class, 'id_paciente', 'id_paciente');
    }

    public function atencionesVeterinarias(): BelongsToMany
    {
        return $this->belongsToMany(
            AtencionVeterinaria::class,
            'atencion_tratamiento',
            'id_tratamiento',
            'id_atencion'
        );
    }

    public function cargos(): HasMany
    {
        return $this->hasMany(Cargo::class, 'id_tratamiento', 'id_tratamiento');
    }
}
