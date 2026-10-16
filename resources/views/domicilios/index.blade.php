@extends('layouts.app')

@section('title', 'Servicio a Domicilio — La Petdiatría')

@section('content')
<div class="space-y-6">

    <!-- Encabezado de la página -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Servicio a Domicilio y Rutas</h1>
            <p class="text-sm text-slate-500 mt-1">Gestión de traslados, visitas veterinarias a domicilio y seguimiento de itinerario.</p>
        </div>
        <div class="flex items-center gap-3">
            <a
                href="{{ route('domicilios.create') }}"
                class="inline-flex items-center gap-2 rounded-xl bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-xs hover:bg-primary-700 transition-colors"
            >
                <i data-lucide="plus" class="size-4"></i>
                <span>Programar Visita</span>
            </a>
        </div>
    </div>

    <!-- 4 Tarjetas KPI -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Traslados Hoy -->
        <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Rutas Hoy</p>
                <div class="flex size-9 items-center justify-center rounded-lg bg-primary-50 text-primary-600">
                    <i data-lucide="calendar" class="size-5"></i>
                </div>
            </div>
            <p class="mt-3 text-3xl font-bold tracking-tight text-slate-900">{{ $trasladosHoy }}</p>
            <p class="mt-1 text-xs text-slate-500">Programados para esta jornada</p>
        </div>

        <!-- En Ruta -->
        <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">En Ruta</p>
                <div class="flex size-9 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                    <i data-lucide="truck" class="size-5"></i>
                </div>
            </div>
            <p class="mt-3 text-3xl font-bold tracking-tight text-blue-600">{{ $enRuta }}</p>
            <p class="mt-1 text-xs text-slate-500">Vehículos en desplazamiento</p>
        </div>

        <!-- Pendientes -->
        <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Por Despachar</p>
                <div class="flex size-9 items-center justify-center rounded-lg bg-amber-50 text-amber-600">
                    <i data-lucide="clock" class="size-5"></i>
                </div>
            </div>
            <p class="mt-3 text-3xl font-bold tracking-tight text-slate-900">{{ $pendientes }}</p>
            <p class="mt-1 text-xs text-slate-500">En espera de salida</p>
        </div>

        <!-- Completados Hoy -->
        <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Completados</p>
                <div class="flex size-9 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                    <i data-lucide="check-circle" class="size-5"></i>
                </div>
            </div>
            <p class="mt-3 text-3xl font-bold tracking-tight text-slate-900">{{ $completadosHoy }}</p>
            <p class="mt-1 text-xs text-slate-500">Llegadas y atenciones con éxito</p>
        </div>
    </div>

    <!-- Filtros de Búsqueda -->
    <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-xs">
        <form action="{{ route('domicilios.index') }}" method="GET" class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="relative flex-1">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-slate-400"></i>
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Buscar por dirección, cliente, mascota o referencia..."
                    class="w-full rounded-xl border border-slate-200 bg-slate-50/50 pl-9 pr-4 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus-ring"
                />
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <select
                    name="estado"
                    class="rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs font-medium text-slate-700 focus-ring"
                >
                    <option value="">Todos los estados</option>
                    <option value="PENDIENTE" {{ request('estado') === 'PENDIENTE' ? 'selected' : '' }}>Pendiente</option>
                    <option value="EN_RUTA" {{ request('estado') === 'EN_RUTA' ? 'selected' : '' }}>En Ruta</option>
                    <option value="COMPLETADO" {{ request('estado') === 'COMPLETADO' ? 'selected' : '' }}>Completado</option>
                    <option value="CANCELADO" {{ request('estado') === 'CANCELADO' ? 'selected' : '' }}>Cancelado</option>
                </select>

                <input
                    type="date"
                    name="fecha"
                    value="{{ request('fecha') }}"
                    class="rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs font-medium text-slate-700 focus-ring"
                />

                <button
                    type="submit"
                    class="rounded-xl bg-slate-900 px-4 py-2 text-xs font-semibold text-white hover:bg-slate-800 transition-colors"
                >
                    Filtrar
                </button>

                @if (request()->hasAny(['search', 'estado', 'fecha']))
                    <a
                        href="{{ route('domicilios.index') }}"
                        class="rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50"
                    >
                        Limpiar
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Si es rol TRANSPORTE: Tarjetas de Itinerario Operativo -->
    @if ($esTransporte)
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-base font-bold text-slate-900">Mis Rutas Asignadas para Hoy</h2>
                <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700">Modo Conductor Activo</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @forelse ($solicitudes as $sol)
                    @php
                        $cita = $sol->cita;
                        $paciente = $cita?->paciente;
                        $propietario = $paciente?->propietario?->persona;
                        $estado = $sol->estado ?? 'PENDIENTE';
                    @endphp
                    <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs space-y-3">
                        <div class="flex items-start justify-between">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-xs font-bold text-slate-400">#DOM-{{ $sol->id_servicio_domicilio }}</span>
                                    <span class="rounded-md px-2 py-0.5 text-[10px] font-bold uppercase {{ $estado === 'EN_RUTA' ? 'bg-blue-50 text-blue-700' : ($estado === 'COMPLETADO' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700') }}">
                                        {{ $estado }}
                                    </span>
                                </div>
                                <h3 class="text-base font-bold text-slate-900 mt-1">
                                    {{ $paciente?->nombre }} ({{ $propietario ? $propietario->nombre . ' ' . $propietario->apellido1 : 'Cliente' }})
                                </h3>
                            </div>
                            <span class="font-mono text-xs font-bold text-primary-700 bg-primary-50 px-2.5 py-1 rounded-lg">
                                {{ $cita?->fecha_hora_inicio ? \Carbon\Carbon::parse($cita->fecha_hora_inicio)->format('H:i') : 'Horario abierto' }}
                            </span>
                        </div>

                        <!-- Dirección y Contacto -->
                        <div class="space-y-1.5 text-xs text-slate-600 bg-slate-50 p-3 rounded-xl">
                            <div class="flex items-start gap-2">
                                <i data-lucide="map-pin" class="size-4 text-rose-500 shrink-0 mt-0.5"></i>
                                <div>
                                    <span class="font-bold text-slate-800">{{ $sol->direccion }}</span>
                                    @if ($sol->referencia)
                                        <p class="text-[11px] text-slate-500 mt-0.5">Ref: {{ $sol->referencia }}</p>
                                    @endif
                                </div>
                            </div>

                            @if ($propietario?->telefono)
                                <div class="flex items-center gap-2 pt-1 border-t border-slate-200/60">
                                    <i data-lucide="phone" class="size-3.5 text-slate-400"></i>
                                    <a href="tel:{{ $propietario->telefono }}" class="text-primary-600 font-semibold hover:underline">
                                        {{ $propietario->telefono }}
                                    </a>
                                </div>
                            @endif
                        </div>

                        <!-- Servicios a realizar -->
                        <div class="text-xs">
                            <span class="text-slate-400 text-[11px] font-semibold uppercase">Servicios:</span>
                            <div class="mt-1 flex flex-wrap gap-1">
                                @forelse ($cita?->detalleCitas ?? [] as $det)
                                    <span class="rounded bg-slate-100 px-2 py-0.5 text-[11px] text-slate-700">
                                        {{ $det->servicio?->nombre }}
                                    </span>
                                @empty
                                    <span class="text-slate-400 text-[11px]">Traslado veterinario</span>
                                @endforelse
                            </div>
                        </div>

                        <!-- Acciones Rápidas del Conductor -->
                        <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                            <a
                                href="https://maps.google.com/?q={{ urlencode($sol->direccion) }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center gap-1.5 text-xs font-semibold text-primary-600 hover:text-primary-700"
                            >
                                <i data-lucide="navigation" class="size-3.5"></i>
                                <span>Abrir en Maps</span>
                            </a>

                            <div class="flex items-center gap-1.5">
                                @if ($estado === 'PENDIENTE')
                                    <form action="{{ route('domicilios.cambiar-estado', $sol->id_servicio_domicilio) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="estado" value="EN_RUTA" />
                                        <button
                                            type="submit"
                                            class="rounded-xl bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700 shadow-2xs"
                                        >
                                            Iniciar Ruta
                                        </button>
                                    </form>
                                @elseif ($estado === 'EN_RUTA')
                                    <form action="{{ route('domicilios.cambiar-estado', $sol->id_servicio_domicilio) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="estado" value="COMPLETADO" />
                                        <button
                                            type="submit"
                                            class="rounded-xl bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700 shadow-2xs"
                                        >
                                            Marcar Llegada
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-8 text-center bg-white rounded-2xl border border-slate-200 p-6">
                        <p class="text-sm font-semibold text-slate-700">No tienes traslados asignados hoy</p>
                        <p class="text-xs text-slate-400 mt-1">Nuevas solicitudes aparecerán en tiempo real cuando recepción las programe.</p>
                    </div>
                @endforelse
            </div>
        </div>
    @endif

    <!-- Tabla General de Servicios a Domicilio (Recepción y Administración) -->
    <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="border-b border-slate-100 bg-slate-50/75 text-xs font-bold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-5 py-3.5">ID / Fecha</th>
                        <th class="px-4 py-3.5">Paciente y Tutor</th>
                        <th class="px-4 py-3.5">Dirección y Referencias</th>
                        <th class="px-4 py-3.5">Servicios</th>
                        <th class="px-4 py-3.5 text-right">Costo Traslado</th>
                        <th class="px-4 py-3.5 text-center">Estado</th>
                        <th class="px-5 py-3.5 text-right">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($solicitudes as $sol)
                        @php
                            $cita = $sol->cita;
                            $paciente = $cita?->paciente;
                            $propietario = $paciente?->propietario?->persona;
                            $estado = $sol->estado ?? 'PENDIENTE';
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <div class="font-mono text-xs font-bold text-slate-900">#DOM-{{ $sol->id_servicio_domicilio }}</div>
                                <div class="text-[11px] text-slate-500">
                                    {{ $cita?->fecha_hora_inicio ? \Carbon\Carbon::parse($cita->fecha_hora_inicio)->format('d/m/Y H:i') : 'S/F' }}
                                </div>
                            </td>

                            <td class="px-4 py-3.5">
                                <div class="font-bold text-slate-900 text-xs">🐾 {{ $paciente?->nombre ?? 'Paciente' }}</div>
                                <div class="text-[11px] text-slate-500">
                                    {{ $propietario ? $propietario->nombre . ' ' . $propietario->apellido1 : 'Sin Propietario' }}
                                    @if ($propietario?->telefono)
                                        · <span class="font-mono">{{ $propietario->telefono }}</span>
                                    @endif
                                </div>
                            </td>

                            <td class="px-4 py-3.5 text-xs">
                                <div class="font-medium text-slate-800">{{ $sol->direccion }}</div>
                                @if ($sol->referencia)
                                    <div class="text-[11px] text-slate-400 mt-0.5">Ref: {{ $sol->referencia }}</div>
                                @endif
                            </td>

                            <td class="px-4 py-3.5 text-xs">
                                <div class="flex flex-wrap gap-1 max-w-xs">
                                    @forelse ($cita?->detalleCitas ?? [] as $det)
                                        <span class="rounded bg-slate-100 px-1.5 py-0.5 text-[10px] text-slate-700">
                                            {{ $det->servicio?->nombre }}
                                        </span>
                                    @empty
                                        <span class="text-slate-400 text-[11px]">Traslado</span>
                                    @endforelse
                                </div>
                            </td>

                            <td class="px-4 py-3.5 text-right font-mono font-bold text-slate-900 text-xs">
                                ${{ number_format((float)($sol->costo_domicilio ?? 0), 2) }}
                            </td>

                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $estado === 'EN_RUTA' ? 'bg-blue-50 text-blue-700' : ($estado === 'COMPLETADO' ? 'bg-emerald-50 text-emerald-700' : ($estado === 'CANCELADO' ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-700')) }}">
                                    {{ $estado }}
                                </span>
                            </td>

                            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    @if ($estado === 'PENDIENTE')
                                        <form action="{{ route('domicilios.cambiar-estado', $sol->id_servicio_domicilio) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="estado" value="EN_RUTA" />
                                            <button
                                                type="submit"
                                                class="rounded-lg bg-blue-50 px-2 py-1 text-xs font-semibold text-blue-700 hover:bg-blue-100 transition-colors"
                                                title="Poner en ruta"
                                            >
                                                Despachar
                                            </button>
                                        </form>
                                    @elseif ($estado === 'EN_RUTA')
                                        <form action="{{ route('domicilios.cambiar-estado', $sol->id_servicio_domicilio) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="estado" value="COMPLETADO" />
                                            <button
                                                type="submit"
                                                class="rounded-lg bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-700 hover:bg-emerald-100 transition-colors"
                                                title="Completar entrega"
                                            >
                                                Completar
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center">
                                <x-empty-state
                                    title="No hay servicios a domicilio"
                                    description="No se encontraron traslados programados con los filtros seleccionados."
                                >
                                    <x-slot:icon>
                                        <i data-lucide="truck" class="size-8 text-slate-300"></i>
                                    </x-slot:icon>
                                </x-empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($solicitudes->hasPages())
            <div class="border-t border-slate-100 px-5 py-3">
                {{ $solicitudes->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
