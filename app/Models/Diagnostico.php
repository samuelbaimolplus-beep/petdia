<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Diagnostico extends Model
{
    use HasFactory;

    protected $table = 'diagnosticos';

    protected $primaryKey = 'id_diagnostico';

    public $timestamps = false;

    protected $fillable = [
        'id_diagnostico', 'id_atencion', 'nombre', 'descripcion', 'tipo', 'observaciones',
    ];

    public function atencionVeterinaria(): BelongsTo
    {
        return $this->belongsTo(AtencionVeterinaria::class, 'id_atencion', 'id_atencion');
    }
}
