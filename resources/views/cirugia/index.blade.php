<x-layout title="Quirófano y Cirugías — La Petdiatría">
    <div x-data="{ modalProgramarOpen: false }" class="space-y-6">
        <x-breadcrumb :items="[
            ['label' => 'Inicio', 'url' => route('dashboard')],
            ['label' => 'Quirófano y Cirugías', 'url' => null],
        ]" />

        <!-- Encabezado Principal y Botón de Programar -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-slate-400">
                    <span>Centro Quirúrgico</span>
                    <span>·</span>
                    <span>Gestión de Quirófano</span>
                </div>
                <div class="flex items-center gap-3 mt-1">
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl flex items-center gap-2.5">
                        <i data-lucide="scissors" class="size-7 text-primary-600"></i>
                        <span>Quirófano y Cirugías</span>
                    </h1>
                </div>
                <p class="text-xs text-slate-500 mt-1">
                    Control de intervenciones quirúrgicas, valoración anestésica preoperatoria (ASA) y emisión de consentimientos informados.
                </p>
            </div>

            <button
                type="button"
                @click="modalProgramarOpen = true"
                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-xs font-semibold shadow-xs transition-all hover:scale-[1.01] active:scale-[0.99]"
            >
                <i data-lucide="plus-circle" class="size-4"></i>
                <span>Programar Cirugía</span>
            </button>
        </div>

        <!-- 4 Tarjetas de Métricas / KPIs de Quirófano -->
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-2xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Cirugías Hoy</span>
                    <div class="p-2 rounded-xl bg-primary-50 text-primary-600">
                        <i data-lucide="calendar" class="size-4"></i>
                    </div>
                </div>
                <div class="mt-2 text-2xl font-bold text-slate-900">{{ $totalHoy }}</div>
                <p class="text-[11px] text-slate-400 mt-0.5">Programadas para la fecha</p>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-2xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-indigo-600 uppercase tracking-wider">En Quirófano</span>
                    <div class="p-2 rounded-xl bg-indigo-50 text-indigo-600 animate-pulse">
                        <i data-lucide="activity" class="size-4"></i>
                    </div>
                </div>
                <div class="mt-2 text-2xl font-bold text-indigo-700">{{ $totalEnQuirofano }}</div>
                <p class="text-[11px] text-indigo-500 mt-0.5">Intervenciones activas ahora</p>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-2xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-amber-600 uppercase tracking-wider">Programadas</span>
                    <div class="p-2 rounded-xl bg-amber-50 text-amber-600">
                        <i data-lucide="clock" class="size-4"></i>
                    </div>
                </div>
                <div class="mt-2 text-2xl font-bold text-slate-900">{{ $totalProgramadas }}</div>
                <p class="text-[11px] text-slate-400 mt-0.5">En espera de ingreso</p>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-2xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-emerald-600 uppercase tracking-wider">Completadas</span>
                    <div class="p-2 rounded-xl bg-emerald-50 text-emerald-600">
                        <i data-lucide="check-circle" class="size-4"></i>
                    </div>
                </div>
                <div class="mt-2 text-2xl font-bold text-slate-900">{{ $totalCompletadas }}</div>
                <p class="text-[11px] text-slate-400 mt-0.5">Intervenciones exitosas</p>
            </div>
        </div>

        <!-- Filtros y Búsqueda -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-3 rounded-2xl bg-white border border-slate-200/80 shadow-2xs">
            <div class="flex items-center gap-1 overflow-x-auto pb-1 sm:pb-0">
                <a
                    href="{{ route('cirugias.index', array_filter(['search' => $filters['search'] ?? null])) }}"
                    class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-colors {{ empty($filters['estado']) ? 'bg-primary-50 text-primary-700 ring-1 ring-primary-600/20' : 'text-slate-600 hover:bg-slate-100' }}"
                >
                    Todas
                </a>
                <a
                    href="{{ route('cirugias.index', ['estado' => 'PROGRAMADA', 'search' => $filters['search'] ?? null]) }}"
                    class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-colors {{ ($filters['estado'] ?? '') === 'PROGRAMADA' ? 'bg-amber-50 text-amber-700 ring-1 ring-amber-600/20' : 'text-slate-600 hover:bg-slate-100' }}"
                >
                    Programadas
                </a>
                <a
                    href="{{ route('cirugias.index', ['estado' => 'EN_QUIROFANO', 'search' => $filters['search'] ?? null]) }}"
                    class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-colors {{ ($filters['estado'] ?? '') === 'EN_QUIROFANO' ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-600/20' : 'text-slate-600 hover:bg-slate-100' }}"
                >
                    En Quirófano
                </a>
                <a
                    href="{{ route('cirugias.index', ['estado' => 'COMPLETADA', 'search' => $filters['search'] ?? null]) }}"
                    class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-colors {{ ($filters['estado'] ?? '') === 'COMPLETADA' ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20' : 'text-slate-600 hover:bg-slate-100' }}"
                >
                    Completadas
                </a>
            </div>

            <form method="GET" action="{{ route('cirugias.index') }}" class="flex items-center gap-2">
                @if (!empty($filters['estado']))
                    <input type="hidden" name="estado" value="{{ $filters['estado'] }}">
                @endif
                <div class="relative">
                    <i data-lucide="search" class="size-4 absolute left-3 top-2.5 text-slate-400"></i>
                    <input
                        type="text"
                        name="search"
                        value="{{ $filters['search'] ?? '' }}"
                        placeholder="Buscar paciente, cirugía..."
                        class="rounded-xl border border-slate-200 pl-9 pr-3 py-1.5 text-xs text-slate-800 placeholder-slate-400 focus:border-primary-500 focus:outline-hidden focus:ring-2 focus:ring-primary-500/20"
                    />
                </div>
                <button type="submit" class="px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                    Filtrar
                </button>
            </form>
        </div>

        <!-- Tabla / Listado de Cirugías -->
        <div class="rounded-2xl border border-slate-200/80 bg-white shadow-2xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-200/80 bg-slate-50/75 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="py-3 px-4">Paciente</th>
                            <th class="py-3 px-4">Cirugía & Diagnóstico</th>
                            <th class="py-3 px-4">Fecha & Hora</th>
                            <th class="py-3 px-4">Equipo Médico</th>
                            <th class="py-3 px-4">Riesgo ASA</th>
                            <th class="py-3 px-4">Estado</th>
                            <th class="py-3 px-4">Consentimiento</th>
                            <th class="py-3 px-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($cirugias as $cir)
                            @php
                                $paciente = $cir->paciente;
                                $propietario = $paciente?->propietario?->persona;
                                $telefono = $propietario?->telefono;
                                $telLimpio = $telefono ? preg_replace('/[^0-9]/', '', $telefono) : null;
                                if ($telLimpio && strlen($telLimpio) === 8) {
                                    $telLimpio = '591' . $telLimpio;
                                }
                                $msgWa = urlencode("Hola {$propietario?->nombre}, te escribimos desde La Petdiatría para recordarte la cirugía de {$paciente?->nombre} ({$cir->tipo_cirugia}) programada para el " . ($cir->fecha_programada ? \Carbon\Carbon::parse($cir->fecha_programada)->translatedFormat('d M Y \a \l\a\s H:i') : '') . ". Recuerda cumplir con el ayuno de {$cir->ayuno_solidos_horas} horas.");
                            @endphp
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <!-- Paciente -->
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="size-8 rounded-full bg-primary-50 text-primary-700 flex items-center justify-center font-bold text-xs shrink-0">
                                            {{ strtoupper(substr($paciente?->nombre ?? 'P', 0, 1)) }}
                                        </div>
                                        <div>
                                            <a href="{{ $paciente ? route('historial.show', $paciente->id_paciente) : '#' }}" class="font-bold text-slate-900 hover:text-primary-700 hover:underline">
                                                {{ $paciente?->nombre ?? 'Sin paciente' }}
                                            </a>
                                            <div class="text-[11px] text-slate-400">
                                                {{ $paciente?->raza?->especie?->nombre ?? 'Canino' }} · {{ $paciente?->raza?->nombre ?? 'Mestizo' }}
                                            </div>
                                            @if ($propietario)
                                                <div class="flex items-center gap-1.5 mt-0.5">
                                                    <span class="text-[10px] text-slate-500 font-medium truncate max-w-[120px]">
                                                        {{ $propietario->nombre }}
                                                    </span>
                                                    @if ($telLimpio)
                                                        <a
                                                            href="https://wa.me/{{ $telLimpio }}?text={{ $msgWa }}"
                                                            target="_blank"
                                                            class="text-emerald-600 hover:text-emerald-700 p-0.5 rounded transition-colors"
                                                            title="Enviar WhatsApp de confirmación prequirúrgica"
                                                        >
                                                            <i data-lucide="message-circle" class="size-3.5"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <!-- Cirugía & Diagnóstico -->
                                <td class="py-3 px-4">
                                    <p class="font-bold text-slate-900">{{ $cir->tipo_cirugia }}</p>
                                    <p class="text-[11px] text-slate-500 line-clamp-1 max-w-xs">
                                        {{ $cir->diagnostico_preoperatorio ?: 'Sin diagnóstico preoperatorio especificado' }}
                                    </p>
                                </td>

                                <!-- Fecha & Hora -->
                                <td class="py-3 px-4">
                                    <div class="font-semibold text-slate-800">
                                        {{ $cir->fecha_programada ? \Carbon\Carbon::parse($cir->fecha_programada)->translatedFormat('d M Y') : 'Sin fecha' }}
                                    </div>
                                    <div class="text-[11px] text-slate-400">
                                        {{ $cir->fecha_programada ? \Carbon\Carbon::parse($cir->fecha_programada)->format('H:i') . ' hrs' : '' }}
                                    </div>
                                </td>

                                <!-- Equipo Médico -->
                                <td class="py-3 px-4">
                                    <div class="text-slate-800 font-medium">
                                        {{ $cir->cirujano?->persona ? 'Dr. ' . $cir->cirujano->persona->nombre . ' ' . $cir->cirujano->persona->apellido1 : 'Cirujano por asignar' }}
                                    </div>
                                    @if ($cir->anestesista?->persona)
                                        <div class="text-[10px] text-slate-400">
                                            Anest.: {{ $cir->anestesista->persona->nombre }}
                                        </div>
                                    @endif
                                </td>

                                <!-- Riesgo ASA -->
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 ring-1 ring-amber-600/20">
                                        {{ $cir->riesgo_asa ?: 'ASA II' }}
                                    </span>
                                </td>

                                <!-- Estado -->
                                <td class="py-3 px-4">
                                    @if ($cir->estado === 'EN_QUIROFANO')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 ring-1 ring-indigo-600/30 animate-pulse">
                                            <span class="size-1.5 rounded-full bg-indigo-600"></span>
                                            EN QUIRÓFANO
                                        </span>
                                    @elseif ($cir->estado === 'COMPLETADA')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20">
                                            <i data-lucide="check" class="size-3"></i>
                                            COMPLETADA
                                        </span>
                                    @elseif ($cir->estado === 'CANCELADA')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">
                                            CANCELADA
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 ring-1 ring-amber-600/20">
                                            <i data-lucide="clock" class="size-3"></i>
                                            PROGRAMADA
                                        </span>
                                    @endif
                                </td>

                                <!-- Consentimiento -->
                                <td class="py-3 px-4">
                                    @if ($cir->autorizacion_firmada)
                                        <span class="inline-flex items-center gap-1 text-emerald-700 font-semibold text-[11px]">
                                            <i data-lucide="check-circle-2" class="size-3.5"></i>
                                            <span>Firmado</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-amber-600 font-semibold text-[11px]">
                                            <i data-lucide="alert-circle" class="size-3.5"></i>
                                            <span>Pendiente</span>
                                        </span>
                                    @endif
                                </td>

                                <!-- Acciones -->
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a
                                            href="{{ route('cirugias.consentimiento.pdf', $cir->id_procedimiento) }}"
                                            target="_blank"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg border border-slate-200 bg-white text-slate-700 hover:text-rose-700 hover:border-rose-200 text-xs font-medium shadow-2xs transition-colors"
                                            title="Descargar Consentimiento Informado en PDF"
                                        >
                                            <i data-lucide="file-text" class="size-3.5 text-rose-500"></i>
                                            <span class="hidden sm:inline">PDF</span>
                                        </a>

                                        <a
                                            href="{{ route('cirugias.show', $cir->id_procedimiento) }}"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-primary-50 text-primary-700 hover:bg-primary-100 font-semibold text-xs transition-colors"
                                        >
                                            <span>Ver Ficha</span>
                                            <i data-lucide="chevron-right" class="size-3.5"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-slate-400">
                                    <div class="size-12 rounded-2xl bg-slate-50 text-slate-400 flex items-center justify-center mx-auto mb-2">
                                        <i data-lucide="scissors" class="size-6"></i>
                                    </div>
                                    <p class="font-semibold text-slate-600 text-xs">No hay cirugías programadas en este criterio.</p>
                                    <p class="text-[11px] text-slate-400 mt-0.5">Haz clic en "Programar Cirugía" para agendar una intervención.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($cirugias->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $cirugias->links() }}
                </div>
            @endif
        </div>

        <!-- MODAL: PROGRAMAR NUEVA CIRUGÍA -->
        <div
            x-show="modalProgramarOpen"
            x-cloak
            class="fixed inset-0 z-50 overflow-y-auto"
            style="display: none;"
        >
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="modalProgramarOpen = false"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div
                    class="relative w-full max-w-2xl rounded-3xl bg-white p-6 shadow-2xl border border-slate-100 transition-all sm:p-7"
                    @click.stop
                >
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                        <div>
                            <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                                <i data-lucide="scissors" class="size-5 text-primary-600"></i>
                                <span>Programación de Intervención Quirúrgica</span>
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">Asignación de quirófano, evaluación ASA y control anestésico</p>
                        </div>
                        <button
                            type="button"
                            @click="modalProgramarOpen = false"
                            class="rounded-xl p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition-colors"
                        >
                            <i data-lucide="x" class="size-5"></i>
                        </button>
                    </div>

                    <form action="{{ route('cirugias.store') }}" method="POST" class="space-y-4 mt-5">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Paciente -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Paciente (Mascota) <span class="text-rose-500">*</span>
                                </label>
                                <select
                                    name="id_paciente"
                                    required
                                    class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-800 focus:border-primary-500 focus:outline-hidden focus:ring-2 focus:ring-primary-500/20"
                                >
                                    <option value="">Selecciona un paciente...</option>
                                    @foreach ($pacientes as $pac)
                                        <option value="{{ $pac->id_paciente }}">
                                            {{ $pac->nombre }} ({{ $pac->raza?->nombre ?? 'Mestizo' }}) — Tutor: {{ $pac->propietario?->persona?->nombre ?? 'S/D' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Tipo de Cirugía -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Procedimiento / Cirugía <span class="text-rose-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    name="tipo_cirugia"
                                    list="cirugias_frecuentes_list"
                                    required
                                    placeholder="Ej: Ovariohisterectomía, Profilaxis, Cistotomía..."
                                    class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-800 focus:border-primary-500 focus:outline-hidden focus:ring-2 focus:ring-primary-500/20"
                                />
                                <datalist id="cirugias_frecuentes_list">
                                    <option value="Ovariohisterectomía (OVH / Esterilización)">
                                    <option value="Orquiectomía (Castración)">
                                    <option value="Limpieza Dental / Profilaxis Ultrasónica">
                                    <option value="Osteosíntesis / Fijación Ósea">
                                    <option value="Cesárea de Urgencia">
                                    <option value="Extirpación de Neoplasia / Tumor Cutáneo">
                                    <option value="Cistotomía / Extracción de Cálculos">
                                    <option value="Entropión / Cirugía Ocular">
                                    <option value="Amputación">
                                    <option value="Laparotomía Exploratoria">
                                </datalist>
                            </div>

                            <!-- Fecha y Hora Programada -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Fecha y Hora Programada <span class="text-rose-500">*</span>
                                </label>
                                <input
                                    type="datetime-local"
                                    name="fecha_programada"
                                    required
                                    value="{{ now()->addDay()->format('Y-m-d\T09:00') }}"
                                    class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-800 focus:border-primary-500 focus:outline-hidden focus:ring-2 focus:ring-primary-500/20"
                                />
                            </div>

                            <!-- Riesgo ASA -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Evaluación de Riesgo ASA <span class="text-rose-500">*</span>
                                </label>
                                <select
                                    name="riesgo_asa"
                                    required
                                    class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-800 focus:border-primary-500 focus:outline-hidden focus:ring-2 focus:ring-primary-500/20"
                                >
                                    <option value="ASA I">ASA I — Paciente normal, sano (electiva)</option>
                                    <option value="ASA II" selected>ASA II — Enfermedad sistémica leve</option>
                                    <option value="ASA III">ASA III — Enfermedad sistémica moderada o grave</option>
                                    <option value="ASA IV">ASA IV — Enfermedad sistémica grave con amenaza vital</option>
                                    <option value="ASA V">ASA V — Paciente moribundo, pocas expectativas</option>
                                </select>
                            </div>

                            <!-- Cirujano Responsable -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Cirujano Principal
                                </label>
                                <select
                                    name="id_cirujano"
                                    class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-800 focus:border-primary-500 focus:outline-hidden focus:ring-2 focus:ring-primary-500/20"
                                >
                                    <option value="">Selecciona cirujano...</option>
                                    @foreach ($profesionales as $prof)
                                        <option value="{{ $prof->id_persona }}">
                                            Dr(a). {{ $prof->persona?->nombre }} {{ $prof->persona?->apellido1 }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Anestesista -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Anestesiólogo
                                </label>
                                <select
                                    name="id_anestesista"
                                    class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-800 focus:border-primary-500 focus:outline-hidden focus:ring-2 focus:ring-primary-500/20"
                                >
                                    <option value="">Selecciona anestesista...</option>
                                    @foreach ($profesionales as $prof)
                                        <option value="{{ $prof->id_persona }}">
                                            Dr(a). {{ $prof->persona?->nombre }} {{ $prof->persona?->apellido1 }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Técnica Anestésica -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Protocolo Anestésico
                                </label>
                                <input
                                    type="text"
                                    name="tipo_anestesia"
                                    value="Inhalatoria (Isoflurano)"
                                    placeholder="Ej: Inhalatoria, Inyectable TIVA, Sedación..."
                                    class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-800 focus:border-primary-500 focus:outline-hidden focus:ring-2 focus:ring-primary-500/20"
                                />
                            </div>

                            <!-- Horas de Ayuno -->
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                        Ayuno Sólidos
                                    </label>
                                    <div class="relative">
                                        <input
                                            type="number"
                                            name="ayuno_solidos_horas"
                                            value="8"
                                            min="0"
                                            max="48"
                                            class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-800 focus:border-primary-500 focus:outline-hidden focus:ring-2 focus:ring-primary-500/20"
                                        />
                                        <span class="absolute right-3 top-2 text-[10px] text-slate-400">hrs</span>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                        Ayuno Líquidos
                                    </label>
                                    <div class="relative">
                                        <input
                                            type="number"
                                            name="ayuno_liquidos_horas"
                                            value="2"
                                            min="0"
                                            max="24"
                                            class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-800 focus:border-primary-500 focus:outline-hidden focus:ring-2 focus:ring-primary-500/20"
                                        />
                                        <span class="absolute right-3 top-2 text-[10px] text-slate-400">hrs</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Diagnóstico Preoperatorio -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Diagnóstico Preoperatorio
                            </label>
                            <input
                                type="text"
                                name="diagnostico_preoperatorio"
                                placeholder="Ej: Piómetra cerrada, Hernia inguinal bilateral, Cuerpos extraños gástricos..."
                                class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-800 focus:border-primary-500 focus:outline-hidden focus:ring-2 focus:ring-primary-500/20"
                            />
                        </div>

                        <!-- Observaciones / Indicaciones Especiales -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Observaciones Pre-quirúrgicas / Alergias
                            </label>
                            <textarea
                                name="observaciones_prequirurgicas"
                                rows="2"
                                placeholder="Notas relevantes sobre analítica previa, medicación preanestésica o precauciones..."
                                class="w-full rounded-xl border border-slate-200 p-2.5 text-xs text-slate-800 focus:border-primary-500 focus:outline-hidden focus:ring-2 focus:ring-primary-500/20"
                            ></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                            <button
                                type="button"
                                @click="modalProgramarOpen = false"
                                class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition-colors"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-xs font-semibold shadow-xs transition-colors"
                            >
                                <i data-lucide="calendar-plus" class="size-4"></i>
                                <span>Agendar Cirugía</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-layout>
