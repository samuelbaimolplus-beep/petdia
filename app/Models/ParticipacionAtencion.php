<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParticipacionAtencion extends Model
{
    use HasFactory;

    protected $table = 'participacion_atencions';

    protected $primaryKey = 'id_participacion';

    public $timestamps = false;

    protected $fillable = [
        'id_participacion', 'id_atencion', 'id_profesional', 'rol_atencion', 'observaciones',
    ];

    public function atencion(): BelongsTo
    {
        return $this->belongsTo(Atencion::class, 'id_atencion', 'id_atencion');
    }

    public function profesional(): BelongsTo
    {
        return $this->belongsTo(Profesional::class, 'id_profesional', 'id_persona');
    }
}
