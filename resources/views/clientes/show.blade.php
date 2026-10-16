<x-layout title="Ficha de Propietario — {{ $propietario->persona ? $propietario->persona->nombre . ' ' . $propietario->persona->apellido1 : 'Cliente' }} — La Petdiatría">
    @php
        $persona = $propietario->persona;
        $nombreCompleto = $persona ? trim("{$persona->nombre} {$persona->apellido1} {$persona->apellido2}") : 'Cliente sin datos';
        $initials = $persona ? strtoupper(substr($persona->nombre, 0, 1) . substr($persona->apellido1, 0, 1)) : 'CL';
    @endphp

    <div
        x-data="{
            editOwnerModalOpen: false,
            addPetModalOpen: {{ $errors->has('nombre') || $errors->has('id_raza') || $errors->has('sexo') ? 'true' : 'false' }},
            fotoPreview: null,
            ownerFormData: {
                documento: @js($persona?->documento ?? ''),
                nombre: @js($persona?->nombre ?? ''),
                apellido1: @js($persona?->apellido1 ?? ''),
                apellido2: @js($persona?->apellido2 ?? ''),
                telefono: @js($persona?->telefono ?? ''),
                email: @js($persona?->email ?? ''),
                direccion: @js($persona?->direccion ?? ''),
            },
            selectedEspecie: @js($especies->first()?->id_especie ?? ''),
            allRazas: @js($especies->mapWithKeys(fn($e) => [$e->id_especie => $e->razas])),
            get filteredRazas() {
                return this.allRazas[this.selectedEspecie] || [];
            },
            petFormData: {
                nombre: '',
                id_raza: '',
                sexo: 'MACHO',
                fecha_nacimiento: '',
                tamanio: 'MEDIANO',
                condicion_reproductiva: 'INTACTO',
                color: '',
                microchip: '',
                fecha_implantacion: '',
                tipo_pelaje: 'CORTO',
                caract_particulares: '',
                comportamiento_normal: '',
                recomendaciones: '',
                autorizacion_redes: true,
                estado: 'ACTIVO',
            },
            openAddPet() {
                this.selectedEspecie = '{{ $especies->first()?->id_especie }}';
                this.petFormData = {
                    nombre: '',
                    id_raza: this.filteredRazas.length > 0 ? this.filteredRazas[0].id_raza : '',
                    sexo: 'MACHO',
                    fecha_nacimiento: '',
                    tamanio: 'MEDIANO',
                    condicion_reproductiva: 'INTACTO',
                    color: '',
                    microchip: '',
                    fecha_implantacion: '',
                    tipo_pelaje: 'CORTO',
                    caract_particulares: '',
                    comportamiento_normal: '',
                    recomendaciones: '',
                    autorizacion_redes: true,
                    estado: 'ACTIVO',
                };
                this.addPetModalOpen = true;
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
        @keydown.escape.window="if(editOwnerModalOpen) editOwnerModalOpen = false; if(addPetModalOpen) addPetModalOpen = false;"
        class="space-y-6"
    >
        <x-breadcrumb :items="[
            ['label' => 'Clientes', 'url' => route('clientes.index')],
            ['label' => $nombreCompleto, 'url' => null],
        ]" />

        <!-- Migas de pan y barra superior -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <a
                    href="{{ route('clientes.index') }}"
                    class="inline-flex size-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 hover:bg-slate-50 hover:text-slate-800 transition-colors shadow-2xs"
                    title="Volver al listado de clientes"
                >
                    <i data-lucide="arrow-left" class="size-4"></i>
                </a>
                <div>
                    <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-slate-400">
                        <span>Clientes y Mascotas</span>
                        <span>·</span>
                        <span>Ficha Propietario</span>
                    </div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                        {{ $nombreCompleto }}
                    </h1>
                </div>
            </div>

            @if ($canWrite)
                <div class="flex items-center gap-3">
                    <x-button variant="secondary" @click="editOwnerModalOpen = true">
                        <i data-lucide="pencil" class="size-4 mr-1.5"></i>
                        <span>Editar Datos</span>
                    </x-button>

                    <x-button variant="primary" @click="openAddPet()">
                        <i data-lucide="plus" class="size-4 mr-1.5"></i>
                        <span>Agregar Mascota</span>
                    </x-button>
                </div>
            @endif
        </div>

        <!-- Tarjeta de Datos del Propietario (Grid de Contacto y Estado) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Datos de contacto y perfil -->
            <div class="lg:col-span-2 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-card">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-3.5">
                        <div class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-primary-100 text-primary-700 font-bold text-base shadow-sm ring-1 ring-primary-500/20">
                            {{ $initials }}
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">{{ $nombreCompleto }}</h2>
                            <p class="text-xs text-slate-400">Cliente ID #{{ $propietario->id_persona }}</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                        <span class="size-1.5 rounded-full bg-emerald-600"></span>
                        Cliente Activo
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 pt-4">
                    <div class="rounded-xl border border-slate-100 bg-slate-50/50 p-3">
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                            <i data-lucide="id-card" class="size-3.5"></i>
                            <span>Documento (CI)</span>
                        </span>
                        <p class="mt-1 font-bold text-slate-900 text-sm">{{ $persona?->documento ?: 'No registrado' }}</p>
                    </div>

                    <div class="rounded-xl border border-slate-100 bg-slate-50/50 p-3">
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                            <i data-lucide="phone" class="size-3.5"></i>
                            <span>Teléfono</span>
                        </span>
                        <div class="mt-1 flex items-center justify-between gap-2">
                            <p class="font-bold text-slate-900 text-sm">{{ $persona?->telefono ?: 'No registrado' }}</p>
                            @php
                                $telCliente = preg_replace('/[^0-9]/', '', $persona?->telefono ?? '');
                                $waClienteUrl = !empty($telCliente) ? 'https://wa.me/' . $telCliente . '?text=' . urlencode("Hola " . ($persona->nombre ?? 'Estimado(a)') . ", te escribimos de la clínica veterinaria La Petdiatría.") : null;
                            @endphp
                            @if($waClienteUrl)
                                <a
                                    href="{{ $waClienteUrl }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg border border-emerald-300 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 text-xs font-semibold transition-colors shadow-2xs"
                                    title="Chatear por WhatsApp"
                                >
                                    <i data-lucide="message-circle" class="size-3"></i>
                                    <span>WhatsApp</span>
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="rounded-xl border border-slate-100 bg-slate-50/50 p-3">
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                            <i data-lucide="mail" class="size-3.5"></i>
                            <span>Correo</span>
                        </span>
                        <p class="mt-1 font-bold text-slate-900 text-sm truncate" title="{{ $persona?->email }}">
                            {{ $persona?->email ?: 'No registrado' }}
                        </p>
                    </div>

                    <div class="sm:col-span-2 md:col-span-3 rounded-xl border border-slate-100 bg-slate-50/50 p-3">
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                            <i data-lucide="map-pin" class="size-3.5"></i>
                            <span>Dirección de Domicilio</span>
                        </span>
                        <p class="mt-1 font-medium text-slate-800 text-sm">{{ $persona?->direccion ?: 'No registrada' }}</p>
                    </div>
                </div>
            </div>

            <!-- Resumen de cuenta y métricas del cliente -->
            <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-card flex flex-col justify-between">
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-2">
                        <i data-lucide="wallet" class="size-4 text-primary-600"></i>
                        <span>Estado Financiero</span>
                    </h3>
                    
                    <div class="mt-4 rounded-xl border border-indigo-100 bg-indigo-50/40 p-4">
                        <span class="text-xs text-slate-500 font-medium">Saldo pendiente en cuenta:</span>
                        <div class="mt-1 flex items-baseline gap-2">
                            <span class="text-2xl font-black text-slate-900">
                                Bs. {{ number_format($propietario->cuenta?->saldo_pendiente ?? 0, 2) }}
                            </span>
                            @if (($propietario->cuenta?->saldo_pendiente ?? 0) > 0)
                                <span class="rounded bg-rose-100 px-1.5 py-0.5 text-[10px] font-bold text-rose-700">Por cobrar</span>
                            @else
                                <span class="rounded bg-emerald-100 px-1.5 py-0.5 text-[10px] font-bold text-emerald-700">Al día</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                    <span>Mascotas registradas:</span>
                    <span class="font-bold text-slate-900 text-sm">{{ $propietario->pacientes->count() }}</span>
                </div>
            </div>
        </div>

        <!-- SECCIÓN: MASCOTAS ASOCIADAS AL PROPIETARIO -->
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold tracking-tight text-slate-900">
                        Mascotas Registradas ({{ $propietario->pacientes->count() }})
                    </h2>
                    <p class="text-xs text-slate-500">Fichas clínicas y accesos directos de los pacientes de este propietario</p>
                </div>

                @if ($canWrite && $propietario->pacientes->count() > 0)
                    <x-button variant="secondary" @click="openAddPet()">
                        <i data-lucide="plus" class="size-4 mr-1.5"></i>
                        <span>Agregar Otra Mascota</span>
                    </x-button>
                @endif
            </div>

            <!-- Grid de Mascotas -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @forelse ($propietario->pacientes as $paciente)
                    @php
                        $especieNombre = strtolower($paciente->raza?->especie?->nombre ?? 'otro');
                        $iconName = str_contains($especieNombre, 'gat') ? 'cat' : (str_contains($especieNombre, 'perr') ? 'dog' : 'paw-print');
                        $edad = $paciente->fecha_nacimiento ? \Carbon\Carbon::parse($paciente->fecha_nacimiento)->age . ' años' : 'Edad S/D';
                    @endphp
                    <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card hover:shadow-pop transition-all group flex flex-col justify-between">
                        <div>
                            <!-- Cabecera de la Mascota -->
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    @if ($paciente->foto)
                                        <img
                                            src="{{ asset('storage/' . $paciente->foto) }}"
                                            alt="{{ $paciente->nombre }}"
                                            class="size-11 rounded-2xl object-cover ring-1 ring-indigo-500/20 group-hover:ring-primary-500/30 transition-all"
                                        />
                                    @else
                                        <div class="flex size-11 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 group-hover:bg-primary-600 group-hover:text-white transition-colors ring-1 ring-indigo-500/20 font-bold text-sm">
                                            {{ strtoupper(substr($paciente->nombre, 0, 2)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <h3 class="font-bold text-slate-900 text-base leading-tight group-hover:text-primary-600 transition-colors">
                                            {{ $paciente->nombre }}
                                        </h3>
                                        <p class="text-xs text-slate-400">
                                            {{ $paciente->raza?->especie?->nombre ?? 'Especie S/D' }} · {{ $paciente->raza?->nombre ?? 'Mestizo' }}
                                        </p>
                                    </div>
                                </div>

                                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $paciente->sexo === 'MACHO' ? 'bg-blue-50 text-blue-700' : 'bg-pink-50 text-pink-700' }}">
                                    {{ $paciente->sexo }}
                                </span>
                            </div>

                            <!-- Fila de Atributos Rápidos -->
                            <div class="mt-4 grid grid-cols-2 gap-2 text-xs">
                                <div class="rounded-lg bg-slate-50 p-2">
                                    <span class="text-[10px] uppercase font-bold text-slate-400">Edad / Nac.</span>
                                    <p class="font-semibold text-slate-800">{{ $edad }}</p>
                                </div>
                                <div class="rounded-lg bg-slate-50 p-2">
                                    <span class="text-[10px] uppercase font-bold text-slate-400">Tamaño</span>
                                    <p class="font-semibold text-slate-800">{{ $paciente->tamanio ?: 'S/D' }}</p>
                                </div>
                                <div class="rounded-lg bg-slate-50 p-2">
                                    <span class="text-[10px] uppercase font-bold text-slate-400">Pelaje / Color</span>
                                    <p class="font-semibold text-slate-800 truncate">{{ $paciente->color ?: 'S/D' }}</p>
                                </div>
                                <div class="rounded-lg bg-slate-50 p-2">
                                    <span class="text-[10px] uppercase font-bold text-slate-400">Condición</span>
                                    <p class="font-semibold text-slate-800">{{ $paciente->condicion_reproductiva ?: 'S/D' }}</p>
                                </div>
                            </div>

                            @if ($paciente->microchip)
                                <div class="mt-3 flex items-center gap-1.5 text-xs text-slate-600 bg-slate-50/80 px-2.5 py-1.5 rounded-lg border border-slate-100">
                                    <i data-lucide="cpu" class="size-3.5 text-primary-600"></i>
                                    <span class="font-medium">Chip: {{ $paciente->microchip }}</span>
                                </div>
                            @endif
                        </div>

                        <!-- Botones de Acción de la Mascota -->
                        <div class="mt-5 pt-4 border-t border-slate-100 flex items-center justify-between gap-2">
                            <a
                                href="{{ route('mascotas.show', $paciente->id_paciente) }}"
                                class="flex-1 inline-flex items-center justify-center rounded-xl bg-slate-100 hover:bg-primary-50 hover:text-primary-700 px-3 py-2 text-xs font-bold text-slate-700 transition-colors"
                            >
                                <i data-lucide="file-text" class="size-3.5 mr-1.5 text-primary-600"></i>
                                <span>Ver Ficha Completa</span>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full rounded-2xl border-2 border-dashed border-slate-200 bg-white p-12 text-center">
                        <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-indigo-50 text-indigo-600">
                            <i data-lucide="paw-print" class="size-6"></i>
                        </div>
                        <h3 class="mt-3 text-base font-bold text-slate-900">Aún no hay mascotas registradas</h3>
                        <p class="mt-1 text-xs text-slate-500 max-w-md mx-auto">
                            Este cliente no tiene mascotas vinculadas. Utiliza el botón de abajo para dar de alta su primera mascota con su raza y ficha médica.
                        </p>
                        @if ($canWrite)
                            <div class="mt-5">
                                <x-button variant="primary" @click="openAddPet()">
                                    <i data-lucide="plus" class="size-4 mr-1.5"></i>
                                    <span>Agregar Primera Mascota</span>
                                </x-button>
                            </div>
                        @endif
                    </div>
                @endforelse
            </div>
        </div>

        <!-- MODAL DE AGREGAR MASCOTA (Con Selects Dependientes: Especie -> Raza) -->
        <div
            x-show="addPetModalOpen"
            x-cloak
            class="fixed inset-0 z-50 overflow-y-auto"
            x-transition.opacity
        >
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" @click="addPetModalOpen = false"></div>

                <div
                    class="relative w-full max-w-2xl rounded-2xl bg-white shadow-pop overflow-hidden"
                    x-show="addPetModalOpen"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                >
                    <!-- Cabecera -->
                    <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4 bg-slate-50/50">
                        <div class="flex items-center gap-3">
                            <div class="flex size-9 items-center justify-center rounded-xl bg-primary-50 text-primary-600">
                                <i data-lucide="paw-print" class="size-5"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900">Registrar Nueva Mascota</h3>
                                <p class="text-xs text-slate-500">Asociada al cliente: {{ $nombreCompleto }}</p>
                            </div>
                        </div>

                        <button type="button" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600" @click="addPetModalOpen = false">
                            <i data-lucide="x" class="size-5"></i>
                        </button>
                    </div>

                    <!-- Formulario -->
                    <form action="{{ route('clientes.mascotas.store', $propietario->id_persona) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="max-h-[75vh] overflow-y-auto p-6 space-y-5">
                            <!-- Sección 1: Datos Principales -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <x-field label="Nombre de la Mascota" :error="$errors->first('nombre')" required>
                                    <x-input
                                        type="text"
                                        name="nombre"
                                        x-model="petFormData.nombre"
                                        placeholder="Ej. Toby, Luna"
                                        required
                                        :invalid="$errors->has('nombre')"
                                    />
                                </x-field>

                                <x-field label="Sexo" :error="$errors->first('sexo')" required>
                                    <x-select name="sexo" x-model="petFormData.sexo" required :invalid="$errors->has('sexo')">
                                        <option value="MACHO">MACHO</option>
                                        <option value="HEMBRA">HEMBRA</option>
                                    </x-select>
                                </x-field>

                                <!-- Select Dependiente: Especie -->
                                <x-field label="Especie" required hint="Filtra las razas automáticamente">
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

                                <!-- Select Dependiente: Raza -->
                                <x-field label="Raza" :error="$errors->first('id_raza')" required>
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

                                <x-field label="Fecha de Nacimiento" :error="$errors->first('fecha_nacimiento')">
                                    <x-input
                                        type="date"
                                        name="fecha_nacimiento"
                                        x-model="petFormData.fecha_nacimiento"
                                        :invalid="$errors->has('fecha_nacimiento')"
                                    />
                                </x-field>

                                <x-field label="Tamaño" :error="$errors->first('tamanio')">
                                    <x-select name="tamanio" x-model="petFormData.tamanio">
                                        <option value="PEQUEÑO">PEQUEÑO</option>
                                        <option value="MEDIANO">MEDIANO</option>
                                        <option value="GRANDE">GRANDE</option>
                                        <option value="GIGANTE">GIGANTE</option>
                                    </x-select>
                                </x-field>

                                <x-field label="Condición Reproductiva" :error="$errors->first('condicion_reproductiva')">
                                    <x-select name="condicion_reproductiva" x-model="petFormData.condicion_reproductiva">
                                        <option value="INTACTO">INTACTO</option>
                                        <option value="CASTRADO">CASTRADO</option>
                                    </x-select>
                                </x-field>

                                <x-field label="Color / Marcas" :error="$errors->first('color')">
                                    <x-input
                                        type="text"
                                        name="color"
                                        x-model="petFormData.color"
                                        placeholder="Ej. Marrón con blanco"
                                        :invalid="$errors->has('color')"
                                    />
                                </x-field>

                                <x-field label="Tipo de Pelaje" :error="$errors->first('tipo_pelaje')">
                                    <x-input
                                        type="text"
                                        name="tipo_pelaje"
                                        x-model="petFormData.tipo_pelaje"
                                        placeholder="Ej. Corto, Largo, Rizado"
                                        :invalid="$errors->has('tipo_pelaje')"
                                    />
                                </x-field>

                                <x-field label="Estado del Paciente" :error="$errors->first('estado')" required>
                                    <x-select name="estado" x-model="petFormData.estado" required>
                                        <option value="ACTIVO">ACTIVO</option>
                                        <option value="INACTIVO">INACTIVO</option>
                                        <option value="FALLECIDO">FALLECIDO</option>
                                    </x-select>
                                </x-field>

                                <x-field label="Número de Microchip" :error="$errors->first('microchip')">
                                    <x-input
                                        type="text"
                                        name="microchip"
                                        x-model="petFormData.microchip"
                                        placeholder="Ej. 981098123456789"
                                        :invalid="$errors->has('microchip')"
                                    />
                                </x-field>

                                <x-field label="Fecha Implantación Chip" :error="$errors->first('fecha_implantacion')">
                                    <x-input
                                        type="date"
                                        name="fecha_implantacion"
                                        x-model="petFormData.fecha_implantacion"
                                        :invalid="$errors->has('fecha_implantacion')"
                                    />
                                </x-field>

                                <div class="sm:col-span-2">
                                    <x-field label="Características Particulares / Señas" :error="$errors->first('caract_particulares')">
                                        <x-input
                                            type="text"
                                            name="caract_particulares"
                                            x-model="petFormData.caract_particulares"
                                            placeholder="Ej. Cicatriz en oreja izquierda, mancha en el lomo"
                                            :invalid="$errors->has('caract_particulares')"
                                        />
                                    </x-field>
                                </div>

                                <div class="sm:col-span-2">
                                    <x-field label="Comportamiento Habitual" :error="$errors->first('comportamiento_normal')">
                                        <x-input
                                            type="text"
                                            name="comportamiento_normal"
                                            x-model="petFormData.comportamiento_normal"
                                            placeholder="Ej. Dócil, nervioso en consultas, sociable con otros perros"
                                            :invalid="$errors->has('comportamiento_normal')"
                                        />
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
                                        <span>Autorización de fotos/videos para redes sociales de la clínica</span>
                                    </label>
                                </div>

                                {{-- Subida de foto --}}
                                <div class="sm:col-span-2">
                                    <x-field label="Foto de la Mascota">
                                        <div class="flex items-center gap-4">
                                            <div class="shrink-0">
                                                <template x-if="fotoPreview">
                                                    <img :src="fotoPreview" alt="Previsualización" class="size-16 rounded-xl object-cover ring-2 ring-primary-500/20" />
                                                </template>
                                                <template x-if="!fotoPreview">
                                                    <div class="flex size-16 items-center justify-center rounded-xl bg-slate-100 text-slate-400">
                                                        <i data-lucide="camera" class="size-6"></i>
                                                    </div>
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

                        <!-- Pie del Modal -->
                        <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-4 bg-slate-50/50">
                            <x-button variant="secondary" type="button" @click="addPetModalOpen = false">
                                Cancelar
                            </x-button>

                            <x-button variant="primary" type="submit">
                                <span>Guardar Mascota</span>
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- MODAL DE EDICIÓN DE PROPIETARIO -->
        <div
            x-show="editOwnerModalOpen"
            x-cloak
            class="fixed inset-0 z-50 overflow-y-auto"
            x-transition.opacity
        >
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" @click="editOwnerModalOpen = false"></div>

                <div
                    class="relative w-full max-w-lg rounded-2xl bg-white shadow-pop overflow-hidden"
                    x-show="editOwnerModalOpen"
                    x-transition.scale.95
                >
                    <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4 bg-slate-50/50">
                        <div class="flex items-center gap-3">
                            <div class="flex size-9 items-center justify-center rounded-xl bg-primary-50 text-primary-600">
                                <i data-lucide="user" class="size-5"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900">Editar Propietario</h3>
                                <p class="text-xs text-slate-500">Actualizar datos de contacto del cliente</p>
                            </div>
                        </div>

                        <button type="button" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600" @click="editOwnerModalOpen = false">
                            <i data-lucide="x" class="size-5"></i>
                        </button>
                    </div>

                    <form action="{{ route('clientes.update', $propietario->id_persona) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="p-6 space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <x-field label="Documento (CI)" required>
                                    <x-input type="text" name="documento" x-model="ownerFormData.documento" required />
                                </x-field>

                                <x-field label="Nombres" required>
                                    <x-input type="text" name="nombre" x-model="ownerFormData.nombre" required />
                                </x-field>

                                <x-field label="Primer Apellido" required>
                                    <x-input type="text" name="apellido1" x-model="ownerFormData.apellido1" required />
                                </x-field>

                                <x-field label="Segundo Apellido">
                                    <x-input type="text" name="apellido2" x-model="ownerFormData.apellido2" />
                                </x-field>

                                <x-field label="Teléfono">
                                    <x-input type="text" name="telefono" x-model="ownerFormData.telefono" />
                                </x-field>

                                <x-field label="Correo Electrónico">
                                    <x-input type="email" name="email" x-model="ownerFormData.email" />
                                </x-field>

                                <div class="sm:col-span-2">
                                    <x-field label="Dirección">
                                        <x-input type="text" name="direccion" x-model="ownerFormData.direccion" />
                                    </x-field>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-4 bg-slate-50/50">
                            <x-button variant="secondary" type="button" @click="editOwnerModalOpen = false">
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
