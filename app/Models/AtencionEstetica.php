<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AtencionEstetica extends Model
{
    use HasFactory;

    protected $table = 'atencion_esteticas';

    protected $primaryKey = 'id_atencion';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'id_atencion', 'modalidad', 'estado_pelaje', 'caracteristicas_observadas',
    ];

    public function atencion(): BelongsTo
    {
        return $this->belongsTo(Atencion::class, 'id_atencion', 'id_atencion');
    }
}
