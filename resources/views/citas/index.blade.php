<x-layout title="Agenda y Citas — La Petdiatría">
    <div
        x-data="{
            activeTab: '{{ $vista === 'bloqueos' ? 'bloqueos' : ($vista === 'lista' ? 'lista' : 'agenda') }}',
            createModalOpen: false,
            urgentModalOpen: false,
            rescheduleModalOpen: false,
            cancelModalOpen: false,
            blockModalOpen: false,
            currentCita: null,
            cancelMotivo: '',
            rescheduleData: {
                id_cita: '',
                id_profesional: '',
                fecha: '{{ $fecha->format('Y-m-d') }}',
                hora: '09:00',
                duracion_minutos: 30
            },
            openReschedule(c) {
                this.currentCita = c;
                this.rescheduleData.id_cita = c.id_cita;
                this.rescheduleData.id_profesional = c.id_profesional;
                this.rescheduleData.fecha = c.fecha_hora_inicio ? c.fecha_hora_inicio.substring(0, 10) : '{{ $fecha->format('Y-m-d') }}';
                this.rescheduleData.hora = c.fecha_hora_inicio ? c.fecha_hora_inicio.substring(11, 16) : '09:00';
                this.rescheduleModalOpen = true;
            },
            openCancel(c) {
                this.currentCita = c;
                this.cancelMotivo = '';
                this.cancelModalOpen = true;
            }
        }"
        @keydown.escape.window="createModalOpen = false; urgentModalOpen = false; rescheduleModalOpen = false; cancelModalOpen = false; blockModalOpen = false;"
        class="space-y-6"
    >
        <!-- Encabezado con información del módulo y botones de acción -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-primary-600">
                    <i data-lucide="calendar" class="size-4"></i>
                    <span>Recepción y Consultorios · Agenda Clínica</span>
                </div>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                    Agenda y Citas
                </h1>
                <p class="mt-1 text-sm text-slate-500 max-w-2xl">
                    Control de horarios, citas programadas por médico veterinario o estética, y atención directa de urgencias.
                </p>
            </div>

            @if (! $isReadOnly)
                <div class="flex items-center gap-2.5">
                    <!-- Botón Atención Urgente -->
                    <button
                        type="button"
                        @click="urgentModalOpen = true"
                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-3.5 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-100 hover:border-rose-300 transition-colors shadow-sm"
                    >
                        <i data-lucide="zap" class="size-4 text-rose-600"></i>
                        <span>Atención Urgente</span>
                    </button>

                    <!-- Botón Nueva Cita -->
                    <x-button variant="primary" @click="createModalOpen = true">
                        <i data-lucide="plus" class="size-4 mr-1.5"></i>
                        <span>Nueva Cita</span>
                    </x-button>
                </div>
            @endif
        </div>

        <!-- Fila de métricas KPI rápidas -->
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-card">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Citas Hoy</span>
                    <div class="flex size-7 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                        <i data-lucide="calendar-check" class="size-4"></i>
                    </div>
                </div>
                <p class="mt-2 text-2xl font-bold text-slate-900">{{ $metrics['hoy'] }}</p>
                <p class="text-xs text-slate-500 mt-0.5">Programadas en agenda</p>
            </div>

            <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-card">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Confirmadas</span>
                    <div class="flex size-7 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                        <i data-lucide="check-circle" class="size-4"></i>
                    </div>
                </div>
                <p class="mt-2 text-2xl font-bold text-emerald-600">{{ $metrics['confirmadas'] }}</p>
                <p class="text-xs text-slate-500 mt-0.5">Listas para ser atendidas</p>
            </div>

            <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-card">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">En Atención</span>
                    <div class="flex size-7 items-center justify-center rounded-lg bg-amber-50 text-amber-600">
                        <i data-lucide="stethoscope" class="size-4"></i>
                    </div>
                </div>
                <p class="mt-2 text-2xl font-bold text-amber-600">{{ $metrics['en_atencion'] }}</p>
                <p class="text-xs text-slate-500 mt-0.5">Pacientes en consultorio</p>
            </div>

            <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-card">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Pendientes</span>
                    <div class="flex size-7 items-center justify-center rounded-lg bg-slate-100 text-slate-600">
                        <i data-lucide="clock" class="size-4"></i>
                    </div>
                </div>
                <p class="mt-2 text-2xl font-bold text-slate-700">{{ $metrics['pendientes'] }}</p>
                <p class="text-xs text-slate-500 mt-0.5">Por confirmar llegada</p>
            </div>
        </div>

        <!-- Contenedor Principal: Filtros y Vistas de Agenda -->
        <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-card space-y-6">
            
            <!-- Barra Superior: Navegación de Fecha + Selector de Vista -->
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between border-b border-slate-100 pb-5">
                
                <!-- Navegador de Fecha -->
                <div class="flex items-center gap-2">
                    <a
                        href="{{ route('citas.index', array_merge($filters, ['fecha' => $fecha->copy()->subDay()->format('Y-m-d')])) }}"
                        class="inline-flex size-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-colors"
                        title="Día anterior"
                    >
                        <i data-lucide="chevron-left" class="size-4"></i>
                    </a>

                    <form action="{{ route('citas.index') }}" method="GET" class="flex items-center gap-2">
                        <input type="hidden" name="vista" value="{{ $vista }}">
                        @if (!empty($filters['profesional'])) <input type="hidden" name="profesional" value="{{ $filters['profesional'] }}"> @endif
                        @if (!empty($filters['estado'])) <input type="hidden" name="estado" value="{{ $filters['estado'] }}"> @endif
                        
                        <input
                            type="date"
                            name="fecha"
                            value="{{ $fecha->format('Y-m-d') }}"
                            onchange="this.form.submit()"
                            class="rounded-xl border-0 bg-slate-50/80 py-1.5 px-3 text-sm font-semibold text-slate-800 shadow-sm ring-1 ring-inset ring-slate-200 focus-ring"
                        />
                    </form>

                    <a
                        href="{{ route('citas.index', array_merge($filters, ['fecha' => $fecha->copy()->addDay()->format('Y-m-d')])) }}"
                        class="inline-flex size-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-colors"
                        title="Día siguiente"
                    >
                        <i data-lucide="chevron-right" class="size-4"></i>
                    </a>

                    <a
                        href="{{ route('citas.index', array_merge($filters, ['fecha' => now()->format('Y-m-d')])) }}"
                        class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-colors"
                    >
                        Hoy
                    </a>

                    <span class="ml-2 text-sm font-bold text-slate-800 capitalize hidden sm:inline">
                        {{ $fecha->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
                    </span>
                </div>

                <!-- Tabs de Modo de Visualización -->
                <div class="flex items-center gap-1 rounded-xl bg-slate-100 p-1">
                    <a
                        href="{{ route('citas.index', array_merge($filters, ['vista' => 'dia'])) }}"
                        class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold transition-colors {{ $vista === 'dia' ? 'bg-white text-primary-700 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}"
                    >
                        <i data-lucide="calendar" class="size-3.5"></i>
                        <span>Agenda del Día</span>
                    </a>

                    <a
                        href="{{ route('citas.index', array_merge($filters, ['vista' => 'lista'])) }}"
                        class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold transition-colors {{ $vista === 'lista' ? 'bg-white text-primary-700 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}"
                    >
                        <i data-lucide="list" class="size-3.5"></i>
                        <span>Lista Detallada</span>
                    </a>

                    <a
                        href="{{ route('citas.index', array_merge($filters, ['vista' => 'bloqueos'])) }}"
                        class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold transition-colors {{ $vista === 'bloqueos' ? 'bg-white text-primary-700 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}"
                    >
                        <i data-lucide="user-x" class="size-3.5"></i>
                        <span>Disponibilidad y Bloqueos</span>
                    </a>
                </div>
            </div>

            <!-- Filtros: Buscador + Profesional + Estado -->
            <form action="{{ route('citas.index') }}" method="GET" class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <input type="hidden" name="fecha" value="{{ $filters['fecha'] }}">
                <input type="hidden" name="vista" value="{{ $vista }}">

                <!-- Buscador -->
                <div class="relative">
                    <i data-lucide="search" class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 size-4 text-slate-400"></i>
                    <input
                        type="text"
                        name="search"
                        value="{{ $filters['search'] ?? '' }}"
                        placeholder="Buscar paciente, dueño, CI..."
                        class="block w-full rounded-xl border-0 py-2 pl-10 pr-4 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-200 placeholder:text-slate-400 focus-ring"
                    />
                </div>

                <!-- Filtro Profesional -->
                <div>
                    <select
                        name="profesional"
                        onchange="this.form.submit()"
                        class="block w-full rounded-xl border-0 bg-white py-2 pl-3.5 pr-8 text-sm text-slate-700 shadow-sm ring-1 ring-inset ring-slate-200 focus-ring"
                    >
                        <option value="">Todos los profesionales</option>
                        @foreach ($profesionales as $prof)
                            <option value="{{ $prof->id_persona }}" {{ ($filters['profesional'] ?? '') == $prof->id_persona ? 'selected' : '' }}>
                                Dr(a). {{ $prof->persona?->nombre }} {{ $prof->persona?->apellido1 }} ({{ $prof->especialidad }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filtro Estado -->
                <div>
                    <select
                        name="estado"
                        onchange="this.form.submit()"
                        class="block w-full rounded-xl border-0 bg-white py-2 pl-3.5 pr-8 text-sm text-slate-700 shadow-sm ring-1 ring-inset ring-slate-200 focus-ring"
                    >
                        <option value="">Todos los estados</option>
                        <option value="CONFIRMADA" {{ ($filters['estado'] ?? '') === 'CONFIRMADA' ? 'selected' : '' }}>Confirmada</option>
                        <option value="PENDIENTE" {{ ($filters['estado'] ?? '') === 'PENDIENTE' ? 'selected' : '' }}>Pendiente</option>
                        <option value="EN_ATENCION" {{ ($filters['estado'] ?? '') === 'EN_ATENCION' ? 'selected' : '' }}>En Atención</option>
                        <option value="COMPLETADA" {{ ($filters['estado'] ?? '') === 'COMPLETADA' ? 'selected' : '' }}>Completada</option>
                        <option value="CANCELADA" {{ ($filters['estado'] ?? '') === 'CANCELADA' ? 'selected' : '' }}>Cancelada</option>
                        <option value="INASISTENCIA" {{ ($filters['estado'] ?? '') === 'INASISTENCIA' ? 'selected' : '' }}>Inasistencia</option>
                    </select>
                </div>

                <!-- Botones Filtrar y Limpiar -->
                <div class="flex items-center gap-2">
                    <x-button variant="secondary" type="submit" class="flex-1">
                        <i data-lucide="filter" class="size-4 mr-1.5"></i>
                        <span>Filtrar</span>
                    </x-button>

                    @if (!empty($filters['search']) || !empty($filters['profesional']) || !empty($filters['estado']))
                        <a
                            href="{{ route('citas.index', ['fecha' => $filters['fecha'], 'vista' => $vista]) }}"
                            class="inline-flex size-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 hover:bg-slate-50 hover:text-slate-700 transition-colors"
                            title="Limpiar filtros"
                        >
                            <i data-lucide="rotate-ccw" class="size-4"></i>
                        </a>
                    @endif
                </div>
            </form>

            <!-- VISTA 1: AGENDA DEL DÍA (Cronograma por Bloques de Horas) -->
            @if ($vista === 'dia')
                <div class="space-y-4">
                    <div class="flex items-center justify-between text-xs text-slate-500 border-b border-slate-100 pb-2">
                        <span>Horario de atención: 08:00 a 19:00</span>
                        <span>{{ $citas->count() }} citas registradas para este día</span>
                    </div>

                    @php
                        // Bloques de horas de 8:00 a 18:00
                        $horas = [
                            '08:00', '09:00', '10:00', '11:00', '12:00',
                            '13:00', '14:00', '15:00', '16:00', '17:00', '18:00'
                        ];
                    @endphp

                    <div class="divide-y divide-slate-100 rounded-xl border border-slate-200/80 bg-white overflow-hidden shadow-sm">
                        @foreach ($horas as $hora)
                            @php
                                $horaCarbon = \Carbon\Carbon::parse($fecha->format('Y-m-d') . ' ' . $hora);
                                $citasEnHora = $citas->filter(function ($c) use ($horaCarbon) {
                                    if (!$c->fecha_hora_inicio) return false;
                                    $inicio = \Carbon\Carbon::parse($c->fecha_hora_inicio);
                                    return $inicio->format('H') === $horaCarbon->format('H');
                                });
                            @endphp

                            <div class="flex flex-col sm:flex-row items-stretch sm:items-start p-4 hover:bg-slate-50/50 transition-colors">
                                <!-- Columna de la Hora -->
                                <div class="w-24 shrink-0 font-mono text-sm font-bold text-slate-600 pb-2 sm:pb-0 flex items-center gap-1.5">
                                    <i data-lucide="clock" class="size-3.5 text-slate-400"></i>
                                    <span>{{ $hora }}</span>
                                </div>

                                <!-- Columna de Contenido / Citas -->
                                <div class="flex-1 space-y-2">
                                    @forelse ($citasEnHora as $cita)
                                        @php
                                            $estado = $cita->estado ?? 'PENDIENTE';
                                            $colorClase = match($estado) {
                                                'CONFIRMADA' => 'border-emerald-200 bg-emerald-50/40 text-emerald-900',
                                                'EN_ATENCION' => 'border-primary-300 bg-primary-50/50 text-primary-900 ring-2 ring-primary-500/20',
                                                'COMPLETADA' => 'border-slate-200 bg-slate-50/70 text-slate-700 opacity-75',
                                                'CANCELADA' => 'border-rose-200 bg-rose-50/50 text-rose-800 opacity-60',
                                                'INASISTENCIA' => 'border-purple-200 bg-purple-50/40 text-purple-800 opacity-60',
                                                default => 'border-amber-200 bg-amber-50/40 text-amber-900',
                                            };
                                            $inicioHora = $cita->fecha_hora_inicio ? \Carbon\Carbon::parse($cita->fecha_hora_inicio)->format('H:i') : '--:--';
                                            $finHora = $cita->fecha_hora_fin ? \Carbon\Carbon::parse($cita->fecha_hora_fin)->format('H:i') : '--:--';
                                        @endphp

                                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 rounded-xl border p-3.5 transition-shadow hover:shadow-md {{ $colorClase }}">
                                            <!-- Info Paciente y Cita -->
                                            <div class="flex items-start gap-3">
                                                <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-white text-primary-700 shadow-sm border border-slate-200/60 font-bold text-sm">
                                                    {{ strtoupper(substr($cita->paciente?->nombre ?? 'P', 0, 2)) }}
                                                </div>

                                                <div class="min-w-0">
                                                    <div class="flex flex-wrap items-center gap-2">
                                                        <span class="font-bold text-slate-900 text-sm">
                                                            {{ $cita->paciente?->nombre ?? 'Sin paciente' }}
                                                        </span>
                                                        <span class="text-xs text-slate-500 font-medium">
                                                            ({{ $cita->paciente?->raza?->nombre ?? 'Mestizo' }} · {{ $cita->paciente?->raza?->especie?->nombre ?? 'Canino' }})
                                                        </span>
                                                        
                                                        <x-badge :tone="match($estado) {
                                                            'CONFIRMADA' => 'success',
                                                            'EN_ATENCION' => 'primary',
                                                            'COMPLETADA' => 'neutral',
                                                            'CANCELADA' => 'danger',
                                                            default => 'warning',
                                                        }">
                                                            {{ $estado }}
                                                        </x-badge>

                                                        @if ($cita->origen === 'URGENCIA')
                                                            <span class="inline-flex items-center gap-1 rounded-md bg-rose-600 px-1.5 py-0.5 text-[10px] font-bold text-white shadow-xs">
                                                                <i data-lucide="zap" class="size-2.5"></i> Urgencia
                                                            </span>
                                                        @endif

                                                        @if ($cita->servicioDomicilio)
                                                            <span class="inline-flex items-center gap-1 rounded-md bg-amber-500 px-1.5 py-0.5 text-[10px] font-bold text-white shadow-xs">
                                                                <i data-lucide="truck" class="size-2.5"></i> Domicilio
                                                            </span>
                                                        @endif
                                                    </div>

                                                    <div class="mt-1 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-600">
                                                        <span class="font-semibold text-primary-700">
                                                            {{ $inicioHora }} - {{ $finHora }}
                                                        </span>
                                                        <span>
                                                            <i data-lucide="user" class="inline size-3.5 text-slate-400 mr-0.5"></i>
                                                            Dueño: <strong>{{ $cita->paciente?->propietario?->persona?->nombre }} {{ $cita->paciente?->propietario?->persona?->apellido1 }}</strong>
                                                        </span>
                                                        <span>
                                                            <i data-lucide="phone" class="inline size-3.5 text-slate-400 mr-0.5"></i>
                                                            {{ $cita->paciente?->propietario?->persona?->telefono ?: 'S/T' }}
                                                        </span>
                                                        <span>
                                                            <i data-lucide="stethoscope" class="inline size-3.5 text-slate-400 mr-0.5"></i>
                                                            Dr(a). {{ $cita->profesional?->persona?->nombre }} {{ $cita->profesional?->persona?->apellido1 }}
                                                        </span>
                                                    </div>

                                                    <!-- Servicios Asociados -->
                                                    @if ($cita->detalleCitas->isNotEmpty())
                                                        <div class="mt-2 flex flex-wrap gap-1.5">
                                                            @foreach ($cita->detalleCitas as $detalle)
                                                                <span class="inline-flex items-center rounded-md bg-white/80 px-2 py-0.5 text-[11px] font-medium text-slate-700 border border-slate-200/80 shadow-2xs">
                                                                    {{ $detalle->servicio?->nombre }} (Bs. {{ number_format($detalle->costo_estimado, 2) }})
                                                                </span>
                                                            @endforeach
                                                        </div>
                                                    @endif

                                                    @if ($cita->motivo_cancelacion)
                                                        <p class="mt-1 text-xs text-rose-600 font-medium">
                                                            Motivo de cancelación: {{ $cita->motivo_cancelacion }}
                                                        </p>
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- Acciones sobre la Cita -->
                                            @if (! $isReadOnly && ! in_array($estado, ['COMPLETADA', 'CANCELADA']))
                                                <div class="flex items-center gap-1.5 self-end md:self-center shrink-0">
                                                    <!-- Pasar a Atención -->
                                                    @if ($estado === 'CONFIRMADA')
                                                        <form action="{{ route('citas.cambiar-estado', $cita->id_cita) }}" method="POST">
                                                            @csrf
                                                            <input type="hidden" name="estado" value="EN_ATENCION">
                                                            <button
                                                                type="submit"
                                                                class="inline-flex items-center gap-1.5 rounded-lg bg-primary-600 px-2.5 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-primary-700 transition-colors"
                                                                title="Ingresar a consultorio"
                                                            >
                                                                <i data-lucide="play" class="size-3.5"></i>
                                                                <span>Atender</span>
                                                            </button>
                                                        </form>
                                                    @endif

                                                    <!-- Confirmar llegada -->
                                                    @if ($estado === 'PENDIENTE')
                                                        <form action="{{ route('citas.cambiar-estado', $cita->id_cita) }}" method="POST">
                                                            @csrf
                                                            <input type="hidden" name="estado" value="CONFIRMADA">
                                                            <button
                                                                type="submit"
                                                                class="inline-flex items-center gap-1 rounded-lg bg-emerald-600 px-2.5 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 transition-colors"
                                                                title="Confirmar llegada del paciente"
                                                            >
                                                                <i data-lucide="check" class="size-3.5"></i>
                                                                <span>Confirmar</span>
                                                            </button>
                                                        </form>
                                                    @endif

                                                    @php
                                                        $telCita = preg_replace('/[^0-9]/', '', $cita->paciente?->propietario?->persona?->telefono ?? '');
                                                        $duenoCita = $cita->paciente?->propietario?->persona?->nombre ?? 'Estimado(a)';
                                                        $mascotaCita = $cita->paciente?->nombre ?? 'su mascota';
                                                        $msgHoraCita = $cita->fecha_hora_inicio ? \Carbon\Carbon::parse($cita->fecha_hora_inicio)->format('H:i') : '';
                                                        $msgFechaCita = $cita->fecha_hora_inicio ? \Carbon\Carbon::parse($cita->fecha_hora_inicio)->format('d/m/Y') : '';
                                                        $waTextCita = urlencode("Hola {$duenoCita}, te escribimos de La Petdiatría para recordarte la cita de {$mascotaCita} programada para el {$msgFechaCita} a las {$msgHoraCita}. ¡Te esperamos!");
                                                    @endphp

                                                    @if (!empty($telCita))
                                                        <a
                                                            href="https://wa.me/{{ $telCita }}?text={{ $waTextCita }}"
                                                            target="_blank"
                                                            rel="noopener noreferrer"
                                                            class="inline-flex size-8 items-center justify-center rounded-lg border border-emerald-300 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 hover:border-emerald-400 transition-colors shadow-2xs"
                                                            title="Enviar recordatorio por WhatsApp a {{ $cita->paciente?->propietario?->persona?->telefono }}"
                                                        >
                                                            <i data-lucide="message-circle" class="size-3.5"></i>
                                                        </a>
                                                    @endif

                                                    <!-- Reprogramar -->
                                                    <button
                                                        type="button"
                                                        @click="openReschedule(@js($cita))"
                                                        class="inline-flex size-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-colors"
                                                        title="Reprogramar fecha u hora"
                                                    >
                                                        <i data-lucide="calendar-clock" class="size-3.5"></i>
                                                    </button>

                                                    <!-- Marcar Inasistencia -->
                                                    @if ($estado !== 'EN_ATENCION')
                                                        <form action="{{ route('citas.cambiar-estado', $cita->id_cita) }}" method="POST">
                                                            @csrf
                                                            <input type="hidden" name="estado" value="INASISTENCIA">
                                                            <button
                                                                type="submit"
                                                                class="inline-flex size-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 hover:bg-slate-100 hover:text-slate-700 transition-colors"
                                                                title="Marcar inasistencia"
                                                            >
                                                                <i data-lucide="user-x" class="size-3.5"></i>
                                                            </button>
                                                        </form>
                                                    @endif

                                                    <!-- Cancelar con Motivo -->
                                                    <button
                                                        type="button"
                                                        @click="openCancel(@js($cita))"
                                                        class="inline-flex size-8 items-center justify-center rounded-lg border border-rose-200 bg-white text-rose-600 hover:bg-rose-50 transition-colors"
                                                        title="Cancelar cita"
                                                    >
                                                        <i data-lucide="x" class="size-3.5"></i>
                                                    </button>
                                                </div>
                                            @endif
                                        </div>
                                    @empty
                                        <div class="rounded-xl border border-dashed border-slate-200 p-2.5 text-center text-xs text-slate-400">
                                            Sin citas programadas a las {{ $hora }}
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- VISTA 2: LISTA DETALLADA DE CITAS -->
            @if ($vista === 'lista')
                <div class="overflow-hidden rounded-xl border border-slate-200/80 bg-white shadow-card">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200/80 text-left text-sm">
                            <thead class="bg-slate-50/75 text-xs font-semibold uppercase tracking-wider text-slate-500">
                                <tr>
                                    <th scope="col" class="px-5 py-3.5 whitespace-nowrap">Fecha / Horario</th>
                                    <th scope="col" class="px-5 py-3.5 whitespace-nowrap">Paciente</th>
                                    <th scope="col" class="px-5 py-3.5 whitespace-nowrap">Propietario</th>
                                    <th scope="col" class="px-5 py-3.5 whitespace-nowrap">Profesional</th>
                                    <th scope="col" class="px-5 py-3.5 whitespace-nowrap">Servicios</th>
                                    <th scope="col" class="px-5 py-3.5 whitespace-nowrap">Estado</th>
                                    <th scope="col" class="px-5 py-3.5 whitespace-nowrap text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white text-slate-700">
                                @forelse ($citas as $cita)
                                    @php
                                        $estado = $cita->estado ?? 'PENDIENTE';
                                        $inicioHora = $cita->fecha_hora_inicio ? \Carbon\Carbon::parse($cita->fecha_hora_inicio)->format('H:i') : '--:--';
                                        $fechaStr = $cita->fecha_hora_inicio ? \Carbon\Carbon::parse($cita->fecha_hora_inicio)->format('d/m/Y') : '--';
                                    @endphp
                                    <tr class="hover:bg-slate-50/70 transition-colors">
                                        <td class="px-5 py-4 whitespace-nowrap">
                                            <div class="font-bold text-slate-900">{{ $fechaStr }}</div>
                                            <div class="text-xs text-primary-600 font-semibold">{{ $inicioHora }} hrs</div>
                                        </td>
                                        <td class="px-5 py-4 whitespace-nowrap">
                                            <div class="font-bold text-slate-900">{{ $cita->paciente?->nombre }}</div>
                                            <div class="text-xs text-slate-400">
                                                {{ $cita->paciente?->raza?->nombre ?? 'Mestizo' }} · {{ $cita->paciente?->raza?->especie?->nombre ?? 'Canino' }}
                                            </div>
                                        </td>
                                        <td class="px-5 py-4 whitespace-nowrap">
                                            <div class="font-medium text-slate-900">
                                                {{ $cita->paciente?->propietario?->persona?->nombre }} {{ $cita->paciente?->propietario?->persona?->apellido1 }}
                                            </div>
                                            <div class="text-xs text-slate-500">
                                                CI: {{ $cita->paciente?->propietario?->persona?->documento }} · Tel: {{ $cita->paciente?->propietario?->persona?->telefono ?: 'S/T' }}
                                            </div>
                                        </td>
                                        <td class="px-5 py-4 whitespace-nowrap">
                                            <div class="font-medium text-slate-800">
                                                Dr(a). {{ $cita->profesional?->persona?->nombre }} {{ $cita->profesional?->persona?->apellido1 }}
                                            </div>
                                            <div class="text-xs text-slate-400">{{ $cita->profesional?->especialidad }}</div>
                                        </td>
                                        <td class="px-5 py-4">
                                            <div class="flex flex-wrap gap-1 max-w-xs">
                                                @foreach ($cita->detalleCitas as $detalle)
                                                    <span class="rounded bg-slate-100 px-1.5 py-0.5 text-[11px] font-medium text-slate-700">
                                                        {{ $detalle->servicio?->nombre }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td class="px-5 py-4 whitespace-nowrap">
                                            <x-badge :tone="match($estado) {
                                                'CONFIRMADA' => 'success',
                                                'EN_ATENCION' => 'primary',
                                                'COMPLETADA' => 'neutral',
                                                'CANCELADA' => 'danger',
                                                default => 'warning',
                                            }">
                                                {{ $estado }}
                                            </x-badge>
                                        </td>
                                        <td class="px-5 py-4 whitespace-nowrap text-right">
                                            <div class="flex items-center justify-end gap-1.5">
                                                @if (! $isReadOnly && ! in_array($estado, ['COMPLETADA', 'CANCELADA']))
                                                    @php
                                                        $telCitaList = preg_replace('/[^0-9]/', '', $cita->paciente?->propietario?->persona?->telefono ?? '');
                                                        $duenoCitaList = $cita->paciente?->propietario?->persona?->nombre ?? 'Estimado(a)';
                                                        $mascotaCitaList = $cita->paciente?->nombre ?? 'su mascota';
                                                        $waTextList = urlencode("Hola {$duenoCitaList}, te escribimos de La Petdiatría para recordarte la cita de {$mascotaCitaList} el {$fechaStr} a las {$inicioHora} hrs.");
                                                    @endphp

                                                    @if (!empty($telCitaList))
                                                        <a
                                                            href="https://wa.me/{{ $telCitaList }}?text={{ $waTextList }}"
                                                            target="_blank"
                                                            rel="noopener noreferrer"
                                                            class="inline-flex size-7 items-center justify-center rounded-lg border border-emerald-300 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition-colors"
                                                            title="WhatsApp a {{ $cita->paciente?->propietario?->persona?->telefono }}"
                                                        >
                                                            <i data-lucide="message-circle" class="size-3.5"></i>
                                                        </a>
                                                    @endif

                                                    <button
                                                        type="button"
                                                        @click="openReschedule(@js($cita))"
                                                        class="inline-flex size-7 items-center justify-center rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-100"
                                                        title="Reprogramar"
                                                    >
                                                        <i data-lucide="calendar-clock" class="size-3.5"></i>
                                                    </button>

                                                    <button
                                                        type="button"
                                                        @click="openCancel(@js($cita))"
                                                        class="inline-flex size-7 items-center justify-center rounded-lg border border-rose-200 text-rose-600 hover:bg-rose-50"
                                                        title="Cancelar"
                                                    >
                                                        <i data-lucide="x" class="size-3.5"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="p-8">
                                            <x-empty-state
                                                icon="calendar-x"
                                                title="No se encontraron citas"
                                                description="No hay registros que coincidan con la fecha o filtros seleccionados."
                                            />
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- VISTA 3: DISPONIBILIDAD Y BLOQUEOS DE AGENDA -->
            @if ($vista === 'bloqueos')
                <div class="space-y-6">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between rounded-xl border border-indigo-100 bg-indigo-50/50 p-4">
                        <div class="flex items-start gap-3">
                            <div class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-primary-100 text-primary-700">
                                <i data-lucide="clock-alert" class="size-4.5"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">Control de Disponibilidad y Ausencias</h3>
                                <p class="text-xs text-slate-600">
                                    Registra bloqueos por vacaciones, congresos o permisos médicos para impedir que se agenden citas en dichos períodos.
                                </p>
                            </div>
                        </div>

                        @if (! $isReadOnly)
                            <x-button variant="primary" type="button" @click="blockModalOpen = true">
                                <i data-lucide="plus" class="size-4 mr-1.5"></i>
                                <span>Registrar Bloqueo</span>
                            </x-button>
                        @endif
                    </div>

                    <!-- Tabla de Bloqueos de Agenda Registrados -->
                    <div class="overflow-hidden rounded-xl border border-slate-200/80 bg-white shadow-card">
                        <div class="px-5 py-3.5 border-b border-slate-100 font-bold text-sm text-slate-800">
                            Bloqueos de Agenda Registrados
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200/80 text-left text-sm">
                                <thead class="bg-slate-50/75 text-xs font-semibold uppercase tracking-wider text-slate-500">
                                    <tr>
                                        <th scope="col" class="px-5 py-3.5">Profesional</th>
                                        <th scope="col" class="px-5 py-3.5">Desde</th>
                                        <th scope="col" class="px-5 py-3.5">Hasta</th>
                                        <th scope="col" class="px-5 py-3.5">Motivo de Ausencia</th>
                                        <th scope="col" class="px-5 py-3.5 text-right">Acción</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white text-slate-700">
                                    @forelse ($bloqueos as $b)
                                        <tr class="hover:bg-slate-50/70">
                                            <td class="px-5 py-4 font-bold text-slate-900">
                                                Dr(a). {{ $b->profesional?->persona?->nombre }} {{ $b->profesional?->persona?->apellido1 }}
                                                <span class="block text-xs font-normal text-slate-400">{{ $b->profesional?->especialidad }}</span>
                                            </td>
                                            <td class="px-5 py-4 whitespace-nowrap text-slate-700">
                                                {{ \Carbon\Carbon::parse($b->fecha_hora_inicio)->format('d/m/Y H:i') }}
                                            </td>
                                            <td class="px-5 py-4 whitespace-nowrap text-slate-700">
                                                {{ \Carbon\Carbon::parse($b->fecha_hora_fin)->format('d/m/Y H:i') }}
                                            </td>
                                            <td class="px-5 py-4 text-slate-600 font-medium">
                                                {{ $b->motivo }}
                                            </td>
                                            <td class="px-5 py-4 text-right">
                                                @if (! $isReadOnly)
                                                    <form action="{{ route('citas.bloqueos.destroy', $b->id_bloqueo) }}" method="POST" onsubmit="return confirm('¿Levantar este bloqueo de agenda?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button
                                                            type="submit"
                                                            class="inline-flex size-7 items-center justify-center rounded-lg border border-rose-200 text-rose-600 hover:bg-rose-50"
                                                            title="Eliminar bloqueo"
                                                        >
                                                            <i data-lucide="trash-2" class="size-3.5"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="p-6 text-center text-xs text-slate-400">
                                                No hay bloqueos de agenda registrados en este momento.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

        </div>

        @if (! $isReadOnly)
        <!-- ========================================== -->
        <!-- MODAL 1: NUEVA CITA PROGRAMADA            -->
        <!-- ========================================== -->
        <div x-show="createModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" x-transition.opacity>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" @click="createModalOpen = false"></div>

                <div class="relative w-full max-w-2xl rounded-2xl bg-white shadow-pop overflow-hidden">
                    <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4 bg-slate-50/50">
                        <div class="flex items-center gap-3">
                            <div class="flex size-9 items-center justify-center rounded-xl bg-primary-50 text-primary-600">
                                <i data-lucide="calendar-plus" class="size-5"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900">Agendar Nueva Cita</h3>
                                <p class="text-xs text-slate-500">Programación de consulta, vacunación o servicio estético</p>
                            </div>
                        </div>
                        <button type="button" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600" @click="createModalOpen = false">
                            <i data-lucide="x" class="size-5"></i>
                        </button>
                    </div>

                    <form action="{{ route('citas.store') }}" method="POST">
                        @csrf
                        <div class="max-h-[75vh] overflow-y-auto p-6 space-y-4">
                            <!-- Selección de Paciente -->
                            <x-field label="Paciente (Mascota)" :error="$errors->first('id_paciente')" required>
                                <select name="id_paciente" required class="block w-full rounded-xl border-0 bg-white py-2.5 pl-3.5 pr-8 text-sm text-slate-800 shadow-sm ring-1 ring-inset ring-slate-200 focus-ring">
                                    <option value="">Seleccione un paciente...</option>
                                    @foreach ($pacientes as $pac)
                                        <option value="{{ $pac->id_paciente }}">
                                            {{ $pac->nombre }} ({{ $pac->raza?->nombre ?? 'Mestizo' }}) — Dueño: {{ $pac->propietario?->persona?->nombre }} {{ $pac->propietario?->persona?->apellido1 }} (CI: {{ $pac->propietario?->persona?->documento }})
                                        </option>
                                    @endforeach
                                </select>
                            </x-field>

                            <!-- Selección de Profesional -->
                            <x-field label="Profesional Asignado" :error="$errors->first('id_profesional')" required>
                                <select name="id_profesional" required class="block w-full rounded-xl border-0 bg-white py-2.5 pl-3.5 pr-8 text-sm text-slate-800 shadow-sm ring-1 ring-inset ring-slate-200 focus-ring">
                                    <option value="">Seleccione un profesional...</option>
                                    @foreach ($profesionales as $prof)
                                        <option value="{{ $prof->id_persona }}">
                                            Dr(a). {{ $prof->persona?->nombre }} {{ $prof->persona?->apellido1 }} — {{ $prof->especialidad }}
                                        </option>
                                    @endforeach
                                </select>
                            </x-field>

                            <!-- Servicios a Realizar -->
                            <x-field label="Servicios a Realizar" :error="$errors->first('id_servicio')" required>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-36 overflow-y-auto rounded-xl border border-slate-200 p-3 bg-slate-50/40">
                                    @foreach ($servicios as $serv)
                                        <label class="flex items-center gap-2 text-xs text-slate-700 cursor-pointer p-1 rounded hover:bg-white">
                                            <input type="checkbox" name="id_servicio[]" value="{{ $serv->id_servicio }}" class="rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                                            <span class="font-medium">{{ $serv->nombre }}</span>
                                            <span class="text-slate-400 ml-auto">Bs. {{ number_format($serv->precio_base, 2) }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </x-field>

                            <!-- Fecha y Hora -->
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <x-field label="Fecha" :error="$errors->first('fecha')" required>
                                    <x-input type="date" name="fecha" value="{{ $fecha->format('Y-m-d') }}" required />
                                </x-field>

                                <x-field label="Hora Inicio" :error="$errors->first('hora')" required>
                                    <x-input type="time" name="hora" value="09:00" required />
                                </x-field>

                                <x-field label="Duración Estimada" :error="$errors->first('duracion_minutos')">
                                    <select name="duracion_minutos" class="block w-full rounded-xl border-0 bg-white py-2 pl-3 text-sm text-slate-800 shadow-sm ring-1 ring-inset ring-slate-200 focus-ring">
                                        <option value="30">30 minutos</option>
                                        <option value="45">45 minutos</option>
                                        <option value="60">1 hora</option>
                                        <option value="90">1 hora y media</option>
                                    </select>
                                </x-field>
                            </div>

                            <!-- Origen y Servicio a Domicilio -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" x-data="{ aDomicilio: false }">
                                <x-field label="Origen de la Cita">
                                    <select name="origen" class="block w-full rounded-xl border-0 bg-white py-2 pl-3 text-sm text-slate-800 shadow-sm ring-1 ring-inset ring-slate-200 focus-ring">
                                        <option value="PRESENCIAL">Presencial en Clínica</option>
                                        <option value="TELEFONO">Llamada Telefónica</option>
                                        <option value="WEB">Portal Web / Mensajería</option>
                                    </select>
                                </x-field>

                                <div class="pt-6">
                                    <label class="flex items-center gap-2 cursor-pointer text-sm font-semibold text-slate-700">
                                        <input type="checkbox" name="es_domicilio" value="1" x-model="aDomicilio" class="rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                                        <span>Requiere Atención a Domicilio</span>
                                    </label>
                                </div>

                                <div class="sm:col-span-2" x-show="aDomicilio" x-cloak>
                                    <x-field label="Dirección para Domicilio">
                                        <x-input type="text" name="direccion_domicilio" placeholder="Ej. Calle Los Álamos #320, Zona Norte" />
                                    </x-field>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-4 bg-slate-50/50">
                            <x-button variant="secondary" type="button" @click="createModalOpen = false">Cancelar</x-button>
                            <x-button variant="primary" type="submit">Agendar Cita</x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- MODAL 2: ATENCIÓN URGENTE DIRECTA         -->
        <!-- ========================================== -->
        <div x-show="urgentModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" x-transition.opacity>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" @click="urgentModalOpen = false"></div>

                <div class="relative w-full max-w-lg rounded-2xl bg-white shadow-pop overflow-hidden">
                    <div class="flex items-center justify-between border-b border-rose-100 px-6 py-4 bg-rose-50/50">
                        <div class="flex items-center gap-3">
                            <div class="flex size-9 items-center justify-center rounded-xl bg-rose-100 text-rose-700">
                                <i data-lucide="zap" class="size-5"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-rose-950">Ingreso de Atención Urgente</h3>
                                <p class="text-xs text-rose-600">Pasa al paciente a sala de atención inmediatamente</p>
                            </div>
                        </div>
                        <button type="button" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600" @click="urgentModalOpen = false">
                            <i data-lucide="x" class="size-5"></i>
                        </button>
                    </div>

                    <form action="{{ route('citas.urgente') }}" method="POST">
                        @csrf
                        <div class="p-6 space-y-4">
                            <x-field label="Paciente" required>
                                <select name="id_paciente" required class="block w-full rounded-xl border-0 bg-white py-2.5 pl-3.5 pr-8 text-sm text-slate-800 shadow-sm ring-1 ring-inset ring-slate-200 focus-ring">
                                    <option value="">Seleccione el paciente que ingresa...</option>
                                    @foreach ($pacientes as $pac)
                                        <option value="{{ $pac->id_paciente }}">
                                            {{ $pac->nombre }} ({{ $pac->raza?->nombre ?? 'Mestizo' }}) — Dueño: {{ $pac->propietario?->persona?->nombre }} {{ $pac->propietario?->persona?->apellido1 }}
                                        </option>
                                    @endforeach
                                </select>
                            </x-field>

                            <x-field label="Médico Veterinario de Guardia" required>
                                <select name="id_profesional" required class="block w-full rounded-xl border-0 bg-white py-2.5 pl-3.5 pr-8 text-sm text-slate-800 shadow-sm ring-1 ring-inset ring-slate-200 focus-ring">
                                    <option value="">Seleccione el profesional...</option>
                                    @foreach ($profesionales as $prof)
                                        <option value="{{ $prof->id_persona }}">
                                            Dr(a). {{ $prof->persona?->nombre }} {{ $prof->persona?->apellido1 }} ({{ $prof->especialidad }})
                                        </option>
                                    @endforeach
                                </select>
                            </x-field>

                            <x-field label="Servicio de Urgencia" required>
                                <select name="id_servicio" required class="block w-full rounded-xl border-0 bg-white py-2.5 pl-3.5 pr-8 text-sm text-slate-800 shadow-sm ring-1 ring-inset ring-slate-200 focus-ring">
                                    @foreach ($servicios as $serv)
                                        <option value="{{ $serv->id_servicio }}">
                                            {{ $serv->nombre }} — Bs. {{ number_format($serv->precio_base, 2) }}
                                        </option>
                                    @endforeach
                                </select>
                            </x-field>
                        </div>

                        <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-4 bg-slate-50/50">
                            <x-button variant="secondary" type="button" @click="urgentModalOpen = false">Cancelar</x-button>
                            <x-button variant="danger" type="submit">Iniciar Atención Ahora</x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- MODAL 3: REPROGRAMAR CITA                 -->
        <!-- ========================================== -->
        <div x-show="rescheduleModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" x-transition.opacity>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" @click="rescheduleModalOpen = false"></div>

                <div class="relative w-full max-w-md rounded-2xl bg-white shadow-pop overflow-hidden">
                    <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4 bg-slate-50/50">
                        <div class="flex items-center gap-3">
                            <div class="flex size-9 items-center justify-center rounded-xl bg-primary-50 text-primary-600">
                                <i data-lucide="calendar-clock" class="size-5"></i>
                            </div>
                            <h3 class="text-base font-bold text-slate-900">Reprogramar Cita</h3>
                        </div>
                        <button type="button" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100" @click="rescheduleModalOpen = false">
                            <i data-lucide="x" class="size-5"></i>
                        </button>
                    </div>

                    <form :action="'/citas/' + rescheduleData.id_cita + '/reprogramar'" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="p-6 space-y-4">
                            <x-field label="Profesional Asignado" required>
                                <select name="id_profesional" x-model="rescheduleData.id_profesional" required class="block w-full rounded-xl border-0 bg-white py-2 pl-3 text-sm text-slate-800 shadow-sm ring-1 ring-inset ring-slate-200 focus-ring">
                                    @foreach ($profesionales as $prof)
                                        <option value="{{ $prof->id_persona }}">
                                            Dr(a). {{ $prof->persona?->nombre }} {{ $prof->persona?->apellido1 }}
                                        </option>
                                    @endforeach
                                </select>
                            </x-field>

                            <x-field label="Nueva Fecha" required>
                                <x-input type="date" name="fecha" x-model="rescheduleData.fecha" required />
                            </x-field>

                            <x-field label="Nueva Hora" required>
                                <x-input type="time" name="hora" x-model="rescheduleData.hora" required />
                            </x-field>
                        </div>

                        <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-4 bg-slate-50/50">
                            <x-button variant="secondary" type="button" @click="rescheduleModalOpen = false">Cancelar</x-button>
                            <x-button variant="primary" type="submit">Guardar Nueva Fecha</x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- MODAL 4: CANCELAR CITA CON MOTIVO         -->
        <!-- ========================================== -->
        <div x-show="cancelModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" x-transition.opacity>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" @click="cancelModalOpen = false"></div>

                <div class="relative w-full max-w-md rounded-2xl bg-white shadow-pop overflow-hidden">
                    <div class="flex items-center justify-between border-b border-rose-100 px-6 py-4 bg-rose-50/50">
                        <div class="flex items-center gap-3">
                            <div class="flex size-9 items-center justify-center rounded-xl bg-rose-100 text-rose-700">
                                <i data-lucide="alert-triangle" class="size-5"></i>
                            </div>
                            <h3 class="text-base font-bold text-rose-950">Cancelar Cita Médica</h3>
                        </div>
                        <button type="button" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100" @click="cancelModalOpen = false">
                            <i data-lucide="x" class="size-5"></i>
                        </button>
                    </div>

                    <form :action="'/citas/' + (currentCita ? currentCita.id_cita : '') + '/cancelar'" method="POST">
                        @csrf
                        <div class="p-6 space-y-4">
                            <p class="text-sm text-slate-600">
                                ¿Estás seguro de cancelar la cita de <strong x-text="currentCita?.paciente?.nombre"></strong>? Esta acción liberará el horario del profesional.
                            </p>

                            <x-field label="Motivo de Cancelación" required hint="Obligatorio para fines de trazabilidad">
                                <textarea
                                    name="motivo_cancelacion"
                                    x-model="cancelMotivo"
                                    required
                                    rows="3"
                                    placeholder="Ej. Propietario solicita postergación por viaje, mascota indispuesta..."
                                    class="block w-full rounded-xl border-0 py-2 px-3 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-200 focus-ring"
                                ></textarea>
                            </x-field>
                        </div>

                        <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-4 bg-slate-50/50">
                            <x-button variant="secondary" type="button" @click="cancelModalOpen = false">Volver</x-button>
                            <x-button variant="danger" type="submit">Confirmar Cancelación</x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- MODAL 5: REGISTRAR BLOQUEO DE AGENDA      -->
        <!-- ========================================== -->
        <div x-show="blockModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" x-transition.opacity>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" @click="blockModalOpen = false"></div>

                <div class="relative w-full max-w-md rounded-2xl bg-white shadow-pop overflow-hidden">
                    <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4 bg-slate-50/50">
                        <div class="flex items-center gap-3">
                            <div class="flex size-9 items-center justify-center rounded-xl bg-primary-50 text-primary-600">
                                <i data-lucide="user-x" class="size-5"></i>
                            </div>
                            <h3 class="text-base font-bold text-slate-900">Registrar Bloqueo de Agenda</h3>
                        </div>
                        <button type="button" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100" @click="blockModalOpen = false">
                            <i data-lucide="x" class="size-5"></i>
                        </button>
                    </div>

                    <form action="{{ route('citas.bloqueos.store') }}" method="POST">
                        @csrf
                        <div class="p-6 space-y-4">
                            <x-field label="Profesional" required>
                                <select name="id_profesional" required class="block w-full rounded-xl border-0 bg-white py-2 pl-3 text-sm text-slate-800 shadow-sm ring-1 ring-inset ring-slate-200 focus-ring">
                                    <option value="">Seleccione el profesional...</option>
                                    @foreach ($profesionales as $prof)
                                        <option value="{{ $prof->id_persona }}">
                                            Dr(a). {{ $prof->persona?->nombre }} {{ $prof->persona?->apellido1 }}
                                        </option>
                                    @endforeach
                                </select>
                            </x-field>

                            <div class="grid grid-cols-2 gap-3">
                                <x-field label="Fecha Inicio" required>
                                    <x-input type="date" name="fecha_inicio" value="{{ $fecha->format('Y-m-d') }}" required />
                                </x-field>
                                <x-field label="Hora Inicio" required>
                                    <x-input type="time" name="hora_inicio" value="08:00" required />
                                </x-field>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <x-field label="Fecha Fin" required>
                                    <x-input type="date" name="fecha_fin" value="{{ $fecha->format('Y-m-d') }}" required />
                                </x-field>
                                <x-field label="Hora Fin" required>
                                    <x-input type="time" name="hora_fin" value="13:00" required />
                                </x-field>
                            </div>

                            <x-field label="Motivo del Bloqueo" required>
                                <x-input type="text" name="motivo" placeholder="Ej. Congreso Veterinario, Cirugía programada, Descanso" required />
                            </x-field>
                        </div>

                        <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-4 bg-slate-50/50">
                            <x-button variant="secondary" type="button" @click="blockModalOpen = false">Cancelar</x-button>
                            <x-button variant="primary" type="submit">Guardar Bloqueo</x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif

    </div>
</x-layout>
