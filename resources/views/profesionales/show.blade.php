@extends('layouts.app')

@section('title', $profesional->persona->nombre . ' ' . $profesional->persona->apellido1 . ' — Ficha Profesional')

@section('content')
<div class="space-y-6" x-data="{ tab: 'datos', nuevoHorarioModal: false, modalCrearUsuario: false }">
    {{-- Breadcrumb y Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('profesionales.index') }}" class="hover:text-indigo-600 transition-colors">Profesionales</a>
                <span>/</span>
                <span class="text-slate-700 font-medium">{{ $profesional->persona->nombre }} {{ $profesional->persona->apellido1 }}</span>
            </div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">
                    {{ $profesional->persona->nombre }} {{ $profesional->persona->apellido1 }} {{ $profesional->persona->apellido2 }}
                </h1>
                <x-state-badge :status="$profesional->estado" />
            </div>
            <p class="text-sm text-slate-500 mt-1">Especialidad: <span class="font-medium text-slate-700">{{ $profesional->especialidad }}</span> · Matrícula: <span class="font-mono text-slate-700">{{ $profesional->registro_profesional }}</span></p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('profesionales.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2 border border-slate-200 text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-50 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Volver</span>
            </a>
            @if (! $isReadOnly)
                <form method="POST" action="{{ route('profesionales.toggle-estado', $profesional->id_persona) }}" onsubmit="return confirm('¿Deseas cambiar el estado de este profesional?');">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 px-3.5 py-2 border border-slate-200 text-slate-700 hover:text-amber-600 text-sm font-semibold rounded-xl hover:bg-slate-50 transition-colors">
                        <span>{{ $profesional->estado === 'ACTIVO' ? 'Desactivar (Baja Lógica)' : 'Reactivar Profesional' }}</span>
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- Tabs de Navegación interna --}}
    <div class="border-b border-slate-200">
        <nav class="flex space-x-6" aria-label="Tabs">
            <button type="button" @click="tab = 'datos'" :class="tab === 'datos' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'" class="whitespace-nowrap py-3 px-1 border-b-2 font-semibold text-sm transition-colors">
                Datos Generales y Profesionales
            </button>
            <button type="button" @click="tab = 'servicios'" :class="tab === 'servicios' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'" class="whitespace-nowrap py-3 px-1 border-b-2 font-semibold text-sm transition-colors">
                Servicios Habilitados ({{ $profesional->servicios->count() }})
            </button>
            <button type="button" @click="tab = 'horarios'" :class="tab === 'horarios' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'" class="whitespace-nowrap py-3 px-1 border-b-2 font-semibold text-sm transition-colors">
                Horario Semanal ({{ $profesional->horarios->count() }})
            </button>
            <button type="button" @click="tab = 'bloqueos'" :class="tab === 'bloqueos' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'" class="whitespace-nowrap py-3 px-1 border-b-2 font-semibold text-sm transition-colors">
                Bloqueos y Ausencias ({{ $profesional->bloqueosAgenda->count() }})
            </button>
        </nav>
    </div>

    {{-- TAB 1: DATOS GENERALES --}}
    <div x-show="tab === 'datos'" class="space-y-6">
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs p-6">
            <h2 class="text-base font-bold text-slate-900 mb-4">Información del Especialista</h2>
            @if ($isReadOnly)
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-xs font-semibold text-slate-400">Documento / CI</span>
                        <p class="font-medium text-slate-800">{{ $profesional->persona->documento }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-slate-400">Nombre Completo</span>
                        <p class="font-medium text-slate-800">{{ $profesional->persona->nombre }} {{ $profesional->persona->apellido1 }} {{ $profesional->persona->apellido2 }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-slate-400">Especialidad</span>
                        <p class="font-medium text-slate-800">{{ $profesional->especialidad }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-slate-400">Registro Profesional</span>
                        <p class="font-mono text-slate-800">{{ $profesional->registro_profesional }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-slate-400">Teléfono</span>
                        <p class="font-medium text-slate-800">{{ $profesional->persona->telefono ?? 'No registrado' }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-slate-400">Correo Electrónico</span>
                        <p class="font-medium text-slate-800 flex items-center gap-1.5">
                            <span>{{ $profesional->persona->email ?? 'No registrado' }}</span>
                            @if($profesional->persona->email)
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">OTP Activo</span>
                            @endif
                        </p>
                    </div>
                    <div class="sm:col-span-2">
                        <span class="text-xs font-semibold text-slate-400">Dirección</span>
                        <p class="font-medium text-slate-800">{{ $profesional->persona->direccion ?? 'No registrada' }}</p>
                    </div>
                </div>
            @else
                <form method="POST" action="{{ route('profesionales.update', $profesional->id_persona) }}" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Documento / CI (Solo lectura)</label>
                            <input type="text" value="{{ $profesional->persona->documento }}" disabled class="w-full text-sm px-3 py-2 border border-slate-200 rounded-lg bg-slate-50 text-slate-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Nombres *</label>
                            <input type="text" name="nombre" value="{{ old('nombre', $profesional->persona->nombre) }}" required class="w-full text-sm px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Primer Apellido *</label>
                            <input type="text" name="apellido1" value="{{ old('apellido1', $profesional->persona->apellido1) }}" required class="w-full text-sm px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Segundo Apellido</label>
                            <input type="text" name="apellido2" value="{{ old('apellido2', $profesional->persona->apellido2) }}" class="w-full text-sm px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Especialidad *</label>
                            <input type="text" name="especialidad" value="{{ old('especialidad', $profesional->especialidad) }}" required class="w-full text-sm px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Registro Profesional / Matrícula *</label>
                            <input type="text" name="registro_profesional" value="{{ old('registro_profesional', $profesional->registro_profesional) }}" required class="w-full text-sm px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Teléfono</label>
                            <input type="text" name="telefono" value="{{ old('telefono', $profesional->persona->telefono) }}" class="w-full text-sm px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Correo Electrónico *</label>
                            <input type="email" name="email" value="{{ old('email', $profesional->persona->email) }}" required class="w-full text-sm px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            <span class="text-[10px] text-slate-400">Enlazado para recuperación OTP de contraseña</span>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Dirección</label>
                            <input type="text" name="direccion" value="{{ old('direccion', $profesional->persona->direccion) }}" class="w-full text-sm px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Estado *</label>
                            <select name="estado" required class="w-full text-sm px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                                <option value="ACTIVO" @selected($profesional->estado === 'ACTIVO')>ACTIVO</option>
                                <option value="INACTIVO" @selected($profesional->estado === 'INACTIVO')>INACTIVO</option>
                            </select>
                        </div>
                    </div>
                    <div class="pt-4 border-t border-slate-100 flex justify-end">
                        <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition-colors">
                            Guardar Cambios
                        </button>
                    </div>
                </form>
            @endif
        </div>

        {{-- Tarjeta: Cuenta de Acceso al Sistema (Usuario) --}}
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2.5">
                    <div class="p-2 rounded-xl bg-indigo-50 text-indigo-700">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Cuenta de Acceso al Sistema (Usuario)</h2>
                        <p class="text-xs text-slate-500">Credenciales para iniciar sesión, permisos y auditoría de este profesional.</p>
                    </div>
                </div>

                @if (! $profesional->usuario && ! $isReadOnly)
                    <button
                        type="button"
                        @click="modalCrearUsuario = true"
                        class="inline-flex items-center gap-2 px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition-all shadow-xs"
                    >
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Crear Cuenta de Usuario</span>
                    </button>
                @endif
            </div>

            @if ($profesional->usuario)
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 p-4 rounded-xl bg-slate-50 border border-slate-100 text-xs">
                    <div>
                        <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block">Nombre de Usuario</span>
                        <span class="font-mono font-bold text-indigo-700 text-sm">{{ $profesional->usuario->nombre_usuario }}</span>
                    </div>
                    <div>
                        <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block">Rol Principal</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800 mt-0.5">
                            {{ $profesional->usuario->rol_principal }}
                        </span>
                    </div>
                    <div>
                        <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block">Estado de Cuenta</span>
                        <div class="mt-0.5">
                            <x-state-badge :status="$profesional->usuario->estado" />
                        </div>
                    </div>
                    <div>
                        <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block">Último Acceso</span>
                        <span class="font-medium text-slate-700">
                            {{ $profesional->usuario->ultimo_acceso ? $profesional->usuario->ultimo_acceso->diffForHumans() : 'Sin accesos registrados' }}
                        </span>
                    </div>
                </div>
            @else
                <div class="p-4 rounded-xl bg-amber-50/70 border border-amber-200/80 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="p-2 rounded-lg bg-amber-100 text-amber-700 shrink-0">
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-amber-900">Sin cuenta de acceso asignada</p>
                            <p class="text-[11px] text-amber-700">Este profesional no puede iniciar sesión en el panel. Puedes crearle su cuenta con un clic reutilizando sus datos ya cargados.</p>
                        </div>
                    </div>
                    @if (! $isReadOnly)
                        <button
                            type="button"
                            @click="modalCrearUsuario = true"
                            class="shrink-0 px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-lg transition-colors shadow-2xs"
                        >
                            + Activar Acceso
                        </button>
                    @endif
                </div>
            @endif
        </div>
    </div>

    {{-- TAB 2: SERVICIOS HABILITADOS --}}
    <div x-show="tab === 'servicios'" class="space-y-6" style="display: none;">
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Servicios que puede realizar</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Marque los servicios del catálogo habilitados para agendar con este profesional.</p>
                </div>
            </div>

            @if ($isReadOnly)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @forelse ($profesional->servicios as $srv)
                        <div class="p-3 border border-slate-100 rounded-xl bg-slate-50/50 flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-slate-800">{{ $srv->nombre }}</p>
                                <p class="text-xs text-slate-400 capitalize">{{ strtolower($srv->tipo_servicio ?? 'General') }}</p>
                            </div>
                            <span class="text-xs font-mono font-medium text-indigo-600">Bs. {{ number_format($srv->precio_base, 2) }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-slate-400 italic col-span-3">No tiene servicios habilitados asignados.</p>
                    @endforelse
                </div>
            @else
                <form method="POST" action="{{ route('profesionales.update-servicios', $profesional->id_persona) }}" class="space-y-4">
                    @csrf
                    @method('PUT')
                    @php
                        $asignadosIds = $profesional->servicios->pluck('id_servicio')->all();
                    @endphp
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 max-h-96 overflow-y-auto p-2 border border-slate-100 rounded-xl">
                        @foreach ($todosServicios as $srv)
                            <label class="p-3 border border-slate-200/80 rounded-xl hover:bg-slate-50/75 cursor-pointer flex items-start gap-3 transition-colors">
                                <input type="checkbox" name="servicios[]" value="{{ $srv->id_servicio }}" @checked(in_array($srv->id_servicio, $asignadosIds, true)) class="mt-1 rounded text-indigo-600 focus:ring-indigo-500">
                                <div class="text-xs">
                                    <p class="font-semibold text-slate-800">{{ $srv->nombre }}</p>
                                    <p class="text-slate-400">{{ $srv->descripcion ?? 'Servicio clínico/estético' }}</p>
                                    <p class="font-mono text-indigo-600 mt-1">Bs. {{ number_format($srv->precio_base, 2) }}</p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    <div class="pt-4 border-t border-slate-100 flex justify-end">
                        <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition-colors">
                            Actualizar Servicios Habilitados
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>

    {{-- TAB 3: HORARIO SEMANAL --}}
    <div x-show="tab === 'horarios'" class="space-y-6" style="display: none;">
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Cronograma Semanal de Trabajo</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Días y franjas horarias en las que el especialista atiende citas.</p>
                </div>
                @if (! $isReadOnly)
                    <button type="button" @click="nuevoHorarioModal = true" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold rounded-lg transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Agregar Turno</span>
                    </button>
                @endif
            </div>

            @if ($profesional->horarios->isEmpty())
                <p class="text-sm text-slate-400 italic">No hay turnos registrados en su horario semanal.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50/75 text-xs font-semibold uppercase tracking-wider text-slate-500">
                                <th class="py-2.5 px-4">Día de la Semana</th>
                                <th class="py-2.5 px-4">Hora Inicio</th>
                                <th class="py-2.5 px-4">Hora Fin</th>
                                <th class="py-2.5 px-4">Estado</th>
                                @if (! $isReadOnly)
                                    <th class="py-2.5 px-4 text-right">Acción</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($profesional->horarios as $h)
                                <tr class="hover:bg-slate-50/50">
                                    <td class="py-3 px-4 font-semibold text-slate-800">
                                        {{ $h->nombre_dia }}
                                    </td>
                                    <td class="py-3 px-4 font-mono text-slate-600">
                                        {{ substr($h->hora_inicio, 0, 5) }}
                                    </td>
                                    <td class="py-3 px-4 font-mono text-slate-600">
                                        {{ substr($h->hora_fin, 0, 5) }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <x-state-badge :status="$h->estado" />
                                    </td>
                                    @if (! $isReadOnly)
                                        <td class="py-3 px-4 text-right">
                                            <form method="POST" action="{{ route('profesionales.delete-horario', $h->id_horario) }}" onsubmit="return confirm('¿Eliminar este turno horario?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-xs text-rose-600 hover:text-rose-800 font-medium">
                                                    Eliminar
                                                </button>
                                            </form>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- TAB 4: BLOQUEOS Y AUSENCIAS --}}
    <div x-show="tab === 'bloqueos'" class="space-y-6" style="display: none;">
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Historial de Bloqueos y Ausencias</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Vacaciones, licencias o permisos que impiden agendar citas.</p>
                </div>
            </div>

            @if ($profesional->bloqueosAgenda->isEmpty())
                <p class="text-sm text-slate-400 italic">No hay bloqueos ni ausencias programadas para este profesional.</p>
            @else
                <div class="space-y-3">
                    @foreach ($profesional->bloqueosAgenda as $bloqueo)
                        <div class="p-4 border border-slate-200/80 rounded-xl bg-slate-50/50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-amber-100 text-amber-800">
                                        Bloqueo
                                    </span>
                                    <p class="text-sm font-semibold text-slate-800">
                                        {{ \Carbon\Carbon::parse($bloqueo->fecha_hora_inicio)->isoFormat('D MMM YYYY, HH:mm') }} —
                                        {{ \Carbon\Carbon::parse($bloqueo->fecha_hora_fin)->isoFormat('D MMM YYYY, HH:mm') }}
                                    </p>
                                </div>
                                <p class="text-xs text-slate-600 mt-1">
                                    Motivo: <span class="font-medium text-slate-800">{{ $bloqueo->motivo }}</span>
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Modal: Agregar Turno --}}
    @if (! $isReadOnly)
        <div x-show="nuevoHorarioModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs transition-opacity" @click="nuevoHorarioModal = false"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <h2 class="text-base font-bold text-slate-900">Agregar Turno Semanal</h2>
                        <button type="button" @click="nuevoHorarioModal = false" class="text-slate-400 hover:text-slate-600">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('profesionales.store-horario', $profesional->id_persona) }}" class="mt-4 space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Día de la Semana *</label>
                            <select name="dia_semana" required class="w-full text-sm px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                                <option value="Lunes">Lunes</option>
                                <option value="Martes">Martes</option>
                                <option value="Miércoles">Miércoles</option>
                                <option value="Jueves">Jueves</option>
                                <option value="Viernes">Viernes</option>
                                <option value="Sábado">Sábado</option>
                                <option value="Domingo">Domingo</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Hora Inicio *</label>
                                <input type="time" name="hora_inicio" required value="08:00" class="w-full text-sm px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Hora Fin *</label>
                                <input type="time" name="hora_fin" required value="16:00" class="w-full text-sm px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Estado *</label>
                            <select name="estado" required class="w-full text-sm px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                                <option value="ACTIVO">ACTIVO</option>
                                <option value="INACTIVO">INACTIVO</option>
                            </select>
                        </div>
                        <div class="pt-3 border-t border-slate-100 flex justify-end gap-2">
                            <button type="button" @click="nuevoHorarioModal = false" class="px-3.5 py-2 border border-slate-200 text-slate-700 text-xs font-semibold rounded-lg hover:bg-slate-50">
                                Cancelar
                            </button>
                            <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg">
                                Guardar Turno
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal: Crear Cuenta de Usuario para este Profesional --}}
    @if (! $profesional->usuario && ! $isReadOnly)
        <div x-show="modalCrearUsuario" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs transition-opacity" @click="modalCrearUsuario = false"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2.5">
                            <div class="p-2 rounded-xl bg-indigo-50 text-indigo-700">
                                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900">Crear Cuenta de Usuario</h3>
                                <p class="text-xs text-slate-500">Para {{ $profesional->persona->nombre }} {{ $profesional->persona->apellido1 }}</p>
                            </div>
                        </div>
                        <button type="button" @click="modalCrearUsuario = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition-colors">
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('profesionales.store-usuario', $profesional->id_persona) }}" class="space-y-4">
                        @csrf

                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 text-xs space-y-1">
                            <p class="text-slate-600"><strong>Documento:</strong> {{ $profesional->persona->documento }}</p>
                            <p class="text-slate-600"><strong>Email:</strong> {{ $profesional->persona->email ?? 'No registrado' }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Nombre de Usuario *</label>
                            <input
                                type="text"
                                name="nombre_usuario"
                                required
                                value="{{ old('nombre_usuario', 'dr_' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $profesional->persona->nombre))) }}"
                                placeholder="Ej. dr_carlos"
                                class="w-full text-xs px-3.5 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 font-mono"
                            >
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Contraseña Inicial *</label>
                            <input
                                type="password"
                                name="password"
                                required
                                placeholder="Mínimo 6 caracteres"
                                class="w-full text-xs px-3.5 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500"
                            >
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Rol en el Sistema *</label>
                            <select name="id_rol" required class="w-full text-xs px-3.5 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 font-medium">
                                @foreach ($rolesDisponibles as $r)
                                    <option value="{{ $r->id_rol }}" @selected(strtoupper($r->nombre) === 'VETERINARIO')>{{ $r->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                            <button type="button" @click="modalCrearUsuario = false" class="px-4 py-2 border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl hover:bg-slate-50 transition-colors">
                                Cancelar
                            </button>
                            <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition-all shadow-xs flex items-center gap-1.5">
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                <span>Crear y Vincular Cuenta</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
