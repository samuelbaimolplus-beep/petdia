<x-layout title="Ficha de Salud: {{ $paciente->nombre }} — La Petdiatría">
    <div class="space-y-6">
        <x-breadcrumb :items="[
            ['label' => 'Inicio', 'url' => route('dashboard')],
            ['label' => 'Portal del Propietario', 'url' => route('portal.index')],
            ['label' => $paciente->nombre, 'url' => null],
        ]" />

        <!-- Encabezado de la Mascota / Tarjeta de Identidad -->
        <div class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div class="flex items-center gap-4">
                    <div class="size-20 rounded-3xl bg-linear-to-tr from-primary-100 to-teal-50 border border-primary-200 flex items-center justify-center text-primary-700 shadow-inner shrink-0">
                        @if(strtolower($paciente->raza?->especie?->nombre ?? '') === 'felino' || strtolower($paciente->raza?->especie?->nombre ?? '') === 'gato')
                            <i data-lucide="cat" class="size-10"></i>
                        @else
                            <i data-lucide="dog" class="size-10"></i>
                        @endif
                    </div>
                    <div>
                        <div class="flex items-center gap-2.5">
                            <h1 class="text-2xl font-extrabold text-slate-900">{{ $paciente->nombre }}</h1>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider {{ $paciente->sexo === 'M' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-pink-50 text-pink-700 border border-pink-200' }}">
                                {{ $paciente->sexo === 'M' ? 'Macho' : 'Hembra' }}
                            </span>
                        </div>
                        <p class="text-xs font-medium text-slate-500 mt-1">
                            {{ $paciente->raza?->nombre ?? 'Raza no especificada' }}
                            <span class="text-slate-300">·</span>
                            <span class="text-slate-400">{{ $paciente->raza?->especie?->nombre ?? 'Especie' }}</span>
                            @if($paciente->microchip)
                                <span class="text-slate-300">·</span>
                                <span class="font-mono text-slate-500">Chip: {{ $paciente->microchip }}</span>
                            @endif
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <a
                        href="{{ route('mascotas.carnet.pdf', $paciente->id_paciente) }}"
                        target="_blank"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold shadow-xs transition-all hover:scale-105 active:scale-95"
                    >
                        <i data-lucide="file-badge" class="size-4"></i>
                        <span>Descargar Carnet Vacunación PDF</span>
                    </a>
                    <a
                        href="{{ route('portal.index') }}"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold transition-colors"
                    >
                        <i data-lucide="arrow-left" class="size-4"></i>
                        <span>Volver al Portal</span>
                    </a>
                </div>
            </div>

            <!-- Resumen de Datos Clave -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-6 pt-6 border-t border-slate-100 text-xs">
                <div class="bg-slate-50 p-3 rounded-2xl">
                    <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block">Edad Calculada</span>
                    <span class="font-bold text-slate-800 text-sm">
                        @if($paciente->fecha_nacimiento)
                            {{ \Carbon\Carbon::parse($paciente->fecha_nacimiento)->diffForHumans(null, true) }}
                        @else
                            No registrada
                        @endif
                    </span>
                </div>
                <div class="bg-slate-50 p-3 rounded-2xl">
                    <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block">Pelaje / Color</span>
                    <span class="font-bold text-slate-800 text-sm">{{ $paciente->color ?: 'No especificado' }}</span>
                </div>
                <div class="bg-slate-50 p-3 rounded-2xl">
                    <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block">Último Peso</span>
                    <span class="font-bold text-slate-800 text-sm">
                        @if($pesos->isNotEmpty())
                            {{ $pesos->first()['peso'] }} kg
                        @else
                            {{ $paciente->peso_actual ? $paciente->peso_actual.' kg' : 'Sin registros' }}
                        @endif
                    </span>
                </div>
                <div class="bg-slate-50 p-3 rounded-2xl">
                    <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block">Tutor Responsable</span>
                    <span class="font-bold text-slate-800 text-sm truncate block">
                        {{ $paciente->propietario?->persona?->nombre }} {{ $paciente->propietario?->persona?->apellido }}
                    </span>
                </div>
            </div>
        </div>

        <!-- TABS O SECCIONES CLÍNICAS -->
        <div x-data="{ tab: 'vacunas' }" class="space-y-4">
            <!-- Barra de Navegación de Pestañas -->
            <div class="flex items-center gap-2 border-b border-slate-200 overflow-x-auto pb-1">
                <button
                    type="button"
                    @click="tab = 'vacunas'"
                    :class="tab === 'vacunas' ? 'border-primary-600 text-primary-700 bg-primary-50/50' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-t-xl border-b-2 text-xs font-bold transition-all shrink-0"
                >
                    <i data-lucide="shield-check" class="size-4"></i>
                    <span>Plan de Vacunación ({{ $vacunas->count() }})</span>
                </button>

                <button
                    type="button"
                    @click="tab = 'desparasitaciones'"
                    :class="tab === 'desparasitaciones' ? 'border-primary-600 text-primary-700 bg-primary-50/50' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-t-xl border-b-2 text-xs font-bold transition-all shrink-0"
                >
                    <i data-lucide="bug" class="size-4"></i>
                    <span>Desparasitaciones ({{ $desparasitaciones->count() }})</span>
                </button>

                <button
                    type="button"
                    @click="tab = 'recetas'"
                    :class="tab === 'recetas' ? 'border-primary-600 text-primary-700 bg-primary-50/50' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-t-xl border-b-2 text-xs font-bold transition-all shrink-0"
                >
                    <i data-lucide="file-text" class="size-4"></i>
                    <span>Recetas y Medicamentos ({{ $recetas->count() }})</span>
                </button>

                <button
                    type="button"
                    @click="tab = 'peso'"
                    :class="tab === 'peso' ? 'border-primary-600 text-primary-700 bg-primary-50/50' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-t-xl border-b-2 text-xs font-bold transition-all shrink-0"
                >
                    <i data-lucide="scale" class="size-4"></i>
                    <span>Control de Peso ({{ $pesos->count() }})</span>
                </button>
            </div>

            <!-- CONTENIDO TAB 1: VACUNAS -->
            <div x-show="tab === 'vacunas'" x-cloak class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm">
                @if($vacunas->isEmpty())
                    <div class="py-10 text-center">
                        <i data-lucide="shield-alert" class="size-10 mx-auto text-slate-300 mb-2"></i>
                        <p class="text-xs font-semibold text-slate-600">No hay vacunas registradas aún</p>
                        <p class="text-[11px] text-slate-400 mt-1">Los registros aparecerán aquí conforme el veterinario aplique las inmunizaciones.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="border-b border-slate-100 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                                    <th class="py-3 px-3">Fecha Aplicación</th>
                                    <th class="py-3 px-3">Biológico / Inmunización</th>
                                    <th class="py-3 px-3">Sitio Aplicación</th>
                                    <th class="py-3 px-3">Próximo Refuerzo</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-700">
                                @foreach($vacunas as $vacuna)
                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                        <td class="py-3 px-3 font-semibold text-slate-900">
                                            {{ \Carbon\Carbon::parse($vacuna['fecha'])->format('d/m/Y') }}
                                        </td>
                                        <td class="py-3 px-3 font-bold text-primary-700">
                                            {{ $vacuna['vacuna'] }}
                                        </td>
                                        <td class="py-3 px-3 text-slate-500">
                                            {{ $vacuna['sitio'] ?: 'Subcutáneo' }}
                                        </td>
                                        <td class="py-3 px-3">
                                            @if($vacuna['proxima'])
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                    <i data-lucide="calendar" class="size-3"></i>
                                                    {{ \Carbon\Carbon::parse($vacuna['proxima'])->format('d/m/Y') }}
                                                </span>
                                            @else
                                                <span class="text-slate-400 text-[11px]">No programado</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <!-- CONTENIDO TAB 2: DESPARASITACIONES -->
            <div x-show="tab === 'desparasitaciones'" x-cloak class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm">
                @if($desparasitaciones->isEmpty())
                    <div class="py-10 text-center">
                        <i data-lucide="bug-off" class="size-10 mx-auto text-slate-300 mb-2"></i>
                        <p class="text-xs font-semibold text-slate-600">No hay desparasitaciones registradas</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="border-b border-slate-100 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                                    <th class="py-3 px-3">Fecha</th>
                                    <th class="py-3 px-3">Producto / Fármaco</th>
                                    <th class="py-3 px-3">Tipo Control</th>
                                    <th class="py-3 px-3">Próxima Dosis</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-700">
                                @foreach($desparasitaciones as $desp)
                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                        <td class="py-3 px-3 font-semibold text-slate-900">
                                            {{ \Carbon\Carbon::parse($desp['fecha'])->format('d/m/Y') }}
                                        </td>
                                        <td class="py-3 px-3 font-bold text-teal-700">
                                            {{ $desp['producto'] }}
                                        </td>
                                        <td class="py-3 px-3 text-slate-500">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-slate-100 text-slate-600">
                                                {{ $desp['tipo'] }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-3">
                                            @if($desp['proxima'])
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                    {{ \Carbon\Carbon::parse($desp['proxima'])->format('d/m/Y') }}
                                                </span>
                                            @else
                                                <span class="text-slate-400 text-[11px]">No programado</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <!-- CONTENIDO TAB 3: RECETAS -->
            <div x-show="tab === 'recetas'" x-cloak class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm">
                @if($recetas->isEmpty())
                    <div class="py-10 text-center">
                        <i data-lucide="file-x" class="size-10 mx-auto text-slate-300 mb-2"></i>
                        <p class="text-xs font-semibold text-slate-600">No hay recetas médicas para esta mascota</p>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach($recetas as $receta)
                            <div class="p-4 rounded-2xl border border-slate-200/80 bg-slate-50/50 space-y-3">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold text-slate-900">
                                            Receta del {{ \Carbon\Carbon::parse($receta->fecha_hora)->format('d/m/Y — h:i A') }}
                                        </span>
                                    </div>
                                    <a
                                        href="{{ route('historial.receta.pdf', $receta->id_receta) }}"
                                        target="_blank"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold shadow-2xs transition-all"
                                    >
                                        <i data-lucide="download" class="size-3.5"></i>
                                        <span>Descargar Receta PDF</span>
                                    </a>
                                </div>

                                <div class="bg-white rounded-xl p-3 border border-slate-100">
                                    <ul class="divide-y divide-slate-100 text-xs">
                                        @foreach($receta->detalleRecetas as $det)
                                            <li class="py-2 flex items-start justify-between gap-4">
                                                <div>
                                                    <span class="font-bold text-slate-800 block">
                                                        {{ $det->medicamento?->nombre ?? 'Medicamento' }}
                                                    </span>
                                                    <span class="text-[11px] text-slate-500">
                                                        Dosis: {{ $det->dosis }} · Frecuencia: {{ $det->frecuencia }} · Duración: {{ $det->duracion_dias }} días
                                                    </span>
                                                </div>
                                                @if($det->indicaciones)
                                                    <span class="text-[11px] text-slate-400 italic text-right">
                                                        {{ $det->indicaciones }}
                                                    </span>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- CONTENIDO TAB 4: CONTROL DE PESO -->
            <div x-show="tab === 'peso'" x-cloak class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm">
                @if($pesos->isEmpty())
                    <div class="py-10 text-center">
                        <i data-lucide="scale" class="size-10 mx-auto text-slate-300 mb-2"></i>
                        <p class="text-xs font-semibold text-slate-600">No hay controles de peso registrados</p>
                    </div>
                @else
                    <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-3">
                        @foreach($pesos as $p)
                            <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100 text-center">
                                <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block">
                                    {{ \Carbon\Carbon::parse($p['fecha'])->format('d/m/Y') }}
                                </span>
                                <span class="text-lg font-extrabold text-slate-800 mt-1 block">
                                    {{ $p['peso'] }} <span class="text-xs font-normal text-slate-400">kg</span>
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layout>
