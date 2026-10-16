<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AtencionTratamiento extends Model
{
    use HasFactory;

    protected $table = 'atencion_tratamiento';

    protected $primaryKey = ['id_atencion', 'id_tratamiento'];

    protected $keyType = 'array';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'id_atencion', 'id_tratamiento', 'tipo_relacion',
    ];

    public function atencionVeterinaria(): BelongsTo
    {
        return $this->belongsTo(AtencionVeterinaria::class, 'id_atencion', 'id_atencion');
    }

    public function tratamiento(): BelongsTo
    {
        return $this->belongsTo(Tratamiento::class, 'id_tratamiento', 'id_tratamiento');
    }
}
