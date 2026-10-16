@forelse ($atenciones as $atencion)
    @php
        $vet = $atencion->atencionVeterinaria;
        $signos = $vet?->registrosSignosVitales->first();
        $medico = $atencion->profesional;
        $badgeTypeColor = match($vet?->tipo) {
            'EMERGENCIA' => 'rose',
            'CIRUGIA' => 'violet',
            'CONTROL' => 'emerald',
            default => 'primary',
        };
    @endphp
    <div class="relative pl-6 sm:pl-8 pb-6 border-l-2 border-slate-200 last:border-transparent last:pb-0">
        <!-- Icono indicador de nodo en la línea de tiempo -->
        <div class="absolute -left-[9px] top-0 size-4 rounded-full bg-primary-600 ring-4 ring-white"></div>

        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs hover:border-slate-300 transition-all space-y-4">
            <!-- Cabecera de la Atención -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2.5">
                    <x-badge :tone="$badgeTypeColor">{{ $vet?->tipo ?? 'CONSULTA' }}</x-badge>
                    <h3 class="font-bold text-slate-900 text-base">
                        {{ $vet?->motivo_consulta ?: 'Atención veterinaria general' }}
                    </h3>
                </div>
                <div class="flex items-center gap-2 text-xs text-slate-400 flex-wrap">
                    <i data-lucide="calendar" class="size-3.5"></i>
                    <span>{{ \Carbon\Carbon::parse($atencion->fecha_hora_inicio)->format('d/m/Y · H:i') }}</span>
                    @if ($medico)
                        <span>· Dr(a). {{ $medico->persona?->nombre }} {{ $medico->persona?->apellido1 }}</span>
                    @endif
                    @if(Auth::user()->hasRole(['SUPER_ADMIN', 'ADMIN', 'VETERINARIO']))
                        <a
                            href="{{ route('historial.receta.pdf', $atencion->id_atencion) }}"
                            target="_blank"
                            class="inline-flex items-center gap-1 rounded-md bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-700 hover:bg-primary-50 hover:text-primary-700 transition-colors"
                            title="Imprimir receta médica"
                        >
                            <i data-lucide="printer" class="size-3"></i>
                            <span>Receta PDF</span>
                        </a>
                    @endif
                </div>
            </div>

            <!-- Signos Vitales si existen -->
            @if ($signos)
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 rounded-xl bg-slate-50/80 p-3 text-xs border border-slate-100">
                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-400">Peso</span>
                        <p class="font-bold text-slate-800">{{ $signos->peso ? $signos->peso . ' kg' : 'S/D' }}</p>
                    </div>
                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-400">Temperatura</span>
                        <p class="font-bold text-slate-800">{{ $signos->temperatura ? $signos->temperatura . ' °C' : 'S/D' }}</p>
                    </div>
                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-400">Frec. Cardíaca</span>
                        <p class="font-bold text-slate-800">{{ $signos->frecuencia_cardiaca ? $signos->frecuencia_cardiaca . ' lpm' : 'S/D' }}</p>
                    </div>
                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-400">Frec. Respiratoria</span>
                        <p class="font-bold text-slate-800">{{ $signos->frecuencia_respiratoria ? $signos->frecuencia_respiratoria . ' rpm' : 'S/D' }}</p>
                    </div>
                </div>
            @endif

            <!-- Anamnesis y Hallazgos -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <div class="rounded-xl border border-slate-100 p-3 bg-slate-50/40">
                    <strong class="font-bold text-slate-800 block mb-1">Anamnesis / Síntomas:</strong>
                    <p class="text-slate-600 leading-relaxed">{{ $vet?->anamnesis }}</p>
                    @if ($vet?->sintomas)
                        <p class="mt-1 text-slate-500 italic">Síntomas: {{ $vet->sintomas }}</p>
                    @endif
                </div>

                <div class="rounded-xl border border-slate-100 p-3 bg-slate-50/40">
                    <strong class="font-bold text-slate-800 block mb-1">Examen Físico y Conclusión:</strong>
                    <p class="text-slate-600 leading-relaxed">{{ $vet?->examen_fisico ?: 'Sin hallazgos atípicos.' }}</p>
                    @if ($vet?->conclusion)
                        <p class="mt-1 font-semibold text-slate-700">Conclusión: {{ $vet->conclusion }}</p>
                    @endif
                </div>
            </div>

            <!-- Diagnósticos y Tratamientos Asociados en esta Consulta -->
            @if (($vet?->diagnosticos->count() ?? 0) > 0 || ($vet?->tratamientos->count() ?? 0) > 0)
                <div class="flex flex-wrap gap-2 pt-2 border-t border-slate-100">
                    @foreach ($vet->diagnosticos as $diag)
                        <span class="inline-flex items-center gap-1 rounded-lg bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700">
                            <i data-lucide="tag" class="size-3"></i>
                            <span>{{ $diag->nombre }} ({{ $diag->tipo }})</span>
                        </span>
                    @endforeach

                    @foreach ($vet->tratamientos as $trat)
                        <span class="inline-flex items-center gap-1 rounded-lg bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">
                            <i data-lucide="pill" class="size-3"></i>
                            <span>{{ $trat->descripcion }}</span>
                        </span>
                    @endforeach
                </div>
            @endif

            @if ($vet?->fecha_reconsulta)
                <div class="flex items-center gap-1.5 text-xs text-primary-700 bg-primary-50/60 p-2.5 rounded-xl border border-primary-100">
                    <i data-lucide="calendar-clock" class="size-3.5"></i>
                    <span>Fecha programada de reconsulta: <strong>{{ \Carbon\Carbon::parse($vet->fecha_reconsulta)->format('d/m/Y H:i') }}</strong></span>
                </div>
            @endif
        </div>
    </div>
@empty
    <x-empty-state
        icon="clipboard-list"
        title="No hay consultas registradas"
        description="Este paciente aún no tiene entradas médicas en su expediente clínico."
    />
@endforelse
