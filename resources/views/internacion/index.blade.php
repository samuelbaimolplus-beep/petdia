@extends('layouts.app')

@section('title', 'Hospitalización e Internación — La Petdiatría')

@section('content')
<div class="space-y-6" x-data="{
    ingresoModalOpen: false,
    canilSeleccionado: '',
    searchCanil: ''
}">

    <!-- Encabezado de la página -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Hospitalización e Internación</h1>
            <p class="text-sm text-slate-500 mt-1">Monitoreo de pacientes hospitalizados, asignación de caniles y bitácora de evolución clínica.</p>
        </div>
        <div class="flex items-center gap-3">
            <button
                type="button"
                @click="ingresoModalOpen = true"
                class="inline-flex items-center gap-2 rounded-xl bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white shadow-xs hover:bg-primary-700 transition-colors"
            >
                <i data-lucide="plus-circle" class="size-4"></i>
                <span>Nuevo Ingreso a Hospital</span>
            </button>
        </div>
    </div>

    <!-- 4 Tarjetas KPI -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Hospitalizados Activos -->
        <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Pacientes Internados</p>
                <div class="flex size-9 items-center justify-center rounded-lg bg-primary-50 text-primary-600">
                    <i data-lucide="bed-double" class="size-5"></i>
                </div>
            </div>
            <p class="mt-3 text-3xl font-bold tracking-tight text-slate-900">{{ $totalActivas }}</p>
            <p class="mt-1 text-xs text-slate-500">Pacientes actualmente en caniles</p>
        </div>

        <!-- Caniles Libres -->
        <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Caniles Libres</p>
                <div class="flex size-9 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                    <i data-lucide="door-open" class="size-5"></i>
                </div>
            </div>
            <p class="mt-3 text-3xl font-bold tracking-tight text-emerald-700">{{ $canilesLibres }}</p>
            <p class="mt-1 text-xs text-slate-500">Listos para recibir pacientes</p>
        </div>

        <!-- Tasa de Ocupación -->
        <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Ocupación</p>
                <div class="flex size-9 items-center justify-center rounded-lg bg-amber-50 text-amber-600">
                    <i data-lucide="activity" class="size-5"></i>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <p class="text-3xl font-bold tracking-tight text-slate-900">{{ $porcentajeOcupacion }}%</p>
                <span class="text-xs text-slate-400">de capacidad</span>
            </div>
            <div class="mt-2 h-1.5 w-full rounded-full bg-slate-100 overflow-hidden">
                <div class="h-full bg-amber-500 rounded-full" style="width: {{ $porcentajeOcupacion }}%"></div>
            </div>
        </div>

        <!-- Altas en la Semana -->
        <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Altas Médicas</p>
                <div class="flex size-9 items-center justify-center rounded-lg bg-violet-50 text-violet-600">
                    <i data-lucide="check-circle-2" class="size-5"></i>
                </div>
            </div>
            <p class="mt-3 text-3xl font-bold tracking-tight text-slate-900">{{ $altasSemana }}</p>
            <p class="mt-1 text-xs text-slate-500">Pacientes recuperados (últimos 7 días)</p>
        </div>
    </div>

    <!-- SECCIÓN: TABLERO VISUAL DE CANILES / JAULAS -->
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="layout-grid" class="size-4 text-primary-600"></i>
                    Tablero de Caniles y Áreas Hospitalarias
                </h2>
                <p class="text-xs text-slate-500">Disponibilidad en tiempo real y asignación de jaulas</p>
            </div>
            <div class="flex items-center gap-2 text-xs">
                <span class="inline-flex items-center gap-1.5 text-slate-600">
                    <span class="size-2.5 rounded-full bg-emerald-500"></span> Libre
                </span>
                <span class="inline-flex items-center gap-1.5 text-slate-600 ml-2">
                    <span class="size-2.5 rounded-full bg-rose-500"></span> Ocupado
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach ($tableroCaniles as $canil)
                @if ($canil['ocupado'])
                    @php
                        $inter = $canil['internacion'];
                        $paciente = $inter->atencionVeterinaria?->atencion?->paciente;
                        $propietario = $paciente?->propietario?->persona;
                        $ultimoSeg = $inter->seguimientos->first();
                        $dias = max(1, (int) ceil(now()->diffInHours(\Carbon\Carbon::parse($inter->fecha_hora_ingreso)) / 24));
                    @endphp
                    <!-- Canil Ocupado -->
                    <div class="relative rounded-2xl border-2 border-rose-200/90 bg-white p-4 shadow-xs flex flex-col justify-between hover:shadow-sm transition-all">
                        <div>
                            <div class="flex items-center justify-between pb-2 border-b border-rose-100">
                                <span class="font-mono text-xs font-bold text-slate-800 flex items-center gap-1.5">
                                    <i data-lucide="shield-alert" class="size-3.5 text-rose-500"></i>
                                    {{ $canil['nombre'] }}
                                </span>
                                <span class="inline-flex items-center rounded-full bg-rose-50 px-2 py-0.5 text-[10px] font-bold text-rose-700">
                                    Ocupado · Día {{ $dias }}
                                </span>
                            </div>

                            <div class="mt-3 flex items-start gap-3">
                                <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-600 font-bold text-base">
                                    {{ substr($paciente?->nombre ?? 'P', 0, 1) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h4 class="font-bold text-slate-900 text-sm truncate">
                                        {{ $paciente?->nombre ?? 'Paciente' }}
                                    </h4>
                                    <p class="text-xs text-slate-500 truncate">
                                        {{ $paciente?->raza?->nombre ?? 'Mestizo' }} · {{ $paciente?->raza?->especie?->nombre ?? 'Mascota' }}
                                    </p>
                                    @if ($propietario)
                                        <p class="text-[11px] text-slate-400 mt-0.5 truncate">
                                            Tutor: {{ $propietario->nombre }} {{ $propietario->apellido1 }}
                                        </p>
                                    @endif
                                </div>
                            </div>

                            <!-- Motivo de ingreso -->
                            <div class="mt-3 rounded-lg bg-slate-50 p-2 text-xs">
                                <p class="text-[10px] font-bold uppercase text-slate-400">Diagnóstico / Motivo</p>
                                <p class="text-slate-700 font-medium line-clamp-2 mt-0.5">{{ $inter->motivo }}</p>
                            </div>

                            <!-- Últimos signos vitales -->
                            @if ($ultimoSeg && ($ultimoSeg->temperatura || $ultimoSeg->frecuencia_cardiaca))
                                <div class="mt-2 flex items-center justify-between text-[11px] text-slate-500 pt-1 border-t border-slate-100">
                                    <span>T: <strong class="text-slate-800">{{ $ultimoSeg->temperatura ?? '—' }}°C</strong></span>
                                    <span>FC: <strong class="text-slate-800">{{ $ultimoSeg->frecuencia_cardiaca ?? '—' }} lpm</strong></span>
                                    <span>Ánimo: <strong class="text-slate-800">{{ $ultimoSeg->estado_animo ?? 'Estable' }}</strong></span>
                                </div>
                            @endif
                        </div>

                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                            <a
                                href="{{ route('internaciones.show', $inter->id_internacion) }}"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white hover:bg-slate-800 transition-colors"
                            >
                                <i data-lucide="clipboard-pen" class="size-3.5"></i>
                                <span>Ver Evolución</span>
                            </a>

                            @if ($propietario && $propietario->telefono)
                                @php
                                    $msgWp = urlencode("Hola {$propietario->nombre}, te informamos sobre la evolución clínica de {$paciente->nombre} en La Petdiatría.");
                                @endphp
                                <a
                                    href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $propietario->telefono) }}?text={{ $msgWp }}"
                                    target="_blank"
                                    class="rounded-lg p-1.5 text-emerald-600 hover:bg-emerald-50 transition-colors"
                                    title="Contactar al tutor por WhatsApp"
                                >
                                    <i data-lucide="message-circle" class="size-4"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                @else
                    <!-- Canil Libre -->
                    <div class="rounded-2xl border border-dashed border-emerald-300 bg-emerald-50/20 p-4 shadow-2xs flex flex-col justify-between hover:bg-emerald-50/40 transition-colors">
                        <div>
                            <div class="flex items-center justify-between pb-2 border-b border-emerald-100">
                                <span class="font-mono text-xs font-bold text-slate-700 flex items-center gap-1.5">
                                    <i data-lucide="check" class="size-3.5 text-emerald-600"></i>
                                    {{ $canil['nombre'] }}
                                </span>
                                <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700">
                                    Libre
                                </span>
                            </div>
                            <div class="py-8 text-center">
                                <div class="flex size-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 mx-auto mb-2">
                                    <i data-lucide="sparkles" class="size-5"></i>
                                </div>
                                <p class="text-xs font-semibold text-slate-700">Listo y Desinfectado</p>
                                <p class="text-[11px] text-slate-400 mt-0.5">Espacio disponible para internación</p>
                            </div>
                        </div>

                        <button
                            type="button"
                            @click="canilSeleccionado = '{{ $canil['nombre'] }}'; ingresoModalOpen = true"
                            class="w-full inline-flex items-center justify-center gap-1.5 rounded-xl border border-emerald-200 bg-white py-2 text-xs font-semibold text-emerald-700 hover:bg-emerald-50 transition-colors"
                        >
                            <i data-lucide="plus" class="size-3.5"></i>
                            <span>Asignar Paciente</span>
                        </button>
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    <!-- SECCIÓN: LISTADO Y FILTRO DE TODAS LAS INTERNACIONES -->
    <div class="rounded-2xl border border-slate-200/80 bg-white shadow-xs overflow-hidden">
        <div class="flex flex-col gap-3 p-5 sm:flex-row sm:items-center sm:justify-between border-b border-slate-100">
            <div>
                <h3 class="text-base font-bold text-slate-900">Historial de Hospitalizaciones</h3>
                <p class="text-xs text-slate-500">Registro histórico de ingresos, altas y tratamientos hospitalarios</p>
            </div>

            <!-- Filtros -->
            <form action="{{ route('internaciones.index') }}" method="GET" class="flex flex-wrap items-center gap-2">
                <div class="relative">
                    <i data-lucide="search" class="absolute left-2.5 top-1/2 -translate-y-1/2 size-3.5 text-slate-400"></i>
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Buscar paciente, canil o motivo..."
                        class="rounded-xl border border-slate-200 pl-8 pr-3 py-1.5 text-xs text-slate-800 placeholder:text-slate-400 focus-ring"
                    />
                </div>
                <select
                    name="estado"
                    onchange="this.form.submit()"
                    class="rounded-xl border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 focus-ring"
                >
                    <option value="ACTIVA" {{ request('estado', 'ACTIVA') === 'ACTIVA' ? 'selected' : '' }}>Solo Activas</option>
                    <option value="ALTA" {{ request('estado') === 'ALTA' ? 'selected' : '' }}>Con Alta Médica</option>
                    <option value="TODOS" {{ request('estado') === 'TODOS' ? 'selected' : '' }}>Todos los estados</option>
                </select>
                @if (request('search') || request('estado'))
                    <a href="{{ route('internaciones.index') }}" class="rounded-xl p-1.5 text-slate-400 hover:text-slate-700" title="Limpiar filtros">
                        <i data-lucide="x" class="size-4"></i>
                    </a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/75 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <th class="px-5 py-3">Canil / Jaula</th>
                        <th class="px-5 py-3">Paciente & Tutor</th>
                        <th class="px-5 py-3">Motivo de Ingreso</th>
                        <th class="px-5 py-3">Fecha Ingreso</th>
                        <th class="px-5 py-3">Duración</th>
                        <th class="px-5 py-3">Estado</th>
                        <th class="px-5 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs text-slate-600">
                    @forelse ($internaciones as $item)
                        @php
                            $pac = $item->atencionVeterinaria?->atencion?->paciente;
                            $tutor = $pac?->propietario?->persona;
                            $esActiva = ($item->estado === 'ACTIVA');
                            $fIngreso = \Carbon\Carbon::parse($item->fecha_hora_ingreso);
                            $fFin = $item->fecha_hora_alta ? \Carbon\Carbon::parse($item->fecha_hora_alta) : now();
                            $diasDuracion = max(1, (int) ceil($fIngreso->diffInHours($fFin) / 24));
                        @endphp
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-5 py-3 whitespace-nowrap font-mono font-bold text-slate-900">
                                {{ $item->jaula_numero ?? 'Sin Canil' }}
                            </td>
                            <td class="px-5 py-3">
                                <div class="font-bold text-slate-900">{{ $pac?->nombre ?? 'Paciente #' . $item->id_atencion_ingreso }}</div>
                                <div class="text-[11px] text-slate-400">
                                    {{ $tutor ? $tutor->nombre . ' ' . $tutor->apellido1 : 'Sin tutor' }}
                                    @if ($tutor?->telefono)
                                        · {{ $tutor->telefono }}
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-3 max-w-xs truncate text-slate-700">
                                {{ $item->motivo }}
                            </td>
                            <td class="px-5 py-3 whitespace-nowrap text-slate-600">
                                {{ $fIngreso->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-5 py-3 whitespace-nowrap font-semibold text-slate-800">
                                {{ $diasDuracion }} {{ $diasDuracion === 1 ? 'día' : 'días' }}
                            </td>
                            <td class="px-5 py-3 whitespace-nowrap">
                                @if ($esActiva)
                                    <span class="inline-flex items-center rounded-full bg-rose-50 px-2.5 py-0.5 text-[10px] font-bold text-rose-700">
                                        Hospitalizado
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-[10px] font-bold text-emerald-700">
                                        Alta Médica
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-right whitespace-nowrap">
                                <a
                                    href="{{ route('internaciones.show', $item->id_internacion) }}"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors"
                                >
                                    <i data-lucide="eye" class="size-3.5 text-slate-400"></i>
                                    <span>Ver Ficha</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-10 text-center text-xs text-slate-400">
                                No se encontraron hospitalizaciones con los filtros seleccionados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($internaciones->hasPages())
            <div class="border-t border-slate-100 px-5 py-3">
                {{ $internaciones->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL NUEVO INGRESO HOSPITALARIO -->
    <div
        x-show="ingresoModalOpen"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        @keydown.escape.window="ingresoModalOpen = false"
    >
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" @click="ingresoModalOpen = false"></div>

            <div
                class="relative w-full max-w-xl rounded-2xl bg-white p-6 shadow-pop border border-slate-200/80"
                x-show="ingresoModalOpen"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
            >
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="flex size-10 items-center justify-center rounded-xl bg-primary-50 text-primary-600">
                            <i data-lucide="bed-double" class="size-5"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Ingreso a Hospitalización</h3>
                            <p class="text-xs text-slate-500">Asigna un paciente a un canil e inicia su seguimiento clínico</p>
                        </div>
                    </div>
                    <button
                        type="button"
                        @click="ingresoModalOpen = false"
                        class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                    >
                        <i data-lucide="x" class="size-4"></i>
                    </button>
                </div>

                <form action="{{ route('internaciones.store') }}" method="POST" class="mt-4 space-y-4">
                    @csrf

                    <!-- Selección de la atención clínica del paciente -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Paciente / Atención Clínica <span class="text-rose-500">*</span>
                        </label>
                        <select
                            name="id_atencion_ingreso"
                            required
                            class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-800 focus-ring"
                        >
                            <option value="">Selecciona la atención médica activa...</option>
                            @foreach ($atencionesDisponibles as $aten)
                                @php
                                    $p = $aten->paciente;
                                    $t = $p?->propietario?->persona;
                                @endphp
                                <option value="{{ $aten->id_atencion }}">
                                    Atención #{{ $aten->id_atencion }} — {{ $p?->nombre ?? 'Mascota' }} (Tutor: {{ $t ? $t->nombre . ' ' . $t->apellido1 : 'Sin tutor' }})
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-slate-400 mt-1">Solo se listan atenciones veterinarias no hospitalizadas actualmente.</p>
                    </div>

                    <!-- Canil / Jaula asignada -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Canil / Jaula Asignada <span class="text-rose-500">*</span>
                        </label>
                        <select
                            name="jaula_numero"
                            x-model="canilSeleccionado"
                            required
                            class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-mono font-semibold text-slate-800 focus-ring"
                        >
                            <option value="">Selecciona un canil disponible...</option>
                            @foreach ($canilesDisponibles as $canilNombre)
                                <option value="{{ $canilNombre }}">{{ $canilNombre }} (Libre)</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Motivo de hospitalización -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Motivo de Hospitalización <span class="text-rose-500">*</span>
                        </label>
                        <textarea
                            name="motivo"
                            required
                            rows="2"
                            placeholder="Ej: Parvovirosis severa con deshidratación. Requiere fluidoterapia continua y monitoreo 24h..."
                            class="w-full rounded-xl border border-slate-200 p-2.5 text-xs text-slate-800 placeholder:text-slate-400 focus-ring"
                        ></textarea>
                    </div>

                    <!-- Observaciones iniciales -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Plan de Cuidados Iniciales / Observaciones (opcional)
                        </label>
                        <textarea
                            name="observaciones"
                            rows="2"
                            placeholder="Ej: Mantener en ayuno las primeras 12h, suero Ringer Lactato a 15 gotas/min..."
                            class="w-full rounded-xl border border-slate-200 p-2.5 text-xs text-slate-800 placeholder:text-slate-400 focus-ring"
                        ></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                        <button
                            type="button"
                            @click="ingresoModalOpen = false"
                            class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition-colors"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-primary-600 px-4 py-2 text-xs font-semibold text-white hover:bg-primary-700 transition-colors shadow-xs"
                        >
                            <i data-lucide="check" class="size-4"></i>
                            <span>Confirmar Ingreso</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
