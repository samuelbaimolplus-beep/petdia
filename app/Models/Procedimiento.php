<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Procedimiento extends Model
{
    use HasFactory;

    protected $table = 'procedimientos';

    protected $primaryKey = 'id_procedimiento';

    public $timestamps = false;

    protected $fillable = [
        'id_procedimiento', 'id_atencion', 'fecha_hora', 'descripcion', 'observaciones',
    ];

    public function atencionVeterinaria(): BelongsTo
    {
        return $this->belongsTo(AtencionVeterinaria::class, 'id_atencion', 'id_atencion');
    }

    public function cirugia(): HasOne
    {
        return $this->hasOne(Cirugia::class, 'id_procedimiento', 'id_procedimiento');
    }

    public function vacunacion(): HasOne
    {
        return $this->hasOne(Vacunacion::class, 'id_procedimiento', 'id_procedimiento');
    }

    public function desparasitacion(): HasOne
    {
        return $this->hasOne(Desparasitacion::class, 'id_procedimiento', 'id_procedimiento');
    }

    public function estudioLaboratorio(): HasOne
    {
        return $this->hasOne(EstudioLaboratorio::class, 'id_procedimiento', 'id_procedimiento');
    }
}
