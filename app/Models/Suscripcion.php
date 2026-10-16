<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Suscripcion extends Model
{
    use HasFactory;

    protected $table = 'suscripciones';

    protected $primaryKey = 'id_suscripcion';

    public $timestamps = false;

    protected $fillable = [
        'id_suscripcion', 'id_paciente', 'id_paquete', 'fecha_inicio', 'fecha_fin', 'estado',
    ];

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class, 'id_paciente', 'id_paciente');
    }

    public function paquete(): BelongsTo
    {
        return $this->belongsTo(Paquete::class, 'id_paquete', 'id_paquete');
    }
}
