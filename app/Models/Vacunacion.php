<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vacunacion extends Model
{
    use HasFactory;

    protected $table = 'vacunaciones';

    protected $primaryKey = 'id_procedimiento';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'id_procedimiento', 'id_uso', 'proxima_dosis', 'sitio_aplicacion',
    ];

    public function procedimiento(): BelongsTo
    {
        return $this->belongsTo(Procedimiento::class, 'id_procedimiento', 'id_procedimiento');
    }

    public function administracionMedicamento(): BelongsTo
    {
        return $this->belongsTo(AdministracionMedicamento::class, 'id_uso', 'id_uso');
    }
}
