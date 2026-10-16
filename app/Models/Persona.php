<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Persona extends Model
{
    use HasFactory;

    protected $table = 'personas';

    protected $primaryKey = 'id_persona';

    public $timestamps = false;

    protected $fillable = [
        'id_persona', 'documento', 'nombre', 'apellido1', 'apellido2',
        'telefono', 'email', 'direccion',
    ];

    public function propietario(): HasOne
    {
        return $this->hasOne(Propietario::class, 'id_persona', 'id_persona');
    }

    public function profesional(): HasOne
    {
        return $this->hasOne(Profesional::class, 'id_persona', 'id_persona');
    }

    public function usuarios(): HasMany
    {
        return $this->hasMany(Usuario::class, 'id_persona', 'id_persona');
    }
}
