<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Mail\VerifyEmailMailable;
use App\Models\Permiso;
use App\Models\Persona;
use App\Models\RegistroAcceso;
use App\Models\Rol;
use App\Models\Usuario;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UsuarioWebController extends Controller
{
    /**
     * Muestra el listado de usuarios con filtros y la matriz de permisos.
     */
    public function index(Request $request): View
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();

        // Verificamos permiso de acceso al módulo según la matriz del Paso 2 (Solo Super-Admin)
        if (! $currentUser->canAccessModule('usuarios')) {
            abort(403, 'Acceso restringido. Este módulo está reservado exclusivamente para Super-Admin.');
        }

        $query = Usuario::with(['persona', 'roles'])->orderBy('id_usuario', 'desc');

        // Filtro por término de búsqueda (nombre_usuario, nombre, apellido o documento)
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre_usuario', 'ilike', "%{$search}%")
                    ->orWhereHas('persona', function ($qp) use ($search) {
                        $qp->where('nombre', 'ilike', "%{$search}%")
                            ->orWhere('apellido1', 'ilike', "%{$search}%")
                            ->orWhere('apellido2', 'ilike', "%{$search}%")
                            ->orWhere('documento', 'ilike', "%{$search}%");
                    });
            });
        }

        // Filtro por Rol
        if ($rolId = $request->input('rol')) {
            $query->whereHas('roles', function ($q) use ($rolId) {
                $q->where('roles.id_rol', $rolId);
            });
        }

        // Filtro por Estado (ACTIVO / INACTIVO)
        if ($estado = $request->input('estado')) {
            $query->where('estado', $estado);
        }

        $usuarios = $query->paginate(10)->withQueryString();
        $roles = Rol::orderBy('nombre')->get();
        $permisos = Permiso::orderBy('nombre')->get();
        $rolesConPermisos = Rol::with('permisos')->orderBy('id_rol')->get();

        return view('usuarios.index', [
            'usuarios' => $usuarios,
            'roles' => $roles,
            'permisos' => $permisos,
            'rolesConPermisos' => $rolesConPermisos,
            'activeTab' => $request->input('tab', 'usuarios'),
            'filters' => [
                'search' => $request->input('search'),
                'rol' => $request->input('rol'),
                'estado' => $request->input('estado'),
            ],
        ]);
    }

    /**
     * Registra un nuevo usuario y su persona vinculada.
     */
    public function store(Request $request): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('usuarios')) {
            abort(403, 'No autorizado.');
        }

        $validated = $request->validate([
            'documento' => ['required', 'string', 'max:20'],
            'nombre' => ['required', 'string', 'max:50'],
            'apellido1' => ['required', 'string', 'max:50'],
            'apellido2' => ['nullable', 'string', 'max:50'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:100'],
            'direccion' => ['nullable', 'string', 'max:200'],
            'nombre_usuario' => ['required', 'string', 'max:50', 'unique:usuarios,nombre_usuario'],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
            'id_rol' => ['required', 'integer', 'exists:roles,id_rol'],
            'estado' => ['required', 'string', 'in:ACTIVO,INACTIVO'],
        ], [
            'email.required' => 'El correo electrónico es obligatorio para vincular la cuenta y permitir la recuperación por OTP.',
            'nombre_usuario.unique' => 'Este nombre de usuario ya está en uso.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
        ]);

        $existingPersona = Persona::where('documento', $validated['documento'])->first();
        if ($existingPersona && Usuario::where('id_persona', $existingPersona->id_persona)->exists()) {
            return back()->withErrors(['documento' => 'Ya existe un usuario con este documento de identidad.'])->withInput();
        }

        $activationOtp = sprintf('%06d', random_int(100000, 999999));

        DB::transaction(function () use ($validated, $existingPersona, $activationOtp) {
            if ($existingPersona) {
                $existingPersona->update([
                    'nombre' => $validated['nombre'],
                    'apellido1' => $validated['apellido1'],
                    'apellido2' => $validated['apellido2'] ?? null,
                    'telefono' => $validated['telefono'] ?? null,
                    'email' => $validated['email'] ?? null,
                    'direccion' => $validated['direccion'] ?? null,
                ]);
                $persona = $existingPersona;
            } else {
                $persona = Persona::create([
                    'documento' => $validated['documento'],
                    'nombre' => $validated['nombre'],
                    'apellido1' => $validated['apellido1'],
                    'apellido2' => $validated['apellido2'] ?? null,
                    'telefono' => $validated['telefono'] ?? null,
                    'email' => $validated['email'] ?? null,
                    'direccion' => $validated['direccion'] ?? null,
                ]);
            }

            $usuario = Usuario::create([
                'id_persona' => $persona->id_persona,
                'nombre_usuario' => $validated['nombre_usuario'],
                'password_hash' => Hash::make($validated['password']),
                'estado' => $validated['estado'],
                'email_verified_at' => $validated['estado'] === 'ACTIVO' ? now() : null,
                'email_verification_token' => $activationOtp,
                'email_verification_expires_at' => now()->addHours(24),
            ]);

            $usuario->roles()->attach($validated['id_rol']);

            // Enviar correo con código OTP de activación de 6 dígitos (válido por 24h)
            if (! empty($validated['email'])) {
                try {
                    Mail::to($validated['email'])->send(new VerifyEmailMailable(
                        $activationOtp,
                        $usuario->nombre_usuario,
                        $persona->nombre.' '.$persona->apellido1,
                        24
                    ));
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        });

        $emailSent = ! empty($validated['email']);

        return redirect()->route('usuarios.index')
            ->with('success', $emailSent
                ? "Usuario '{$validated['nombre_usuario']}' registrado exitosamente. Se envió un código de activación OTP al correo {$validated['email']} (válido por 24 horas)."
                : "Usuario '{$validated['nombre_usuario']}' registrado exitosamente."
            );
    }

    /**
     * Actualiza los datos de un usuario existente.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('usuarios')) {
            abort(403, 'No autorizado.');
        }

        $usuario = Usuario::with('persona')->findOrFail($id);
        $persona = $usuario->persona;

        $validated = $request->validate([
            'documento' => ['required', 'string', 'max:20', Rule::unique('personas', 'documento')->ignore($persona->id_persona, 'id_persona')],
            'nombre' => ['required', 'string', 'max:50'],
            'apellido1' => ['required', 'string', 'max:50'],
            'apellido2' => ['nullable', 'string', 'max:50'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:100'],
            'direccion' => ['nullable', 'string', 'max:200'],
            'nombre_usuario' => ['required', 'string', 'max:50', Rule::unique('usuarios', 'nombre_usuario')->ignore($usuario->id_usuario, 'id_usuario')],
            'password' => ['nullable', 'string', 'confirmed', Password::defaults()],
            'id_rol' => ['required', 'integer', 'exists:roles,id_rol'],
            'estado' => ['required', 'string', 'in:ACTIVO,INACTIVO'],
        ], [
            'email.required' => 'El correo electrónico es obligatorio para vincular la cuenta y permitir la recuperación por OTP.',
            'documento.unique' => 'Ya existe una persona registrada con este documento.',
            'nombre_usuario.unique' => 'Este nombre de usuario ya está en uso.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
        ]);

        DB::transaction(function () use ($usuario, $persona, $validated) {
            $persona->update([
                'documento' => $validated['documento'],
                'nombre' => $validated['nombre'],
                'apellido1' => $validated['apellido1'],
                'apellido2' => $validated['apellido2'] ?? null,
                'telefono' => $validated['telefono'] ?? null,
                'email' => $validated['email'] ?? null,
                'direccion' => $validated['direccion'] ?? null,
            ]);

            $userData = [
                'nombre_usuario' => $validated['nombre_usuario'],
                'estado' => $validated['estado'],
            ];

            if (! empty($validated['password'])) {
                $userData['password_hash'] = Hash::make($validated['password']);
            }

            $usuario->update($userData);
            $usuario->roles()->sync([$validated['id_rol']]);

            AuditService::log('MODIFICAR_USUARIO', 'USUARIOS', [
                'usuario' => $usuario->nombre_usuario,
                'id_rol' => $validated['id_rol'],
                'estado' => $validated['estado'],
            ], $usuario);
        });

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    /**
     * Acción de baja lógica: alterna entre ACTIVO e INACTIVO sin eliminar el registro.
     */
    public function toggleEstado(int $id): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('usuarios')) {
            abort(403, 'No autorizado.');
        }

        $usuario = Usuario::findOrFail($id);

        // Prevenir desactivarse a sí mismo
        if ($usuario->id_usuario === $currentUser->id_usuario) {
            return back()->with('error', 'No puedes desactivar tu propia cuenta activa.');
        }

        $nuevoEstado = $usuario->estado === 'ACTIVO' ? 'INACTIVO' : 'ACTIVO';
        $usuario->update(['estado' => $nuevoEstado]);

        AuditService::log('CAMBIO_ESTADO_USUARIO', 'USUARIOS', [
            'usuario' => $usuario->nombre_usuario,
            'nuevo_estado' => $nuevoEstado,
        ], $usuario);

        $accion = $nuevoEstado === 'ACTIVO' ? 'activado' : 'desactivado (baja lógica)';

        return redirect()->route('usuarios.index')
            ->with('success', "Usuario {$usuario->nombre_usuario} {$accion} correctamente.");
    }

    /**
     * Actualiza la matriz de permisos por rol.
     */
    public function updatePermisos(Request $request): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('usuarios')) {
            abort(403, 'No autorizado.');
        }

        $permisosPorRol = $request->input('permisos', []);

        DB::transaction(function () use ($permisosPorRol, $currentUser, $request) {
            $roles = Rol::all();
            foreach ($roles as $rol) {
                if (array_key_exists($rol->id_rol, $permisosPorRol) || $request->has('permisos_todos')) {
                    $permisosIds = $permisosPorRol[$rol->id_rol] ?? [];
                    $rol->permisos()->sync($permisosIds);
                }
            }

            AuditService::log(
                'ACTUALIZAR_MATRIZ_PERMISOS',
                'USUARIOS',
                ['roles_afectados' => array_keys($permisosPorRol)],
                null,
                $currentUser->id_usuario,
                'EXITO',
                'Matriz de privilegios por grupos actualizada desde la interfaz'
            );
        });

        return redirect()->route('usuarios.index', ['tab' => 'roles'])
            ->with('success', 'Matriz de permisos actualizada exitosamente.');
    }

    /**
     * Registra un nuevo rol institucional en el sistema.
     */
    public function storeRol(Request $request): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('usuarios')) {
            abort(403, 'No autorizado.');
        }

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:50', 'unique:roles,nombre'],
            'descripcion' => ['nullable', 'string', 'max:255'],
        ], [
            'nombre.required' => 'El nombre del rol es obligatorio.',
            'nombre.unique' => 'Ya existe un rol registrado con este nombre.',
        ]);

        $nombre = strtoupper(trim(str_replace(' ', '_', $validated['nombre'])));

        $rol = Rol::create([
            'nombre' => $nombre,
            'descripcion' => $validated['descripcion'] ?? null,
            'estado' => 'ACTIVO',
        ]);

        AuditService::log(
            'CREAR_ROL',
            'USUARIOS',
            ['nombre' => $nombre, 'descripcion' => $validated['descripcion'] ?? null],
            (string) $rol->id_rol,
            $currentUser->id_usuario,
            'EXITO',
            "Nuevo grupo de rol creado: {$nombre}"
        );

        return redirect()->route('usuarios.index', ['tab' => 'roles'])
            ->with('success', "Rol '{$nombre}' creado exitosamente.");
    }

    /**
     * Actualiza los datos de un rol institucional existente.
     */
    public function updateRol(Request $request, int $id): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('usuarios')) {
            abort(403, 'No autorizado.');
        }

        $rol = Rol::findOrFail($id);

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:50', Rule::unique('roles', 'nombre')->ignore($rol->id_rol, 'id_rol')],
            'descripcion' => ['nullable', 'string', 'max:255'],
        ], [
            'nombre.required' => 'El nombre del rol es obligatorio.',
            'nombre.unique' => 'Ya existe un rol con este nombre.',
        ]);

        $nombre = strtoupper(trim(str_replace(' ', '_', $validated['nombre'])));

        $rol->update([
            'nombre' => $nombre,
            'descripcion' => $validated['descripcion'] ?? null,
        ]);

        AuditService::log(
            'MODIFICAR_ROL',
            'USUARIOS',
            ['id_rol' => $rol->id_rol, 'nuevo_nombre' => $nombre],
            (string) $rol->id_rol,
            $currentUser->id_usuario,
            'EXITO',
            "Grupo de rol #{$rol->id_rol} actualizado a: {$nombre}"
        );

        return redirect()->route('usuarios.index', ['tab' => 'roles'])
            ->with('success', "Rol '{$nombre}' actualizado correctamente.");
    }

    /**
     * Activa o desactiva un rol/grupo (baja lógica de grupo).
     */
    public function toggleRolEstado(int $id): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('usuarios')) {
            abort(403, 'No tienes privilegios para administrar grupos de roles.');
        }

        $rol = Rol::findOrFail($id);

        if ($rol->isSuperAdmin()) {
            return redirect()->route('usuarios.index', ['tab' => 'roles'])
                ->with('error', 'El grupo SUPER_ADMIN es vital para el sistema y no puede ser desactivado.');
        }

        $nuevoEstado = $rol->estado === 'ACTIVO' ? 'INACTIVO' : 'ACTIVO';
        $rol->update(['estado' => $nuevoEstado]);

        AuditService::log(
            'CAMBIO_ESTADO_ROL',
            'USUARIOS',
            ['id_rol' => $rol->id_rol, 'nombre' => $rol->nombre, 'nuevo_estado' => $nuevoEstado],
            (string) $rol->id_rol,
            $currentUser->id_usuario,
            'EXITO',
            "Estado del grupo {$rol->nombre} cambiado a {$nuevoEstado}"
        );

        return redirect()->route('usuarios.index', ['tab' => 'roles'])
            ->with('success', "Grupo {$rol->nombre} marcado como {$nuevoEstado}.");
    }

    /**
     * Muestra la bitácora y registro de accesos (sesiones) para el Super-Administrador.
     */
    public function accesos(Request $request): View
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('usuarios')) {
            abort(403, 'Acceso restringido. Este módulo está reservado exclusivamente para Super-Admin.');
        }

        $query = RegistroAcceso::with('usuario.persona')->orderBy('id_acceso', 'desc');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre_usuario', 'ilike', "%{$search}%")
                    ->orWhere('rol', 'ilike', "%{$search}%")
                    ->orWhere('ip_address', 'ilike', "%{$search}%");
            });
        }

        if ($rol = $request->input('rol')) {
            $query->where('rol', $rol);
        }

        if ($fecha = $request->input('fecha')) {
            $query->whereDate('fecha_ingreso', $fecha);
        }

        if ($estado = $request->input('estado')) {
            $query->where('estado', $estado);
        }

        $accesos = $query->paginate(15)->withQueryString();
        $totalAccesos = RegistroAcceso::count();
        $sesionesActivas = RegistroAcceso::where('estado', 'ACTIVA')->count();
        $accesosHoy = RegistroAcceso::whereDate('fecha_ingreso', today())->count();

        return view('usuarios.accesos', [
            'accesos' => $accesos,
            'totalAccesos' => $totalAccesos,
            'sesionesActivas' => $sesionesActivas,
            'accesosHoy' => $accesosHoy,
            'filters' => [
                'search' => $request->input('search'),
                'rol' => $request->input('rol'),
                'fecha' => $request->input('fecha'),
                'estado' => $request->input('estado'),
            ],
        ]);
    }
}
