<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Rol extends Model
{
    use HasFactory;

    protected $table = 'roles';

    protected $primaryKey = 'id_rol';

    public $timestamps = false;

    protected $fillable = [
        'id_rol', 'nombre', 'descripcion', 'estado',
    ];

    public function scopeActivos($query)
    {
        return $query->where('estado', 'ACTIVO');
    }

    public function isSuperAdmin(): bool
    {
        return in_array(strtoupper(str_replace(['-', ' '], '_', $this->nombre)), ['SUPER_ADMIN', 'SUPERADMIN'], true);
    }

    public function permisos(): BelongsToMany
    {
        return $this->belongsToMany(Permiso::class, 'rol_permiso', 'id_rol', 'id_permiso');
    }

    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(Usuario::class, 'usuario_rol', 'id_rol', 'id_usuario');
    }

    public function hasPermiso(int|string $permiso): bool
    {
        if (is_int($permiso)) {
            return $this->permisos->contains('id_permiso', $permiso);
        }

        return $this->permisos->contains('nombre', $permiso);
    }
}
