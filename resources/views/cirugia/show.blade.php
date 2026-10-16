<x-layout title="Ficha Quirúrgica — {{ $cirugia->tipo_cirugia }} — La Petdiatría">
    @php
        $paciente = $cirugia->paciente;
        $propietario = $paciente?->propietario?->persona;
        $telefono = $propietario?->telefono;
        $telLimpio = $telefono ? preg_replace('/[^0-9]/', '', $telefono) : null;
        if ($telLimpio && strlen($telLimpio) === 8) {
            $telLimpio = '591' . $telLimpio;
        }
        $msgWa = urlencode("Hola {$propietario?->nombre}, te escribimos desde La Petdiatría respecto a la cirugía de {$paciente?->nombre} ({$cirugia->tipo_cirugia}).");
    @endphp

    <div
        x-data="{
            modalFinalizarOpen: false,
            modalConsentimientoOpen: false,
            modalCancelarOpen: false,
            modalControlOpen: false
        }"
        class="space-y-6"
    >
        <x-breadcrumb :items="[
            ['label' => 'Quirófano y Cirugías', 'url' => route('cirugias.index')],
            ['label' => $paciente ? $paciente->nombre : 'Paciente', 'url' => $paciente ? route('historial.show', $paciente->id_paciente) : null],
            ['label' => 'Ficha Quirúrgica #' . $cirugia->id_procedimiento, 'url' => null],
        ]" />

        <!-- Encabezado con Estado y Botones de Acción -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <a
                    href="{{ route('cirugias.index') }}"
                    class="inline-flex size-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 hover:bg-slate-50 hover:text-slate-800 transition-colors shadow-2xs"
                    title="Volver al quirófano"
                >
                    <i data-lucide="arrow-left" class="size-4"></i>
                </a>
                <div>
                    <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-slate-400">
                        <span>Ficha Quirúrgica #{{ $cirugia->id_procedimiento }}</span>
                        <span>·</span>
                        <span>{{ $cirugia->fecha_programada ? \Carbon\Carbon::parse($cirugia->fecha_programada)->translatedFormat('d M Y, H:i') : 'Fecha S/D' }}</span>
                    </div>
                    <div class="flex items-center gap-3 mt-0.5">
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                            {{ $cirugia->tipo_cirugia }}
                        </h1>

                        <!-- Badges de Estado Quirúrgico -->
                        @if ($cirugia->estado === 'EN_QUIROFANO')
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 ring-1 ring-indigo-600/30 animate-pulse">
                                <span class="size-2 rounded-full bg-indigo-600"></span>
                                EN QUIRÓFANO
                            </span>
                        @elseif ($cirugia->estado === 'COMPLETADA')
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20">
                                <i data-lucide="check" class="size-3.5"></i>
                                COMPLETADA
                            </span>
                        @elseif ($cirugia->estado === 'CANCELADA')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600">
                                CANCELADA
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 ring-1 ring-amber-600/20">
                                <i data-lucide="clock" class="size-3.5"></i>
                                PROGRAMADA
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Botones de Acción en Quirófano -->
            <div class="flex items-center gap-2 flex-wrap">
                <!-- Descargar Consentimiento PDF -->
                <a
                    href="{{ route('cirugias.consentimiento.pdf', $cirugia->id_procedimiento) }}"
                    target="_blank"
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border border-slate-200 bg-white text-slate-700 hover:text-rose-700 hover:border-rose-200 text-xs font-semibold shadow-2xs transition-colors"
                >
                    <i data-lucide="file-text" class="size-4 text-rose-500"></i>
                    <span>Consentimiento PDF</span>
                </a>

                @if ($cirugia->estado === 'PROGRAMADA')
                    <form action="{{ route('cirugias.cambiar-estado', $cirugia->id_procedimiento) }}" method="POST">
                        @csrf
                        <input type="hidden" name="estado" value="EN_QUIROFANO">
                        <button
                            type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-xs transition-colors"
                        >
                            <i data-lucide="play" class="size-4"></i>
                            <span>Ingresar a Quirófano</span>
                        </button>
                    </form>
                @elseif ($cirugia->estado === 'EN_QUIROFANO')
                    <button
                        type="button"
                        @click="modalFinalizarOpen = true"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-xs transition-colors"
                    >
                        <i data-lucide="check-circle" class="size-4"></i>
                        <span>Finalizar Cirugía</span>
                    </button>
                @endif

                @if ($cirugia->estado !== 'COMPLETADA' && $cirugia->estado !== 'CANCELADA')
                    <button
                        type="button"
                        @click="modalCancelarOpen = true"
                        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-rose-200 text-rose-700 hover:bg-rose-50 text-xs font-semibold transition-colors"
                    >
                        <i data-lucide="x" class="size-4"></i>
                        <span>Cancelar</span>
                    </button>
                @endif
            </div>
        </div>

        <!-- Banner del Paciente y Tutor -->
        <div class="rounded-3xl border border-slate-200/80 bg-white p-5 sm:p-6 shadow-2xs">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <!-- Datos Mascota -->
                <div class="flex items-center gap-4">
                    <div class="size-16 rounded-2xl bg-primary-100 text-primary-700 flex items-center justify-center font-bold text-2xl shadow-inner shrink-0">
                        {{ strtoupper(substr($paciente?->nombre ?? 'P', 0, 1)) }}
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-xl font-bold text-slate-900">{{ $paciente?->nombre ?? 'Paciente' }}</h2>
                            <span class="rounded-full px-2 py-0.5 text-xs font-bold {{ ($paciente?->sexo ?? '') === 'MACHO' ? 'bg-blue-50 text-blue-700' : 'bg-pink-50 text-pink-700' }}">
                                {{ $paciente?->sexo ?? 'S/D' }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">
                            {{ $paciente?->raza?->especie?->nombre ?? 'Especie' }} · {{ $paciente?->raza?->nombre ?? 'Raza' }} · 
                            {{ $paciente?->fecha_nacimiento ? \Carbon\Carbon::parse($paciente->fecha_nacimiento)->age . ' años' : 'Edad S/D' }}
                        </p>
                        @if ($paciente)
                            <a href="{{ route('historial.show', $paciente->id_paciente) }}" class="inline-flex items-center gap-1 text-[11px] text-primary-600 hover:underline mt-1 font-semibold">
                                <i data-lucide="folder" class="size-3.5"></i>
                                <span>Ver Expediente Clínico Completo</span>
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Datos Tutor -->
                <div class="flex items-center gap-4 border-t md:border-t-0 md:border-l border-slate-100 pt-4 md:pt-0 md:pl-6">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Tutor Responsable</span>
                        <h4 class="font-bold text-slate-900 text-sm mt-0.5">
                            {{ $propietario ? trim("{$propietario->nombre} {$propietario->apellido1}") : 'Propietario no registrado' }}
                        </h4>
                        <p class="text-xs text-slate-500">CI / Doc: {{ $propietario?->documento ?: 'S/D' }}</p>
                        <p class="text-xs text-slate-500">Tel: {{ $propietario?->telefono ?: 'S/D' }}</p>
                    </div>

                    @if ($telLimpio)
                        <a
                            href="https://wa.me/{{ $telLimpio }}?text={{ $msgWa }}"
                            target="_blank"
                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-emerald-50 text-emerald-700 hover:bg-emerald-100 text-xs font-semibold shadow-2xs transition-colors shrink-0"
                            title="Comunicar por WhatsApp"
                        >
                            <i data-lucide="message-circle" class="size-4 text-emerald-600"></i>
                            <span>WhatsApp</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Fila de Tarjetas: Valoración Preoperatoria & Consentimiento Informado -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- 1. VALORACIÓN PRE-QUIRÚRGICA Y ANESTÉSICA -->
            <div class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-2xs space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i data-lucide="clipboard-check" class="size-4 text-primary-600"></i>
                        <span>Evaluación Pre-Quirúrgica y Anestésica</span>
                    </h3>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 ring-1 ring-amber-600/20">
                        {{ $cirugia->riesgo_asa ?: 'ASA II' }}
                    </span>
                </div>

                <div class="space-y-3 text-xs">
                    <div>
                        <span class="font-bold text-slate-700 uppercase tracking-wider text-[10px] block">Diagnóstico Preoperatorio:</span>
                        <p class="text-slate-800 mt-0.5 font-medium">
                            {{ $cirugia->diagnostico_preoperatorio ?: 'Intervención quirúrgica electiva / control.' }}
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-3 p-3 rounded-2xl bg-slate-50 border border-slate-100">
                        <div>
                            <span class="text-[10px] uppercase font-bold text-slate-400 block">Cirujano Principal</span>
                            <span class="font-semibold text-slate-800">
                                {{ $cirugia->cirujano?->persona ? 'Dr. ' . $cirugia->cirujano->persona->nombre . ' ' . $cirugia->cirujano->persona->apellido1 : 'Sin asignar' }}
                            </span>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase font-bold text-slate-400 block">Anestesiólogo</span>
                            <span class="font-semibold text-slate-800">
                                {{ $cirugia->anestesista?->persona ? 'Dr. ' . $cirugia->anestesista->persona->nombre . ' ' . $cirugia->anestesista->persona->apellido1 : 'Sin asignar' }}
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 p-3 rounded-2xl bg-slate-50 border border-slate-100">
                        <div>
                            <span class="text-[10px] uppercase font-bold text-slate-400 block">Protocolo Anestésico</span>
                            <span class="font-semibold text-slate-800">{{ $cirugia->tipo_anestesia ?: 'Inhalatoria (Isoflurano)' }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase font-bold text-slate-400 block">Ayuno Requerido</span>
                            <span class="font-semibold text-slate-800">
                                Sólidos: {{ $cirugia->ayuno_solidos_horas ?? 8 }}h · Líquidos: {{ $cirugia->ayuno_liquidos_horas ?? 2 }}h
                            </span>
                        </div>
                    </div>

                    @if ($cirugia->observaciones_prequirurgicas)
                        <div class="p-3 rounded-2xl bg-amber-50/60 border border-amber-100 text-amber-950">
                            <span class="text-[10px] uppercase font-bold text-amber-800 block">Observaciones Pre-quirúrgicas / Alergias</span>
                            <p class="mt-0.5 italic">{{ $cirugia->observaciones_prequirurgicas }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- 2. ESTADO DEL CONSENTIMIENTO INFORMADO -->
            <div class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-2xs space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i data-lucide="shield-alert" class="size-4 text-emerald-600"></i>
                        <span>Consentimiento Informado Médico-Legal</span>
                    </h3>
                    @if ($cirugia->autorizacion_firmada)
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20">
                            <i data-lucide="check" class="size-3"></i>
                            FIRMADO
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 ring-1 ring-amber-600/20">
                            <i data-lucide="alert-triangle" class="size-3"></i>
                            PENDIENTE DE FIRMA
                        </span>
                    @endif
                </div>

                <div class="space-y-4 text-xs">
                    @if ($cirugia->autorizacion_firmada)
                        <div class="p-4 rounded-2xl bg-emerald-50/60 border border-emerald-100 space-y-2">
                            <div class="flex items-center gap-2 text-emerald-800 font-bold text-sm">
                                <i data-lucide="badge-check" class="size-5 text-emerald-600"></i>
                                <span>Autorización prequirúrgica validada</span>
                            </div>
                            <p class="text-emerald-950">
                                <strong>Firmante:</strong> {{ $cirugia->firma_propietario_nombre }}<br>
                                <strong>Cédula / Documento:</strong> {{ $cirugia->firma_propietario_documento }}
                            </p>
                            <p class="text-[11px] text-emerald-700">
                                El tutor legal ha aceptado expresamente los riesgos anestésicos y quirúrgicos del procedimiento.
                            </p>
                        </div>
                    @else
                        <div class="p-4 rounded-2xl bg-amber-50/60 border border-amber-100 space-y-2">
                            <div class="flex items-center gap-2 text-amber-800 font-bold text-sm">
                                <i data-lucide="alert-circle" class="size-5 text-amber-600"></i>
                                <span>Requiere autorización firmada antes de anestesiar</span>
                            </div>
                            <p class="text-amber-950">
                                Por normativa médico-legal, el tutor debe firmar el documento de autorización informada antes del inicio del acto quirúrgico.
                            </p>
                            <button
                                type="button"
                                @click="modalConsentimientoOpen = true"
                                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-semibold shadow-2xs transition-colors"
                            >
                                <i data-lucide="pen-tool" class="size-3.5"></i>
                                <span>Registrar Firma del Tutor</span>
                            </button>
                        </div>
                    @endif

                    <div class="flex items-center justify-between p-3 rounded-2xl bg-slate-50 border border-slate-100">
                        <div>
                            <span class="font-bold text-slate-800 block">Documento Oficial PDF</span>
                            <span class="text-[11px] text-slate-400">Imprime el documento para archivo físico o firma manuscrita</span>
                        </div>
                        <a
                            href="{{ route('cirugias.consentimiento.pdf', $cirugia->id_procedimiento) }}"
                            target="_blank"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 bg-white text-slate-700 hover:bg-slate-100 font-medium shadow-2xs transition-colors"
                        >
                            <i data-lucide="printer" class="size-4 text-slate-600"></i>
                            <span>Imprimir</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. INFORME POSTOPERATORIO Y EVOLUCIÓN (Si está completada o en quirófano) -->
        <div class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-2xs space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="file-check-2" class="size-4 text-primary-600"></i>
                    <span>Informe Quirúrgico Postoperatorio y Recuperación</span>
                </h3>
                @if ($cirugia->estado === 'COMPLETADA')
                    <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700">
                        Resultado: {{ $cirugia->resultado ?: 'Satisfactorio' }}
                    </span>
                @endif
            </div>

            @if ($cirugia->estado === 'COMPLETADA')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-1">
                        <span class="font-bold text-slate-700 uppercase tracking-wider text-[10px] block">Complicaciones Intraoperatorias:</span>
                        <p class="text-slate-800 font-medium">
                            {{ $cirugia->complicaciones ?: 'Ninguna complicación reportada durante el procedimiento.' }}
                        </p>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-1">
                        <span class="font-bold text-slate-700 uppercase tracking-wider text-[10px] block">Indicaciones Postoperatorias y Cuidados:</span>
                        <p class="text-slate-800 font-medium">
                            {{ $cirugia->indicaciones_postoperatorias ?: 'Seguir pauta analgésica y antibiótica prescrita. Control de herida quirúrgica y uso de collar isabelino.' }}
                        </p>
                    </div>
                </div>
            @else
                <div class="py-8 text-center text-slate-400 text-xs">
                    <i data-lucide="clock" class="size-6 mx-auto mb-2 text-slate-300"></i>
                    <p>El informe postoperatorio se redactará una vez que la cirugía sea marcada como Finalizada.</p>
                </div>
            @endif
        </div>

        <!-- 4. CONTROLES POSTOPERATORIOS Y EVOLUCIÓN (CU11) -->
        <div class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-2xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i data-lucide="stethoscope" class="size-4 text-primary-600"></i>
                        <span>Controles Postoperatorios y Evolución (CU11)</span>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Seguimiento de cicatrización, evolución clínica y retiro de puntos.</p>
                </div>

                @if ($cirugia->estado === 'COMPLETADA' || $cirugia->estado === 'EN_QUIROFANO')
                    <button
                        type="button"
                        @click="modalControlOpen = true"
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-xs font-semibold shadow-2xs transition-colors shrink-0"
                    >
                        <i data-lucide="plus" class="size-3.5"></i>
                        <span>Registrar Control</span>
                    </button>
                @endif
            </div>

            @if ($cirugia->controles && $cirugia->controles->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-slate-200/80 bg-slate-50/75 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                <th class="py-2.5 px-3">Fecha y Día</th>
                                <th class="py-2.5 px-3">Profesional</th>
                                <th class="py-2.5 px-3">Estado Herida</th>
                                <th class="py-2.5 px-3">Evolución</th>
                                <th class="py-2.5 px-3">Constantes</th>
                                <th class="py-2.5 px-3">Observaciones e Indicaciones</th>
                                <th class="py-2.5 px-3 text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($cirugia->controles as $ctrl)
                                @php
                                    $evoTone = match($ctrl->evolucion) {
                                        'FAVORABLE' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
                                        'ALTA_MEDICA' => 'bg-indigo-50 text-indigo-700 ring-indigo-600/20',
                                        'COMPLICADA' => 'bg-rose-50 text-rose-700 ring-rose-600/20',
                                        default => 'bg-slate-50 text-slate-700 ring-slate-500/20',
                                    };
                                @endphp
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <td class="py-3 px-3 whitespace-nowrap">
                                        <span class="font-bold text-slate-800">
                                            {{ \Carbon\Carbon::parse($ctrl->fecha_control)->translatedFormat('d M Y, H:i') }}
                                        </span>
                                        @if ($ctrl->dias_postoperatorio !== null)
                                            <div class="text-[11px] font-semibold text-primary-600">
                                                Día +{{ $ctrl->dias_postoperatorio }} postquirúrgico
                                            </div>
                                        @endif
                                    </td>
                                    <td class="py-3 px-3 whitespace-nowrap font-medium text-slate-800">
                                        {{ $ctrl->profesional?->persona?->nombre ?? 'Profesional' }} {{ $ctrl->profesional?->persona?->apellido1 ?? '' }}
                                    </td>
                                    <td class="py-3 px-3">
                                        <span class="font-medium text-slate-700">{{ $ctrl->estado_herida ?: 'Adecuada' }}</span>
                                    </td>
                                    <td class="py-3 px-3 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold ring-1 {{ $evoTone }}">
                                            {{ $ctrl->evolucion }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 whitespace-nowrap text-[11px] text-slate-600 font-mono">
                                        @if ($ctrl->temperatura) <span>T: {{ $ctrl->temperatura }}°C</span><br> @endif
                                        @if ($ctrl->peso) <span>P: {{ $ctrl->peso }} kg</span> @endif
                                        @if (!$ctrl->temperatura && !$ctrl->peso) <span class="text-slate-400">—</span> @endif
                                    </td>
                                    <td class="py-3 px-3 max-w-xs">
                                        @if ($ctrl->observaciones)
                                            <p class="text-slate-800 text-[11px]">{{ $ctrl->observaciones }}</p>
                                        @endif
                                        @if ($ctrl->indicaciones)
                                            <p class="text-slate-500 text-[10px] mt-0.5"><span class="font-semibold text-slate-600">Indicaciones:</span> {{ $ctrl->indicaciones }}</p>
                                        @endif
                                    </td>
                                    <td class="py-3 px-3 text-right whitespace-nowrap">
                                        <form action="{{ route('cirugias.controles.delete', [$cirugia->id_procedimiento, $ctrl->id_control]) }}" method="POST" onsubmit="return confirm('¿Eliminar este control postoperatorio?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1 text-slate-400 hover:text-rose-600 transition-colors" title="Eliminar control">
                                                <i data-lucide="trash-2" class="size-4"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="py-6 text-center text-slate-400 text-xs">
                    <i data-lucide="calendar-check" class="size-6 mx-auto mb-2 text-slate-300"></i>
                    <p class="font-semibold text-slate-600">No se han registrado controles postoperatorios todavía.</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Permite asentar evaluaciones de la herida, retiro de suturas y evolución médica.</p>
                </div>
            @endif
        </div>

        <!-- MODAL: REGISTRAR CONTROL POSTOPERATORIO (CU11) -->
        <div
            x-show="modalControlOpen"
            x-cloak
            class="fixed inset-0 z-50 overflow-y-auto"
            style="display: none;"
        >
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="modalControlOpen = false"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-lg rounded-3xl bg-white p-6 shadow-2xl border border-slate-100 transition-all sm:p-7" @click.stop>
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                        <div>
                            <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                                <i data-lucide="clipboard-check" class="size-5 text-primary-600"></i>
                                <span>Registrar Control Postoperatorio (CU11)</span>
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">Control y evolución de la intervención #{{ $cirugia->id_procedimiento }}</p>
                        </div>
                        <button type="button" @click="modalControlOpen = false" class="text-slate-400 hover:text-slate-700">
                            <i data-lucide="x" class="size-5"></i>
                        </button>
                    </div>

                    <form action="{{ route('cirugias.controles.store', $cirugia->id_procedimiento) }}" method="POST" class="space-y-4 mt-5">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                    Fecha y Hora <span class="text-rose-500">*</span>
                                </label>
                                <input
                                    type="datetime-local"
                                    name="fecha_control"
                                    required
                                    value="{{ now()->format('Y-m-d\TH:i') }}"
                                    class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-800 focus:border-primary-500 focus:outline-hidden"
                                />
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                    Día Postquirúrgico
                                </label>
                                <input
                                    type="number"
                                    name="dias_postoperatorio"
                                    placeholder="Ej. 3, 7, 10..."
                                    min="0"
                                    class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-800 focus:border-primary-500 focus:outline-hidden"
                                />
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                Profesional Responsable <span class="text-rose-500">*</span>
                            </label>
                            <select
                                name="id_profesional"
                                required
                                class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-800 focus:border-primary-500 focus:outline-hidden"
                            >
                                <option value="">Selecciona el profesional...</option>
                                @foreach ($profesionales as $prof)
                                    <option value="{{ $prof->id_profesional }}" {{ ($cirugia->id_veterinario == $prof->id_persona) ? 'selected' : '' }}>
                                        {{ $prof->persona?->nombre }} {{ $prof->persona?->apellido1 }} ({{ $prof->especialidad }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                    Estado de la Herida <span class="text-rose-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    name="estado_herida"
                                    required
                                    placeholder="Ej. Limpia y seca, sin secreción"
                                    class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-800 focus:border-primary-500 focus:outline-hidden"
                                />
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                    Evolución Clínica <span class="text-rose-500">*</span>
                                </label>
                                <select
                                    name="evolucion"
                                    required
                                    class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-800 focus:border-primary-500 focus:outline-hidden"
                                >
                                    <option value="FAVORABLE">Favorable / Cicatrización normal</option>
                                    <option value="ESTABLE">Estable / En evolución esperada</option>
                                    <option value="COMPLICADA">Complicada / Requiere intervención</option>
                                    <option value="ALTA_MEDICA">Alta Médica / Retiro de puntos</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                    Temperatura (°C)
                                </label>
                                <input
                                    type="number"
                                    step="0.1"
                                    name="temperatura"
                                    placeholder="Ej. 38.5"
                                    class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-800 focus:border-primary-500 focus:outline-hidden"
                                />
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                    Peso (kg)
                                </label>
                                <input
                                    type="number"
                                    step="0.01"
                                    name="peso"
                                    placeholder="Ej. 12.40"
                                    class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-800 focus:border-primary-500 focus:outline-hidden"
                                />
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                Observaciones Clínicas
                            </label>
                            <textarea
                                name="observaciones"
                                rows="2"
                                placeholder="Evolución de la herida, nivel de dolor, apetito, movilidad..."
                                class="w-full rounded-xl border border-slate-200 p-2 text-xs text-slate-800 focus:border-primary-500 focus:outline-hidden"
                            ></textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                Indicaciones Médicas
                            </label>
                            <textarea
                                name="indicaciones"
                                rows="2"
                                placeholder="Ajuste de dosis, retiro de puntos, fecha del próximo control..."
                                class="w-full rounded-xl border border-slate-200 p-2 text-xs text-slate-800 focus:border-primary-500 focus:outline-hidden"
                            ></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                            <button type="button" @click="modalControlOpen = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                                Cancelar
                            </button>
                            <button type="submit" class="px-5 py-2 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-xs font-semibold shadow-xs">
                                Guardar Control
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- MODAL: REGISTRAR FIRMA DE CONSENTIMIENTO INFORMADO -->
        <div
            x-show="modalConsentimientoOpen"
            x-cloak
            class="fixed inset-0 z-50 overflow-y-auto"
            style="display: none;"
        >
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="modalConsentimientoOpen = false"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl border border-slate-100 transition-all sm:p-7" @click.stop>
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                        <h3 class="text-base font-bold text-slate-900">Validar Consentimiento Informado</h3>
                        <button type="button" @click="modalConsentimientoOpen = false" class="text-slate-400 hover:text-slate-700">
                            <i data-lucide="x" class="size-5"></i>
                        </button>
                    </div>

                    <form action="{{ route('cirugias.consentimiento', $cirugia->id_procedimiento) }}" method="POST" class="space-y-4 mt-5">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Nombre del Tutor Firmante <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="text"
                                name="firma_propietario_nombre"
                                required
                                value="{{ $propietario ? trim("{$propietario->nombre} {$propietario->apellido1} {$propietario->apellido2}") : '' }}"
                                class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-800 focus:border-primary-500 focus:outline-hidden focus:ring-2 focus:ring-primary-500/20"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Cédula de Identidad / Pasaporte <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="text"
                                name="firma_propietario_documento"
                                required
                                value="{{ $propietario?->documento ?: '' }}"
                                class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-800 focus:border-primary-500 focus:outline-hidden focus:ring-2 focus:ring-primary-500/20"
                            />
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                            <button type="button" @click="modalConsentimientoOpen = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                                Cancelar
                            </button>
                            <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-xs">
                                Registrar Consentimiento
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- MODAL: FINALIZAR CIRUGÍA Y REGISTRAR INFORME -->
        <div
            x-show="modalFinalizarOpen"
            x-cloak
            class="fixed inset-0 z-50 overflow-y-auto"
            style="display: none;"
        >
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="modalFinalizarOpen = false"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-lg rounded-3xl bg-white p-6 shadow-2xl border border-slate-100 transition-all sm:p-7" @click.stop>
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                        <div>
                            <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                                <i data-lucide="check-circle" class="size-5 text-emerald-600"></i>
                                <span>Finalizar Intervención Quirúrgica</span>
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">Redacta el resultado y las indicaciones postoperatorias</p>
                        </div>
                        <button type="button" @click="modalFinalizarOpen = false" class="text-slate-400 hover:text-slate-700">
                            <i data-lucide="x" class="size-5"></i>
                        </button>
                    </div>

                    <form action="{{ route('cirugias.cambiar-estado', $cirugia->id_procedimiento) }}" method="POST" class="space-y-4 mt-5">
                        @csrf
                        <input type="hidden" name="estado" value="COMPLETADA">

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Resultado del Procedimiento
                            </label>
                            <select
                                name="resultado"
                                class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-800 focus:border-primary-500 focus:outline-hidden focus:ring-2 focus:ring-primary-500/20"
                            >
                                <option value="EXITOSA">Exitosa / Sin incidentes</option>
                                <option value="COMPLETA_CON_HALLAZGOS">Completada con hallazgos adicionales</option>
                                <option value="COMPLICADA">Con complicaciones intraoperatorias</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Complicaciones / Incidentes
                            </label>
                            <textarea
                                name="complicaciones"
                                rows="2"
                                placeholder="Describe si hubo hemorragia, bradicardia, hipotensión u otra eventualidad..."
                                class="w-full rounded-xl border border-slate-200 p-2.5 text-xs text-slate-800 focus:border-primary-500 focus:outline-hidden focus:ring-2 focus:ring-primary-500/20"
                            ></textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Indicaciones Postoperatorias y Medicación
                            </label>
                            <textarea
                                name="indicaciones_postoperatorias"
                                rows="3"
                                placeholder="Pauta analgésica, antibióticos, curaciones de herida, tiempo de retiro de puntos..."
                                class="w-full rounded-xl border border-slate-200 p-2.5 text-xs text-slate-800 focus:border-primary-500 focus:outline-hidden focus:ring-2 focus:ring-primary-500/20"
                            ></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                            <button type="button" @click="modalFinalizarOpen = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                                Cancelar
                            </button>
                            <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-xs">
                                Confirmar y Completar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- MODAL: CANCELAR CIRUGÍA -->
        <div
            x-show="modalCancelarOpen"
            x-cloak
            class="fixed inset-0 z-50 overflow-y-auto"
            style="display: none;"
        >
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="modalCancelarOpen = false"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl border border-slate-100 transition-all sm:p-7" @click.stop>
                    <h3 class="text-base font-bold text-slate-900">¿Cancelar esta cirugía?</h3>
                    <p class="text-xs text-slate-500 mt-1">El estado cambiará a CANCELADA y se liberará el quirófano.</p>

                    <form action="{{ route('cirugias.cambiar-estado', $cirugia->id_procedimiento) }}" method="POST" class="mt-4">
                        @csrf
                        <input type="hidden" name="estado" value="CANCELADA">

                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                            <button type="button" @click="modalCancelarOpen = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                                Volver
                            </button>
                            <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shadow-xs">
                                Sí, Cancelar Cirugía
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-layout>
