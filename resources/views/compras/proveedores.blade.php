@extends('layouts.app')

@section('title', 'Directorio de Proveedores')

@section('content')
<div class="space-y-6" x-data="{
    modalNuevo: false,
    modalEditar: false,
    editData: {
        id: null,
        nombre: '',
        nit: '',
        telefono: '',
        email: '',
        direccion: '',
        estado: 'ACTIVO',
        url: ''
    },
    abrirEditar(prov) {
        this.editData = {
            id: prov.id_proveedor,
            nombre: prov.nombre,
            nit: prov.nit || '',
            telefono: prov.telefono || '',
            email: prov.email || '',
            direccion: prov.direccion || '',
            estado: prov.estado || 'ACTIVO',
            url: '{{ url('compras/proveedores') }}/' + prov.id_proveedor
        };
        this.modalEditar = true;
    }
}">
    <!-- Header de navegación y acciones -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-100 shadow-xs">
        <div>
            <div class="flex items-center gap-2 text-sm text-slate-500 mb-1">
                <a href="{{ route('compras.index') }}" class="hover:text-indigo-600 transition-colors">Compras</a>
                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="text-slate-800 font-medium">Proveedores</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Directorio de Proveedores</h1>
            <p class="text-sm text-slate-500">Gestión de contactos comerciales, laboratorios y distribuidores</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('compras.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 transition-colors shadow-xs">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Volver a Órdenes
            </a>
            <button type="button" @click="modalNuevo = true" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold shadow-xs hover:shadow transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nuevo Proveedor
            </button>
        </div>
    </div>

    <!-- Barra de Filtros -->
    <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-xs">
        <form method="GET" action="{{ route('compras.proveedores') }}" class="flex flex-col md:flex-row gap-3">
            <div class="relative flex-1">
                <svg class="w-5 h-5 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por nombre, NIT, teléfono o email..." class="w-full pl-10 pr-4 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-sm font-medium transition-colors">
                    Filtrar
                </button>
                @if(request('search'))
                    <a href="{{ route('compras.proveedores') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-sm font-medium transition-colors">
                        Limpiar
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabla de Proveedores -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-xs overflow-hidden">
        @if($proveedores->isEmpty())
            <x-empty-state 
                title="No se encontraron proveedores" 
                description="Registra distribuidores y laboratorios para abastecer tu inventario y compras."
                actionLabel="Registrar Proveedor"
                actionUrl="#"
                @click.prevent="modalNuevo = true"
            />
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50/75 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                        <tr>
                            <th class="py-3 px-4">Proveedor / Laboratorio</th>
                            <th class="py-3 px-4">NIT / Identificación</th>
                            <th class="py-3 px-4">Contacto</th>
                            <th class="py-3 px-4">Dirección</th>
                            <th class="py-3 px-4 text-center">Compras Realizadas</th>
                            <th class="py-3 px-4 text-center">Estado</th>
                            <th class="py-3 px-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($proveedores as $prov)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-sm">
                                            {{ strtoupper(substr($prov->nombre, 0, 2)) }}
                                        </div>
                                        <div>
                                            <span class="font-semibold text-slate-800">{{ $prov->nombre }}</span>
                                            <p class="text-xs text-slate-400">ID: #{{ $prov->id_proveedor }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 font-mono text-xs text-slate-700">
                                    {{ $prov->nit ?: '—' }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="space-y-0.5 text-xs">
                                        @if($prov->telefono)
                                            <div class="flex items-center gap-1.5 text-slate-600">
                                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                                <span>{{ $prov->telefono }}</span>
                                            </div>
                                        @endif
                                        @if($prov->email)
                                            <div class="flex items-center gap-1.5 text-slate-600">
                                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>
                                                <span>{{ $prov->email }}</span>
                                            </div>
                                        @endif
                                        @if(!$prov->telefono && !$prov->email)
                                            <span class="text-slate-400">Sin datos</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-xs text-slate-600 max-w-[200px] truncate" title="{{ $prov->direccion }}">
                                    {{ $prov->direccion ?: '—' }}
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                                        {{ $prov->compras_count }} orden{{ $prov->compras_count == 1 ? '' : 'es' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if(($prov->estado ?? 'ACTIVO') === 'ACTIVO')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Activo
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200">
                                            Inactivo
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <button 
                                        type="button" 
                                        data-proveedor="{{ json_encode($prov) }}"
                                        @click="abrirEditar(JSON.parse($el.dataset.proveedor))" 
                                        class="p-1.5 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors"
                                        title="Editar Proveedor"
                                    >
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($proveedores->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $proveedores->links() }}
                </div>
            @endif
        @endif
    </div>

    <!-- Modal Nuevo Proveedor -->
    <div x-show="modalNuevo" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-cloak>
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity" @click="modalNuevo = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl border border-slate-100">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <h3 class="text-lg font-bold text-slate-900">Registrar Proveedor</h3>
                    <button type="button" @click="modalNuevo = false" class="text-slate-400 hover:text-slate-500">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form action="{{ route('compras.proveedores.store') }}" method="POST" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Nombre Comercial / Razón Social *</label>
                        <input type="text" name="nombre" required placeholder="Ej: Laboratorios Bagó / Distribuidora Pets" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">NIT / RUC</label>
                            <input type="text" name="nit" placeholder="Ej: 102938475" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Teléfono / WhatsApp</label>
                            <input type="text" name="telefono" placeholder="Ej: +591 70012345" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Correo Electrónico</label>
                        <input type="email" name="email" placeholder="contacto@proveedor.com" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Dirección / Sucursal</label>
                        <input type="text" name="direccion" placeholder="Ej: Av. Las Américas #450" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600">
                    </div>
                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button type="button" @click="modalNuevo = false" class="px-4 py-2 rounded-xl border border-slate-200 text-sm font-medium text-slate-600 hover:bg-slate-50">
                            Cancelar
                        </button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold shadow-xs">
                            Guardar Proveedor
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Proveedor -->
    <div x-show="modalEditar" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-cloak>
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity" @click="modalEditar = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl border border-slate-100">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <h3 class="text-lg font-bold text-slate-900">Editar Proveedor</h3>
                    <button type="button" @click="modalEditar = false" class="text-slate-400 hover:text-slate-500">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form :action="editData.url" method="POST" class="mt-4 space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Nombre Comercial *</label>
                        <input type="text" name="nombre" x-model="editData.nombre" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">NIT / RUC</label>
                            <input type="text" name="nit" x-model="editData.nit" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Teléfono</label>
                            <input type="text" name="telefono" x-model="editData.telefono" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Correo Electrónico</label>
                        <input type="email" name="email" x-model="editData.email" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Dirección</label>
                        <input type="text" name="direccion" x-model="editData.direccion" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Estado</label>
                        <select name="estado" x-model="editData.estado" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600">
                            <option value="ACTIVO">Activo</option>
                            <option value="INACTIVO">Inactivo</option>
                        </select>
                    </div>
                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button type="button" @click="modalEditar = false" class="px-4 py-2 rounded-xl border border-slate-200 text-sm font-medium text-slate-600 hover:bg-slate-50">
                            Cancelar
                        </button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold shadow-xs">
                            Actualizar Proveedor
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
