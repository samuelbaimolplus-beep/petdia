<x-layout title="Expediente Clínico — {{ $paciente->nombre }} — La Petdiatría">
    @php
        $especieNombre = strtolower($paciente->raza?->especie?->nombre ?? 'otro');
        $iconName = str_contains($especieNombre, 'gat') ? 'cat' : (str_contains($especieNombre, 'perr') ? 'dog' : 'paw-print');
        $edad = $paciente->fecha_nacimiento ? \Carbon\Carbon::parse($paciente->fecha_nacimiento)->age . ' años' : 'Edad S/D';
        $persona = $paciente->propietario?->persona;
        $nombrePropietario = $persona ? trim("{$persona->nombre} {$persona->apellido1} {$persona->apellido2}") : 'Propietario S/D';
    @endphp

    <div
        x-data="historialManager()"
        @keydown.escape.window="if(newEntryModalOpen) newEntryModalOpen = false; if(modalEstudioOpen) modalEstudioOpen = false; if(lightboxModalOpen) lightboxModalOpen = false;"
        class="space-y-6"
    >
        <x-breadcrumb :items="[
            ['label' => 'Historial Clínico', 'url' => route('historial.index')],
            ['label' => $paciente->nombre, 'url' => route('mascotas.show', $paciente->id_paciente)],
            ['label' => 'Expediente Clínico #' . $paciente->id_paciente, 'url' => null],
        ]" />

        <!-- Encabezado con Migas y Botón de Nueva Entrada Clínica -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <a
                    href="{{ route('historial.index') }}"
                    class="inline-flex size-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 hover:bg-slate-50 hover:text-slate-800 transition-colors shadow-2xs"
                    title="Volver al directorio de historiales"
                >
                    <i data-lucide="arrow-left" class="size-4"></i>
                </a>
                <div>
                    <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-slate-400">
                        <a href="{{ route('historial.index') }}" class="hover:underline">Historial Clínico</a>
                        <span>·</span>
                        <span>Expediente #{{ $paciente->id_paciente }}</span>
                    </div>
                    <div class="flex items-center gap-3 mt-0.5">
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                            {{ $paciente->nombre }}
                        </h1>
                        <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $paciente->sexo === 'MACHO' ? 'bg-blue-50 text-blue-700' : 'bg-pink-50 text-pink-700' }}">
                            {{ $paciente->sexo }}
                        </span>
                        <x-state-badge :state="$paciente->estado" />
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a
                    href="{{ route('mascotas.show', $paciente->id_paciente) }}"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm font-medium text-slate-700 shadow-xs hover:bg-slate-50 transition-colors"
                >
                    <i data-lucide="info" class="size-4 mr-1.5 text-slate-500"></i>
                    <span>Ficha Biológica</span>
                </a>

                @if ($canWrite)
                    <x-button variant="primary" @click="newEntryModalOpen = true">
                        <i data-lucide="file-plus" class="size-4 mr-2"></i>
                        <span>Nueva Entrada Clínica</span>
                    </x-button>
                @endif
            </div>
        </div>

        <!-- Banner de Datos Rápidos del Paciente -->
        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="flex size-14 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 ring-1 ring-indigo-500/20">
                        <i data-lucide="{{ $iconName }}" class="size-7"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-lg font-bold text-slate-900">{{ $paciente->nombre }}</h3>
                            <span class="text-xs text-slate-500">· {{ $paciente->raza?->especie?->nombre ?? 'Especie' }} ({{ $paciente->raza?->nombre ?? 'Mestizo' }})</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Edad: <strong class="text-slate-800">{{ $edad }}</strong> ·
                            Tamaño: <strong class="text-slate-800">{{ $paciente->tamanio ?: 'S/D' }}</strong> ·
                            Chip: <strong class="text-slate-800">{{ $paciente->microchip ?: 'Sin chip' }}</strong>
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-3 sm:pt-0 border-t sm:border-t-0 border-slate-100">
                    <div class="text-left sm:text-right">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Propietario</span>
                        <a href="{{ route('clientes.show', $paciente->id_propietario) }}" class="text-xs font-bold text-primary-600 hover:underline">
                            {{ $nombrePropietario }}
                        </a>
                        <span class="text-xs text-slate-400 block">Tel: {{ $persona?->telefono ?: 'S/D' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pestañas Especializadas de la Ficha Clínica -->
        <div class="rounded-2xl border border-slate-200/80 bg-white shadow-card overflow-hidden">
            <div class="border-b border-slate-200/80 bg-slate-50/50 px-6 pt-3 overflow-x-auto">
                <nav class="flex space-x-2 sm:space-x-6 min-w-max" aria-label="Secciones del expediente clínico">
                    <button
                        type="button"
                        @click="activeTab = 'timeline'"
                        class="inline-flex items-center gap-2 border-b-2 py-3 px-1 text-xs font-bold uppercase tracking-wider transition-colors"
                        :class="activeTab === 'timeline' ? 'border-primary-600 text-primary-600' : 'border-transparent text-slate-500 hover:text-slate-700'"
                    >
                        <i data-lucide="activity" class="size-4"></i>
                        <span>Línea de Tiempo</span>
                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-bold text-slate-600">
                            {{ $atenciones->count() }}
                        </span>
                    </button>

                    <button
                        type="button"
                        @click="activeTab = 'diagnosticos'"
                        class="inline-flex items-center gap-2 border-b-2 py-3 px-1 text-xs font-bold uppercase tracking-wider transition-colors"
                        :class="activeTab === 'diagnosticos' ? 'border-primary-600 text-primary-600' : 'border-transparent text-slate-500 hover:text-slate-700'"
                    >
                        <i data-lucide="file-check" class="size-4"></i>
                        <span>Diagnósticos</span>
                        <span class="rounded-full bg-indigo-50 px-2 py-0.5 text-[11px] font-bold text-indigo-700">
                            {{ $diagnosticos->count() }}
                        </span>
                    </button>

                    <button
                        type="button"
                        @click="activeTab = 'tratamientos'"
                        class="inline-flex items-center gap-2 border-b-2 py-3 px-1 text-xs font-bold uppercase tracking-wider transition-colors"
                        :class="activeTab === 'tratamientos' ? 'border-primary-600 text-primary-600' : 'border-transparent text-slate-500 hover:text-slate-700'"
                    >
                        <i data-lucide="pill" class="size-4"></i>
                        <span>Tratamientos</span>
                        <span class="rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-bold text-amber-700">
                            {{ $tratamientos->count() }}
                        </span>
                    </button>

                    <button
                        type="button"
                        @click="activeTab = 'estudios'"
                        class="inline-flex items-center gap-2 border-b-2 py-3 px-1 text-xs font-bold uppercase tracking-wider transition-colors"
                        :class="activeTab === 'estudios' ? 'border-primary-600 text-primary-600' : 'border-transparent text-slate-500 hover:text-slate-700'"
                    >
                        <i data-lucide="microscope" class="size-4"></i>
                        <span>Estudios y Lab</span>
                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-bold text-slate-600">
                            {{ $estudios->count() }}
                        </span>
                    </button>

                    <button
                        type="button"
                        @click="activeTab = 'cirugias'"
                        class="inline-flex items-center gap-2 border-b-2 py-3 px-1 text-xs font-bold uppercase tracking-wider transition-colors"
                        :class="activeTab === 'cirugias' ? 'border-primary-600 text-primary-600' : 'border-transparent text-slate-500 hover:text-slate-700'"
                    >
                        <i data-lucide="scissors" class="size-4"></i>
                        <span>Cirugías e Internación</span>
                        <span class="rounded-full bg-rose-50 px-2 py-0.5 text-[11px] font-bold text-rose-700">
                            {{ $cirugias->count() + $internaciones->count() }}
                        </span>
                    </button>

                    <button
                        type="button"
                        @click="activeTab = 'preventivos'"
                        class="inline-flex items-center gap-2 border-b-2 py-3 px-1 text-xs font-bold uppercase tracking-wider transition-colors"
                        :class="activeTab === 'preventivos' ? 'border-primary-600 text-primary-600' : 'border-transparent text-slate-500 hover:text-slate-700'"
                    >
                        <i data-lucide="shield-plus" class="size-4"></i>
                        <span>Vacunas y Desparasitación</span>
                        <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-bold text-emerald-700">
                            {{ $vacunaciones->count() + $desparasitaciones->count() }}
                        </span>
                    </button>

                    <button
                        type="button"
                        @click="activeTab = 'recetas'"
                        class="inline-flex items-center gap-2 border-b-2 py-3 px-1 text-xs font-bold uppercase tracking-wider transition-colors"
                        :class="activeTab === 'recetas' ? 'border-primary-600 text-primary-600' : 'border-transparent text-slate-500 hover:text-slate-700'"
                    >
                        <i data-lucide="file-text" class="size-4"></i>
                        <span>Recetas</span>
                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-bold text-slate-600">
                            {{ $recetas->count() }}
                        </span>
                    </button>
                </nav>
            </div>

            <!-- CONTENIDO DE LAS PESTAÑAS -->
                 <!-- 1. LÍNEA DE TIEMPO CRONOLÓGICA GENERAL (Mejora 6.1: Scroll Infinito) -->
                <div x-show="activeTab === 'timeline'" class="space-y-6">
                    <div id="timeline-items-container" class="space-y-6">
                        @include('historial._timeline_items', ['atenciones' => $atenciones])
                    </div>

                    @if($atenciones->count() >= 5)
                        <div class="mt-6 pt-4 border-t border-slate-100 text-center" x-show="hasMoreEntries">
                            <button
                                type="button"
                                @click="loadMoreTimelineEntries()"
                                :disabled="isLoadingEntries"
                                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-2xs hover:shadow transition-all disabled:opacity-50"
                            >
                                <span x-show="!isLoadingEntries">Cargar más entradas anteriores</span>
                                <span x-show="isLoadingEntries" class="flex items-center gap-2 text-indigo-600">
                                    <svg class="animate-spin h-3.5 w-3.5 text-indigo-600" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Cargando entradas anteriores...
                                </span>
                            </button>
                        </div>
                    @endif
                </div>

                <!-- 2. PESTAÑA: DIAGNÓSTICOS -->
                <div x-show="activeTab === 'diagnosticos'" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @forelse ($diagnosticos as $diag)
                            <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-xs space-y-2">
                                <div class="flex items-center justify-between">
                                    <h4 class="font-bold text-slate-900 text-sm">{{ $diag->nombre }}</h4>
                                    <span class="rounded-full px-2 py-0.5 text-[11px] font-bold {{ $diag->tipo === 'DEFINITIVO' ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20' : 'bg-amber-50 text-amber-700 ring-1 ring-amber-600/20' }}">
                                        {{ $diag->tipo }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-600">{{ $diag->descripcion ?: 'Sin descripción adicional registrada.' }}</p>
                            </div>
                        @empty
                            <div class="col-span-full py-8 text-center text-xs text-slate-400">
                                No hay diagnósticos clínicos registrados para este paciente.
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- 3. PESTAÑA: TRATAMIENTOS -->
                <div x-show="activeTab === 'tratamientos'" class="space-y-4">
                    <div class="space-y-3">
                        @forelse ($tratamientos as $trat)
                            <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-xs flex items-start justify-between gap-4">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <h4 class="font-bold text-slate-900 text-sm">{{ $trat->descripcion }}</h4>
                                        <span class="rounded px-2 py-0.5 text-[10px] font-bold {{ $trat->estado === 'ACTIVO' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-600' }}">
                                            {{ $trat->estado }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-600">{{ $trat->indicaciones ?: 'Sin indicaciones especiales.' }}</p>
                                    <p class="text-[11px] text-slate-400">Inicio: {{ $trat->fecha_inicio }} · Fin: {{ $trat->fecha_fin ?: 'En curso' }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="py-8 text-center text-xs text-slate-400">
                                Sin tratamientos terapéuticos registrados.
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- 4. PESTAÑA: ESTUDIOS Y PROCEDIMIENTOS -->
                <div x-show="activeTab === 'estudios'" class="space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 p-4 rounded-2xl bg-white border border-slate-200/80 shadow-2xs">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                <i data-lucide="microscope" class="size-4 text-primary-600"></i>
                                <span>Estudios Auxiliares e Imágenes Diagnósticas</span>
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Informes de hemogramas, radiografías, ecografías y pruebas de laboratorio con soporte para PDF e imágenes.
                            </p>
                        </div>
                        @if ($canWrite)
                            <button
                                type="button"
                                @click="modalEstudioOpen = true"
                                class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-xs font-semibold shadow-xs transition-all hover:scale-[1.01] active:scale-[0.99]"
                            >
                                <i data-lucide="upload-cloud" class="size-4"></i>
                                <span>Adjuntar Estudio</span>
                            </button>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @forelse ($estudios as $proc)
                            @php
                                $lab = $proc->estudioLaboratorio;
                                $hasFile = !empty($lab?->archivo_adjunto);
                                $isImg = $lab?->esImagen();
                                $fileUrl = $lab?->url;
                            @endphp
                            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs space-y-4 hover:shadow-md transition-shadow">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="flex items-center gap-2.5">
                                        <div class="p-2 rounded-xl {{ $isImg ? 'bg-indigo-50 text-indigo-600' : 'bg-rose-50 text-rose-600' }}">
                                            @if ($isImg)
                                                <i data-lucide="image" class="size-5"></i>
                                            @elseif ($hasFile)
                                                <i data-lucide="file-text" class="size-5"></i>
                                            @else
                                                <i data-lucide="microscope" class="size-5"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-slate-900 text-sm">{{ $lab?->tipo_estudio ?: 'Estudio Clínico' }}</h4>
                                            <p class="text-[11px] text-slate-400">
                                                {{ $lab?->fecha_resultado ? \Carbon\Carbon::parse($lab->fecha_resultado)->translatedFormat('d M Y, H:i') : ($proc->fecha_hora ?: 'Fecha no registrada') }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        @if ($hasFile)
                                            <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-bold {{ $isImg ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-600/20' : 'bg-rose-50 text-rose-700 ring-1 ring-rose-600/20' }}">
                                                {{ $isImg ? 'IMAGEN' : 'DOCUMENTO' }}
                                            </span>
                                        @endif
                                        @if ($canWrite)
                                            <form
                                                action="{{ route('historial.estudios.destroy', ['id' => $paciente->id_paciente, 'idProcedimiento' => $proc->id_procedimiento]) }}"
                                                method="POST"
                                                onsubmit="return confirm('¿Seguro que deseas eliminar este estudio y su archivo adjunto? Esta acción no se puede deshacer.');"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    type="submit"
                                                    class="text-slate-400 hover:text-rose-600 p-1 rounded-lg hover:bg-rose-50 transition-colors"
                                                    title="Eliminar estudio"
                                                >
                                                    <i data-lucide="trash-2" class="size-3.5"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>

                                {{-- Archivo Adjunto (Preview o Link) --}}
                                @if ($hasFile)
                                    @if ($isImg)
                                        <div class="relative group overflow-hidden rounded-xl border border-slate-200 bg-slate-50 cursor-pointer" @click="openLightbox('{{ $fileUrl }}', '{{ $lab->tipo_estudio }}')">
                                            <img
                                                src="{{ $fileUrl }}"
                                                alt="{{ $lab->tipo_estudio }}"
                                                class="w-full h-44 object-cover group-hover:scale-105 transition-transform duration-300"
                                                loading="lazy"
                                            />
                                            <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2 text-white text-xs font-semibold">
                                                <i data-lucide="maximize-2" class="size-4"></i>
                                                <span>Ver imagen completa</span>
                                            </div>
                                        </div>
                                    @else
                                        <div class="flex items-center justify-between p-3 rounded-xl border border-slate-200/90 bg-slate-50/70 hover:bg-slate-100/70 transition-colors">
                                            <div class="flex items-center gap-3 overflow-hidden">
                                                <div class="size-9 rounded-lg bg-rose-100 text-rose-700 flex items-center justify-center shrink-0">
                                                    <i data-lucide="file-text" class="size-5"></i>
                                                </div>
                                                <div class="truncate">
                                                    <p class="text-xs font-semibold text-slate-800 truncate" title="{{ $lab->nombre_archivo_original ?: 'Informe_Estudio.pdf' }}">
                                                        {{ $lab->nombre_archivo_original ?: 'Informe_Estudio.pdf' }}
                                                    </p>
                                                    <p class="text-[10px] text-slate-500">Documento clínico adjunto</p>
                                                </div>
                                            </div>
                                            <a
                                                href="{{ $fileUrl }}"
                                                target="_blank"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-slate-700 hover:text-primary-700 hover:border-primary-200 text-xs font-medium shadow-2xs shrink-0 transition-colors"
                                            >
                                                <i data-lucide="external-link" class="size-3.5"></i>
                                                <span>Abrir</span>
                                            </a>
                                        </div>
                                    @endif
                                @endif

                                {{-- Resultados e Interpretación --}}
                                <div class="space-y-2 text-xs">
                                    <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                                        <span class="font-bold text-slate-700">Resultado:</span>
                                        <span class="text-slate-800 ml-1 font-medium">{{ $lab?->resultado ?: 'Registrado sin detalles cuantitativos.' }}</span>
                                    </div>
                                    @if ($lab?->interpretacion)
                                        <div class="p-2.5 rounded-xl bg-indigo-50/40 border border-indigo-100/60">
                                            <span class="font-bold text-indigo-900 block mb-0.5">Interpretación clínica:</span>
                                            <p class="text-indigo-950 text-xs italic">{{ $lab->interpretacion }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full py-12 text-center rounded-2xl border border-dashed border-slate-200 bg-white p-8">
                                <div class="size-12 rounded-2xl bg-primary-50 text-primary-600 flex items-center justify-center mx-auto mb-3">
                                    <i data-lucide="microscope" class="size-6"></i>
                                </div>
                                <h4 class="text-sm font-bold text-slate-900">Sin estudios de laboratorio registrados</h4>
                                <p class="text-xs text-slate-500 max-w-sm mx-auto mt-1">
                                    No se han adjuntado análisis de sangre, orina, coprológicos ni placas radiográficas o ecográficas para esta mascota.
                                </p>
                                @if ($canWrite)
                                    <button
                                        type="button"
                                        @click="modalEstudioOpen = true"
                                        class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-xs font-semibold shadow-xs transition-colors"
                                    >
                                        <i data-lucide="plus" class="size-4"></i>
                                        <span>Subir primer estudio</span>
                                    </button>
                                @endif
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- 5. PESTAÑA: CIRUGÍAS E INTERNACIONES -->
                <div x-show="activeTab === 'cirugias'" class="space-y-4">
                    <div class="space-y-3">
                        @forelse ($cirugias as $proc)
                            @php $cir = $proc->cirugia; @endphp
                            <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-xs space-y-2">
                                <div class="flex items-center justify-between">
                                    <h4 class="font-bold text-slate-900 text-sm">{{ $cir?->tipo_cirugia }}</h4>
                                    <span class="text-xs text-slate-400">{{ $proc->fecha_hora }}</span>
                                </div>
                                <p class="text-xs text-slate-700">Preoperatorio: {{ $cir?->diagnostico_preoperatorio ?: 'S/D' }}</p>
                                <p class="text-xs text-slate-500">Postoperatorio: {{ $cir?->indicaciones_postoperatorias ?: 'S/D' }}</p>
                            </div>
                        @empty
                            <div class="py-6 text-center text-xs text-slate-400">
                                Sin registros quirúrgicos.
                            </div>
                        @endforelse

                        @foreach ($internaciones as $inter)
                            <div class="rounded-2xl border border-indigo-100 bg-indigo-50/30 p-4 shadow-xs space-y-1">
                                <div class="flex items-center justify-between">
                                    <h4 class="font-bold text-slate-900 text-sm">Internación Hospitalaria</h4>
                                    <span class="rounded bg-indigo-100 px-2 py-0.5 text-[10px] font-bold text-indigo-700">{{ $inter->estado }}</span>
                                </div>
                                <p class="text-xs text-slate-600">Motivo: {{ $inter->motivo }}</p>
                                <p class="text-[11px] text-slate-400">Ingreso: {{ $inter->fecha_hora_ingreso }} · Alta: {{ $inter->fecha_hora_alta ?: 'En internación' }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- 6. PESTAÑA: VACUNAS Y DESPARASITACIONES -->
                <div x-show="activeTab === 'preventivos'" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3 flex items-center gap-1.5">
                                <i data-lucide="shield-check" class="size-4 text-emerald-600"></i>
                                <span>Vacunas Aplicadas</span>
                            </h4>
                            <div class="space-y-2">
                                @forelse ($vacunaciones as $proc)
                                    <div class="rounded-xl border border-slate-100 bg-slate-50/60 p-3 text-xs">
                                        <p class="font-bold text-slate-800">{{ $proc->descripcion ?: 'Vacunación' }}</p>
                                        <p class="text-slate-500">Fecha: {{ $proc->fecha_hora }} · Sitio: {{ $proc->vacunacion?->sitio_aplicacion ?: 'Subcutánea' }}</p>
                                        @if ($proc->vacunacion?->proxima_dosis)
                                            <p class="text-primary-700 font-semibold mt-1">Próxima dosis: {{ $proc->vacunacion->proxima_dosis }}</p>
                                        @endif
                                    </div>
                                @empty
                                    <p class="text-xs text-slate-400 italic">Sin vacunas registradas en el historial.</p>
                                @endforelse
                            </div>
                        </div>

                        <div>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3 flex items-center gap-1.5">
                                <i data-lucide="bug" class="size-4 text-amber-600"></i>
                                <span>Desparasitaciones</span>
                            </h4>
                            <div class="space-y-2">
                                @forelse ($desparasitaciones as $proc)
                                    <div class="rounded-xl border border-slate-100 bg-slate-50/60 p-3 text-xs">
                                        <p class="font-bold text-slate-800">{{ $proc->descripcion ?: 'Desparasitación' }}</p>
                                        <p class="text-slate-500">Tipo: {{ $proc->desparasitacion?->tipo ?: 'Interna y externa' }}</p>
                                        @if ($proc->desparasitacion?->proxima_dosis)
                                            <p class="text-amber-700 font-semibold mt-1">Próxima fecha: {{ $proc->desparasitacion->proxima_dosis }}</p>
                                        @endif
                                    </div>
                                @empty
                                    <p class="text-xs text-slate-400 italic">Sin desparasitaciones registradas.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 7. PESTAÑA: RECETAS MÉDICAS -->
                <div x-show="activeTab === 'recetas'" class="space-y-4">
                    <div class="space-y-3">
                        @forelse ($recetas as $receta)
                            <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-xs space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-slate-900">Receta Médica #{{ $receta->id_receta }}</span>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-slate-400">{{ $receta->fecha_hora }}</span>
                                        @if(Auth::user()->hasRole(['SUPER_ADMIN', 'ADMIN', 'VETERINARIO']))
                                            <a
                                                href="{{ route('historial.receta.pdf', $receta->id_receta) }}"
                                                target="_blank"
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-lg bg-primary-50 text-primary-700 hover:bg-primary-100 transition-colors"
                                                title="Descargar o imprimir receta"
                                            >
                                                <i data-lucide="printer" class="size-3.5"></i>
                                                <span>Imprimir Receta</span>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                                <p class="text-xs text-slate-700 font-serif leading-relaxed">
                                    {{ $receta->indicaciones_generales ?: 'Sin indicaciones detalladas.' }}
                                </p>
                            </div>
                        @empty
                            <div class="py-8 text-center text-xs text-slate-400">
                                No hay recetas emitidas para este paciente.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL: NUEVA ENTRADA CLÍNICA (Solo Veterinario) -->
        @if ($canWrite)
            <div
                x-show="newEntryModalOpen"
                x-cloak
                class="fixed inset-0 z-50 overflow-y-auto"
                x-transition.opacity
            >
                <div class="flex min-h-full items-center justify-center p-4">
                    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" @click="newEntryModalOpen = false"></div>

                    <div
                        class="relative w-full max-w-3xl rounded-2xl bg-white shadow-pop overflow-hidden"
                        x-show="newEntryModalOpen"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    >
                        <!-- Cabecera -->
                        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4 bg-slate-50/50">
                            <div class="flex items-center gap-3">
                                <div class="flex size-9 items-center justify-center rounded-xl bg-primary-50 text-primary-600">
                                    <i data-lucide="stethoscope" class="size-5"></i>
                                </div>
                                <div>
                                    <h3 class="text-base font-bold text-slate-900">Nueva Entrada Clínica</h3>
                                    <p class="text-xs text-slate-500">Expediente de {{ $paciente->nombre }} · Dr(a). {{ Auth::user()->nombre_completo }}</p>
                                </div>
                            </div>

                            <button type="button" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600" @click="newEntryModalOpen = false">
                                <i data-lucide="x" class="size-5"></i>
                            </button>
                        </div>

                        <!-- Formulario de Consulta -->
                        <form action="{{ route('historial.entradas.store', $paciente->id_paciente) }}" method="POST" @submit="clearDraftOnSubmit()">
                            @csrf

                            <!-- Banner de Recuperación de Autoguardado (Mejora 5.2) -->
                            <div x-show="draftRecoverBanner" x-cloak class="p-3.5 bg-amber-50 border-b border-amber-200 flex items-center justify-between gap-3 text-xs">
                                <div class="flex items-center gap-2 text-amber-800">
                                    <i data-lucide="save" class="size-4 text-amber-600 shrink-0"></i>
                                    <span>Hay un borrador guardado automáticamente de una consulta previa. ¿Deseas recuperarlo?</span>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <button type="button" @click="recoverDraft()" class="px-2.5 py-1 bg-amber-600 hover:bg-amber-700 text-white font-semibold rounded-lg shadow-2xs transition-colors">
                                        Sí, recuperar
                                    </button>
                                    <button type="button" @click="discardDraft()" class="px-2.5 py-1 bg-white hover:bg-amber-100 text-amber-800 border border-amber-300 font-semibold rounded-lg transition-colors">
                                        Descartar
                                    </button>
                                </div>
                            </div>

                            <div class="max-h-[75vh] overflow-y-auto p-6 space-y-6">
                                <!-- Datos de la Consulta -->
                                <div>
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3 flex items-center gap-1.5">
                                        <i data-lucide="clipboard" class="size-3.5"></i>
                                        <span>Datos de la Consulta</span>
                                    </h4>
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                        <div class="sm:col-span-2">
                                            <x-field label="Motivo de la Consulta" :error="$errors->first('motivo_consulta')" required>
                                                <x-input
                                                    type="text"
                                                    name="motivo_consulta"
                                                    x-model="entryFormData.motivo_consulta"
                                                    placeholder="Ej. Control de rutina, tos persistente, decaimiento"
                                                    required
                                                    :invalid="$errors->has('motivo_consulta')"
                                                />
                                            </x-field>
                                        </div>

                                        <x-field label="Tipo de Atención" :error="$errors->first('tipo')" required>
                                            <x-select name="tipo" x-model="entryFormData.tipo" required>
                                                <option value="CONSULTA">CONSULTA GENERAL</option>
                                                <option value="EMERGENCIA">EMERGENCIA</option>
                                                <option value="CONTROL">CONTROL MÉDICO</option>
                                                <option value="CIRUGIA">PROCEDIMIENTO / CIRUGÍA</option>
                                                <option value="INTERNACION">INTERNACIÓN</option>
                                            </x-select>
                                        </x-field>
                                    </div>
                                </div>

                                <!-- Triaje y Signos Vitales -->
                                <div>
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3 flex items-center gap-1.5">
                                        <i data-lucide="heart-pulse" class="size-3.5"></i>
                                        <span>Triaje y Signos Vitales</span>
                                    </h4>
                                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                        <x-field label="Peso (kg)">
                                            <x-input type="number" step="0.01" name="peso" x-model="entryFormData.peso" placeholder="Ej. 12.5" />
                                        </x-field>

                                        <x-field label="Temperatura (°C)">
                                            <x-input type="number" step="0.1" name="temperatura" x-model="entryFormData.temperatura" placeholder="Ej. 38.5" />
                                        </x-field>

                                        <x-field label="Frec. Cardíaca (lpm)">
                                            <x-input type="number" name="frecuencia_cardiaca" x-model="entryFormData.frecuencia_cardiaca" placeholder="Ej. 110" />
                                        </x-field>

                                        <x-field label="Frec. Respiratoria (rpm)">
                                            <x-input type="number" name="frecuencia_respiratoria" x-model="entryFormData.frecuencia_respiratoria" placeholder="Ej. 24" />
                                        </x-field>
                                    </div>
                                </div>

                                <!-- Anamnesis y Exploración Física -->
                                <div>
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3 flex items-center gap-1.5">
                                        <i data-lucide="stethoscope" class="size-3.5"></i>
                                        <span>Evolución y Hallazgos Clínicos</span>
                                    </h4>
                                    <div class="space-y-4">
                                        <x-field label="Anamnesis / Historia Actual" :error="$errors->first('anamnesis')" required>
                                            <textarea
                                                name="anamnesis"
                                                x-model="entryFormData.anamnesis"
                                                rows="2"
                                                class="block w-full rounded-xl border-0 py-2 px-3 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-200 placeholder:text-slate-400 focus-ring"
                                                placeholder="Describa el motivo, evolución y tiempo de los síntomas..."
                                                required
                                            ></textarea>
                                        </x-field>

                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                            <x-field label="Síntomas Específicos">
                                                <x-input type="text" name="sintomas" x-model="entryFormData.sintomas" placeholder="Ej. Vómitos matutinos, letargia" />
                                            </x-field>

                                            <x-field label="Examen Físico / Auscultación">
                                                <x-input type="text" name="examen_fisico" x-model="entryFormData.examen_fisico" placeholder="Ej. Mucosas rosadas, abdomen blando" />
                                            </x-field>
                                        </div>

                                        <x-field label="Conclusión / Resumen Clínico">
                                            <x-input type="text" name="conclusion" x-model="entryFormData.conclusion" placeholder="Ej. Cuadro de gastroenteritis leve autolimitada" />
                                        </x-field>
                                    </div>
                                </div>

                                <!-- Diagnóstico y Tratamiento Inicial -->
                                <div>
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3 flex items-center gap-1.5">
                                        <i data-lucide="pill" class="size-3.5"></i>
                                        <span>Diagnóstico y Tratamiento</span>
                                    </h4>
                                    <div class="space-y-4">
                                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                            <div class="sm:col-span-2">
                                                <x-field label="Diagnóstico">
                                                    <x-input type="text" name="diagnostico_nombre" x-model="entryFormData.diagnostico_nombre" placeholder="Ej. Gastroenteritis aguda" />
                                                </x-field>
                                            </div>

                                            <x-field label="Tipo de Diagnóstico">
                                                <x-select name="diagnostico_tipo" x-model="entryFormData.diagnostico_tipo">
                                                    <option value="PRESUNTIVO">PRESUNTIVO</option>
                                                    <option value="DEFINITIVO">DEFINITIVO</option>
                                                </x-select>
                                            </x-field>
                                        </div>

                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                            <x-field label="Tratamiento / Terapia">
                                                <x-input type="text" name="tratamiento_descripcion" x-model="entryFormData.tratamiento_descripcion" placeholder="Ej. Protector gástrico + Dieta blanda" />
                                            </x-field>

                                            <x-field label="Indicaciones y Posología">
                                                <x-input type="text" name="tratamiento_indicaciones" x-model="entryFormData.tratamiento_indicaciones" placeholder="Ej. 1 comprimido c/12h por 5 días" />
                                            </x-field>
                                        </div>

                                        <x-field label="Fecha de Reconsulta">
                                            <x-input type="date" name="fecha_reconsulta" x-model="entryFormData.fecha_reconsulta" />
                                        </x-field>
                                    </div>
                                </div>
                            </div>

                            <!-- Pie del Modal -->
                            <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-4 bg-slate-50/50">
                                <x-button variant="secondary" type="button" @click="newEntryModalOpen = false">
                                    Cancelar
                                </x-button>

                                <x-button variant="primary" type="submit">
                                    <span>Guardar Entrada Clínica</span>
                                </x-button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        {{-- MODAL PARA SUBIR ESTUDIO O IMAGEN MÉDICA --}}
        @if ($canWrite)
            <div
                x-show="modalEstudioOpen"
                x-cloak
                class="fixed inset-0 z-50 overflow-y-auto"
                style="display: none;"
            >
                <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="modalEstudioOpen = false"></div>
                <div class="flex min-h-full items-center justify-center p-4">
                    <div
                        class="relative w-full max-w-lg rounded-3xl bg-white p-6 shadow-2xl border border-slate-100 transition-all sm:p-7"
                        @click.stop
                    >
                        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                            <div>
                                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                                    <i data-lucide="upload-cloud" class="size-5 text-primary-600"></i>
                                    <span>Adjuntar Estudio o Imagen Médica</span>
                                </h3>
                                <p class="text-xs text-slate-500 mt-0.5">Expediente de {{ $paciente->nombre }}</p>
                            </div>
                            <button
                                type="button"
                                @click="modalEstudioOpen = false"
                                class="rounded-xl p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition-colors"
                            >
                                <i data-lucide="x" class="size-5"></i>
                            </button>
                        </div>

                        <form
                            action="{{ route('historial.estudios.store', $paciente->id_paciente) }}"
                            method="POST"
                            enctype="multipart/form-data"
                            class="space-y-4 mt-5"
                        >
                            @csrf
                            
                            {{-- Tipo de estudio --}}
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Tipo de Examen o Estudio <span class="text-rose-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    name="tipo_estudio"
                                    list="tipos_estudio_list"
                                    required
                                    placeholder="Ej: Hemograma Completo, Radiografía de Tórax..."
                                    class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 focus:border-primary-500 focus:outline-hidden focus:ring-2 focus:ring-primary-500/20"
                                />
                                <datalist id="tipos_estudio_list">
                                    <option value="Hemograma Completo">
                                    <option value="Perfil Bioquímico Sanguíneo">
                                    <option value="Radiografía (Rayos X)">
                                    <option value="Ecografía / Ultrasonido Abdominal">
                                    <option value="Coproparasitológico">
                                    <option value="Urianálisis Completo">
                                    <option value="Citología / Frotis">
                                    <option value="Biopsia / Histopatología">
                                    <option value="Electrocardiograma">
                                    <option value="Test Rápido Infeccioso (Ehrlichia, Parvovirus, FelV/FIV)">
                                </datalist>
                            </div>

                            {{-- Fecha del resultado --}}
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Fecha del Examen
                                </label>
                                <input
                                    type="date"
                                    name="fecha_resultado"
                                    value="{{ date('Y-m-d') }}"
                                    class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 focus:border-primary-500 focus:outline-hidden focus:ring-2 focus:ring-primary-500/20"
                                />
                            </div>

                            {{-- Selector de Archivo con Drag & Drop --}}
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Archivo del Estudio (PDF o Imagen) <span class="text-rose-500">*</span>
                                </label>
                                <div
                                    class="relative border-2 border-dashed border-slate-300 hover:border-primary-500 rounded-2xl p-4 text-center cursor-pointer transition-colors bg-slate-50/50 hover:bg-primary-50/20"
                                    @click="$refs.estudioFileInput.click()"
                                >
                                    <input
                                        type="file"
                                        x-ref="estudioFileInput"
                                        name="archivo"
                                        required
                                        accept=".pdf,.jpg,.jpeg,.png,.webp"
                                        class="hidden"
                                        @change="handleFileSelect($event)"
                                    />
                                    
                                    <div x-show="!selectedFileName" class="space-y-1">
                                        <div class="size-10 rounded-xl bg-primary-100 text-primary-600 flex items-center justify-center mx-auto mb-2">
                                            <i data-lucide="file-up" class="size-5"></i>
                                        </div>
                                        <p class="text-xs font-semibold text-slate-700">
                                            Haz clic para seleccionar o arrastra el archivo aquí
                                        </p>
                                        <p class="text-[11px] text-slate-400">
                                            PDF, JPG, PNG o WEBP (Máximo 10 MB)
                                        </p>
                                    </div>

                                    <div x-show="selectedFileName" class="space-y-2" style="display: none;">
                                        <template x-if="previewImageUrl">
                                            <img :src="previewImageUrl" class="h-28 mx-auto object-contain rounded-lg border border-slate-200 shadow-2xs" />
                                        </template>
                                        <div class="flex items-center justify-center gap-2 text-xs font-semibold text-primary-700">
                                            <i data-lucide="check-circle" class="size-4 text-emerald-500"></i>
                                            <span x-text="selectedFileName" class="truncate max-w-xs"></span>
                                            <span class="text-slate-400 font-normal text-[11px]" x-text="'(' + selectedFileSize + ')'"></span>
                                        </div>
                                        <p class="text-[11px] text-slate-400">Haz clic para cambiar el archivo</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Resumen de resultado --}}
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Resultado / Hallazgos Principales
                                </label>
                                <input
                                    type="text"
                                    name="resultado"
                                    placeholder="Ej: Dentro de parámetros normales, Leucocitosis leve, Fractura de fémur..."
                                    class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 focus:border-primary-500 focus:outline-hidden focus:ring-2 focus:ring-primary-500/20"
                                />
                            </div>

                            {{-- Interpretación / Observaciones --}}
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Interpretación Médica / Indicaciones
                                </label>
                                <textarea
                                    name="interpretacion"
                                    rows="3"
                                    placeholder="Comentarios clínicos veterinarios, correlación con la patología o recomendaciones de seguimiento..."
                                    class="w-full rounded-xl border border-slate-200 p-3 text-xs text-slate-800 focus:border-primary-500 focus:outline-hidden focus:ring-2 focus:ring-primary-500/20"
                                ></textarea>
                            </div>

                            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                                <button
                                    type="button"
                                    @click="modalEstudioOpen = false"
                                    class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition-colors"
                                >
                                    Cancelar
                                </button>
                                <button
                                    type="submit"
                                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-xs font-semibold shadow-xs transition-colors"
                                >
                                    <i data-lucide="check" class="size-4"></i>
                                    <span>Guardar y Adjuntar</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        {{-- LIGHTBOX PARA IMÁGENES COMPLETAS --}}
        <div
            x-show="lightboxModalOpen"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/85 backdrop-blur-md"
            style="display: none;"
            @click="lightboxModalOpen = false"
        >
            <div class="relative max-w-4xl max-h-[90vh] flex flex-col items-center" @click.stop>
                <div class="w-full flex items-center justify-between pb-3 text-white">
                    <span class="text-sm font-semibold truncate" x-text="lightboxImageTitle"></span>
                    <div class="flex items-center gap-3">
                        <a
                            :href="lightboxImageUrl"
                            target="_blank"
                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-white/20 hover:bg-white/30 text-white text-xs font-medium transition-colors"
                        >
                            <i data-lucide="external-link" class="size-3.5"></i>
                            <span>Abrir original</span>
                        </a>
                        <button
                            type="button"
                            @click="lightboxModalOpen = false"
                            class="rounded-lg p-1.5 text-white/70 hover:text-white hover:bg-white/10 transition-colors"
                        >
                            <i data-lucide="x" class="size-5"></i>
                        </button>
                    </div>
                </div>
                <img
                    :src="lightboxImageUrl"
                    :alt="lightboxImageTitle"
                    class="max-w-full max-h-[80vh] rounded-2xl shadow-2xl object-contain border border-white/10"
                />
            </div>
        </div>

    </div>

    <script>
    function historialManager() {
        return {
            newEntryModalOpen: {{ $errors->has('motivo_consulta') || $errors->has('anamnesis') ? 'true' : 'false' }},
            modalEstudioOpen: {{ $errors->has('archivo') || $errors->has('tipo_estudio') ? 'true' : 'false' }},
            lightboxModalOpen: false,
            lightboxImageUrl: '',
            lightboxImageTitle: '',
            selectedFileName: '',
            selectedFileSize: '',
            previewImageUrl: null,
            activeTab: @js($activeTab ?? 'timeline'),
            timelinePage: 1,
            hasMoreEntries: true,
            isLoadingEntries: false,
            draftKey: 'draft_historial_{{ $paciente->id_paciente }}',
            hasDraft: false,
            draftRecoverBanner: false,
            autoSaveTimer: null,
            handleFileSelect(event) {
                const file = event.target.files[0];
                if (!file) return;
                this.selectedFileName = file.name;
                const sizeKb = file.size / 1024;
                this.selectedFileSize = sizeKb > 1024 ? (sizeKb / 1024).toFixed(1) + ' MB' : Math.round(sizeKb) + ' KB';
                if (file.type.startsWith('image/')) {
                    this.previewImageUrl = URL.createObjectURL(file);
                } else {
                    this.previewImageUrl = null;
                }
            },
            openLightbox(url, title) {
                this.lightboxImageUrl = url;
                this.lightboxImageTitle = title || 'Imagen de Estudio';
                this.lightboxModalOpen = true;
                this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
            },
            entryFormData: {
                tipo: 'CONSULTA',
                motivo_consulta: '',
                anamnesis: '',
                sintomas: '',
                examen_fisico: '',
                conclusion: '',
                fecha_reconsulta: '',
                observaciones_clinicas: '',
                peso: '',
                temperatura: '',
                frecuencia_cardiaca: '',
                frecuencia_respiratoria: '',
                diagnostico_nombre: '',
                diagnostico_tipo: 'PRESUNTIVO',
                diagnostico_descripcion: '',
                tratamiento_descripcion: '',
                tratamiento_indicaciones: '',
            },
            init() {
                this.checkDraft();
                // Autoguardado cada 30 segundos
                this.autoSaveTimer = setInterval(() => {
                    this.saveDraft();
                }, 30000);
            },
            checkDraft() {
                try {
                    const saved = sessionStorage.getItem(this.draftKey);
                    if (saved) {
                        const parsed = JSON.parse(saved);
                        if (parsed && (parsed.motivo_consulta || parsed.anamnesis || parsed.diagnostico_nombre)) {
                            this.hasDraft = true;
                            this.draftRecoverBanner = true;
                        }
                    }
                } catch (e) {}
            },
            saveDraft() {
                if (this.entryFormData.motivo_consulta || this.entryFormData.anamnesis || this.entryFormData.diagnostico_nombre) {
                    try {
                        sessionStorage.setItem(this.draftKey, JSON.stringify(this.entryFormData));
                    } catch (e) {}
                }
            },
            recoverDraft() {
                try {
                    const saved = sessionStorage.getItem(this.draftKey);
                    if (saved) {
                        this.entryFormData = Object.assign({}, this.entryFormData, JSON.parse(saved));
                        this.draftRecoverBanner = false;
                    }
                } catch (e) {}
            },
            discardDraft() {
                try {
                    sessionStorage.removeItem(this.draftKey);
                    this.hasDraft = false;
                    this.draftRecoverBanner = false;
                } catch (e) {}
            },
            clearDraftOnSubmit() {
                try {
                    sessionStorage.removeItem(this.draftKey);
                } catch (e) {}
            },
            async loadMoreTimelineEntries() {
                if (this.isLoadingEntries || !this.hasMoreEntries) return;
                this.isLoadingEntries = true;
                const nextPage = this.timelinePage + 1;
                try {
                    const res = await fetch('/historial-clinico/{{ $paciente->id_paciente }}/entradas?page=' + nextPage, {
                        headers: { 'Accept': 'application/json' }
                    });
                    if (res.ok) {
                        const data = await res.json();
                        const container = document.getElementById('timeline-items-container');
                        if (container && data.html) {
                            container.insertAdjacentHTML('beforeend', data.html);
                            if (window.lucide) window.lucide.createIcons();
                        }
                        this.timelinePage = nextPage;
                        this.hasMoreEntries = data.has_more;
                    } else {
                        this.hasMoreEntries = false;
                    }
                } catch (e) {
                    console.error(e);
                } finally {
                    this.isLoadingEntries = false;
                }
            }
        };
    }
    </script>
</x-layout>
