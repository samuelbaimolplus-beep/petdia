<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ObservacionRegistro extends Model
{
    use HasFactory;

    protected $table = 'observaciones_registros';

    protected $primaryKey = 'id_observacion';

    public $timestamps = false;

    protected $fillable = [
        'id_observacion',
        'id_paciente',
        'id_atencion',
        'id_procedimiento',
        'id_usuario',
        'tipo',
        'categoria',
        'observacion',
        'fecha_hora',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'fecha_hora' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class, 'id_paciente', 'id_paciente');
    }

    public function atencion(): BelongsTo
    {
        return $this->belongsTo(Atencion::class, 'id_atencion', 'id_atencion');
    }

    public function procedimiento(): BelongsTo
    {
        return $this->belongsTo(Procedimiento::class, 'id_procedimiento', 'id_procedimiento');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }
}
