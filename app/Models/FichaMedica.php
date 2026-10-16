<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FichaMedica extends Model
{
    use HasFactory;

    protected $table = 'ficha_medicas';

    protected $primaryKey = 'id_ficha';

    public $timestamps = false;

    protected $fillable = [
        'id_ficha', 'id_paciente', 'fecha_apertura', 'antecedentes', 'alergias',
        'enfermedades_previas', 'cirugias_previas', 'medicamentos_habituales', 'observaciones',
    ];

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class, 'id_paciente', 'id_paciente');
    }
}
