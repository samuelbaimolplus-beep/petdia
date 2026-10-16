<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CierreCaja extends Model
{
    use HasFactory;

    protected $table = 'cierre_cajas';

    protected $primaryKey = 'id_cierre';

    public $timestamps = false;

    protected $fillable = [
        'id_cierre', 'id_usuario', 'fecha_hora_apertura', 'fecha_hora_cierre',
        'total_registrado', 'observaciones',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class, 'id_cierre', 'id_cierre');
    }

    public function egresos(): HasMany
    {
        return $this->hasMany(EgresoCaja::class, 'id_cierre', 'id_cierre');
    }
}
