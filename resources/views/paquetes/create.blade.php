@extends('layouts.app')

@section('title', 'Crear Paquete — La Petdiatría')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{
    serviciosDisponibles: {{ Js::from($servicios->map(fn($s) => [
        'id_servicio' => $s->id_servicio,
        'nombre' => $s->nombre,
        'precio_base' => (float) $s->precio_base,
        'categoria' => $s->categoria
    ])) }},
    serviciosSeleccionados: [
        {
            id_servicio: '{{ $servicios->first()?->id_servicio ?? "" }}',
            cantidad_incluida: 1
        }
    ],

    agregarServicio() {
        this.serviciosSeleccionados.push({
            id_servicio: this.serviciosDisponibles[0]?.id_servicio || '',
            cantidad_incluida: 1
        });
    },

    eliminarServicio(index) {
        if (this.serviciosSeleccionados.length > 1) {
            this.serviciosSeleccionados.splice(index, 1);
        }
    },

    get totalValorEstimado() {
        return this.serviciosSeleccionados.reduce((total, item) => {
            const s = this.serviciosDisponibles.find(sd => sd.id_servicio == item.id_servicio);
            const precio = s ? s.precio_base : 0;
            return total + (precio * (parseInt(item.cantidad_incluida) || 1));
        }, 0);
    }
}">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400">
                <a href="{{ route('paquetes.index') }}" class="hover:text-indigo-600 transition-colors">Paquetes</a>
                <span>/</span>
                <span class="text-slate-600 font-medium">Crear Paquete</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 mt-1">Nuevo Paquete de Servicios</h1>
            <p class="text-sm text-slate-500">Configura un plan con asistencias y servicios incluidos a precio preferencial.</p>
        </div>
        <a href="{{ route('paquetes.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-200 bg-white text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 transition-colors">
            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Volver
        </a>
    </div>

    <!-- Formulario -->
    <form action="{{ route('paquetes.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Datos Básicos -->
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-xs space-y-4">
            <h2 class="text-base font-bold text-slate-900 pb-3 border-b border-slate-100">Información General del Plan</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Nombre del Paquete / Plan *</label>
                    <input type="text" name="nombre" value="{{ old('nombre') }}" required placeholder="Ej: Plan Cachorro Sano / Combo Spa Relax 4 Sesiones" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600">
                    @error('nombre') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Descripción y Beneficios</label>
                    <textarea name="descripcion" rows="3" placeholder="Describe los beneficios para la mascota, vigencia recomendada, etc." class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600">{{ old('descripcion') }}</textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Precio Final del Paquete (Bs.) *</label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-400 font-mono">Bs.</span>
                        <input type="number" step="0.01" min="0" name="precio" value="{{ old('precio', 0) }}" required class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600">
                    </div>
                    @error('precio') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Estado de Comercialización *</label>
                    <select name="estado" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600">
                        <option value="ACTIVO" {{ old('estado') === 'ACTIVO' ? 'selected' : '' }}>Activo (Disponible)</option>
                        <option value="INACTIVO" {{ old('estado') === 'INACTIVO' ? 'selected' : '' }}>Inactivo (Pausado)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Servicios y Asistencias Incluidas -->
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-xs space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Servicios Incluidos en el Paquete</h2>
                    <p class="text-xs text-slate-400">Define cuántas sesiones o asistencias de cada servicio se otorgan al comprar el paquete.</p>
                </div>
                <button type="button" @click="agregarServicio()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-semibold transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Añadir Servicio
                </button>
            </div>

            <!-- Lista dinámica -->
            <div class="space-y-3">
                <template x-for="(item, idx) in serviciosSeleccionados" :key="idx">
                    <div class="p-3.5 bg-slate-50/75 rounded-xl border border-slate-200/80 flex flex-col sm:flex-row sm:items-center gap-3">
                        <div class="flex-1">
                            <label class="block text-[11px] font-bold uppercase text-slate-500 mb-1">Servicio Catálogo</label>
                            <select :name="'servicios[' + idx + '][id_servicio]'" x-model="item.id_servicio" required class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600">
                                <template x-for="srv in serviciosDisponibles" :key="srv.id_servicio">
                                    <option :value="srv.id_servicio" x-text="srv.nombre + ' (' + srv.categoria + ' - Bs. ' + srv.precio_base + ')'"></option>
                                </template>
                            </select>
                        </div>
                        <div class="w-full sm:w-36">
                            <label class="block text-[11px] font-bold uppercase text-slate-500 mb-1">Cantidad / Sesiones</label>
                            <input type="number" min="1" :name="'servicios[' + idx + '][cantidad_incluida]'" x-model="item.cantidad_incluida" required class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm bg-white font-mono font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600">
                        </div>
                        <div class="sm:pt-5 flex justify-end">
                            <button type="button" @click="eliminarServicio(idx)" :disabled="serviciosSeleccionados.length === 1" class="p-2 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition-colors disabled:opacity-30 disabled:cursor-not-allowed" title="Eliminar servicio">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Resumen estimativo -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500 bg-indigo-50/50 p-4 rounded-xl border border-indigo-100/60">
                <span>Valor referencial si se compraran los servicios por separado:</span>
                <span class="font-mono font-bold text-slate-800 text-sm" x-text="'Bs. ' + totalValorEstimado.toFixed(2)"></span>
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('paquetes.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
                Cancelar
            </a>
            <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold shadow-xs hover:shadow transition-all">
                Guardar Paquete
            </button>
        </div>
    </form>
</div>
@endsection
