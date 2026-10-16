<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class EstudioLaboratorio extends Model
{
    use HasFactory;

    protected $table = 'estudio_laboratorios';

    protected $primaryKey = 'id_procedimiento';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'id_procedimiento',
        'tipo_estudio',
        'fecha_resultado',
        'resultado',
        'interpretacion',
        'archivo_adjunto',
        'nombre_archivo_original',
    ];

    public function procedimiento(): BelongsTo
    {
        return $this->belongsTo(Procedimiento::class, 'id_procedimiento', 'id_procedimiento');
    }

    public function getUrlAttribute(): ?string
    {
        if (! $this->archivo_adjunto) {
            return null;
        }

        return Storage::disk('public')->url($this->archivo_adjunto);
    }

    public function esImagen(): bool
    {
        if (! $this->archivo_adjunto) {
            return false;
        }

        $extension = strtolower(pathinfo($this->archivo_adjunto, PATHINFO_EXTENSION));

        return in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif']);
    }
}
