<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfesionalServicio extends Model
{
    use HasFactory;

    protected $table = 'profesional_servicio';

    protected $primaryKey = ['id_profesional', 'id_servicio'];

    protected $keyType = 'array';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'id_profesional', 'id_servicio', 'estado',
    ];

    public function profesional(): BelongsTo
    {
        return $this->belongsTo(Profesional::class, 'id_profesional', 'id_persona');
    }

    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class, 'id_servicio', 'id_servicio');
    }
}
