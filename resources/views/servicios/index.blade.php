@extends('layouts.app')

@section('title', 'Catálogo de Servicios — La Petdiatría')

@section('content')
<div class="space-y-6">

    <!-- Encabezado de la página -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Catálogo de Servicios</h1>
            <p class="text-sm text-slate-500 mt-1">Configuración de prestaciones clínicas, estéticas, duraciones estimadas y precios base.</p>
        </div>
        <div class="flex items-center gap-3">
            <a
                href="{{ route('servicios.create') }}"
                class="inline-flex items-center gap-2 rounded-xl bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-xs hover:bg-primary-700 transition-colors"
            >
                <i data-lucide="plus" class="size-4"></i>
                <span>Nuevo Servicio</span>
            </a>
        </div>
    </div>

    <!-- 4 Tarjetas KPI -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Total Servicios -->
        <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Servicios</p>
                <div class="flex size-9 items-center justify-center rounded-lg bg-primary-50 text-primary-600">
                    <i data-lucide="settings-2" class="size-5"></i>
                </div>
            </div>
            <p class="mt-3 text-3xl font-bold tracking-tight text-slate-900">{{ $totalServicios }}</p>
            <p class="mt-1 text-xs text-slate-500">Servicios en el portafolio</p>
        </div>

        <!-- Veterinarios -->
        <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Clínicos / Vet</p>
                <div class="flex size-9 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                    <i data-lucide="stethoscope" class="size-5"></i>
                </div>
            </div>
            <p class="mt-3 text-3xl font-bold tracking-tight text-slate-900">{{ $serviciosVeterinarios }}</p>
            <p class="mt-1 text-xs text-slate-500">Consultas, vacunas y cirugías</p>
        </div>

        <!-- Estéticos / SPA -->
        <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Estética / SPA</p>
                <div class="flex size-9 items-center justify-center rounded-lg bg-violet-50 text-violet-600">
                    <i data-lucide="scissors" class="size-5"></i>
                </div>
            </div>
            <p class="mt-3 text-3xl font-bold tracking-tight text-slate-900">{{ $serviciosEstetica }}</p>
            <p class="mt-1 text-xs text-slate-500">Baños, cortes y tratamientos</p>
        </div>

        <!-- A Domicilio -->
        <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">A Domicilio</p>
                <div class="flex size-9 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                    <i data-lucide="truck" class="size-5"></i>
                </div>
            </div>
            <p class="mt-3 text-3xl font-bold tracking-tight text-slate-900">{{ $disponiblesDomicilio }}</p>
            <p class="mt-1 text-xs text-slate-500">Con traslado habilitado</p>
        </div>
    </div>

    <!-- Barra de Búsqueda y Filtros -->
    <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-xs">
        <form action="{{ route('servicios.index') }}" method="GET" class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="relative flex-1">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-slate-400"></i>
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Buscar servicio por nombre o descripción..."
                    class="w-full rounded-xl border border-slate-200 bg-slate-50/50 pl-9 pr-4 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus-ring"
                />
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <!-- Filtro Categoría -->
                <select
                    name="categoria"
                    class="rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs font-medium text-slate-700 focus-ring"
                >
                    <option value="">Todas las categorías</option>
                    @foreach ($categorias as $cat)
                        <option value="{{ $cat }}" {{ request('categoria') == $cat ? 'selected' : '' }}>
                            {{ $cat }}
                        </option>
                    @endforeach
                </select>

                <!-- Filtro Domicilio -->
                <select
                    name="domicilio"
                    class="rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs font-medium text-slate-700 focus-ring"
                >
                    <option value="">Todo tipo de atención</option>
                    <option value="1" {{ request('domicilio') === '1' ? 'selected' : '' }}>Solo a domicilio</option>
                    <option value="0" {{ request('domicilio') === '0' ? 'selected' : '' }}>Solo en clínica</option>
                </select>

                <!-- Filtro Estado -->
                <select
                    name="estado"
                    class="rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs font-medium text-slate-700 focus-ring"
                >
                    <option value="">Todos los estados</option>
                    <option value="ACTIVO" {{ request('estado') === 'ACTIVO' ? 'selected' : '' }}>Activos</option>
                    <option value="INACTIVO" {{ request('estado') === 'INACTIVO' ? 'selected' : '' }}>Inactivos</option>
                </select>

                <button
                    type="submit"
                    class="rounded-xl bg-slate-900 px-4 py-2 text-xs font-semibold text-white hover:bg-slate-800 transition-colors"
                >
                    Filtrar
                </button>

                @if (request()->hasAny(['search', 'categoria', 'domicilio', 'estado']))
                    <a
                        href="{{ route('servicios.index') }}"
                        class="rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50"
                    >
                        Limpiar
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabla de Servicios -->
    <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="border-b border-slate-100 bg-slate-50/75 text-xs font-bold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-5 py-3.5">Servicio</th>
                        <th class="px-4 py-3.5">Categoría</th>
                        <th class="px-4 py-3.5 text-right">Precio Base</th>
                        <th class="px-4 py-3.5 text-center">A Domicilio</th>
                        <th class="px-4 py-3.5">Duraciones Estimadas</th>
                        <th class="px-4 py-3.5 text-center">Estado</th>
                        <th class="px-5 py-3.5 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($servicios as $serv)
                        @php
                            $esActivo = ($serv->estado === 'ACTIVO');
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition-colors {{ ! $esActivo ? 'opacity-60 bg-slate-50/30' : '' }}">
                            <!-- Nombre y Descripción -->
                            <td class="px-5 py-3.5">
                                <div class="font-bold text-slate-900 text-xs">{{ $serv->nombre }}</div>
                                @if ($serv->descripcion)
                                    <div class="text-[11px] text-slate-400 max-w-xs truncate" title="{{ $serv->descripcion }}">{{ $serv->descripcion }}</div>
                                @endif
                            </td>

                            <!-- Categoría -->
                            <td class="px-4 py-3.5 text-xs">
                                <span class="rounded-md px-2 py-0.5 font-semibold text-[11px] {{ $serv->categoria === 'VETERINARIA' ? 'bg-blue-50 text-blue-700' : ($serv->categoria === 'ESTETICA' ? 'bg-violet-50 text-violet-700' : 'bg-slate-100 text-slate-700') }}">
                                    {{ $serv->categoria }}
                                </span>
                            </td>

                            <!-- Precio Base -->
                            <td class="px-4 py-3.5 text-right font-mono font-bold text-slate-900 text-xs">
                                ${{ number_format((float)$serv->precio_base, 2) }}
                            </td>

                            <!-- A Domicilio -->
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                @if ($serv->disponible_domicilio)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700">
                                        <i data-lucide="check" class="size-3"></i>
                                        <span>Sí</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-400">
                                        No
                                    </span>
                                @endif
                            </td>

                            <!-- Duraciones Estimadas -->
                            <td class="px-4 py-3.5 text-xs">
                                @if ($serv->tiemposEstimados->isNotEmpty())
                                    <div class="flex flex-wrap gap-1">
                                        @foreach ($serv->tiemposEstimados->take(2) as $t)
                                            <span class="rounded-md bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-600">
                                                {{ $t->especie?->nombre ?? 'Gral' }}: {{ $t->duracion_minutos }}m
                                            </span>
                                        @endforeach
                                        @if ($serv->tiemposEstimados->count() > 2)
                                            <span class="text-[10px] text-slate-400">+{{ $serv->tiemposEstimados->count() - 2 }} más</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-[11px] text-slate-400 italic">30m (defecto)</span>
                                @endif
                            </td>

                            <!-- Switch de Estado (Activar/Desactivar) -->
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                <form action="{{ route('servicios.toggle-estado', $serv->id_servicio) }}" method="POST">
                                    @csrf
                                    <button
                                        type="submit"
                                        class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold cursor-pointer transition-colors {{ $esActivo ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' }}"
                                        title="Clic para cambiar estado"
                                    >
                                        <span class="size-1.5 rounded-full {{ $esActivo ? 'bg-emerald-600' : 'bg-slate-400' }}"></span>
                                        <span>{{ $esActivo ? 'Activo' : 'Inactivo' }}</span>
                                    </button>
                                </form>
                            </td>

                            <!-- Acciones -->
                            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                <a
                                    href="{{ route('servicios.edit', $serv->id_servicio) }}"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 transition-colors"
                                >
                                    <i data-lucide="edit-3" class="size-3.5 text-slate-400"></i>
                                    <span>Editar</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center">
                                <x-empty-state
                                    title="No hay servicios registrados"
                                    description="No se encontraron prestaciones con los filtros seleccionados."
                                >
                                    <x-slot:icon>
                                        <i data-lucide="settings-2" class="size-8 text-slate-300"></i>
                                    </x-slot:icon>
                                </x-empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($servicios->hasPages())
            <div class="border-t border-slate-100 px-5 py-3">
                {{ $servicios->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
