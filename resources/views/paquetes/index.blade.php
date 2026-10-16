@extends('layouts.app')

@section('title', 'Paquetes y Planes — La Petdiatría')

@section('content')
<div class="space-y-6">
    <!-- Header de navegación y acciones -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-100 shadow-xs">
        <div>
            <div class="flex items-center gap-2 text-sm text-slate-500 mb-1">
                <span>Administración</span>
                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="text-slate-800 font-medium">Paquetes y Planes</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Catálogo de Paquetes y Planes</h1>
            <p class="text-sm text-slate-500">Configuración de membresías de salud preventiva, estética recurrente y paquetes clínicos</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('paquetes.suscripciones') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 transition-colors shadow-xs">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                Gestionar Suscripciones
            </a>
            <a href="{{ route('paquetes.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold shadow-xs hover:shadow transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nuevo Paquete
            </a>
        </div>
    </div>

    <!-- 4 Tarjetas KPI -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Paquetes -->
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Paquetes</span>
                <p class="text-2xl font-bold text-slate-900 mt-1">{{ $totalPaquetes }}</p>
                <span class="text-xs text-slate-500 mt-0.5 block">Configurados en catálogo</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            </div>
        </div>

        <!-- Paquetes Activos -->
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Planes Activos</span>
                <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $paquetesActivos }}</p>
                <span class="text-xs text-slate-500 mt-0.5 block">Disponibles para venta</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <!-- Suscripciones Activas -->
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Suscripciones Vigentes</span>
                <p class="text-2xl font-bold text-indigo-600 mt-1">{{ $suscripcionesActivas }}</p>
                <span class="text-xs text-slate-500 mt-0.5 block">Clientes con plan en curso</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-violet-50 border border-violet-100 flex items-center justify-center text-violet-600">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            </div>
        </div>

        <!-- Pacientes Beneficiados -->
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Mascotas Suscritas</span>
                <p class="text-2xl font-bold text-slate-900 mt-1">{{ $totalPacientesSuscritos }}</p>
                <span class="text-xs text-slate-500 mt-0.5 block">Pacientes fidelizados</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-600">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
            </div>
        </div>
    </div>

    <!-- Barra de Filtros -->
    <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-xs">
        <form method="GET" action="{{ route('paquetes.index') }}" class="flex flex-col md:flex-row gap-3">
            <div class="relative flex-1">
                <svg class="w-5 h-5 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar paquete por nombre o descripción..." class="w-full pl-10 pr-4 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600">
            </div>
            <div class="w-full md:w-48">
                <select name="estado" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600">
                    <option value="">Todos los Estados</option>
                    <option value="ACTIVO" {{ request('estado') === 'ACTIVO' ? 'selected' : '' }}>Activo</option>
                    <option value="INACTIVO" {{ request('estado') === 'INACTIVO' ? 'selected' : '' }}>Inactivo</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-sm font-medium transition-colors">
                    Filtrar
                </button>
                @if(request('search') || request('estado'))
                    <a href="{{ route('paquetes.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-sm font-medium transition-colors">
                        Limpiar
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Grilla de Paquetes -->
    @if($paquetes->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-100 p-6 shadow-xs">
            <x-empty-state 
                title="No se encontraron paquetes registrados" 
                description="Crea paquetes de servicios clínicos o planes de spa con asistencias incluidas para fidelizar clientes."
                actionLabel="Crear Primer Paquete"
                actionUrl="{{ route('paquetes.create') }}"
            />
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($paquetes as $pkg)
                <div class="bg-white rounded-2xl border border-slate-100 shadow-xs hover:shadow-md transition-all flex flex-col justify-between overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <div>
                                <h3 class="text-lg font-bold text-slate-900">{{ $pkg->nombre }}</h3>
                                <span class="text-xs text-slate-400 font-mono">Plan #{{ $pkg->id_paquete }}</span>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $pkg->estado === 'ACTIVO' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600 border border-slate-200' }}">
                                {{ $pkg->estado }}
                            </span>
                        </div>

                        <p class="text-xs text-slate-500 mb-4 line-clamp-2">{{ $pkg->descripcion ?: 'Sin descripción detallada.' }}</p>

                        <!-- Precio -->
                        <div class="bg-slate-50 rounded-xl p-3 mb-4 flex items-center justify-between">
                            <span class="text-xs text-slate-500 font-medium">Costo Total</span>
                            <span class="text-lg font-extrabold text-indigo-700 font-mono">Bs. {{ number_format($pkg->precio, 2) }}</span>
                        </div>

                        <!-- Servicios Incluidos -->
                        <div>
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-2">Servicios / Asistencias Incluidas</span>
                            <ul class="space-y-1.5 text-xs text-slate-600">
                                @forelse($pkg->detallePaquetes as $det)
                                    <li class="flex items-center justify-between py-1 border-b border-slate-50 last:border-0">
                                        <div class="flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            <span class="truncate">{{ $det->servicio->nombre ?? 'Servicio' }}</span>
                                        </div>
                                        <span class="font-bold text-slate-800 bg-slate-100 px-2 py-0.5 rounded-md font-mono">{{ $det->cantidad_incluida }}x</span>
                                    </li>
                                @empty
                                    <li class="text-slate-400 italic">No tiene servicios asociados</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>

                    <!-- Footer de Acciones -->
                    <div class="px-6 py-3 bg-slate-50/75 border-t border-slate-100 flex items-center justify-between text-xs">
                        <span class="text-slate-400">
                            {{ $pkg->suscripciones->count() }} suscripcion{{ $pkg->suscripciones->count() == 1 ? '' : 'es' }}
                        </span>
                        <div class="flex items-center gap-2">
                            <form action="{{ route('paquetes.toggle-estado', $pkg) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="px-2.5 py-1 text-slate-600 hover:text-slate-900 font-medium hover:bg-slate-200/60 rounded-lg transition-colors" title="Cambiar Estado">
                                    {{ $pkg->estado === 'ACTIVO' ? 'Desactivar' : 'Activar' }}
                                </button>
                            </form>
                            <a href="{{ route('paquetes.edit', $pkg) }}" class="inline-flex items-center gap-1 px-3 py-1 bg-white hover:bg-slate-100 border border-slate-200 font-semibold text-slate-700 rounded-lg shadow-2xs transition-colors">
                                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                Editar
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if($paquetes->hasPages())
            <div class="p-4 bg-white rounded-2xl border border-slate-100 shadow-xs">
                {{ $paquetes->links() }}
            </div>
        @endif
    @endif
</div>
@endsection
