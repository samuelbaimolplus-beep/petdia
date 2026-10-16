<x-layout title="Historial Clínico Veterinario — La Petdiatría">
    <div class="space-y-6">
        <!-- Encabezado con información del módulo -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-primary-600">
                    <i data-lucide="stethoscope" class="size-4"></i>
                    <span>Área Médica · Consultorios</span>
                </div>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                    Historial Clínico Veterinario
                </h1>
                <p class="mt-1 text-sm text-slate-500 max-w-2xl">
                    Directorio médico de pacientes, expedientes de consulta, evolución clínica, tratamientos y cirugías.
                </p>
            </div>
        </div>

        <!-- Tarjetas KPI Clínicas -->
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-card">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Pacientes</span>
                    <div class="flex size-7 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                        <i data-lucide="paw-print" class="size-4"></i>
                    </div>
                </div>
                <p class="mt-2 text-2xl font-bold text-slate-900">{{ $totalPacientes }}</p>
                <p class="text-xs text-slate-500 mt-0.5">Expedientes activos</p>
            </div>

            <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-card">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Consultas Clínicas</span>
                    <div class="flex size-7 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                        <i data-lucide="activity" class="size-4"></i>
                    </div>
                </div>
                <p class="mt-2 text-2xl font-bold text-emerald-600">{{ $totalAtencionesClinicas }}</p>
                <p class="text-xs text-slate-500 mt-0.5">Atenciones veterinarias</p>
            </div>

            <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-card">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Diagnósticos</span>
                    <div class="flex size-7 items-center justify-center rounded-lg bg-violet-50 text-violet-600">
                        <i data-lucide="file-check" class="size-4"></i>
                    </div>
                </div>
                <p class="mt-2 text-2xl font-bold text-slate-900">{{ $totalDiagnosticos }}</p>
                <p class="text-xs text-slate-500 mt-0.5">En base de datos</p>
            </div>

            <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-card">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Tratamientos</span>
                    <div class="flex size-7 items-center justify-center rounded-lg bg-amber-50 text-amber-600">
                        <i data-lucide="pill" class="size-4"></i>
                    </div>
                </div>
                <p class="mt-2 text-2xl font-bold text-amber-600">{{ $totalTratamientosActivos }}</p>
                <p class="text-xs text-slate-500 mt-0.5">En curso activo</p>
            </div>
        </div>

        <!-- Buscador y Tabla de Expedientes -->
        <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-card space-y-5">
            <form action="{{ route('historial.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3 items-center justify-between">
                <div class="relative flex-1 w-full">
                    <i data-lucide="search" class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 size-4 text-slate-400"></i>
                    <input
                        type="text"
                        name="search"
                        value="{{ $filters['search'] ?? '' }}"
                        placeholder="Buscar paciente por nombre, raza, microchip o propietario..."
                        class="block w-full rounded-xl border-0 py-2 pl-10 pr-4 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-200 placeholder:text-slate-400 focus-ring"
                    />
                </div>

                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <x-button variant="secondary" type="submit">
                        <i data-lucide="filter" class="size-4 mr-1.5"></i>
                        <span>Buscar</span>
                    </x-button>

                    @if (!empty($filters['search']))
                        <a
                            href="{{ route('historial.index') }}"
                            class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-500 hover:bg-slate-50 transition-colors"
                            title="Limpiar búsqueda"
                        >
                            <i data-lucide="rotate-ccw" class="size-4"></i>
                        </a>
                    @endif
                </div>
            </form>

            <div class="overflow-hidden rounded-xl border border-slate-200/80 bg-white shadow-card">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200/80 text-left text-sm">
                        <thead class="bg-slate-50/75 text-xs font-semibold uppercase tracking-wider text-slate-500">
                            <tr>
                                <th scope="col" class="px-5 py-3.5 whitespace-nowrap">Paciente</th>
                                <th scope="col" class="px-5 py-3.5 whitespace-nowrap">Especie y Raza</th>
                                <th scope="col" class="px-5 py-3.5 whitespace-nowrap">Propietario</th>
                                <th scope="col" class="px-5 py-3.5 whitespace-nowrap">Última Atención</th>
                                <th scope="col" class="px-5 py-3.5 whitespace-nowrap text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white text-slate-700">
                            @forelse ($pacientes as $paciente)
                                @php
                                    $especieNombre = strtolower($paciente->raza?->especie?->nombre ?? 'otro');
                                    $iconName = str_contains($especieNombre, 'gat') ? 'cat' : (str_contains($especieNombre, 'perr') ? 'dog' : 'paw-print');
                                    $ultimaAtencion = $paciente->atenciones->first();
                                    $persona = $paciente->propietario?->persona;
                                @endphp
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <!-- Paciente -->
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <div class="flex size-10 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 font-bold ring-1 ring-indigo-500/20">
                                                <i data-lucide="{{ $iconName }}" class="size-5"></i>
                                            </div>
                                            <div>
                                                <a href="{{ route('historial.show', $paciente->id_paciente) }}" class="font-bold text-slate-900 hover:text-primary-600 transition-colors">
                                                    {{ $paciente->nombre }}
                                                </a>
                                                <div class="text-xs text-slate-400">
                                                    {{ $paciente->sexo }} · ID #{{ $paciente->id_paciente }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Especie / Raza -->
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <p class="font-semibold text-slate-800 text-xs">{{ $paciente->raza?->especie?->nombre ?? 'Especie S/D' }}</p>
                                        <p class="text-xs text-slate-500">{{ $paciente->raza?->nombre ?? 'Mestizo' }}</p>
                                    </td>

                                    <!-- Propietario -->
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <p class="font-semibold text-slate-900 text-xs">
                                            {{ $persona ? "{$persona->nombre} {$persona->apellido1}" : 'S/D' }}
                                        </p>
                                        <p class="text-xs text-slate-400">CI: {{ $persona?->documento ?: 'S/D' }} · Tel: {{ $persona?->telefono ?: 'S/D' }}</p>
                                    </td>

                                    <!-- Última Consulta -->
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        @if ($ultimaAtencion)
                                            <p class="font-bold text-slate-800 text-xs">
                                                {{ \Carbon\Carbon::parse($ultimaAtencion->fecha_hora_inicio)->format('d/m/Y H:i') }}
                                            </p>
                                            <p class="text-[11px] text-slate-500">
                                                {{ $ultimaAtencion->atencionVeterinaria?->motivo_consulta ?: 'Consulta médica' }}
                                            </p>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-xs text-slate-400">
                                                <i data-lucide="clock" class="size-3.5"></i>
                                                <span>Sin consultas previas</span>
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Botón Acción -->
                                    <td class="px-5 py-4 whitespace-nowrap text-right">
                                        <a
                                            href="{{ route('historial.show', $paciente->id_paciente) }}"
                                            class="inline-flex items-center gap-1.5 rounded-xl bg-primary-50 text-primary-700 hover:bg-primary-600 hover:text-white px-3 py-1.5 text-xs font-bold transition-all shadow-2xs"
                                        >
                                            <i data-lucide="folder-open" class="size-3.5"></i>
                                            <span>Abrir Expediente</span>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="p-8">
                                        <x-empty-state
                                            icon="stethoscope"
                                            title="No se encontraron expedientes clínicos"
                                            description="No hay pacientes que coincidan con los criterios de búsqueda médica."
                                        />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($pacientes->hasPages())
                    <div class="border-t border-slate-200/80 px-5 py-3.5 bg-slate-50/40">
                        {{ $pacientes->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layout>
