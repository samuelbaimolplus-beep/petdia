<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RolPermiso extends Model
{
    use HasFactory;

    protected $table = 'rol_permiso';

    protected $primaryKey = ['id_rol', 'id_permiso'];

    protected $keyType = 'array';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'id_rol', 'id_permiso',
    ];

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'id_rol', 'id_rol');
    }

    public function permiso(): BelongsTo
    {
        return $this->belongsTo(Permiso::class, 'id_permiso', 'id_permiso');
    }
}
