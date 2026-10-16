<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BloqueoAgenda extends Model
{
    use HasFactory;

    protected $table = 'bloqueo_agendas';

    protected $primaryKey = 'id_bloqueo';

    public $timestamps = false;

    protected $fillable = [
        'id_bloqueo', 'id_profesional', 'fecha_hora_inicio', 'fecha_hora_fin', 'motivo',
    ];

    public function profesional(): BelongsTo
    {
        return $this->belongsTo(Profesional::class, 'id_profesional', 'id_persona');
    }
}
