<x-layout title="Ficha de Mascota — {{ $paciente->nombre }} — La Petdiatría">
    @php
        $especieNombre = strtolower($paciente->raza?->especie?->nombre ?? 'otro');
        $iconName = str_contains($especieNombre, 'gat') ? 'cat' : (str_contains($especieNombre, 'perr') ? 'dog' : 'paw-print');
        $edad = $paciente->fecha_nacimiento ? \Carbon\Carbon::parse($paciente->fecha_nacimiento)->age . ' años' : 'No registrada';
        $persona = $paciente->propietario?->persona;
        $nombrePropietario = $persona ? trim("{$persona->nombre} {$persona->apellido1} {$persona->apellido2}") : 'Sin propietario';
    @endphp

    <div
        x-data="{
            editPetModalOpen: false,
            fotoPreview: null,
            selectedEspecie: @js($paciente->raza?->id_especie ?? $especies->first()?->id_especie),
            allRazas: @js($especies->mapWithKeys(fn($e) => [$e->id_especie => $e->razas])),
            get filteredRazas() {
                return this.allRazas[this.selectedEspecie] || [];
            },
            petFormData: {
                id_propietario: @js($paciente->id_propietario),
                nombre: @js($paciente->nombre),
                id_raza: @js($paciente->id_raza),
                sexo: @js($paciente->sexo),
                fecha_nacimiento: @js($paciente->fecha_nacimiento),
                tamanio: @js($paciente->tamanio ?? 'MEDIANO'),
                condicion_reproductiva: @js($paciente->condicion_reproductiva ?? 'INTACTO'),
                color: @js($paciente->color),
                microchip: @js($paciente->microchip),
                fecha_implantacion: @js($paciente->fecha_implantacion),
                tipo_pelaje: @js($paciente->tipo_pelaje ?? 'CORTO'),
                caract_particulares: @js($paciente->caract_particulares),
                comportamiento_normal: @js($paciente->comportamiento_normal),
                recomendaciones: @js($paciente->recomendaciones),
                autorizacion_redes: {{ $paciente->autorizacion_redes ? 'true' : 'false' }},
                estado: @js($paciente->estado),
            },
            onEspecieChange() {
                if (this.filteredRazas.length > 0) {
                    this.petFormData.id_raza = this.filteredRazas[0].id_raza;
                } else {
                    this.petFormData.id_raza = '';
                }
            },
            previewFoto(event) {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = (e) => { this.fotoPreview = e.target.result; };
                    reader.readAsDataURL(file);
                }
            }
        }"
        @keydown.escape.window="if(editPetModalOpen) editPetModalOpen = false;"
        class="space-y-6"
    >
        <x-breadcrumb :items="[
            ['label' => 'Clientes', 'url' => route('clientes.index')],
            ['label' => $nombrePropietario, 'url' => $paciente->id_propietario ? route('clientes.show', $paciente->id_propietario) : null],
            ['label' => 'Mascotas', 'url' => $paciente->id_propietario ? route('clientes.show', $paciente->id_propietario) : null],
            ['label' => $paciente->nombre, 'url' => null],
        ]" />

        <!-- Navegación superior y Migas -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <a
                    href="{{ route('clientes.show', $paciente->id_propietario) }}"
                    class="inline-flex size-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 hover:bg-slate-50 hover:text-slate-800 transition-colors shadow-2xs"
                    title="Volver a la ficha del propietario"
                >
                    <i data-lucide="arrow-left" class="size-4"></i>
                </a>
                {{-- Foto / Avatar de la mascota --}}
                @if ($paciente->foto)
                    <img
                        src="{{ asset('storage/' . $paciente->foto) }}"
                        alt="Foto de {{ $paciente->nombre }}"
                        class="size-14 rounded-2xl object-cover ring-2 ring-primary-500/20 shadow-sm"
                    />
                @else
                    <div class="flex size-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-primary-100 to-indigo-100 text-primary-700 font-bold text-lg ring-2 ring-primary-500/20 shadow-sm">
                        {{ strtoupper(substr($paciente->nombre, 0, 2)) }}
                    </div>
                @endif
                <div>
                    <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-slate-400">
                        <a href="{{ route('clientes.index') }}" class="hover:underline">Clientes</a>
                        <span>·</span>
                        <a href="{{ route('clientes.show', $paciente->id_propietario) }}" class="hover:underline">{{ $nombrePropietario }}</a>
                        <span>·</span>
                        <span>Ficha Clínica</span>
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

            <div class="flex items-center gap-2.5 flex-wrap">
                <a
                    href="{{ route('mascotas.carnet.pdf', $paciente->id_paciente) }}"
                    target="_blank"
                    class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-emerald-200 bg-emerald-50/50 px-3.5 py-2 text-xs font-semibold text-emerald-800 shadow-2xs hover:bg-emerald-100/60 transition-colors"
                    title="Descargar Carnet de Vacunación oficial en PDF"
                >
                    <i data-lucide="shield-check" class="size-4 text-emerald-600"></i>
                    <span>Descargar Carnet</span>
                </a>

                @if ($canWrite)
                    <x-button variant="secondary" @click="editPetModalOpen = true">
                        <i data-lucide="pencil" class="size-4 mr-1.5"></i>
                        <span>Editar Mascota</span>
                    </x-button>
                @endif
            </div>
        </div>

        <!-- Barra de Accesos Directos (CU13 Historial de Servicios y CU20 Historial Clínico) -->
        <div class="grid grid-cols-1 {{ $canAccessClinico ? 'sm:grid-cols-2' : 'sm:grid-cols-1' }} gap-4">
            <!-- Acceso Directo: Historial de Servicios (CU13 - Accesible a todos los roles autorizados) -->
            <a
                href="#servicios-historial-{{ $paciente->id_paciente }}"
                class="group flex items-center justify-between rounded-2xl border border-slate-200/80 bg-white p-4 shadow-card hover:border-purple-500 hover:shadow-pop transition-all cursor-pointer"
            >
                <div class="flex items-center gap-3.5">
                    <div class="flex size-11 items-center justify-center rounded-xl bg-purple-50 text-purple-600 group-hover:bg-purple-600 group-hover:text-white transition-colors">
                        <i data-lucide="sparkles" class="size-5"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-slate-900 group-hover:text-purple-600 transition-colors">Historial de Servicios (CU13)</h4>
                        <p class="text-xs text-slate-400">Servicios clínicos, baños, cortes de peluquería y atenciones</p>
                    </div>
                </div>
                <i data-lucide="chevron-right" class="size-4 text-slate-400 group-hover:translate-x-1 group-hover:text-purple-600 transition-all"></i>
            </a>

            @if ($canAccessClinico)
                <!-- Acceso Directo: Historial Clínico (CU20 - Exclusivo Veterinario / Super-Admin) -->
                <a
                    href="{{ route('historial.show', $paciente->id_paciente) }}"
                    class="group flex items-center justify-between rounded-2xl border border-slate-200/80 bg-white p-4 shadow-card hover:border-primary-500 hover:shadow-pop transition-all cursor-pointer"
                >
                    <div class="flex items-center gap-3.5">
                        <div class="flex size-11 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 group-hover:bg-primary-600 group-hover:text-white transition-colors">
                            <i data-lucide="clipboard-list" class="size-5"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h4 class="text-sm font-bold text-slate-900 group-hover:text-primary-600 transition-colors">Expediente Clínico (CU20)</h4>
                                <span class="rounded-full bg-indigo-50 px-2 py-0.5 text-[10px] font-bold text-indigo-700">Médico</span>
                            </div>
                            <p class="text-xs text-slate-400">{{ $paciente->atenciones->count() }} atenciones médicas registradas</p>
                        </div>
                    </div>
                    <i data-lucide="chevron-right" class="size-4 text-slate-400 group-hover:translate-x-1 group-hover:text-primary-600 transition-all"></i>
                </a>
            @endif
        </div>

        <!-- Contenedor Principal: Información de la Mascota y Propietario -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Datos Biológicos y Ficha General -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Tarjeta: Datos Generales -->
                <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-card">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                        <div class="flex items-center gap-3">
                            <div class="flex size-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                                <i data-lucide="{{ $iconName }}" class="size-5"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900">Perfil Biológico y Características</h3>
                                <p class="text-xs text-slate-500">Datos morfológicos y registro de identificación</p>
                            </div>
                        </div>

                        <span class="rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">
                            ID: #{{ $paciente->id_paciente }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 pt-5">
                        <div class="rounded-xl border border-slate-100 bg-slate-50/50 p-3">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Especie</span>
                            <p class="mt-1 font-bold text-slate-900 text-sm">{{ $paciente->raza?->especie?->nombre ?: 'S/D' }}</p>
                        </div>

                        <div class="rounded-xl border border-slate-100 bg-slate-50/50 p-3">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Raza</span>
                            <p class="mt-1 font-bold text-slate-900 text-sm">{{ $paciente->raza?->nombre ?: 'Mestizo' }}</p>
                        </div>

                        <div class="rounded-xl border border-slate-100 bg-slate-50/50 p-3">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Edad Estimada</span>
                            <p class="mt-1 font-bold text-slate-900 text-sm">{{ $edad }}</p>
                        </div>

                        <div class="rounded-xl border border-slate-100 bg-slate-50/50 p-3">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Fecha Nacimiento</span>
                            <p class="mt-1 font-bold text-slate-900 text-sm">{{ $paciente->fecha_nacimiento ?: 'No registrada' }}</p>
                        </div>

                        <div class="rounded-xl border border-slate-100 bg-slate-50/50 p-3">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Tamaño</span>
                            <p class="mt-1 font-bold text-slate-900 text-sm">{{ $paciente->tamanio ?: 'S/D' }}</p>
                        </div>

                        <div class="rounded-xl border border-slate-100 bg-slate-50/50 p-3">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Condición Reproductiva</span>
                            <p class="mt-1 font-bold text-slate-900 text-sm">{{ $paciente->condicion_reproductiva ?: 'S/D' }}</p>
                        </div>

                        <div class="rounded-xl border border-slate-100 bg-slate-50/50 p-3">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Color / Marcas</span>
                            <p class="mt-1 font-bold text-slate-900 text-sm">{{ $paciente->color ?: 'S/D' }}</p>
                        </div>

                        <div class="rounded-xl border border-slate-100 bg-slate-50/50 p-3">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Tipo de Pelaje</span>
                            <p class="mt-1 font-bold text-slate-900 text-sm">{{ $paciente->tipo_pelaje ?: 'S/D' }}</p>
                        </div>

                        <div class="rounded-xl border border-slate-100 bg-slate-50/50 p-3">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Redes Sociales</span>
                            <p class="mt-1 font-bold text-sm {{ $paciente->autorizacion_redes ? 'text-emerald-600' : 'text-slate-400' }}">
                                {{ $paciente->autorizacion_redes ? 'Autorizado' : 'No autorizado' }}
                            </p>
                        </div>
                    </div>

                    <!-- Identificación por Microchip -->
                    <div class="mt-4 rounded-xl border border-indigo-100 bg-indigo-50/40 p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="flex size-9 items-center justify-center rounded-lg bg-primary-100 text-primary-700">
                                <i data-lucide="cpu" class="size-4.5"></i>
                            </div>
                            <div>
                                <span class="text-xs font-bold text-slate-900">Microchip Electrónico</span>
                                <p class="text-xs text-slate-600 font-mono">
                                    {{ $paciente->microchip ?: 'Sin microchip registrado' }}
                                </p>
                            </div>
                        </div>
                        @if ($paciente->fecha_implantacion)
                            <span class="text-xs text-slate-500">
                                Implantado: {{ $paciente->fecha_implantacion }}
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Tarjeta: Comportamiento y Observaciones Especiales -->
                <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-card space-y-4">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i data-lucide="smile" class="size-4 text-primary-600"></i>
                        <span>Comportamiento y Particularidades</span>
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="rounded-xl border border-slate-100 bg-slate-50/60 p-4">
                            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Comportamiento Habitual</span>
                            <p class="mt-1 text-xs text-slate-700 leading-relaxed">
                                {{ $paciente->comportamiento_normal ?: 'Sin observaciones conductuales registradas.' }}
                            </p>
                        </div>

                        <div class="rounded-xl border border-slate-100 bg-slate-50/60 p-4">
                            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Recomendaciones de Manejo</span>
                            <p class="mt-1 text-xs text-slate-700 leading-relaxed">
                                {{ $paciente->recomendaciones ?: 'Sin recomendaciones especiales.' }}
                            </p>
                        </div>
                    </div>

                    @if ($paciente->caract_particulares)
                        <div class="rounded-xl border border-slate-100 bg-slate-50/60 p-4">
                            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Señas / Características Particulares</span>
                            <p class="mt-1 text-xs text-slate-700 leading-relaxed">
                                {{ $paciente->caract_particulares }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Columna Lateral: Ficha del Propietario e Información Médica -->
            <div class="space-y-6">
                <!-- Tarjeta del Propietario Vinculado -->
                <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-card">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">
                            Propietario Responsable
                        </h3>
                        <a
                            href="{{ route('clientes.show', $paciente->id_propietario) }}"
                            class="text-xs font-bold text-primary-600 hover:text-primary-700"
                        >
                            Ver Perfil >
                        </a>
                    </div>

                    <div class="mt-4 flex items-center gap-3">
                        <div class="flex size-11 items-center justify-center rounded-2xl bg-primary-100 text-primary-700 font-bold text-sm">
                            {{ $persona ? strtoupper(substr($persona->nombre, 0, 1) . substr($persona->apellido1, 0, 1)) : 'PR' }}
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-900 text-sm">{{ $nombrePropietario }}</h4>
                            <p class="text-xs text-slate-400">CI: {{ $persona?->documento ?: 'S/D' }}</p>
                        </div>
                    </div>

                    <div class="mt-4 space-y-2 pt-3 border-t border-slate-100 text-xs">
                        <div class="flex items-center gap-2 text-slate-700">
                            <i data-lucide="phone" class="size-3.5 text-slate-400"></i>
                            <span>{{ $persona?->telefono ?: 'Sin teléfono' }}</span>
                        </div>
                        <div class="flex items-center gap-2 text-slate-700 truncate" title="{{ $persona?->email }}">
                            <i data-lucide="mail" class="size-3.5 text-slate-400"></i>
                            <span class="truncate">{{ $persona?->email ?: 'Sin correo' }}</span>
                        </div>
                        <div class="flex items-center gap-2 text-slate-700">
                            <i data-lucide="map-pin" class="size-3.5 text-slate-400"></i>
                            <span class="line-clamp-2">{{ $persona?->direccion ?: 'Sin dirección' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Resumen Ficha Clínica Base -->
                <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-card space-y-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center justify-between">
                        <span>Ficha Médica Inicial</span>
                        @if ($paciente->fichaMedica)
                            <span class="text-[10px] text-emerald-600 font-bold">Activa</span>
                        @endif
                    </h3>

                    <div class="space-y-2 text-xs">
                        <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="font-bold text-slate-700 block">Alergias Conocidas:</span>
                            <span class="text-slate-500">{{ $paciente->fichaMedica?->alergias ?: 'Ninguna reportada' }}</span>
                        </div>

                        <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="font-bold text-slate-700 block">Enfermedades Previas:</span>
                            <span class="text-slate-500">{{ $paciente->fichaMedica?->enfermedades_previas ?: 'Sin antecedentes' }}</span>
                        </div>

                        <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="font-bold text-slate-700 block">Medicamentos Habituales:</span>
                            <span class="text-slate-500">{{ $paciente->fichaMedica?->medicamentos_habituales ?: 'Ninguno' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CU13 - Historial de Servicios Recibidos por la Mascota -->
        <div id="servicios-historial-{{ $paciente->id_paciente }}" class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-card space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="flex size-9 items-center justify-center rounded-xl bg-purple-50 text-purple-600">
                        <i data-lucide="scissors" class="size-4.5"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Historial de Servicios (CU13)</h3>
                        <p class="text-xs text-slate-500">Servicios de peluquería/SPA, baños, controles y atenciones recibidas por {{ $paciente->nombre }}</p>
                    </div>
                </div>
                <x-badge tone="primary">{{ $paciente->citas->count() + $paciente->atenciones->count() }} Registros</x-badge>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50/70 text-slate-400 uppercase font-bold border-b border-slate-100">
                        <tr>
                            <th class="px-4 py-3">Fecha</th>
                            <th class="px-4 py-3">Servicio / Tratamiento</th>
                            <th class="px-4 py-3">Categoría</th>
                            <th class="px-4 py-3">Profesional a Cargo</th>
                            <th class="px-4 py-3">Estado</th>
                            <th class="px-4 py-3">Detalle / Motivo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @php
                            $serviciosRegistrados = collect();

                            // Servicios desde Citas (SPA, Baños, Cortes, etc.)
                            foreach ($paciente->citas as $c) {
                                foreach ($c->detalles as $d) {
                                    $serviciosRegistrados->push([
                                        'fecha' => $c->fecha_hora_inicio,
                                        'tipo' => $d->servicio?->nombre ?? 'Servicio Programado',
                                        'categoria' => $d->servicio?->categoria ?? 'ESTETICA',
                                        'profesional' => $c->profesional?->persona ? trim("{$c->profesional->persona->nombre} {$c->profesional->persona->apellido1}") : 'Personal General',
                                        'estado' => $c->estado,
                                        'observaciones' => $c->motivo,
                                    ]);
                                }
                                if ($c->detalles->isEmpty()) {
                                    $serviciosRegistrados->push([
                                        'fecha' => $c->fecha_hora_inicio,
                                        'tipo' => $c->servicio?->nombre ?? 'Servicio General',
                                        'categoria' => $c->servicio?->categoria ?? 'ESTETICA',
                                        'profesional' => $c->profesional?->persona ? trim("{$c->profesional->persona->nombre} {$c->profesional->persona->apellido1}") : 'Personal General',
                                        'estado' => $c->estado,
                                        'observaciones' => $c->motivo,
                                    ]);
                                }
                            }

                            // Atenciones
                            foreach ($paciente->atenciones as $a) {
                                $serviciosRegistrados->push([
                                    'fecha' => $a->fecha_hora_inicio,
                                    'tipo' => 'Atención Médica Veterinaria',
                                    'categoria' => 'VETERINARIA',
                                    'profesional' => $a->profesionales->first()?->persona ? trim("{$a->profesionales->first()->persona->nombre} {$a->profesionales->first()->persona->apellido1}") : 'Médico Veterinario',
                                    'estado' => $a->estado,
                                    'observaciones' => 'Atención clínica ambulatoria',
                                ]);
                            }

                            $serviciosOrdenados = $serviciosRegistrados->sortByDesc('fecha');
                        @endphp

                        @forelse ($serviciosOrdenados as $srv)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-4 py-3 font-semibold text-slate-700">
                                    {{ \Carbon\Carbon::parse($srv['fecha'])->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-4 py-3 font-bold text-slate-900">
                                    {{ $srv['tipo'] }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="rounded-lg px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $srv['categoria'] === 'ESTETICA' ? 'bg-pink-50 text-pink-700' : 'bg-blue-50 text-blue-700' }}">
                                        {{ $srv['categoria'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-600">
                                    {{ $srv['profesional'] }}
                                </td>
                                <td class="px-4 py-3">
                                    <x-state-badge :state="$srv['estado']" />
                                </td>
                                <td class="px-4 py-3 text-slate-500 max-w-xs truncate" title="{{ $srv['observaciones'] }}">
                                    {{ $srv['observaciones'] ?: 'Sin observaciones' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-slate-400">
                                    No hay servicios previos registrados para este paciente.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- MODAL DE EDICIÓN DE MASCOTA -->
        <div
            x-show="editPetModalOpen"
            x-cloak
            class="fixed inset-0 z-50 overflow-y-auto"
            x-transition.opacity
        >
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" @click="editPetModalOpen = false"></div>

                <div
                    class="relative w-full max-w-2xl rounded-2xl bg-white shadow-pop overflow-hidden"
                    x-show="editPetModalOpen"
                    x-transition.scale.95
                >
                    <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4 bg-slate-50/50">
                        <div class="flex items-center gap-3">
                            <div class="flex size-9 items-center justify-center rounded-xl bg-primary-50 text-primary-600">
                                <i data-lucide="paw-print" class="size-5"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900">Editar Mascota: {{ $paciente->nombre }}</h3>
                                <p class="text-xs text-slate-500">Actualizar datos biológicos y ficha del paciente</p>
                            </div>
                        </div>

                        <button type="button" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600" @click="editPetModalOpen = false">
                            <i data-lucide="x" class="size-5"></i>
                        </button>
                    </div>

                    <form action="{{ route('mascotas.update', $paciente->id_paciente) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="max-h-[75vh] overflow-y-auto p-6 space-y-5">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="sm:col-span-2">
                                    <x-field label="Propietario Asociado (CU11 - Asociar Mascota a Propietario)" required>
                                        <select
                                            name="id_propietario"
                                            x-model="petFormData.id_propietario"
                                            class="block w-full rounded-xl border-0 bg-white py-2 pl-3.5 pr-8 text-sm text-slate-700 shadow-sm ring-1 ring-inset ring-slate-200 focus-ring"
                                            required
                                        >
                                            @foreach ($propietarios as $prop)
                                                <option value="{{ $prop->id_persona }}" {{ $prop->id_persona == $paciente->id_propietario ? 'selected' : '' }}>
                                                    {{ $prop->persona?->documento }} — {{ $prop->persona?->nombre }} {{ $prop->persona?->apellido1 }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </x-field>
                                </div>

                                <x-field label="Nombre" required>
                                    <x-input type="text" name="nombre" x-model="petFormData.nombre" required />
                                </x-field>

                                <x-field label="Sexo" required>
                                    <x-select name="sexo" x-model="petFormData.sexo" required>
                                        <option value="MACHO">MACHO</option>
                                        <option value="HEMBRA">HEMBRA</option>
                                    </x-select>
                                </x-field>

                                <x-field label="Especie" required>
                                    <select
                                        x-model="selectedEspecie"
                                        @change="onEspecieChange()"
                                        class="block w-full rounded-xl border-0 bg-white py-2 pl-3.5 pr-8 text-sm text-slate-700 shadow-sm ring-1 ring-inset ring-slate-200 focus-ring"
                                        required
                                    >
                                        @foreach ($especies as $esp)
                                            <option value="{{ $esp->id_especie }}">{{ $esp->nombre }}</option>
                                        @endforeach
                                    </select>
                                </x-field>

                                <x-field label="Raza" required>
                                    <select
                                        name="id_raza"
                                        x-model="petFormData.id_raza"
                                        class="block w-full rounded-xl border-0 bg-white py-2 pl-3.5 pr-8 text-sm text-slate-700 shadow-sm ring-1 ring-inset ring-slate-200 focus-ring"
                                        required
                                    >
                                        <template x-for="raza in filteredRazas" :key="raza.id_raza">
                                            <option :value="raza.id_raza" x-text="raza.nombre"></option>
                                        </template>
                                    </select>
                                </x-field>

                                <x-field label="Fecha Nacimiento">
                                    <x-input type="date" name="fecha_nacimiento" x-model="petFormData.fecha_nacimiento" />
                                </x-field>

                                <x-field label="Tamaño">
                                    <x-select name="tamanio" x-model="petFormData.tamanio">
                                        <option value="PEQUEÑO">PEQUEÑO</option>
                                        <option value="MEDIANO">MEDIANO</option>
                                        <option value="GRANDE">GRANDE</option>
                                        <option value="GIGANTE">GIGANTE</option>
                                    </x-select>
                                </x-field>

                                <x-field label="Condición Reproductiva">
                                    <x-select name="condicion_reproductiva" x-model="petFormData.condicion_reproductiva">
                                        <option value="INTACTO">INTACTO</option>
                                        <option value="CASTRADO">CASTRADO</option>
                                    </x-select>
                                </x-field>

                                <x-field label="Color / Marcas">
                                    <x-input type="text" name="color" x-model="petFormData.color" />
                                </x-field>

                                <x-field label="Tipo de Pelaje">
                                    <x-input type="text" name="tipo_pelaje" x-model="petFormData.tipo_pelaje" />
                                </x-field>

                                <x-field label="Estado" required>
                                    <x-select name="estado" x-model="petFormData.estado" required>
                                        <option value="ACTIVO">ACTIVO</option>
                                        <option value="INACTIVO">INACTIVO</option>
                                        <option value="FALLECIDO">FALLECIDO</option>
                                    </x-select>
                                </x-field>

                                <x-field label="Número de Microchip">
                                    <x-input type="text" name="microchip" x-model="petFormData.microchip" />
                                </x-field>

                                <x-field label="Fecha Implantación">
                                    <x-input type="date" name="fecha_implantacion" x-model="petFormData.fecha_implantacion" />
                                </x-field>

                                <div class="sm:col-span-2">
                                    <x-field label="Características Particulares">
                                        <x-input type="text" name="caract_particulares" x-model="petFormData.caract_particulares" />
                                    </x-field>
                                </div>

                                <div class="sm:col-span-2">
                                    <x-field label="Comportamiento Habitual">
                                        <x-input type="text" name="comportamiento_normal" x-model="petFormData.comportamiento_normal" />
                                    </x-field>
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700">
                                        <input
                                            type="checkbox"
                                            name="autorizacion_redes"
                                            value="1"
                                            x-model="petFormData.autorizacion_redes"
                                            class="rounded border-slate-300 text-primary-600 focus:ring-primary-500 cursor-pointer"
                                        />
                                        <span>Autorización de fotos/videos para redes sociales</span>
                                    </label>
                                </div>

                                {{-- Subida de foto de la mascota --}}
                                <div class="sm:col-span-2">
                                    <x-field label="Foto de la Mascota">
                                        <div class="flex items-center gap-4">
                                            {{-- Previsualización --}}
                                            <div class="shrink-0">
                                                <template x-if="fotoPreview">
                                                    <img :src="fotoPreview" alt="Previsualización" class="size-16 rounded-xl object-cover ring-2 ring-primary-500/20" />
                                                </template>
                                                <template x-if="!fotoPreview">
                                                    @if ($paciente->foto)
                                                        <img src="{{ asset('storage/' . $paciente->foto) }}" alt="Foto actual" class="size-16 rounded-xl object-cover ring-2 ring-primary-500/20" />
                                                    @else
                                                        <div class="flex size-16 items-center justify-center rounded-xl bg-slate-100 text-slate-400">
                                                            <i data-lucide="camera" class="size-6"></i>
                                                        </div>
                                                    @endif
                                                </template>
                                            </div>
                                            <div class="flex-1">
                                                <input
                                                    type="file"
                                                    name="foto"
                                                    accept="image/jpeg,image/png,image/webp"
                                                    @change="previewFoto($event)"
                                                    class="block w-full text-sm text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 cursor-pointer"
                                                />
                                                <p class="mt-1 text-[11px] text-slate-400">JPG, PNG o WEBP. Máximo 2 MB.</p>
                                            </div>
                                        </div>
                                    </x-field>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-4 bg-slate-50/50">
                            <x-button variant="secondary" type="button" @click="editPetModalOpen = false">
                                Cancelar
                            </x-button>

                            <x-button variant="primary" type="submit">
                                <span>Guardar Cambios</span>
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-layout>
