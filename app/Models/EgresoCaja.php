<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EgresoCaja extends Model
{
    use HasFactory;

    protected $table = 'egresos_caja';

    protected $primaryKey = 'id_egreso';

    protected $fillable = [
        'id_cierre',
        'id_usuario',
        'monto',
        'concepto',
        'categoria',
        'comprobante',
        'fecha_hora',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'monto' => 'decimal:2',
            'fecha_hora' => 'datetime',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function cierreCaja(): BelongsTo
    {
        return $this->belongsTo(CierreCaja::class, 'id_cierre', 'id_cierre');
    }

    public function cierre(): BelongsTo
    {
        return $this->belongsTo(CierreCaja::class, 'id_cierre', 'id_cierre');
    }
}
