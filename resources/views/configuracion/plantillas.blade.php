@extends('layouts.app')

@section('title', 'Plantillas de Mensajes — La Petdiatría')

@section('content')
<div class="space-y-6" x-data="plantillaManager()">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-100 shadow-xs">
        <div>
            <div class="flex items-center gap-2 text-sm text-slate-500 mb-1">
                <span>Configuración</span>
                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="text-slate-800 font-medium">Plantillas de Notificación</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Plantillas de Mensajes y Correos</h1>
            <p class="text-sm text-slate-500">Personaliza los avisos automáticos enviados a los propietarios de mascotas</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('configuracion.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-sm font-semibold transition-all">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Configuración General
            </a>
            <a href="{{ route('recordatorios.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-sm font-semibold transition-all">
                Ver Recordatorios
            </a>
        </div>
    </div>

    <!-- Contenido de Edición y Previsualización -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Formulario -->
        <div class="lg:col-span-7 bg-white p-6 rounded-2xl border border-slate-100 shadow-xs space-y-6">
            <div class="border-b border-slate-100 pb-4">
                <h2 class="text-lg font-bold text-slate-900">Plantilla de Recordatorio por Correo</h2>
                <p class="text-xs text-slate-500 mt-1">Este correo se envía automáticamente al marcar un recordatorio como enviado.</p>
            </div>

            <!-- Variables disponibles -->
            <div class="bg-indigo-50/50 border border-indigo-100 rounded-xl p-4">
                <span class="text-xs font-bold text-indigo-900 uppercase tracking-wider block mb-2">Variables dinámicas disponibles (haz clic para insertar):</span>
                <div class="flex flex-wrap gap-2">
                    <button type="button" @click="insertVariable('@{{nombre_mascota}}')" class="px-2.5 py-1 bg-white hover:bg-indigo-100 border border-indigo-200 rounded-lg text-xs font-mono font-medium text-indigo-700 transition-colors shadow-2xs">
                        @{{nombre_mascota}}
                    </button>
                    <button type="button" @click="insertVariable('@{{nombre_propietario}}')" class="px-2.5 py-1 bg-white hover:bg-indigo-100 border border-indigo-200 rounded-lg text-xs font-mono font-medium text-indigo-700 transition-colors shadow-2xs">
                        @{{nombre_propietario}}
                    </button>
                    <button type="button" @click="insertVariable('@{{fecha_cita}}')" class="px-2.5 py-1 bg-white hover:bg-indigo-100 border border-indigo-200 rounded-lg text-xs font-mono font-medium text-indigo-700 transition-colors shadow-2xs">
                        @{{fecha_cita}}
                    </button>
                    <button type="button" @click="insertVariable('@{{tipo_recordatorio}}')" class="px-2.5 py-1 bg-white hover:bg-indigo-100 border border-indigo-200 rounded-lg text-xs font-mono font-medium text-indigo-700 transition-colors shadow-2xs">
                        @{{tipo_recordatorio}}
                    </button>
                    <button type="button" @click="insertVariable('@{{clinica_nombre}}')" class="px-2.5 py-1 bg-white hover:bg-indigo-100 border border-indigo-200 rounded-lg text-xs font-mono font-medium text-indigo-700 transition-colors shadow-2xs">
                        @{{clinica_nombre}}
                    </button>
                </div>
            </div>

            <form action="{{ route('configuracion.plantillas.update') }}" method="POST" class="space-y-5">
                @csrf

                <!-- Asunto -->
                <div>
                    <label for="asunto" class="block text-sm font-semibold text-slate-700 mb-1">Asunto del Correo</label>
                    <input 
                        type="text" 
                        name="asunto" 
                        id="asunto" 
                        x-model="asunto"
                        required 
                        maxlength="200"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600"
                    >
                    @error('asunto')
                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Cuerpo -->
                <div>
                    <label for="cuerpo" class="block text-sm font-semibold text-slate-700 mb-1">Cuerpo del Mensaje</label>
                    <textarea 
                        name="cuerpo" 
                        id="cuerpo" 
                        rows="8" 
                        x-ref="cuerpoInput"
                        x-model="cuerpo"
                        required 
                        maxlength="3000"
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm font-sans focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 leading-relaxed"
                    ></textarea>
                    @error('cuerpo')
                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                    <span class="text-xs text-slate-400 block mt-1">El mensaje soporta saltos de línea y las variables dinámicas se sustituirán antes de enviar.</span>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold shadow-xs hover:shadow transition-all">
                        Guardar Plantilla
                    </button>
                </div>
            </form>
        </div>

        <!-- Vista Previa Interactiva -->
        <div class="lg:col-span-5 space-y-4">
            <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    Vista Previa del Correo en Tiempo Real
                </span>

                <!-- Simulación Cliente Email -->
                <div class="border border-slate-200 rounded-xl overflow-hidden bg-slate-50 text-xs">
                    <!-- Barra de correo -->
                    <div class="bg-slate-100 p-3 border-b border-slate-200 space-y-1">
                        <div><strong class="text-slate-600">De:</strong> <span class="text-slate-500">La Petdiatría &lt;contacto@lapetdiatria.com.py&gt;</span></div>
                        <div><strong class="text-slate-600">Para:</strong> <span class="text-slate-500">Carlos Giménez &lt;carlos@ejemplo.com&gt;</span></div>
                        <div><strong class="text-slate-600">Asunto:</strong> <span class="text-slate-800 font-semibold" x-text="previewAsunto"></span></div>
                    </div>

                    <!-- Contenedor del Correo -->
                    <div class="p-4 bg-white">
                        <div class="rounded-xl border border-teal-100 overflow-hidden shadow-2xs">
                            <div class="bg-gradient-to-r from-teal-600 to-teal-700 text-white p-4 text-center">
                                <h3 class="font-bold text-sm">🐾 Veterinaria La Petdiatría</h3>
                                <p class="text-[11px] opacity-90 mt-0.5">Atención médica de confianza</p>
                            </div>
                            <div class="p-4 space-y-3 bg-white text-slate-700 leading-relaxed text-xs">
                                <p class="whitespace-pre-line" x-text="previewCuerpo"></p>
                                
                                <div class="bg-teal-50/70 border border-teal-100 rounded-lg p-3 space-y-1 text-[11px]">
                                    <div><strong class="text-teal-800">Mascota:</strong> 🐾 Toby</div>
                                    <div><strong class="text-teal-800">Tipo de servicio:</strong> Vacuna Séxtuple</div>
                                    <div><strong class="text-teal-800">Fecha y hora:</strong> 📅 28/09/2026 10:30</div>
                                </div>

                                <p class="text-[10px] text-slate-400">
                                    Si necesitas reprogramar, comunícate al +595 21 600 700.
                                </p>
                            </div>
                            <div class="bg-slate-50 p-2 text-center text-[10px] text-slate-400 border-t border-slate-100">
                                © 2026 Veterinaria La Petdiatría. Todos los derechos reservados.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function plantillaManager() {
    return {
        asunto: @json($plantillaAsunto),
        cuerpo: @json($plantillaCuerpo),
        get previewAsunto() {
            return this.asunto
                .replaceAll('@{{nombre_mascota}}', 'Toby')
                .replaceAll('@{{clinica_nombre}}', 'Veterinaria La Petdiatría');
        },
        get previewCuerpo() {
            return this.cuerpo
                .replaceAll('@{{nombre_mascota}}', 'Toby')
                .replaceAll('@{{nombre_propietario}}', 'Carlos Giménez')
                .replaceAll('@{{fecha_cita}}', '28/09/2026 10:30')
                .replaceAll('@{{tipo_recordatorio}}', 'Vacuna Séxtuple')
                .replaceAll('@{{clinica_nombre}}', 'Veterinaria La Petdiatría');
        },
        insertVariable(variable) {
            const el = this.$refs.cuerpoInput;
            const start = el.selectionStart;
            const end = el.selectionEnd;
            const text = this.cuerpo;
            this.cuerpo = text.substring(0, start) + variable + text.substring(end);
            this.$nextTick(() => {
                el.focus();
                el.setSelectionRange(start + variable.length, start + variable.length);
            });
        }
    };
}
</script>
@endsection
