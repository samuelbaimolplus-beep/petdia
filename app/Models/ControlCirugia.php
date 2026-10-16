<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ControlCirugia extends Model
{
    use HasFactory;

    protected $table = 'controles_cirugia';

    protected $primaryKey = 'id_control';

    public $timestamps = false;

    protected $fillable = [
        'id_control',
        'id_cirugia',
        'id_profesional',
        'fecha_control',
        'estado_herida',
        'temperatura',
        'peso',
        'evolucion',
        'indicaciones',
        'proximo_control',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'fecha_control' => 'datetime',
            'proximo_control' => 'date',
            'temperatura' => 'decimal:2',
            'peso' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    public function cirugia(): BelongsTo
    {
        return $this->belongsTo(Cirugia::class, 'id_cirugia', 'id_procedimiento');
    }

    public function profesional(): BelongsTo
    {
        return $this->belongsTo(Profesional::class, 'id_profesional', 'id_persona');
    }
}
