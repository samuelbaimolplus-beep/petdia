<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\HorarioProfesional;
use App\Models\Persona;
use App\Models\Profesional;
use App\Models\Rol;
use App\Models\Servicio;
use App\Models\Usuario;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfesionalWebController extends Controller
{
    /**
     * Listado general de profesionales con filtros y métricas.
     */
    public function index(Request $request): View
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('profesionales')) {
            abort(403, 'No tienes permisos para ver el personal profesional.');
        }

        $query = Profesional::with(['persona', 'usuario.roles', 'servicios', 'horarios', 'bloqueosAgenda']);

        // Búsqueda por nombre, apellido, CI o registro profesional
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('registro_profesional', 'ilike', "%{$search}%")
                    ->orWhere('especialidad', 'ilike', "%{$search}%")
                    ->orWhereHas('persona', function ($qp) use ($search) {
                        $qp->where('nombre', 'ilike', "%{$search}%")
                            ->orWhere('apellido1', 'ilike', "%{$search}%")
                            ->orWhere('documento', 'ilike', "%{$search}%");
                    });
            });
        }

        // Filtro por especialidad
        if ($especialidad = $request->input('especialidad')) {
            $query->where('especialidad', $especialidad);
        }

        // Filtro por estado
        if ($estado = $request->input('estado')) {
            $query->where('estado', $estado);
        }

        $profesionales = $query->orderBy('id_persona', 'desc')->paginate(12)->withQueryString();

        // Métricas
        $totalCount = Profesional::count();
        $activosCount = Profesional::where('estado', 'ACTIVO')->count();
        $especialidades = Profesional::distinct()->pluck('especialidad')->filter()->values();
        $serviciosDisponibles = Servicio::where('estado', 'ACTIVO')->orderBy('nombre')->get();

        // Usuarios del sistema que aún no son profesionales (para vincular sin reescribir datos)
        $profesionalesPersonaIds = Profesional::pluck('id_persona')->toArray();
        $usuariosDisponibles = Usuario::with(['persona', 'roles'])
            ->whereNotIn('id_persona', $profesionalesPersonaIds)
            ->where('estado', 'ACTIVO')
            ->orderBy('nombre_usuario')
            ->get();

        $rolesDisponibles = Rol::orderBy('nombre')->get();

        $normalize = fn ($str) => strtoupper(str_replace(['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú', '-', ' '], ['a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U', '_', '_'], $str));
        $userRoles = $currentUser->roles->pluck('nombre')->map($normalize)->all();
        $isReadOnly = in_array('RECEPCION', $userRoles, true) && ! in_array('ADMIN', $userRoles, true) && ! in_array('SUPER_ADMIN', $userRoles, true);

        return view('profesionales.index', [
            'profesionales' => $profesionales,
            'especialidades' => $especialidades,
            'serviciosDisponibles' => $serviciosDisponibles,
            'usuariosDisponibles' => $usuariosDisponibles,
            'rolesDisponibles' => $rolesDisponibles,
            'isReadOnly' => $isReadOnly,
            'metrics' => [
                'total' => $totalCount,
                'activos' => $activosCount,
                'especialidades_count' => $especialidades->count(),
            ],
            'filters' => [
                'search' => $search,
                'especialidad' => $especialidad,
                'estado' => $estado,
            ],
        ]);
    }

    /**
     * Registra un nuevo profesional con opción de vincular usuario existente o crear nuevo usuario en 1 solo paso.
     */
    public function store(Request $request): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('profesionales')) {
            abort(403);
        }

        $validated = $request->validate([
            'id_usuario_existente' => ['nullable', 'integer', 'exists:usuarios,id_usuario'],
            'documento' => ['required_without:id_usuario_existente', 'nullable', 'string', 'max:20'],
            'nombre' => ['required_without:id_usuario_existente', 'nullable', 'string', 'max:50'],
            'apellido1' => ['required_without:id_usuario_existente', 'nullable', 'string', 'max:50'],
            'apellido2' => ['nullable', 'string', 'max:50'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'email' => ['required_without:id_usuario_existente', 'nullable', 'email', 'max:100'],
            'direccion' => ['nullable', 'string', 'max:200'],
            'especialidad' => ['required', 'string', 'max:100'],
            'registro_profesional' => ['required', 'string', 'max:50', 'unique:profesionales,registro_profesional'],
            'estado' => ['required', 'string', 'in:ACTIVO,INACTIVO'],
            'servicios' => ['nullable', 'array'],
            'servicios.*' => ['integer', 'exists:servicios,id_servicio'],

            // Datos para creación simultánea de cuenta de usuario
            'crear_usuario' => ['nullable'],
            'nombre_usuario' => ['required_if:crear_usuario,1,true,on', 'nullable', 'string', 'max:50', 'unique:usuarios,nombre_usuario'],
            'password' => ['required_if:crear_usuario,1,true,on', 'nullable', 'string', 'min:6'],
            'id_rol' => ['required_if:crear_usuario,1,true,on', 'nullable', 'integer', 'exists:roles,id_rol'],
        ], [
            'email.required_without' => 'El correo electrónico es obligatorio para el registro del profesional.',
            'nombre_usuario.required_if' => 'El nombre de usuario es requerido para habilitar el acceso al sistema.',
            'nombre_usuario.unique' => 'Este nombre de usuario ya está en uso.',
            'password.required_if' => 'Debes ingresar una contraseña para la cuenta del usuario.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
            'registro_profesional.unique' => 'Ya existe un profesional con este número de registro.',
        ]);

        $crearCuenta = $request->boolean('crear_usuario') || $request->input('crear_usuario') === '1' || $request->input('crear_usuario') === 'on';

        $resultado = DB::transaction(function () use ($validated, $crearCuenta) {
            $nuevoUsuario = null;
            $usuarioVinculado = null;

            if (! empty($validated['id_usuario_existente'])) {
                $usuarioVinculado = Usuario::with('persona')->findOrFail($validated['id_usuario_existente']);

                if (Profesional::where('id_persona', $usuarioVinculado->id_persona)->exists()) {
                    abort(422, 'El usuario seleccionado ya está registrado como profesional.');
                }

                $persona = $usuarioVinculado->persona;
                if (! $persona) {
                    $persona = Persona::create([
                        'nombre' => $usuarioVinculado->nombre_usuario,
                        'apellido1' => 'Profesional',
                        'documento' => 'CI-USR-'.$usuarioVinculado->id_usuario,
                        'email' => $validated['email'] ?? null,
                        'telefono' => $validated['telefono'] ?? null,
                    ]);
                    $usuarioVinculado->update(['id_persona' => $persona->id_persona]);
                } else {
                    if (! empty($validated['telefono']) || ! empty($validated['email']) || ! empty($validated['direccion'])) {
                        $persona->update(array_filter([
                            'telefono' => $validated['telefono'] ?? null,
                            'email' => $validated['email'] ?? null,
                            'direccion' => $validated['direccion'] ?? null,
                        ]));
                    }
                }
            } else {
                $persona = Persona::firstOrCreate(
                    ['documento' => $validated['documento']],
                    [
                        'nombre' => $validated['nombre'],
                        'apellido1' => $validated['apellido1'],
                        'apellido2' => $validated['apellido2'] ?? null,
                        'telefono' => $validated['telefono'] ?? null,
                        'email' => $validated['email'] ?? null,
                        'direccion' => $validated['direccion'] ?? null,
                    ]
                );

                if (Profesional::where('id_persona', $persona->id_persona)->exists()) {
                    abort(422, 'Ya existe un profesional registrado con este documento de identidad.');
                }

                if ($crearCuenta && ! empty($validated['nombre_usuario'])) {
                    $nuevoUsuario = Usuario::create([
                        'id_persona' => $persona->id_persona,
                        'nombre_usuario' => $validated['nombre_usuario'],
                        'password_hash' => Hash::make($validated['password']),
                        'estado' => 'ACTIVO',
                        'email_verified_at' => now(),
                    ]);

                    if (! empty($validated['id_rol'])) {
                        $nuevoUsuario->roles()->sync([$validated['id_rol']]);
                    }
                }
            }

            $profesional = Profesional::create([
                'id_persona' => $persona->id_persona,
                'especialidad' => $validated['especialidad'],
                'registro_profesional' => $validated['registro_profesional'],
                'estado' => $validated['estado'],
            ]);

            if (! empty($validated['servicios'])) {
                $profesional->servicios()->sync($validated['servicios']);
            }

            // Horarios semanales por defecto (Lunes a Viernes de 8:00 a 16:00)
            $dias = [1, 2, 3, 4, 5];
            foreach ($dias as $diaNum) {
                HorarioProfesional::create([
                    'id_profesional' => $profesional->id_persona,
                    'dia_semana' => $diaNum,
                    'hora_inicio' => '08:00:00',
                    'hora_fin' => '16:00:00',
                    'estado' => 'ACTIVO',
                ]);
            }

            AuditService::log('REGISTRAR_PROFESIONAL', 'PROFESIONALES', [
                'id_persona' => $profesional->id_persona,
                'especialidad' => $profesional->especialidad,
                'registro' => $profesional->registro_profesional,
                'usuario_creado' => $nuevoUsuario?->nombre_usuario,
                'usuario_vinculado' => $usuarioVinculado?->nombre_usuario,
            ], $profesional);

            return [
                'profesional' => $profesional,
                'usuario' => $nuevoUsuario ?? $usuarioVinculado,
                'fue_creado' => (bool) $nuevoUsuario,
            ];
        });

        $msg = 'Profesional registrado exitosamente.';
        if ($resultado['fue_creado']) {
            $msg = "¡Profesional registrado y cuenta de usuario '{$resultado['usuario']->nombre_usuario}' creada exitosamente!";
        } elseif ($resultado['usuario']) {
            $msg = "¡Profesional registrado y vinculado a la cuenta existente '{$resultado['usuario']->nombre_usuario}'!";
        }

        return redirect()->route('profesionales.show', $resultado['profesional']->id_persona)
            ->with('success', $msg);
    }

    /**
     * Muestra la ficha detallada del profesional con horarios, servicios y bloqueos.
     */
    public function show(int $id): View
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('profesionales')) {
            abort(403);
        }

        $profesional = Profesional::with([
            'persona',
            'usuario.roles',
            'servicios',
            'horarios' => fn ($q) => $q->orderBy('id_horario'),
            'bloqueosAgenda' => fn ($q) => $q->orderBy('fecha_hora_inicio', 'desc'),
        ])->findOrFail($id);

        $todosServicios = Servicio::where('estado', 'ACTIVO')->orderBy('nombre')->get();
        $rolesDisponibles = Rol::orderBy('nombre')->get();

        $normalize = fn ($str) => strtoupper(str_replace(['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú', '-', ' '], ['a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U', '_', '_'], $str));
        $userRoles = $currentUser->roles->pluck('nombre')->map($normalize)->all();
        $isReadOnly = in_array('RECEPCION', $userRoles, true) && ! in_array('ADMIN', $userRoles, true) && ! in_array('SUPER_ADMIN', $userRoles, true);

        return view('profesionales.show', [
            'profesional' => $profesional,
            'todosServicios' => $todosServicios,
            'rolesDisponibles' => $rolesDisponibles,
            'isReadOnly' => $isReadOnly,
        ]);
    }

    /**
     * Crea y vincula una cuenta de usuario para un profesional que aún no la tenga.
     */
    public function storeUsuario(Request $request, int $id): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('profesionales') && ! $currentUser->canAccessModule('usuarios')) {
            abort(403);
        }

        $profesional = Profesional::with('persona')->findOrFail($id);

        if (Usuario::where('id_persona', $profesional->id_persona)->exists()) {
            return back()->withErrors(['usuario' => 'Este profesional ya cuenta con una cuenta de usuario en el sistema.']);
        }

        $validated = $request->validate([
            'nombre_usuario' => ['required', 'string', 'max:50', 'unique:usuarios,nombre_usuario'],
            'password' => ['required', 'string', 'min:6'],
            'id_rol' => ['required', 'integer', 'exists:roles,id_rol'],
        ], [
            'nombre_usuario.required' => 'El nombre de usuario es obligatorio.',
            'nombre_usuario.unique' => 'Este nombre de usuario ya está en uso.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
            'id_rol.required' => 'Debes asignar un rol al usuario.',
        ]);

        $usuario = Usuario::create([
            'id_persona' => $profesional->id_persona,
            'nombre_usuario' => $validated['nombre_usuario'],
            'password_hash' => Hash::make($validated['password']),
            'estado' => 'ACTIVO',
            'email_verified_at' => now(),
        ]);

        $usuario->roles()->sync([$validated['id_rol']]);

        AuditService::log('CREAR_USUARIO_PROFESIONAL', 'PROFESIONALES', [
            'id_persona' => $profesional->id_persona,
            'profesional' => $profesional->persona->nombre.' '.$profesional->persona->apellido1,
            'nombre_usuario' => $usuario->nombre_usuario,
        ], $usuario);

        return back()->with('success', "¡Cuenta de usuario '{$usuario->nombre_usuario}' creada y vinculada correctamente al profesional!");
    }

    /**
     * Actualiza datos personales y profesionales.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('profesionales')) {
            abort(403);
        }

        $profesional = Profesional::with('persona')->findOrFail($id);

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:50'],
            'apellido1' => ['required', 'string', 'max:50'],
            'apellido2' => ['nullable', 'string', 'max:50'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:100'],
            'direccion' => ['nullable', 'string', 'max:200'],
            'especialidad' => ['required', 'string', 'max:100'],
            'registro_profesional' => ['required', 'string', 'max:50', Rule::unique('profesionales', 'registro_profesional')->ignore($profesional->id_persona, 'id_persona')],
            'estado' => ['required', 'string', 'in:ACTIVO,INACTIVO'],
        ], [
            'email.required' => 'El correo electrónico es obligatorio para vincular al profesional con su cuenta y permitir recuperación de acceso.',
        ]);

        DB::transaction(function () use ($profesional, $validated) {
            $profesional->persona->update([
                'nombre' => $validated['nombre'],
                'apellido1' => $validated['apellido1'],
                'apellido2' => $validated['apellido2'] ?? null,
                'telefono' => $validated['telefono'] ?? null,
                'email' => $validated['email'] ?? null,
                'direccion' => $validated['direccion'] ?? null,
            ]);

            $profesional->update([
                'especialidad' => $validated['especialidad'],
                'registro_profesional' => $validated['registro_profesional'],
                'estado' => $validated['estado'],
            ]);
        });

        return back()->with('success', 'Ficha del profesional actualizada correctamente.');
    }

    /**
     * Sincroniza los servicios que el profesional está habilitado para realizar.
     */
    public function updateServicios(Request $request, int $id): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('profesionales')) {
            abort(403);
        }

        $profesional = Profesional::findOrFail($id);
        $servicios = $request->input('servicios', []);

        $profesional->servicios()->sync($servicios);

        return back()->with('success', 'Servicios habilitados actualizados.');
    }

    /**
     * Actualiza o agrega un turno en el horario semanal de trabajo.
     */
    public function storeHorario(Request $request, int $id): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('profesionales')) {
            abort(403);
        }

        $validated = $request->validate([
            'dia_semana' => ['required'],
            'hora_inicio' => ['required', 'string'],
            'hora_fin' => ['required', 'string'],
            'estado' => ['required', 'string', 'in:ACTIVO,INACTIVO'],
        ]);

        $mapDias = [
            'Lunes' => 1, '1' => 1, 1 => 1,
            'Martes' => 2, '2' => 2, 2 => 2,
            'Miércoles' => 3, 'Miercoles' => 3, '3' => 3, 3 => 3,
            'Jueves' => 4, '4' => 4, 4 => 4,
            'Viernes' => 5, '5' => 5, 5 => 5,
            'Sábado' => 6, 'Sabado' => 6, '6' => 6, 6 => 6,
            'Domingo' => 7, '7' => 7, 7 => 7,
        ];
        $diaNum = $mapDias[$validated['dia_semana']] ?? 1;

        // Validación de coherencia de horario (CU29)
        if ($validated['hora_fin'] <= $validated['hora_inicio']) {
            return back()->withInput()->withErrors([
                'hora_fin' => 'La hora de finalización debe ser posterior a la hora de inicio.',
            ]);
        }

        // Validación de solapamiento con otros turnos del mismo día
        $solapamientoTurno = HorarioProfesional::where('id_profesional', $id)
            ->where('dia_semana', $diaNum)
            ->where(function ($q) use ($validated) {
                $q->where('hora_inicio', '<', $validated['hora_fin'])
                    ->where('hora_fin', '>', $validated['hora_inicio']);
            })
            ->exists();

        if ($solapamientoTurno) {
            return back()->withInput()->withErrors([
                'hora_inicio' => 'El horario ingresado se solapa con otro turno ya existente para este mismo día.',
            ]);
        }

        $horario = HorarioProfesional::create([
            'id_profesional' => $id,
            'dia_semana' => $diaNum,
            'hora_inicio' => $validated['hora_inicio'],
            'hora_fin' => $validated['hora_fin'],
            'estado' => $validated['estado'],
        ]);

        AuditService::log(
            'REGISTRAR_HORARIO',
            'PROFESIONALES',
            [
                'id_profesional' => $id,
                'dia_semana' => $diaNum,
                'hora_inicio' => $validated['hora_inicio'],
                'hora_fin' => $validated['hora_fin'],
            ],
            $horario,
            null,
            'EXITO',
            "Horario registrado para el profesional #{$id} en día {$diaNum} ({$validated['hora_inicio']} - {$validated['hora_fin']})"
        );

        return back()->with('success', 'Turno horario agregado al cronograma semanal.');
    }

    /**
     * Elimina un turno del horario semanal.
     */
    public function deleteHorario(int $id): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('profesionales')) {
            abort(403);
        }

        $horario = HorarioProfesional::findOrFail($id);
        $profId = $horario->id_profesional;

        AuditService::log(
            'ELIMINAR_HORARIO',
            'PROFESIONALES',
            [
                'id_horario' => $id,
                'id_profesional' => $profId,
                'dia_semana' => $horario->dia_semana,
            ],
            $horario,
            null,
            'EXITO',
            "Horario eliminado para el profesional #{$profId}"
        );

        $horario->delete();

        return back()->with('success', 'Turno eliminado del horario semanal.');
    }

    /**
     * Baja lógica del profesional (Activar / Desactivar).
     */
    public function toggleEstado(int $id): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('profesionales')) {
            abort(403);
        }

        $profesional = Profesional::findOrFail($id);
        $nuevo = $profesional->estado === 'ACTIVO' ? 'INACTIVO' : 'ACTIVO';
        $profesional->update(['estado' => $nuevo]);

        $accion = $nuevo === 'ACTIVO' ? 'activado' : 'desactivado (baja lógica)';

        return back()->with('success', "Profesional {$accion} correctamente.");
    }
}
