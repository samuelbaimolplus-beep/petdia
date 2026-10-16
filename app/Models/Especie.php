<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Especie extends Model
{
    use HasFactory;

    protected $table = 'especies';

    protected $primaryKey = 'id_especie';

    public $timestamps = false;

    protected $fillable = [
        'id_especie', 'nombre',
    ];

    public function razas(): HasMany
    {
        return $this->hasMany(Raza::class, 'id_especie', 'id_especie');
    }

    public function tiemposEstimados(): HasMany
    {
        return $this->hasMany(TiempoEstimado::class, 'id_especie', 'id_especie');
    }
}
