<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MetodoPago extends Model
{
    use HasFactory;

    protected $table = 'metodos_pago';

    protected $primaryKey = 'id_metodo_pago';

    public $timestamps = false;

    protected $fillable = [
        'id_metodo_pago', 'nombre', 'estado',
    ];

    public function detallePagos(): HasMany
    {
        return $this->hasMany(DetallePago::class, 'id_metodo_pago', 'id_metodo_pago');
    }
}
