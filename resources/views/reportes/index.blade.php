@extends('layouts.app')

@section('title', 'Reportes y Analítica — La Petdiatría')

@section('content')
<div class="space-y-6" x-data="reportesManager(@js($charts ?? []))" x-init="initCharts()">
    <!-- Header de navegación y acciones -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-100 shadow-xs">
        <div>
            <div class="flex items-center gap-2 text-sm text-slate-500 mb-1">
                <span>Administración</span>
                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="text-slate-800 font-medium">Reportes</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Panel de Reportes y Analítica</h1>
            <p class="text-sm text-slate-500">Métricas operativas, financieras y de rendimiento clínico en tiempo real</p>
        </div>
        <div class="flex items-center gap-3">
            <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 transition-colors shadow-xs">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Imprimir Reporte
            </button>
        </div>
    </div>

    <!-- Filtro de Fechas y Acceso Rápido -->
    <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-xs">
        <form id="formFiltros" method="GET" action="{{ route('reportes.index') }}" class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider mr-1">Rango rápido:</span>
                <button type="button" @click="setRango('hoy')" class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-slate-100 hover:bg-indigo-50 hover:text-indigo-600 transition-colors text-slate-700">
                    Hoy
                </button>
                <button type="button" @click="setRango('7dias')" class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-slate-100 hover:bg-indigo-50 hover:text-indigo-600 transition-colors text-slate-700">
                    Últimos 7 días
                </button>
                <button type="button" @click="setRango('mes')" class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition-colors">
                    Mes Actual
                </button>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <div class="flex items-center gap-2 text-sm text-slate-600">
                    <label for="inputDesde" class="text-xs font-medium text-slate-400">Desde:</label>
                    <input type="date" id="inputDesde" name="desde" value="{{ $desde->toDateString() }}" class="px-3 py-1.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600">
                </div>
                <div class="flex items-center gap-2 text-sm text-slate-600">
                    <label for="inputHasta" class="text-xs font-medium text-slate-400">Hasta:</label>
                    <input type="date" id="inputHasta" name="hasta" value="{{ $hasta->toDateString() }}" class="px-3 py-1.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600">
                </div>
                <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-sm font-semibold transition-colors">
                    Aplicar
                </button>
            </div>
        </form>
    </div>

    <!-- 4 Tarjetas KPI -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Ingresos Totales -->
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Ingresos del Período</span>
                <p class="text-2xl font-bold text-slate-900 mt-1">Bs. {{ number_format($totalIngresos, 2) }}</p>
                <span class="text-xs text-slate-500 mt-0.5 block">{{ $cantidadTransacciones }} cobro(s) realizados</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <!-- Total Atenciones -->
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Atenciones Médicas</span>
                <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $totalAtenciones }}</p>
                <span class="text-xs text-slate-500 mt-0.5 block">{{ $atencionesCompletadas }} concluidas con éxito</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
        </div>

        <!-- Vacunas Aplicadas -->
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Inmunizaciones</span>
                <p class="text-2xl font-bold text-indigo-600 mt-1">{{ $vacunasAplicadas }}</p>
                <span class="text-xs text-slate-500 mt-0.5 block">Dosis registradas</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-violet-50 border border-violet-100 flex items-center justify-center text-violet-600">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
            </div>
        </div>

        <!-- Alertas de Inventario -->
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Stock Crítico</span>
                <p class="text-2xl font-bold text-amber-600 mt-1">{{ $productosCriticos->count() }}</p>
                <span class="text-xs text-slate-500 mt-0.5 block">{{ $lotesPorVencer->count() }} lotes por vencer</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-600">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
        </div>
    </div>

    <!-- Navegación por Pestañas -->
    <div class="border-b border-slate-200">
        <nav class="flex gap-4" aria-label="Tabs">
            <button 
                type="button" 
                @click="tab = 'ventas'; $nextTick(() => initVentasCharts())" 
                :class="tab === 'ventas' ? 'border-indigo-600 text-indigo-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700'"
                class="py-3 px-1 border-b-2 text-sm font-semibold transition-colors"
            >
                Ingresos y Métodos de Pago
            </button>
            <button 
                type="button" 
                @click="tab = 'atenciones'; $nextTick(() => initAtencionesCharts())" 
                :class="tab === 'atenciones' ? 'border-indigo-600 text-indigo-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700'"
                class="py-3 px-1 border-b-2 text-sm font-semibold transition-colors"
            >
                Atenciones y Vacunas
            </button>
            <button 
                type="button" 
                @click="tab = 'inventario'" 
                :class="tab === 'inventario' ? 'border-indigo-600 text-indigo-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700'"
                class="py-3 px-1 border-b-2 text-sm font-semibold transition-colors"
            >
                Inventario y Lotes Críticos
            </button>
        </nav>
    </div>

    <!-- Pestaña 1: Ventas y Finanzas -->
    <div x-show="tab === 'ventas'" class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Gráfica de Tendencia de Ingresos (Chart.js) -->
            <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-slate-100 shadow-xs">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Evolución de Ingresos</h2>
                        <p class="text-xs text-slate-400">Comportamiento financiero en el rango de fechas seleccionado</p>
                    </div>
                    <a href="{{ route('reportes.exportar', ['tipo' => 'ingresos', 'desde' => $desde->toDateString(), 'hasta' => $hasta->toDateString()]) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Exportar CSV
                    </a>
                </div>

                <div class="h-64 pt-2">
                    <canvas id="reporteIngresosChart"></canvas>
                </div>
            </div>

            <!-- Desglose por Método de Pago (Dona Chart.js) -->
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-xs flex flex-col justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900 mb-1">Métodos de Pago</h2>
                    <p class="text-xs text-slate-400 mb-4">Distribución porcentual de recaudación</p>

                    <div class="h-52 flex items-center justify-center">
                        <canvas id="reporteMetodosChart"></canvas>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 mt-4 flex items-center justify-between">
                    <span class="text-xs text-slate-500">Total Recaudado:</span>
                    <span class="text-base font-bold font-mono text-slate-900">Gs. {{ number_format($totalIngresos, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Pestaña 2: Atenciones y Vacunas -->
    <div x-show="tab === 'atenciones'" class="space-y-6" style="display: none;">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Gráfico de Barras: Distribución de Atenciones por Tipo -->
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-xs">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Atenciones por Tipo</h2>
                        <p class="text-xs text-slate-400">Veterinaria, Estética/Spa, Domicilio y General</p>
                    </div>
                    <a href="{{ route('reportes.exportar', ['tipo' => 'atenciones', 'desde' => $desde->toDateString(), 'hasta' => $hasta->toDateString()]) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Exportar CSV
                    </a>
                </div>

                <div class="h-64 pt-2">
                    <canvas id="reporteAtencionesChart"></canvas>
                </div>
            </div>

            <!-- Gráfico de Barras Horizontales: Top 10 Servicios más solicitados -->
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-xs">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Top 10 Servicios Más Solicitados</h2>
                        <p class="text-xs text-slate-400">Procedimientos con mayor demanda en el período</p>
                    </div>
                    <span class="rounded-full bg-violet-50 px-2.5 py-1 text-[11px] font-semibold text-violet-700">Top 10</span>
                </div>

                <div class="h-64 pt-2">
                    <canvas id="reporteServiciosChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Inmunizaciones y Vacunas Resumen -->
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-base font-bold text-slate-900 mb-1">Control de Vacunaciones e Inmunizaciones</h2>
                    <p class="text-xs text-slate-500">Dosis aplicadas en pacientes y sincronizadas con recordatorios preventivos</p>
                </div>
                <div class="inline-flex items-center gap-3 bg-violet-50/80 px-4 py-2.5 rounded-xl border border-violet-100">
                    <span class="text-xs font-semibold text-violet-700 uppercase">Total Aplicadas:</span>
                    <span class="text-2xl font-extrabold text-violet-900 font-mono">{{ $vacunasAplicadas }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Pestaña 3: Inventario Crítico -->
    <div x-show="tab === 'inventario'" class="space-y-6" style="display: none;">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Productos Bajo Stock Mínimo -->
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-xs">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Productos con Stock Crítico</h2>
                        <p class="text-xs text-slate-400">Existencias actuales por debajo o iguales al mínimo de seguridad</p>
                    </div>
                    <a href="{{ route('reportes.exportar', ['tipo' => 'inventario', 'desde' => $desde->toDateString(), 'hasta' => $hasta->toDateString()]) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Exportar Catálogo
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-50 text-slate-500 uppercase font-semibold">
                            <tr>
                                <th class="py-2.5 px-3">Producto</th>
                                <th class="py-2.5 px-3 text-center">Stock Actual</th>
                                <th class="py-2.5 px-3 text-center">Mínimo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($productosCriticos as $prod)
                                <tr>
                                    <td class="py-2.5 px-3 font-semibold text-slate-800">{{ $prod->nombre }}</td>
                                    <td class="py-2.5 px-3 text-center font-bold text-rose-600 font-mono">{{ $prod->stock_actual ?? 0 }}</td>
                                    <td class="py-2.5 px-3 text-center text-slate-400 font-mono">{{ $prod->stock_minimo }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-4 text-center text-slate-400 italic">No hay productos en estado crítico</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Lotes por Vencer -->
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-xs">
                <h2 class="text-base font-bold text-slate-900 mb-1">Lotes Próximos a Vencer</h2>
                <p class="text-xs text-slate-400 mb-4">Vencimiento dentro de los próximos 30 días</p>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-50 text-slate-500 uppercase font-semibold">
                            <tr>
                                <th class="py-2.5 px-3">Medicamento / Insumo</th>
                                <th class="py-2.5 px-3">Nº Lote</th>
                                <th class="py-2.5 px-3 text-right">Fecha Vencimiento</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($lotesPorVencer as $lt)
                                <tr>
                                    <td class="py-2.5 px-3 font-semibold text-slate-800">{{ $lt->producto->nombre ?? 'Insumo' }}</td>
                                    <td class="py-2.5 px-3 font-mono text-slate-500">{{ $lt->numero_lote }}</td>
                                    <td class="py-2.5 px-3 text-right font-mono font-semibold text-amber-600">
                                        {{ \Carbon\Carbon::parse($lt->fecha_vencimiento)->format('d/m/Y') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-4 text-center text-slate-400 italic">No hay lotes con vencimiento próximo</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function reportesManager(charts) {
    return {
        tab: 'ventas',
        chartsData: charts || {},
        chartsInstances: {},
        initCharts() {
            this.$nextTick(() => {
                this.initVentasCharts();
                this.initAtencionesCharts();
            });
        },
        initVentasCharts() {
            const ctxIngresos = document.getElementById('reporteIngresosChart');
            if (ctxIngresos && window.Chart && !this.chartsInstances.ingresos) {
                this.chartsInstances.ingresos = new Chart(ctxIngresos, {
                    type: 'line',
                    data: {
                        labels: this.chartsData.ingresos?.labels || [],
                        datasets: [{
                            label: 'Ingresos (Gs.)',
                            data: this.chartsData.ingresos?.valores || [],
                            borderColor: '#4f46e5',
                            backgroundColor: 'rgba(79, 70, 229, 0.1)',
                            fill: true,
                            tension: 0.35,
                            pointRadius: 3,
                            pointHoverRadius: 6,
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
                            x: { grid: { display: false }, ticks: { font: { size: 10 } } }
                        }
                    }
                });
            }

            const ctxMetodos = document.getElementById('reporteMetodosChart');
            if (ctxMetodos && window.Chart && !this.chartsInstances.metodos) {
                this.chartsInstances.metodos = new Chart(ctxMetodos, {
                    type: 'doughnut',
                    data: {
                        labels: this.chartsData.metodos?.labels || [],
                        datasets: [{
                            data: this.chartsData.metodos?.valores || [],
                            backgroundColor: ['#4f46e5', '#10b981', '#f59e0b', '#06b6d4', '#ec4899', '#8b5cf6'],
                            borderWidth: 2,
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } },
                            tooltip: {
                                callbacks: {
                                    label: function(c) {
                                        return ' ' + c.label + ': Gs. ' + Number(c.raw).toLocaleString('es-PY');
                                    }
                                }
                            }
                        }
                    }
                });
            }
        },
        initAtencionesCharts() {
            const ctxAtenciones = document.getElementById('reporteAtencionesChart');
            if (ctxAtenciones && window.Chart && !this.chartsInstances.atenciones) {
                this.chartsInstances.atenciones = new Chart(ctxAtenciones, {
                    type: 'bar',
                    data: {
                        labels: this.chartsData.atenciones?.labels || [],
                        datasets: [{
                            label: 'Atenciones',
                            data: this.chartsData.atenciones?.valores || [],
                            backgroundColor: ['#0284c7', '#ec4899', '#10b981', '#64748b'],
                            borderRadius: 6,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, ticks: { precision: 0, font: { size: 10 } } },
                            x: { grid: { display: false }, ticks: { font: { size: 10 } } }
                        }
                    }
                });
            }

            const ctxServicios = document.getElementById('reporteServiciosChart');
            if (ctxServicios && window.Chart && !this.chartsInstances.servicios) {
                this.chartsInstances.servicios = new Chart(ctxServicios, {
                    type: 'bar',
                    data: {
                        labels: this.chartsData.servicios?.labels || [],
                        datasets: [{
                            label: 'Solicitudes',
                            data: this.chartsData.servicios?.valores || [],
                            backgroundColor: '#8b5cf6',
                            borderRadius: 6,
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { beginAtZero: true, ticks: { precision: 0, font: { size: 10 } } },
                            y: { grid: { display: false }, ticks: { font: { size: 10 } } }
                        }
                    }
                });
            }
        },
        setRango(tipo) {
            const hoy = new Date();
            const formatDate = (d) => d.toISOString().split('T')[0];
            const inputDesde = document.getElementById('inputDesde');
            const inputHasta = document.getElementById('inputHasta');

            if (tipo === 'hoy') {
                inputDesde.value = formatDate(hoy);
                inputHasta.value = formatDate(hoy);
            } else if (tipo === '7dias') {
                const hace7 = new Date();
                hace7.setDate(hoy.getDate() - 7);
                inputDesde.value = formatDate(hace7);
                inputHasta.value = formatDate(hoy);
            } else if (tipo === 'mes') {
                const inicioMes = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
                inputDesde.value = formatDate(inicioMes);
                inputHasta.value = formatDate(hoy);
            }
            document.getElementById('formFiltros').submit();
        }
    };
}
</script>
@endsection
