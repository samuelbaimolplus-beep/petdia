@extends('layouts.app')

@section('title', 'Iniciar Atención — La Petdiatría')

@section('content')
<div class="space-y-6" x-data="{
    tipoAtencion: '{{ $cita && str_contains(strtolower($cita->servicios->first()?->tipo_servicio ?? ''), 'estet') ? 'ESTETICA' : 'VETERINARIA' }}',
    serviciosRows: [
        @if ($cita && $cita->servicios->isNotEmpty())
            @foreach ($cita->servicios as $s)
                { id_servicio: '{{ $s->id_servicio }}', precio: {{ (float) $s->precio_base }}, cantidad: 1, descuento: 0 },
            @endforeach
        @else
            { id_servicio: '', precio: 0, cantidad: 1, descuento: 0 }
        @endif
    ],
    catalogoServicios: {{ Js::from($servicios) }},
    calcularTotal() {
        return this.serviciosRows.reduce((acc, row) => {
            let sub = (parseFloat(row.precio || 0) * parseFloat(row.cantidad || 1)) - parseFloat(row.descuento || 0);
            return acc + (sub > 0 ? sub : 0);
        }, 0);
    },
    actualizarPrecio(index) {
        let srvId = this.serviciosRows[index].id_servicio;
        let srv = this.catalogoServicios.find(s => s.id_servicio == srvId);
        if (srv) {
            this.serviciosRows[index].precio = parseFloat(srv.precio_base || 0);
        } else {
            this.serviciosRows[index].precio = 0;
        }
    },
    agregarServicio() {
        this.serviciosRows.push({ id_servicio: '', precio: 0, cantidad: 1, descuento: 0 });
    },
    quitarServicio(index) {
        if (this.serviciosRows.length > 1) {
            this.serviciosRows.splice(index, 1);
        }
    }
}">
    {{-- Breadcrumb y Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('atenciones.index') }}" class="hover:text-indigo-600 transition-colors">Atenciones</a>
                <span>/</span>
                <span class="text-slate-700 font-medium">Nueva Atención</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Iniciar Nueva Atención</h1>
            <p class="text-sm text-slate-500 mt-1">Registre el ingreso de un paciente a consultorio médico o al área de estética canina/felina.</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('atenciones.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2 border border-slate-200 text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-50 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Volver</span>
            </a>
        </div>
    </div>

    @if ($cita)
        <div class="p-4 bg-indigo-50/70 border border-indigo-100 rounded-xl flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold text-sm">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-indigo-950">Atención derivada de Cita #{{ $cita->id_cita }}</p>
                    <p class="text-xs text-indigo-700">Paciente: <span class="font-medium">{{ $cita->paciente->nombre }}</span> · Agendada para: {{ \Carbon\Carbon::parse($cita->fecha_hora_inicio)->isoFormat('D MMM YYYY, HH:mm') }}</p>
                </div>
            </div>
            <input type="hidden" name="id_cita" value="{{ $cita->id_cita }}" form="formAtencion">
        </div>
    @endif

    <form id="formAtencion" method="POST" action="{{ route('atenciones.store') }}" class="space-y-6">
        @csrf
        @if ($cita)
            <input type="hidden" name="id_cita" value="{{ $cita->id_cita }}">
        @endif

        {{-- PASO 1: PACIENTE Y PROFESIONAL --}}
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs p-6 space-y-4">
            <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-2">1. Datos Generales de la Atención</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Paciente *</label>
                    @if ($cita)
                        <input type="hidden" name="id_paciente" value="{{ $cita->id_paciente }}">
                        <input type="text" disabled value="{{ $cita->paciente->nombre }} ({{ $cita->paciente->propietario->persona->nombre }} {{ $cita->paciente->propietario->persona->apellido1 }})" class="w-full text-sm px-3 py-2 border border-slate-200 rounded-lg bg-slate-50 text-slate-700">
                    @else
                        <select name="id_paciente" required class="w-full text-sm px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            <option value="">Seleccione paciente...</option>
                            @foreach ($pacientes as $pac)
                                <option value="{{ $pac->id_paciente }}">
                                    {{ $pac->nombre }} — {{ $pac->especie->nombre ?? 'Mascota' }} (Dueño: {{ $pac->propietario->persona->nombre ?? '' }} {{ $pac->propietario->persona->apellido1 ?? '' }})
                                </option>
                            @endforeach
                        </select>
                    @endif
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Profesional Responsable *</label>
                    <select name="id_profesional" required class="w-full text-sm px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option value="">Seleccione profesional...</option>
                        @foreach ($profesionales as $p)
                            <option value="{{ $p->id_persona }}" @selected($cita && $cita->id_profesional == $p->id_persona)>
                                {{ $p->persona->nombre }} {{ $p->persona->apellido1 }} ({{ $p->especialidad }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Fecha y Hora de Inicio *</label>
                    <input type="datetime-local" name="fecha_hora_inicio" value="{{ date('Y-m-d\TH:i') }}" required class="w-full text-sm px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tipo de Servicio / Área *</label>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" @click="tipoAtencion = 'VETERINARIA'" :class="tipoAtencion === 'VETERINARIA' ? 'bg-indigo-600 text-white font-bold' : 'bg-slate-100 text-slate-700 font-medium'" class="py-2 px-3 text-xs rounded-lg transition-colors">
                            Atención Médica
                        </button>
                        <button type="button" @click="tipoAtencion = 'ESTETICA'" :class="tipoAtencion === 'ESTETICA' ? 'bg-indigo-600 text-white font-bold' : 'bg-slate-100 text-slate-700 font-medium'" class="py-2 px-3 text-xs rounded-lg transition-colors">
                            Estética / Spa
                        </button>
                    </div>
                    <input type="hidden" name="tipo_atencion" :value="tipoAtencion">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Estado Inicial *</label>
                    <select name="estado" required class="w-full text-sm px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option value="EN_CURSO">EN CURSO (Iniciando ahora)</option>
                        <option value="FINALIZADA">FINALIZADA (Concluida)</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- PASO 2: CAMPOS ESPECÍFICOS SEGÚN TIPO --}}
        {{-- Variante Médica --}}
        <div x-show="tipoAtencion === 'VETERINARIA'" class="bg-white rounded-xl border border-slate-200/80 shadow-xs p-6 space-y-4">
            <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-2">2. Detalle Clínico (Veterinaria)</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Motivo de la Consulta</label>
                    <input type="text" name="motivo_consulta" placeholder="Ej. Control de rutina, tos seca, claudicación pata derecha..." class="w-full text-sm px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Anamnesis / Antecedentes</label>
                    <textarea name="anamnesis" rows="3" placeholder="Información provista por el tutor, tiempo de evolución, apetito..." class="w-full text-sm px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Síntomas y Hallazgos del Examen</label>
                    <textarea name="sintomas" rows="3" placeholder="Signos observados, temperatura, mucosas, auscultación..." class="w-full text-sm px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Conclusión / Diagnóstico Presuntivo</label>
                    <textarea name="conclusion" rows="2" placeholder="Diagnóstico principal o conclusión del médico..." class="w-full text-sm px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Fecha Sugerida de Reconsulta</label>
                    <input type="date" name="fecha_reconsulta" class="w-full text-sm px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
        </div>

        {{-- Variante Estética --}}
        <div x-show="tipoAtencion === 'ESTETICA'" class="bg-white rounded-xl border border-slate-200/80 shadow-xs p-6 space-y-4" style="display: none;">
            <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-2">2. Detalle del Servicio Estético (Grooming)</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Modalidad de Servicio</label>
                    <select name="modalidad" class="w-full text-sm px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option value="BAÑO Y CORTE">Baño y Corte Estándar</option>
                        <option value="BAÑO MEDICADO">Baño Medicado / Dermatológico</option>
                        <option value="CORTE HIGIENICO">Corte Higiénico</option>
                        <option value="DESLANADO">Deslanado / Cepillado Profundo</option>
                        <option value="TRATAMIENTO_PELAJE">Tratamiento Hidratante de Pelaje</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Estado del Pelaje al Recibir</label>
                    <select name="estado_pelaje" class="w-full text-sm px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option value="BUENO">Buen estado general (sin nudos)</option>
                        <option value="ENREDADO_LEVE">Enredado leve</option>
                        <option value="MOTAS_SEVERAS">Motas severas / Fieltrado</option>
                        <option value="SUCIO">Suciedad abundante</option>
                        <option value="DERMATITIS">Presencia de pulgas / Dermatitis visible</option>
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Características Observadas / Instrucciones del Tutor</label>
                    <textarea name="caracteristicas_observadas" rows="3" placeholder="Ej. No cortar mucho las orejas, aplicar perfume hipoalergénico, cicatriz en lomo..." class="w-full text-sm px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>
            </div>
        </div>

        {{-- PASO 3: SERVICIOS APLICADOS Y CARGOS --}}
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                <div>
                    <h2 class="text-base font-bold text-slate-900">3. Servicios Realizados</h2>
                    <p class="text-xs text-slate-500">Detalle los servicios que componen esta atención para la facturación.</p>
                </div>
                <button type="button" @click="agregarServicio()" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold rounded-lg transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Agregar Fila</span>
                </button>
            </div>

            <div class="space-y-3">
                <template x-for="(row, index) in serviciosRows" :key="index">
                    <div class="grid grid-cols-12 gap-3 items-center p-3 border border-slate-100 rounded-xl bg-slate-50/50">
                        <div class="col-span-5">
                            <label class="block text-[11px] font-semibold text-slate-500 mb-0.5">Servicio *</label>
                            <select :name="'servicios[' + index + '][id_servicio]'" x-model="row.id_servicio" @change="actualizarPrecio(index)" required class="w-full text-xs px-2.5 py-1.5 border border-slate-200 rounded-lg bg-white">
                                <option value="">Seleccione servicio...</option>
                                <template x-for="srv in catalogoServicios" :key="srv.id_servicio">
                                    <option :value="srv.id_servicio" x-text="srv.nombre + ' (Bs. ' + parseFloat(srv.precio_base).toFixed(2) + ')'"></option>
                                </template>
                            </select>
                        </div>

                        <div class="col-span-2">
                            <label class="block text-[11px] font-semibold text-slate-500 mb-0.5">Cant.</label>
                            <input type="number" :name="'servicios[' + index + '][cantidad]'" x-model.number="row.cantidad" min="1" step="1" required class="w-full text-xs px-2.5 py-1.5 border border-slate-200 rounded-lg bg-white">
                        </div>

                        <div class="col-span-2">
                            <label class="block text-[11px] font-semibold text-slate-500 mb-0.5">Precio Unit.</label>
                            <p class="font-mono text-xs font-semibold text-slate-800 py-1.5" x-text="'Bs. ' + (parseFloat(row.precio || 0)).toFixed(2)"></p>
                        </div>

                        <div class="col-span-2">
                            <label class="block text-[11px] font-semibold text-slate-500 mb-0.5">Descuento</label>
                            <input type="number" :name="'servicios[' + index + '][descuento]'" x-model.number="row.descuento" min="0" step="0.5" class="w-full text-xs px-2.5 py-1.5 border border-slate-200 rounded-lg bg-white">
                        </div>

                        <div class="col-span-1 text-right pt-4">
                            <button type="button" @click="quitarServicio(index)" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-white" title="Quitar">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Resumen Total en Tiempo Real --}}
            <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                <span class="text-sm font-semibold text-slate-700">Total Estimado de la Atención:</span>
                <span class="text-xl font-bold font-mono text-indigo-600" x-text="'Bs. ' + calcularTotal().toFixed(2)"></span>
            </div>
        </div>

        {{-- PASO 4: OBSERVACIONES Y CIERRE --}}
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs p-6 space-y-4">
            <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-2">4. Observaciones y Recomendaciones</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Observaciones Internas</label>
                    <textarea name="observaciones" rows="2" placeholder="Comportamiento del paciente, detalles técnicos..." class="w-full text-sm px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Recomendaciones para el Propietario</label>
                    <textarea name="recomendaciones" rows="2" placeholder="Cuidados posteriores, dieta, pauta de medicación..." class="w-full text-sm px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('atenciones.index') }}" class="px-4 py-2 border border-slate-200 text-slate-700 text-sm font-medium rounded-xl hover:bg-slate-50 transition-colors">
                    Cancelar
                </a>
                <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-xs transition-colors">
                    Guardar e Iniciar Atención
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
