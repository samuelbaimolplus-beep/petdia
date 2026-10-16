<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cuenta extends Model
{
    use HasFactory;

    protected $table = 'cuentas';

    protected $primaryKey = 'id_cuenta';

    public $timestamps = false;

    protected $fillable = [
        'id_cuenta', 'id_propietario', 'fecha_apertura', 'estado',
    ];

    public function propietario(): BelongsTo
    {
        return $this->belongsTo(Propietario::class, 'id_propietario', 'id_persona');
    }

    public function cargos(): HasMany
    {
        return $this->hasMany(Cargo::class, 'id_cuenta', 'id_cuenta');
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class, 'id_cuenta', 'id_cuenta');
    }
}
