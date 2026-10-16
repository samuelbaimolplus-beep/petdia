<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Propietario extends Model
{
    use HasFactory;

    protected $table = 'propietarios';

    protected $primaryKey = 'id_persona';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'id_persona',
    ];

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'id_persona', 'id_persona');
    }

    public function pacientes(): HasMany
    {
        return $this->hasMany(Paciente::class, 'id_propietario', 'id_persona');
    }

    public function cuenta(): HasOne
    {
        return $this->hasOne(Cuenta::class, 'id_propietario', 'id_persona');
    }

    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class, 'id_propietario', 'id_persona');
    }
}
