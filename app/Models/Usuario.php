<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Usuario extends Model implements AuthenticatableContract
{
    use Authenticatable;
    use HasFactory;

    protected $table = 'usuarios';

    protected $primaryKey = 'id_usuario';

    public $timestamps = false;

    protected $fillable = [
        'id_usuario', 'id_persona', 'nombre_usuario', 'password_hash',
        'estado', 'ultimo_acceso',
        'email_verified_at', 'email_verification_token', 'email_verification_expires_at',
        'two_factor_secret', 'two_factor_confirmed_at', 'two_factor_recovery_codes',
    ];

    protected $hidden = [
        'password_hash',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'email_verification_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'email_verification_expires_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
            'ultimo_acceso' => 'datetime',
            'two_factor_recovery_codes' => 'array',
        ];
    }

    public function hasVerifiedEmail(): bool
    {
        return ! is_null($this->email_verified_at);
    }

    public function hasTwoFactorEnabled(): bool
    {
        return ! is_null($this->two_factor_confirmed_at) && ! empty($this->two_factor_secret);
    }

    public function isAdministrator(): bool
    {
        return $this->hasRole(['SUPER_ADMIN', 'ADMIN']);
    }

    public function getAuthPasswordName(): string
    {
        return 'password_hash';
    }

    public function getNameAttribute(): string
    {
        if ($this->persona) {
            return trim("{$this->persona->nombre} {$this->persona->apellido1}");
        }

        return $this->nombre_usuario;
    }

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'id_persona', 'id_persona');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Rol::class, 'usuario_rol', 'id_usuario', 'id_rol');
    }

    public function cierresCaja(): HasMany
    {
        return $this->hasMany(CierreCaja::class, 'id_usuario', 'id_usuario');
    }

    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class, 'id_usuario', 'id_usuario');
    }

    public function movimientoInventarios(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class, 'id_usuario', 'id_usuario');
    }

    public function devolucionesPago(): HasMany
    {
        return $this->hasMany(DevolucionPago::class, 'id_usuario', 'id_usuario');
    }

    public function getNombreCompletoAttribute(): string
    {
        if ($this->persona) {
            return trim("{$this->persona->nombre} {$this->persona->apellido1} {$this->persona->apellido2}");
        }

        return $this->nombre_usuario;
    }

    public function getRolPrincipalAttribute(): string
    {
        $normalize = fn ($str) => strtoupper(str_replace(['á', 'é', 'í', 'ó', 'ú', '-', ' '], ['a', 'e', 'i', 'o', 'u', '_', '_'], (string) $str));
        $realRoles = $this->roles->pluck('nombre')->map($normalize)->all();
        $isRealSuperAdmin = in_array('SUPER_ADMIN', $realRoles, true);
        $isRealAdmin = in_array('ADMIN', $realRoles, true) || in_array('ADMINISTRADOR', $realRoles, true);

        // SEC: Solo en entorno local o tests — si hay un rol activo en sesión para demos
        if ((app()->isLocal() || app()->runningUnitTests()) && session()->has('demo_role')) {
            $demoRole = (string) session('demo_role');

            // Seguridad jerárquica:
            // 1. Roles operativos no pueden usar simulación
            if (! $isRealSuperAdmin && ! $isRealAdmin) {
                session()->forget('demo_role');
            } elseif (! $isRealSuperAdmin && $demoRole === 'SUPER_ADMIN') {
                // 2. Un Admin NO puede simular a Super-Admin
                session()->forget('demo_role');
            } else {
                return $demoRole;
            }
        }

        $rol = $this->roles->first();

        return $rol ? $rol->nombre : 'ADMIN';
    }

    public function hasRole(string|array $roles): bool
    {
        $roles = is_array($roles) ? $roles : [$roles];
        $normalize = fn ($str) => strtoupper(str_replace(['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú', '-', ' '], ['a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U', '_', '_'], $str));
        $currentRole = $normalize($this->rol_principal);
        $normalizedTargets = array_map($normalize, $roles);

        if ($currentRole === 'ESTETICO' && in_array('SPA', $normalizedTargets, true)) {
            return true;
        }
        if ($currentRole === 'SPA' && in_array('ESTETICO', $normalizedTargets, true)) {
            return true;
        }

        return in_array($currentRole, $normalizedTargets, true);
    }

    public function canAccessModule(string $module): bool
    {
        $normalize = fn ($str) => strtoupper(str_replace(['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú', '-', ' '], ['a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U', '_', '_'], (string) $str));

        $realRoles = $this->roles->pluck('nombre')->map($normalize)->all();
        $isRealSuperAdmin = in_array('SUPER_ADMIN', $realRoles, true);
        $isRealAdmin = in_array('ADMIN', $realRoles, true) || in_array('ADMINISTRADOR', $realRoles, true);

        // Limpieza de sesión si se intenta violar jerarquía
        if (! $isRealSuperAdmin && ! $isRealAdmin) {
            session()->forget('demo_role');
        } elseif (! $isRealSuperAdmin && session('demo_role') === 'SUPER_ADMIN') {
            session()->forget('demo_role');
        }

        $activeRoles = ((app()->isLocal() || app()->runningUnitTests()) && session()->has('demo_role'))
            ? [$normalize(session('demo_role'))]
            : $realRoles;

        if (in_array('ESTETICO', $activeRoles, true) && ! in_array('SPA', $activeRoles, true)) {
            $activeRoles[] = 'SPA';
        }
        if (in_array('SPA', $activeRoles, true) && ! in_array('ESTETICO', $activeRoles, true)) {
            $activeRoles[] = 'ESTETICO';
        }

        // Bitácora / Auditoría es EXCLUSIVA de Super-Admin (Sección 3)
        if ($module === 'auditoria' || $module === 'usuarios.auditoria') {
            return in_array('SUPER_ADMIN', $activeRoles, true);
        }

        // Super-Admin tiene acceso a todos los módulos operativos y administrativos
        if (in_array('SUPER_ADMIN', $activeRoles, true)) {
            return true;
        }

        $matrix = [
            'usuarios' => ['SUPER_ADMIN'],
            'accesos' => ['SUPER_ADMIN'],
            'auditoria' => ['SUPER_ADMIN'],
            'profesionales' => ['SUPER_ADMIN', 'ADMIN', 'ADMINISTRADOR', 'RECEPCION'],
            'clientes' => ['SUPER_ADMIN', 'ADMIN', 'ADMINISTRADOR', 'RECEPCION', 'VETERINARIO', 'SPA', 'ESTETICO', 'TRANSPORTE'],
            'historial_clinico' => ['SUPER_ADMIN', 'VETERINARIO'],
            'citas' => ['SUPER_ADMIN', 'ADMIN', 'ADMINISTRADOR', 'RECEPCION', 'VETERINARIO', 'SPA', 'ESTETICO'],
            'atenciones' => ['SUPER_ADMIN', 'ADMIN', 'ADMINISTRADOR', 'RECEPCION', 'VETERINARIO', 'SPA', 'ESTETICO'],
            'cirugias' => ['SUPER_ADMIN', 'ADMIN', 'ADMINISTRADOR', 'VETERINARIO'],
            'reportes' => ['SUPER_ADMIN', 'ADMIN', 'ADMINISTRADOR', 'RECEPCION', 'VETERINARIO'],
            'caja' => ['SUPER_ADMIN', 'ADMIN', 'ADMINISTRADOR', 'RECEPCION'],
            'inventario' => ['SUPER_ADMIN', 'ADMIN', 'ADMINISTRADOR', 'VETERINARIO'],
            'compras' => ['SUPER_ADMIN', 'ADMIN', 'ADMINISTRADOR'],
            'petshop' => ['SUPER_ADMIN', 'ADMIN', 'ADMINISTRADOR', 'RECEPCION'],
            'servicios' => ['SUPER_ADMIN', 'ADMIN', 'ADMINISTRADOR'],
            'servicio_domicilio' => ['SUPER_ADMIN', 'ADMIN', 'RECEPCION', 'TRANSPORTE'],
            'paquetes' => ['SUPER_ADMIN', 'ADMIN', 'ADMINISTRADOR'],
            'recordatorios' => ['SUPER_ADMIN', 'ADMIN', 'RECEPCION', 'VETERINARIO'],
            'configuracion' => ['SUPER_ADMIN', 'ADMIN', 'ADMINISTRADOR'],
        ];

        // 1. Verificación por base de datos de permisos dinámicos (Sección 4)
        $normalizedModule = strtolower($module);
        $hasDynamicPermission = $this->roles()
            ->where(function ($q) {
                $q->whereNull('roles.estado')->orWhere('roles.estado', '!=', 'INACTIVO');
            })
            ->whereHas('permisos', function ($qp) use ($normalizedModule, $module) {
                $qp->where('permisos.modulo', 'ilike', $module)
                    ->orWhere('permisos.modulo', 'ilike', $normalizedModule)
                    ->orWhere('permisos.codigo', 'ilike', "%{$normalizedModule}%")
                    ->orWhere('permisos.nombre', 'ilike', 'VER_'.strtoupper($module).'%')
                    ->orWhere('permisos.nombre', 'ilike', 'ACCESO_'.strtoupper($module).'%');
            })
            ->exists();

        if ($hasDynamicPermission) {
            return true;
        }

        // 2. Verificación por matriz de roles por defecto
        if (! isset($matrix[$module])) {
            return false;
        }

        return ! empty(array_intersect($activeRoles, $matrix[$module]));
    }

    public function hasPermission(string $permiso): bool
    {
        $normalize = fn ($str) => strtoupper(str_replace(['á', 'é', 'í', 'ó', 'ú', '-', ' '], ['a', 'e', 'i', 'o', 'u', '_', '_'], (string) $str));
        $realRoles = $this->roles->pluck('nombre')->map($normalize)->all();

        if (in_array('SUPER_ADMIN', $realRoles, true)) {
            return true;
        }

        $activeRoles = ((app()->isLocal() || app()->runningUnitTests()) && session()->has('demo_role'))
            ? [$normalize(session('demo_role'))]
            : $realRoles;

        if (in_array('SUPER_ADMIN', $activeRoles, true)) {
            return true;
        }

        return $this->roles()
            ->where(function ($q) {
                $q->whereNull('roles.estado')->orWhere('roles.estado', '!=', 'INACTIVO');
            })
            ->whereHas('permisos', function ($q) use ($permiso) {
                $q->where('nombre', $permiso)->orWhere('codigo', $permiso);
            })
            ->exists();
    }
}
