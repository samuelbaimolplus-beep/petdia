<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Raza extends Model
{
    use HasFactory;

    protected $table = 'razas';

    protected $primaryKey = 'id_raza';

    public $timestamps = false;

    protected $fillable = [
        'id_raza', 'id_especie', 'nombre',
    ];

    public function especie(): BelongsTo
    {
        return $this->belongsTo(Especie::class, 'id_especie', 'id_especie');
    }

    public function pacientes(): HasMany
    {
        return $this->hasMany(Paciente::class, 'id_raza', 'id_raza');
    }

    public function ajustesTiempoRaza(): HasMany
    {
        return $this->hasMany(AjusteTiempoRaza::class, 'id_raza', 'id_raza');
    }
}
