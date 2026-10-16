@extends('layouts.app')

@section('title', 'Profesionales y Disponibilidad — La Petdiatría')

@section('content')
<div class="space-y-6" x-data="{
    nuevoModal: false,
    modoRegistro: 'nuevo',
    crearUsuario: true,
    usuarioSeleccionado: '',
    usuariosList: {{ Js::from($usuariosDisponibles) }},
    onSelectUsuario(id) {
        this.usuarioSeleccionado = id;
        const u = this.usuariosList.find(x => x.id_usuario == id);
        if (u && u.persona) {
            if (this.$refs.inputDoc) this.$refs.inputDoc.value = u.persona.documento || '';
            if (this.$refs.inputNom) this.$refs.inputNom.value = u.persona.nombre || '';
            if (this.$refs.inputAp1) this.$refs.inputAp1.value = u.persona.apellido1 || '';
            if (this.$refs.inputAp2) this.$refs.inputAp2.value = u.persona.apellido2 || '';
            if (this.$refs.inputTel) this.$refs.inputTel.value = u.persona.telefono || '';
            if (this.$refs.inputEmail) this.$refs.inputEmail.value = u.persona.email || '';
            if (this.$refs.inputDir) this.$refs.inputDir.value = u.persona.direccion || '';
        }
    }
}">
    {{-- Header de Sección --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Equipo Profesional y Disponibilidad</h1>
            <p class="text-sm text-slate-500 mt-1">Gestión de especialistas médicos, técnicos, servicios habilitados y horarios laborales.</p>
        </div>
        @if (! $isReadOnly)
            <div class="flex items-center gap-3">
                <button type="button" @click="nuevoModal = true" class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-xs transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Nuevo Profesional</span>
                </button>
            </div>
        @endif
    </div>

    {{-- Tarjetas KPI --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Plantel</span>
                <p class="text-3xl font-bold text-slate-900 mt-1">{{ $metrics['total'] }}</p>
                <p class="text-xs text-slate-500 mt-1">Especialistas registrados</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Activos en Servicio</span>
                <p class="text-3xl font-bold text-emerald-600 mt-1">{{ $metrics['activos'] }}</p>
                <p class="text-xs text-slate-500 mt-1">Con turnos y agenda disponibles</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Especialidades</span>
                <p class="text-3xl font-bold text-violet-600 mt-1">{{ $metrics['especialidades_count'] }}</p>
                <p class="text-xs text-slate-500 mt-1">Áreas clínicas y técnicas</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                </svg>
            </div>
        </div>
    </div>

    {{-- Filtros y Buscador --}}
    <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('profesionales.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
            <div class="sm:col-span-5">
                <label for="search" class="block text-xs font-semibold text-slate-600 mb-1">Buscar profesional</label>
                <div class="relative">
                    <input type="text" name="search" id="search" value="{{ $filters['search'] }}" placeholder="Nombre, apellido, CI o reg. profesional..." class="w-full text-sm pl-9 pr-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            <div class="sm:col-span-3">
                <label for="especialidad" class="block text-xs font-semibold text-slate-600 mb-1">Especialidad</label>
                <select name="especialidad" id="especialidad" class="w-full text-sm px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Todas las especialidades</option>
                    @foreach ($especialidades as $esp)
                        <option value="{{ $esp }}" @selected($filters['especialidad'] === $esp)>{{ $esp }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2">
                <label for="estado" class="block text-xs font-semibold text-slate-600 mb-1">Estado</label>
                <select name="estado" id="estado" class="w-full text-sm px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Todos</option>
                    <option value="ACTIVO" @selected($filters['estado'] === 'ACTIVO')>Activos</option>
                    <option value="INACTIVO" @selected($filters['estado'] === 'INACTIVO')>Inactivos</option>
                </select>
            </div>

            <div class="sm:col-span-2 flex items-center gap-2">
                <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white text-sm font-semibold rounded-lg transition-colors">
                    Filtrar
                </button>
                @if ($filters['search'] || $filters['especialidad'] || $filters['estado'])
                    <a href="{{ route('profesionales.index') }}" class="p-2 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition-colors" title="Limpiar filtros">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Tabla de Profesionales --}}
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden">
        @if ($profesionales->isEmpty())
            <div class="p-8">
                <x-empty-state
                    title="No se encontraron profesionales"
                    description="No hay especialistas que coincidan con los criterios de búsqueda o filtros seleccionados."
                >
                    @if (! $isReadOnly)
                        <x-slot:action>
                            <button type="button" @click="nuevoModal = true" class="inline-flex items-center gap-2 px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg transition-colors">
                                Registrar Profesional
                            </button>
                        </x-slot:action>
                    @endif
                </x-empty-state>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/75 text-xs font-semibold uppercase tracking-wider text-slate-500">
                            <th class="py-3 px-4">Profesional</th>
                            <th class="py-3 px-4">Especialidad</th>
                            <th class="py-3 px-4">Reg. Profesional</th>
                            <th class="py-3 px-4">Servicios Habilitados</th>
                            <th class="py-3 px-4">Horario / Turnos</th>
                            <th class="py-3 px-4">Estado</th>
                            <th class="py-3 px-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($profesionales as $prof)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xs uppercase shrink-0">
                                            {{ substr($prof->persona->nombre, 0, 1) }}{{ substr($prof->persona->apellido1, 0, 1) }}
                                        </div>
                                        <div>
                                            <a href="{{ route('profesionales.show', $prof->id_persona) }}" class="font-semibold text-slate-900 hover:text-indigo-600 transition-colors">
                                                {{ $prof->persona->nombre }} {{ $prof->persona->apellido1 }} {{ $prof->persona->apellido2 }}
                                            </a>
                                            <p class="text-xs text-slate-400">CI: {{ $prof->persona->documento }} · {{ $prof->persona->telefono ?? 'Sin teléfono' }}</p>
                                            @if ($prof->usuario)
                                                <div class="mt-1 flex items-center gap-1.5 text-[11px] font-semibold text-indigo-700 bg-indigo-50 border border-indigo-100/80 px-2 py-0.5 rounded-full w-fit">
                                                    <svg class="size-3 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                    </svg>
                                                    <span>Usuario: <strong>{{ $prof->usuario->nombre_usuario }}</strong> ({{ $prof->usuario->rol_principal }})</span>
                                                </div>
                                            @else
                                                <span class="mt-1 inline-flex items-center text-[10px] font-medium text-slate-400 bg-slate-100 px-2 py-0.5 rounded-full">
                                                    Sin usuario de acceso
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 font-medium text-slate-700">
                                    {{ $prof->especialidad }}
                                </td>
                                <td class="py-3.5 px-4 font-mono text-xs text-slate-500">
                                    {{ $prof->registro_profesional }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="flex flex-wrap gap-1 max-w-xs">
                                        @forelse ($prof->servicios->take(3) as $srv)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-slate-100 text-slate-700">
                                                {{ $srv->nombre }}
                                            </span>
                                        @empty
                                            <span class="text-xs text-slate-400 italic">Sin servicios asignados</span>
                                        @endforelse
                                        @if ($prof->servicios->count() > 3)
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-xs font-medium bg-indigo-50 text-indigo-700">
                                                +{{ $prof->servicios->count() - 3 }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-xs text-slate-600">
                                    @php
                                        $diasCount = $prof->horarios->where('estado', 'ACTIVO')->unique('dia_semana')->count();
                                    @endphp
                                    <span class="font-medium text-slate-800">{{ $diasCount }} días/semana</span>
                                    <p class="text-slate-400">{{ $prof->horarios->where('estado', 'ACTIVO')->count() }} turno(s)</p>
                                </td>
                                <td class="py-3.5 px-4">
                                    <x-state-badge :status="$prof->estado" />
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('profesionales.show', $prof->id_persona) }}" class="p-1.5 text-slate-400 hover:text-indigo-600 rounded-lg hover:bg-slate-100 transition-colors" title="Ver ficha técnica">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>

                                        @if (! $isReadOnly)
                                            <form method="POST" action="{{ route('profesionales.toggle-estado', $prof->id_persona) }}" onsubmit="return confirm('¿Deseas cambiar el estado de este profesional?');">
                                                @csrf
                                                <button type="submit" class="p-1.5 text-slate-400 hover:text-amber-600 rounded-lg hover:bg-slate-100 transition-colors" title="{{ $prof->estado === 'ACTIVO' ? 'Dar de baja lógica' : 'Activar profesional' }}">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($profesionales->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $profesionales->links() }}
                </div>
            @endif
        @endif
    </div>

    {{-- Modal: Nuevo Profesional con Opción de Usuario --}}
    @if (! $isReadOnly)
        <div x-show="nuevoModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs transition-opacity" @click="nuevoModal = false"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white rounded-3xl max-w-2xl w-full p-6 shadow-2xl border border-slate-200 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">Registrar Profesional</h2>
                            <p class="text-xs text-slate-500">Habilita especialistas clínicos y técnicos vinculando o creando su usuario.</p>
                        </div>
                        <button type="button" @click="nuevoModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition-colors">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- Selector de Modo de Registro --}}
                    <div class="flex items-center gap-2 p-1 bg-slate-100 rounded-2xl text-xs font-semibold">
                        <button
                            type="button"
                            @click="modoRegistro = 'nuevo'; usuarioSeleccionado = ''"
                            :class="modoRegistro === 'nuevo' ? 'bg-white text-indigo-700 shadow-2xs' : 'text-slate-500 hover:text-slate-800'"
                            class="flex-1 py-2 px-3 rounded-xl transition-all text-center flex items-center justify-center gap-1.5"
                        >
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
                            <span>Nuevo Profesional (+ Usuario)</span>
                        </button>

                        <button
                            type="button"
                            @click="modoRegistro = 'existente'"
                            :class="modoRegistro === 'existente' ? 'bg-white text-indigo-700 shadow-2xs' : 'text-slate-500 hover:text-slate-800'"
                            class="flex-1 py-2 px-3 rounded-xl transition-all text-center flex items-center justify-center gap-1.5"
                        >
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
                            <span>Vincular Usuario Existente ({{ $usuariosDisponibles->count() }})</span>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('profesionales.store') }}" class="space-y-4">
                        @csrf

                        {{-- SECCIÓN MODO 1: VINCULAR USUARIO EXISTENTE --}}
                        <div x-show="modoRegistro === 'existente'" class="space-y-3">
                            <div class="bg-indigo-50/80 border border-indigo-100 rounded-2xl p-4">
                                <label class="block text-xs font-bold text-indigo-900 uppercase tracking-wider mb-1.5">
                                    Selecciona el usuario del sistema a registrar como profesional *
                                </label>
                                @if ($usuariosDisponibles->isEmpty())
                                    <p class="text-xs text-amber-700 bg-amber-50 p-2.5 rounded-xl border border-amber-200">
                                        No hay usuarios disponibles sin ficha profesional. Todos los usuarios existentes ya son profesionales o puedes registrar uno nuevo desde cero.
                                    </p>
                                @else
                                    <select
                                        name="id_usuario_existente"
                                        x-model="usuarioSeleccionado"
                                        @change="onSelectUsuario($event.target.value)"
                                        :required="modoRegistro === 'existente'"
                                        class="w-full text-xs px-3.5 py-2.5 border border-indigo-200 bg-white rounded-xl focus:ring-2 focus:ring-indigo-500 font-medium text-slate-800"
                                    >
                                        <option value="">-- Elige un usuario del sistema --</option>
                                        @foreach ($usuariosDisponibles as $u)
                                            <option value="{{ $u->id_usuario }}">
                                                {{ $u->nombre_usuario }} — {{ $u->name }} (CI: {{ $u->persona?->documento ?? 'S/D' }}) · Rol: {{ $u->rol_principal }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <p class="text-[11px] text-indigo-700 mt-2 flex items-center gap-1">
                                        <svg class="size-3.5 text-indigo-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                        <span>Los datos de identidad (CI, nombre, teléfono, email) se enlazarán automáticamente sin necesidad de escribirlos dos veces.</span>
                                    </p>
                                @endif
                            </div>
                        </div>

                        {{-- SECCIÓN MODO 2: NUEVO PROFESIONAL DESDE CERO --}}
                        <div x-show="modoRegistro === 'nuevo'" class="space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Documento / CI *</label>
                                    <input type="text" name="documento" x-ref="inputDoc" :required="modoRegistro === 'nuevo'" class="w-full text-xs px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nombres *</label>
                                    <input type="text" name="nombre" x-ref="inputNom" :required="modoRegistro === 'nuevo'" class="w-full text-xs px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Primer Apellido *</label>
                                    <input type="text" name="apellido1" x-ref="inputAp1" :required="modoRegistro === 'nuevo'" class="w-full text-xs px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Segundo Apellido</label>
                                    <input type="text" name="apellido2" x-ref="inputAp2" class="w-full text-xs px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Teléfono</label>
                                    <input type="text" name="telefono" x-ref="inputTel" class="w-full text-xs px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Correo Electrónico *</label>
                                    <input type="email" name="email" x-ref="inputEmail" :required="modoRegistro === 'nuevo'" placeholder="profesional@ejemplo.com" class="w-full text-xs px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Dirección</label>
                                    <input type="text" name="direccion" x-ref="inputDir" class="w-full text-xs px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500">
                                </div>
                            </div>

                            {{-- Tarjeta de Crear Cuenta de Usuario en un Solo Paso --}}
                            <div class="p-3.5 rounded-2xl border border-indigo-100 bg-linear-to-r from-indigo-50/70 via-purple-50/40 to-slate-50 space-y-2.5">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <div class="p-1 rounded-lg bg-indigo-100 text-indigo-700">
                                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" /></svg>
                                        </div>
                                        <div>
                                            <span class="text-xs font-bold text-slate-900 block leading-tight">Crear cuenta de acceso al sistema (Usuario)</span>
                                            <span class="text-[10px] text-slate-500">Permite que este profesional inicie sesión inmediatamente</span>
                                        </div>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="crear_usuario" value="1" x-model="crearUsuario" class="sr-only peer">
                                        <div class="w-9 h-5 bg-slate-300 peer-focus:outline-hidden rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                                    </label>
                                </div>

                                <div x-show="crearUsuario" class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 pt-2 border-t border-indigo-100/70">
                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Nombre de Usuario *</label>
                                        <input type="text" name="nombre_usuario" :required="crearUsuario && modoRegistro === 'nuevo'" placeholder="Ej. dr_carlos" class="w-full text-xs px-3 py-1.5 border border-slate-200 bg-white rounded-lg focus:ring-2 focus:ring-indigo-500 font-mono">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Contraseña Inicial *</label>
                                        <input type="password" name="password" :required="crearUsuario && modoRegistro === 'nuevo'" placeholder="Mínimo 6 caracteres" class="w-full text-xs px-3 py-1.5 border border-slate-200 bg-white rounded-lg focus:ring-2 focus:ring-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Rol en el Sistema *</label>
                                        <select name="id_rol" :required="crearUsuario && modoRegistro === 'nuevo'" class="w-full text-xs px-2.5 py-1.5 border border-slate-200 bg-white rounded-lg focus:ring-2 focus:ring-indigo-500 font-medium">
                                            @foreach ($rolesDisponibles as $r)
                                                <option value="{{ $r->id_rol }}" @selected(strtoupper($r->nombre) === 'VETERINARIO')>{{ $r->nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- CAMPOS PROFESIONALES COMUNES --}}
                        <div class="pt-2 border-t border-slate-100 grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div class="sm:col-span-1">
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Especialidad *</label>
                                <input type="text" name="especialidad" placeholder="Ej. Cirugía, Medicina Interna, Peluquería" required class="w-full text-xs px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div class="sm:col-span-1">
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Matrícula / Registro Prof. *</label>
                                <input type="text" name="registro_profesional" placeholder="Ej. MP-8492" required class="w-full text-xs px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 font-mono">
                            </div>
                            <div class="sm:col-span-1">
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Estado *</label>
                                <select name="estado" required class="w-full text-xs px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500">
                                    <option value="ACTIVO">ACTIVO</option>
                                    <option value="INACTIVO">INACTIVO</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Servicios Habilitados Iniciales</label>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 max-h-36 overflow-y-auto p-2.5 border border-slate-100 rounded-xl bg-slate-50/50">
                                @foreach ($serviciosDisponibles as $srv)
                                    <label class="inline-flex items-center gap-2 text-xs text-slate-700 cursor-pointer hover:text-indigo-600 transition-colors">
                                        <input type="checkbox" name="servicios[]" value="{{ $srv->id_servicio }}" class="rounded text-indigo-600 focus:ring-indigo-500">
                                        <span>{{ $srv->nombre }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <p class="text-[11px] text-slate-400 mt-1">Se generará automáticamente un cronograma base de Lunes a Viernes de 8:00 a 16:00 que podrá personalizar en la ficha técnica.</p>
                        </div>

                        <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                            <button type="button" @click="nuevoModal = false" class="px-4 py-2 border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl hover:bg-slate-50 transition-colors">
                                Cancelar
                            </button>
                            <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition-all shadow-xs hover:scale-105 active:scale-95 flex items-center gap-2">
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                <span>Guardar Profesional</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
