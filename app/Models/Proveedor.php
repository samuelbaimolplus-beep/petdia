<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Proveedor extends Model
{
    use HasFactory;

    protected $table = 'proveedores';

    protected $primaryKey = 'id_proveedor';

    public $timestamps = false;

    protected $fillable = [
        'id_proveedor', 'nombre', 'nit', 'telefono', 'email', 'direccion', 'estado',
    ];

    public function compras(): HasMany
    {
        return $this->hasMany(Compra::class, 'id_proveedor', 'id_proveedor');
    }
}
