@extends('layouts.app')

@section('title', 'Configuración del Sistema — La Petdiatría')

@section('content')
<div class="space-y-6" x-data="configuracionManager()">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-100 shadow-xs">
        <div>
            <div class="flex items-center gap-2 text-sm text-slate-500 mb-1">
                <span>Administración</span>
                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="text-slate-800 font-medium">Configuración</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Configuración General de la Clínica</h1>
            <p class="text-sm text-slate-500">Parámetros globales de la clínica, identidad visual, moneda y datos de contacto</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('configuracion.plantillas') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-sm font-semibold transition-all">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                Plantillas de Correo
            </a>
        </div>
    </div>

    <!-- Formulario de Configuración -->
    <form action="{{ route('configuracion.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Columna Izquierda: Identidad y Logo -->
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-xs space-y-6">
                <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">Identidad Visual</h2>
                
                <!-- Logo actual y subida -->
                <div class="space-y-3">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500">Logo de la Clínica</label>
                    <div class="flex items-center gap-4">
                        <div class="w-24 h-24 rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50 flex items-center justify-center overflow-hidden">
                            <template x-if="logoPreview">
                                <img :src="logoPreview" class="w-full h-full object-contain p-2" alt="Logo preview">
                            </template>
                            <template x-if="!logoPreview">
                                @if(!empty($config['logo']))
                                    <img src="{{ asset('storage/' . $config['logo']) }}" class="w-full h-full object-contain p-2" alt="Logo actual">
                                @else
                                    <span class="text-3xl text-slate-300">🐾</span>
                                @endif
                            </template>
                        </div>
                        <div class="flex-1">
                            <input 
                                type="file" 
                                name="logo" 
                                id="logo" 
                                accept="image/png,image/jpeg,image/webp,image/svg+xml"
                                @change="handleLogoChange($event)"
                                class="hidden"
                            >
                            <label for="logo" class="inline-flex items-center gap-2 px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl cursor-pointer transition-colors">
                                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                Cambiar Logo
                            </label>
                            <p class="text-[11px] text-slate-400 mt-1.5">PNG, JPG o SVG. Máx 2MB. Usado en recetas, carnets y tickets.</p>
                        </div>
                    </div>
                </div>

                <!-- Nombre -->
                <div>
                    <label for="nombre" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Nombre Comercial</label>
                    <input 
                        type="text" 
                        name="nombre" 
                        id="nombre" 
                        value="{{ old('nombre', $config['nombre']) }}" 
                        required 
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600"
                    >
                    @error('nombre')
                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- RUC -->
                <div>
                    <label for="ruc" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">RUC / Identificación Fiscal</label>
                    <input 
                        type="text" 
                        name="ruc" 
                        id="ruc" 
                        value="{{ old('ruc', $config['ruc']) }}" 
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600"
                    >
                    @error('ruc')
                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Columna Central: Contacto y Ubicación -->
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-xs space-y-6">
                <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">Datos de Contacto y Atención</h2>

                <!-- Dirección -->
                <div>
                    <label for="direccion" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Dirección Principal</label>
                    <input 
                        type="text" 
                        name="direccion" 
                        id="direccion" 
                        value="{{ old('direccion', $config['direccion']) }}" 
                        required 
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600"
                    >
                    @error('direccion')
                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Teléfono -->
                <div>
                    <label for="telefono" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Teléfono / WhatsApp</label>
                    <input 
                        type="text" 
                        name="telefono" 
                        id="telefono" 
                        value="{{ old('telefono', $config['telefono']) }}" 
                        required 
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600"
                    >
                    @error('telefono')
                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Correo Electrónico -->
                <div>
                    <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Correo Electrónico de Contacto</label>
                    <input 
                        type="email" 
                        name="email" 
                        id="email" 
                        value="{{ old('email', $config['email']) }}" 
                        required 
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600"
                    >
                    @error('email')
                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Horario de Atención -->
                <div>
                    <label for="horario" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Horario de Atención General</label>
                    <input 
                        type="text" 
                        name="horario" 
                        id="horario" 
                        value="{{ old('horario', $config['horario']) }}" 
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600"
                    >
                    @error('horario')
                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Columna Derecha: Moneda y Formato -->
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-xs space-y-6">
                <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">Moneda y Formatos</h2>

                <!-- Moneda de Cobro -->
                <div>
                    <label for="moneda" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Símbolo de Moneda</label>
                    <input 
                        type="text" 
                        name="moneda" 
                        id="moneda" 
                        value="{{ old('moneda', $config['moneda']) }}" 
                        required 
                        placeholder="Gs."
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600"
                    >
                    @error('moneda')
                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                    <span class="text-xs text-slate-400 mt-1 block">Ejemplo: Gs., USD, $, etc.</span>
                </div>

                <!-- Separador de Miles -->
                <div>
                    <label for="separador_miles" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Separador de Miles</label>
                    <input 
                        type="text" 
                        name="separador_miles" 
                        id="separador_miles" 
                        value="{{ old('separador_miles', $config['separador_miles']) }}" 
                        maxlength="5"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600"
                    >
                </div>

                <!-- Separador de Decimales -->
                <div>
                    <label for="separador_decimales" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Separador de Decimales</label>
                    <input 
                        type="text" 
                        name="separador_decimales" 
                        id="separador_decimales" 
                        value="{{ old('separador_decimales', $config['separador_decimales']) }}" 
                        maxlength="5"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600"
                    >
                </div>

                <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 text-xs text-slate-500 space-y-1">
                    <span class="font-bold text-slate-700 block">Ejemplo de Formato:</span>
                    <p class="font-mono text-indigo-700 font-semibold text-sm">1.500.000 Gs.</p>
                </div>
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="flex items-center justify-end gap-3 bg-white p-5 rounded-2xl border border-slate-100 shadow-xs">
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold shadow-xs hover:shadow transition-all">
                Guardar Configuración
            </button>
        </div>
    </form>
</div>

<script>
function configuracionManager() {
    return {
        logoPreview: null,
        handleLogoChange(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.logoPreview = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        }
    };
}
</script>
@endsection

