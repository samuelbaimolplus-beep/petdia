<x-layout title="Dashboard — La Petdiatría">

    <!-- Modal de Nueva Cita Rápida -->
    <div
        x-data="{ newAppointmentModal: false }"
        @open-new-appointment.window="newAppointmentModal = true"
        x-cloak
    >
        <div x-show="newAppointmentModal" class="fixed inset-0 z-50 overflow-y-auto" x-transition.opacity>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" @click="newAppointmentModal = false"></div>

                <div
                    class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-pop border border-slate-200/80"
                    x-show="newAppointmentModal"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                >
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2.5">
                            <div class="flex size-8 items-center justify-center rounded-lg bg-primary-50 text-primary-600">
                                <i data-lucide="calendar-plus" class="size-4.5"></i>
                            </div>
                            <h3 class="text-base font-semibold text-slate-900">Agendar Nueva Cita</h3>
                        </div>
                        <button type="button" @click="newAppointmentModal = false" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100">
                            <i data-lucide="x" class="size-4"></i>
                        </button>
                    </div>

                    <form class="mt-4 space-y-3.5">
                        <x-field label="Paciente / Mascota" required>
                            <x-select>
                                <option value="1">Max (Pastor Alemán - Juan Perez)</option>
                                <option value="2">Luna (Persa - Maria Lopez)</option>
                                <option value="3">Rocky (Labrador - Carlos Mendoza)</option>
                                <option value="4">Nieve (Holland Lop - Ana Torres)</option>
                            </x-select>
                        </x-field>

                        <div class="grid grid-cols-2 gap-3">
                            <x-field label="Servicio principal" required>
                                <x-select>
                                    <option value="4">Consulta general</option>
                                    <option value="5">Vacunación</option>
                                    <option value="6">Desparasitación</option>
                                    <option value="1">Baño completo</option>
                                    <option value="2">Corte estético</option>
                                </x-select>
                            </x-field>

                            <x-field label="Profesional" required>
                                <x-select>
                                    <option value="1">Dra. Sofia Garcia (Veterinaria)</option>
                                    <option value="2">Pedro Fernandez (Estética)</option>
                                    <option value="3">Dra. Laura Gonzales (Especialista)</option>
                                </x-select>
                            </x-field>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <x-field label="Fecha" required>
                                <x-date-input type="date" value="{{ date('Y-m-d') }}" />
                            </x-field>

                            <x-field label="Hora de inicio" required>
                                <x-date-input type="time" value="10:00" />
                            </x-field>
                        </div>

                        <x-field label="Motivo / Observaciones">
                            <x-input placeholder="Ej. Control de vacunas, decaimiento..." />
                        </x-field>

                        <div class="mt-6 flex justify-end gap-2.5 pt-3 border-t border-slate-100">
                            <x-button variant="secondary" @click="newAppointmentModal = false">
                                Cancelar
                            </x-button>
                            <x-button variant="primary" icon="check" @click="newAppointmentModal = false; $store.toast.success('Cita agendada correctamente.')">
                                Guardar Cita
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Encabezado del área principal (Título + Bienvenida + Fecha + Botón Nueva Cita) -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">Dashboard</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Bienvenido de nuevo, <span class="font-medium text-slate-800 dark:text-slate-200">{{ $user->nombre_completo }}</span>.
                </p>
            </div>

            <div class="flex flex-wrap sm:flex-nowrap items-center gap-2.5 sm:gap-3">
                <!-- Fecha actual del día en español (ej. "vie, 7 ago 2026") -->
                <div class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 px-3.5 py-2 text-xs sm:text-sm font-medium text-slate-600 dark:text-slate-300 shadow-xs">
                    <i data-lucide="calendar" class="size-4 text-slate-400 shrink-0"></i>
                    <span>{{ ucfirst(Carbon\Carbon::now()->locale('es')->isoFormat('ddd, D MMM YYYY')) }}</span>
                </div>

                <!-- Botón primario azul índigo "+" ("Nueva cita") -->
                <x-button
                    type="button"
                    variant="primary"
                    icon="plus"
                    class="shadow-sm"
                    @click="newAppointmentModal = true"
                >
                    Nueva cita
                </x-button>
            </div>
        </div>

        <!-- Fila de Tarjetas KPI (Grid de 4 columnas) -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
            @foreach ($kpis as $kpi)
                <x-stat-card
                    :label="$kpi['label']"
                    :value="$kpi['value']"
                    :description="$kpi['description']"
                    :icon="$kpi['icon']"
                    :tone="$kpi['tone']"
                />
            @endforeach
        </div>

        <!-- Gráficas Reales del Dashboard (Chart.js — Mejora 3.1) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8" x-data="dashboardChartsManager(@js($chartData ?? []))">
            <!-- Card Gráfico Citas (Últimos 7 días) -->
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Citas por Estado</h3>
                        <p class="text-xs text-slate-500">Últimos 7 días de actividad médica</p>
                    </div>
                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-600">7 días</span>
                </div>
                <div class="mt-4 h-64">
                    <canvas id="dashboardCitasChart"></canvas>
                </div>
            </div>

            <!-- Card Gráfico Ingresos (Últimos 30 días) -->
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Ingresos Diarios de Caja</h3>
                        <p class="text-xs text-slate-500">Comportamiento financiero en los últimos 30 días</p>
                    </div>
                    <span class="rounded-full bg-indigo-50 px-2.5 py-1 text-[11px] font-semibold text-indigo-700">30 días</span>
                </div>
                <div class="mt-4 h-64">
                    <canvas id="dashboardIngresosChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Panel de Gráfica Ancho: "Carga de trabajo de hoy" -->
        <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-card mb-8">
            
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-5 border-b border-slate-100">
                <div>
                    <h2 class="text-base font-bold text-slate-900 tracking-tight">Carga de trabajo de hoy</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Distribución horaria de consultas y procedimientos agendados (8:00 a 19:00)</p>
                </div>

                <div class="flex items-center gap-4">
                    <!-- Indicador de Hora Pico -->
                    @if ($horaPico)
                        <div class="inline-flex items-center gap-1.5 rounded-full bg-primary-50 px-3 py-1 text-xs font-semibold text-primary-700 ring-1 ring-inset ring-primary-600/20">
                            <i data-lucide="flame" class="size-3.5 text-primary-600"></i>
                            <span>Hora pico {{ $horaPico['label'] }} · {{ $horaPico['total'] }} citas</span>
                        </div>
                    @else
                        <div class="inline-flex items-center gap-1.5 rounded-full bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-500 ring-1 ring-inset ring-slate-200">
                            <i data-lucide="check-check" class="size-3.5 text-emerald-600"></i>
                            <span>Carga regular sin saturación</span>
                        </div>
                    @endif

                    <a href="#agenda" class="inline-flex items-center gap-1 text-xs font-semibold text-primary-600 hover:text-primary-700 hover:underline">
                        <span>Abrir calendario</span>
                        <i data-lucide="chevron-right" class="size-3.5"></i>
                    </a>
                </div>
            </div>

            <!-- Resumen de Métrica Grande con Leyenda de Puntos de Color -->
            <div class="mt-5 flex flex-wrap items-baseline justify-between gap-4">
                <div class="flex items-baseline gap-3">
                    <span class="text-4xl font-extrabold tracking-tight text-slate-900">{{ $totalCitasHoy }}</span>
                    <span class="text-sm font-medium text-slate-500">citas programadas en total</span>
                </div>

                <!-- Leyenda de colores -->
                <div class="flex items-center gap-5 text-xs font-medium text-slate-600">
                    <div class="flex items-center gap-2">
                        <span class="size-2.5 rounded-full bg-emerald-500"></span>
                        <span>Completadas ({{ $totalCompletadas }})</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="size-2.5 rounded-full bg-primary-600"></span>
                        <span>Restantes / En curso ({{ $totalRestantes }})</span>
                    </div>
                </div>
            </div>

            <!-- Gráfica de Barras Verticales Suaves (8a - 7p) -->
            <div class="mt-6 pt-4 overflow-x-auto pb-2">
                <div class="min-w-[480px] grid grid-cols-12 gap-2 sm:gap-3 items-end h-48 border-b border-slate-100 dark:border-slate-800 pb-2">
                    @php
                        $maxAppointments = max($horas->max('total') ?? 1, 4);
                    @endphp

                    @foreach ($horas as $h)
                        @php
                            $totalH = $h['total'];
                            $completedH = $h['completadas'];
                            $remainingH = $h['restantes'];
                            $heightPercent = $totalH > 0 ? max(round(($totalH / $maxAppointments) * 100), 16) : 0;
                            $completedPercent = $totalH > 0 ? round(($completedH / $totalH) * 100) : 0;
                        @endphp

                        <div class="group relative flex flex-col items-center h-full justify-end">
                            
                            <!-- Tooltip al pasar el ratón -->
                            <div class="pointer-events-none absolute -top-10 opacity-0 group-hover:opacity-100 transition-opacity bg-slate-900 text-white text-[11px] rounded-lg py-1 px-2 whitespace-nowrap z-20 shadow-md">
                                {{ $h['label'] }}: {{ $totalH }} cita(s) ({{ $completedH }} comp. / {{ $remainingH }} rest.)
                            </div>

                            @if ($totalH > 0)
                                <!-- Barra activa con gradiente índigo y desglose de completadas -->
                                <div
                                    class="w-full max-w-[28px] rounded-t-lg bg-gradient-to-t from-primary-600 to-primary-400 overflow-hidden flex flex-col justify-end transition-all duration-300 group-hover:brightness-110 shadow-xs"
                                    style="height: {{ $heightPercent }}%;"
                                >
                                    @if ($completedPercent > 0)
                                        <div
                                            class="w-full bg-emerald-500"
                                            style="height: {{ $completedPercent }}%;"
                                        ></div>
                                    @endif
                                </div>
                            @else
                                <!-- Barra vacía tenue como guía visual requerida en 1.1 -->
                                <div class="w-full max-w-[28px] h-3 rounded-t-md bg-slate-100 border border-dashed border-slate-200 group-hover:bg-slate-200 transition-colors"></div>
                            @endif

                            <!-- Etiqueta de hora -->
                            <span class="mt-2 text-[11px] font-medium text-slate-400 group-hover:text-slate-700 transition-colors">
                                {{ $h['hora'] }}h
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Secciones Operativas: Citas del Día y Paneles de Información -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Columna Izquierda (2 cols): Listado de Citas de Hoy -->
            <div class="lg:col-span-2 space-y-6">
                
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-slate-900 tracking-tight">Citas programadas para hoy</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Control de admisiones y atenciones del día</p>
                    </div>

                    <a href="#ver-todas" class="text-xs font-semibold text-primary-600 hover:text-primary-700 hover:underline">
                        Ver agenda completa →
                    </a>
                </div>

                @if ($citasHoy->isEmpty())
                    <x-empty-state
                        icon="calendar"
                        title="No hay citas registradas para hoy"
                        description="Los turnos agendados para esta fecha se mostrarán aquí de forma cronológica."
                    >
                        <x-slot:action>
                            <x-button variant="secondary" icon="plus" @click="newAppointmentModal = true">
                                Agendar primera cita
                            </x-button>
                        </x-slot:action>
                    </x-empty-state>
                @else
                    <x-table :headers="['Hora', 'Paciente / Dueño', 'Servicio', 'Profesional', 'Estado', 'Acción']">
                        @foreach ($citasHoy as $cita)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="px-5 py-3.5 whitespace-nowrap font-semibold text-slate-900">
                                    {{ Carbon\Carbon::parse($cita->fecha_hora_inicio)->format('H:i') }}
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-2.5">
                                        <div class="flex size-7 items-center justify-center rounded-lg bg-slate-100 text-slate-600 font-bold text-xs">
                                            {{ strtoupper(substr($cita->paciente?->nombre ?? 'P', 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="font-semibold text-slate-900">{{ $cita->paciente?->nombre ?? 'Paciente #' . $cita->id_paciente }}</p>
                                            <p class="text-xs text-slate-500">{{ $cita->paciente?->propietario?->persona?->nombre ?? 'Propietario' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 text-xs text-slate-600">
                                    {{ $cita->detalles->first()?->servicio?->nombre ?? 'Consulta' }}
                                </td>
                                <td class="px-5 py-3.5 text-xs text-slate-600">
                                    {{ $cita->profesional?->persona?->nombre ?? 'Por asignar' }}
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <x-state-badge :value="$cita->estado" />
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap text-right">
                                    <button
                                        type="button"
                                        class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                                        title="Opciones"
                                    >
                                        <i data-lucide="more-horizontal" class="size-4"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </x-table>
                @endif

                <!-- Sala de Espera / Pacientes en curso -->
                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2.5">
                            <div class="flex size-8 items-center justify-center rounded-lg bg-amber-50 text-amber-600">
                                <i data-lucide="clock" class="size-4.5"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">Sala de Espera y Atenciones en Curso</h3>
                                <p class="text-xs text-slate-500">Mascotas listas para consultorio o baño</p>
                            </div>
                        </div>
                        <span class="rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-bold text-amber-700 ring-1 ring-inset ring-amber-600/20">
                            {{ $salaEsperaCount }} en espera
                        </span>
                    </div>

                    @if ($salaEspera->isEmpty())
                        <div class="py-6 text-center text-slate-400 text-xs">
                            <i data-lucide="check-circle" class="size-6 mx-auto mb-1 text-slate-300"></i>
                            <p>No hay pacientes en espera en este momento.</p>
                        </div>
                    @else
                        <div class="divide-y divide-slate-100 mt-2">
                            @foreach ($salaEspera as $atencion)
                                <div class="flex items-center justify-between py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="flex size-8 items-center justify-center rounded-lg bg-slate-100 text-slate-700 font-semibold text-xs">
                                            {{ strtoupper(substr($atencion->paciente?->nombre ?? 'P', 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-slate-900">{{ $atencion->paciente?->nombre }}</p>
                                            <p class="text-xs text-slate-500">Atendido por: {{ $atencion->profesional?->persona?->nombre ?? 'Personal de turno' }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <x-state-badge :value="$atencion->estado" />
                                        <x-button size="sm" variant="secondary">Ver</x-button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

            </div>

            <!-- Columna Derecha (1 col): Alertas del Sistema y Recordatorios -->
            <div class="space-y-6">

                <!-- Alertas de Inventario Médico -->
                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2">
                            <div class="flex size-8 items-center justify-center rounded-lg bg-rose-50 text-rose-600">
                                <i data-lucide="alert-triangle" class="size-4.5"></i>
                            </div>
                            <h3 class="text-sm font-bold text-slate-900">Alertas de Inventario</h3>
                        </div>
                        <span class="rounded-full bg-rose-50 px-2.5 py-0.5 text-xs font-bold text-rose-700 ring-1 ring-inset ring-rose-600/20">
                            {{ $stockBajoCount + $vencimientosCount }} alertas
                        </span>
                    </div>

                    <div class="mt-4 space-y-3">
                        @if ($stockBajoCount > 0)
                            <div class="rounded-xl border border-amber-200 bg-amber-50/50 p-3">
                                <p class="text-xs font-bold text-amber-900 uppercase tracking-wider">Stock Mínimo Alcanzado</p>
                                <p class="text-xs text-amber-700 mt-0.5">Hay {{ $stockBajoCount }} producto(s) con existencia menor o igual al umbral.</p>
                                <div class="mt-2 space-y-1">
                                    @foreach ($productosStockBajo as $prod)
                                        <div class="flex items-center justify-between text-xs text-amber-900 bg-white/80 px-2 py-1 rounded">
                                            <span class="font-medium truncate">{{ $prod->nombre }}</span>
                                            <span class="font-bold">{{ $prod->stock_actual }} / {{ $prod->stock_minimo }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if ($vencimientosCount > 0)
                            <div class="rounded-xl border border-rose-200 bg-rose-50/50 p-3">
                                <p class="text-xs font-bold text-rose-900 uppercase tracking-wider">Medicamentos por Vencer (&le; 30 días)</p>
                                <p class="text-xs text-rose-700 mt-0.5">Hay {{ $vencimientosCount }} lote(s) próximo(s) a expirar.</p>
                                <div class="mt-2 space-y-1">
                                    @foreach ($lotesPorVencer as $lote)
                                        <div class="flex items-center justify-between text-xs text-rose-900 bg-white/80 px-2 py-1 rounded">
                                            <span class="font-medium truncate">{{ $lote->producto?->nombre ?? 'Lote #' . $lote->id_lote }}</span>
                                            <span class="font-semibold">{{ Carbon\Carbon::parse($lote->fecha_vencimiento)->format('d/m/Y') }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if ($stockBajoCount === 0 && $vencimientosCount === 0)
                            <div class="py-6 text-center text-slate-400 text-xs">
                                <i data-lucide="shield-check" class="size-6 mx-auto mb-1 text-emerald-500"></i>
                                <p>Inventario al día sin alertas activas.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Próximos Recordatorios -->
                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2">
                            <div class="flex size-8 items-center justify-center rounded-lg bg-violet-50 text-violet-600">
                                <i data-lucide="bell" class="size-4.5"></i>
                            </div>
                            <h3 class="text-sm font-bold text-slate-900">Recordatorios (7 días)</h3>
                        </div>
                        <span class="text-xs text-slate-400 font-medium">Controles / Vacunas</span>
                    </div>

                    @if ($recordatorios->isEmpty())
                        <div class="py-6 text-center text-slate-400 text-xs">
                            <i data-lucide="bell-off" class="size-6 mx-auto mb-1 text-slate-300"></i>
                            <p>No hay recordatorios pendientes para esta semana.</p>
                        </div>
                    @else
                        <div class="mt-3 space-y-2.5">
                            @foreach ($recordatorios as $rec)
                                <div class="flex items-start justify-between p-2.5 rounded-xl border border-slate-100 bg-slate-50/50 hover:bg-slate-50 transition-colors">
                                    <div>
                                        <p class="text-xs font-bold text-slate-900">{{ $rec->paciente?->nombre }}</p>
                                        <p class="text-[11px] text-slate-500">{{ $rec->tipo }} · {{ $rec->paciente?->propietario?->persona?->nombre ?? 'Propietario' }}</p>
                                    </div>
                                    <span class="text-[11px] font-semibold text-primary-600 bg-primary-50 px-2 py-0.5 rounded-full">
                                        {{ Carbon\Carbon::parse($rec->fecha_hora_programada)->format('d/m H:i') }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

            </div>

        </div>

    </div>

    <script>
    function dashboardChartsManager(data) {
        return {
            chartData: data || {},
            init() {
                this.$nextTick(() => {
                    this.initCitasChart();
                    this.initIngresosChart();
                });
            },
            initCitasChart() {
                const ctx = document.getElementById('dashboardCitasChart');
                if (!ctx || !window.Chart) return;
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: this.chartData.citas?.labels || [],
                        datasets: [
                            {
                                label: 'Atendidas',
                                data: this.chartData.citas?.atendidas || [],
                                backgroundColor: '#10b981',
                                borderRadius: 6,
                            },
                            {
                                label: 'Agendadas',
                                data: this.chartData.citas?.agendadas || [],
                                backgroundColor: '#0284c7',
                                borderRadius: 6,
                            },
                            {
                                label: 'Canceladas',
                                data: this.chartData.citas?.canceladas || [],
                                backgroundColor: '#f43f5e',
                                borderRadius: 6,
                            },
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'top', labels: { boxWidth: 12, font: { size: 11 } } },
                        },
                        scales: {
                            y: { beginAtZero: true, ticks: { precision: 0, font: { size: 10 } } },
                            x: { grid: { display: false }, ticks: { font: { size: 10 } } }
                        }
                    }
                });
            },
            initIngresosChart() {
                const ctx = document.getElementById('dashboardIngresosChart');
                if (!ctx || !window.Chart) return;
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: this.chartData.ingresos?.labels || [],
                        datasets: [{
                            label: 'Ingresos (Gs.)',
                            data: this.chartData.ingresos?.valores || [],
                            borderColor: '#6366f1',
                            backgroundColor: 'rgba(99, 102, 241, 0.12)',
                            fill: true,
                            tension: 0.35,
                            pointRadius: 2,
                            pointHoverRadius: 5,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return 'Gs. ' + Number(context.raw).toLocaleString('es-PY');
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    font: { size: 10 },
                                    callback: function(v) { return 'Gs. ' + Number(v).toLocaleString('es-PY'); }
                                }
                            },
                            x: { grid: { display: false }, ticks: { font: { size: 10 }, maxTicksLimit: 10 } }
                        }
                    }
                });
            }
        };
    }
    </script>
</x-layout>

