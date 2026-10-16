<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistroAcceso extends Model
{
    use HasFactory;

    protected $table = 'registro_accesos';

    protected $primaryKey = 'id_acceso';

    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'nombre_usuario',
        'rol',
        'fecha_ingreso',
        'hora_ingreso',
        'fecha_hora_cierre',
        'estado',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_ingreso' => 'date',
            'fecha_hora_cierre' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }
}
