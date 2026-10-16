<x-layout title="Clientes y Mascotas — La Petdiatría">
    <div
        x-data="{
            clientModalOpen: {{ $errors->has('documento') || $errors->has('nombre') || $errors->has('apellido1') ? 'true' : 'false' }},
            isEditing: {{ old('_is_editing') ? 'true' : 'false' }},
            modalTitle: 'Nuevo Propietario / Cliente',
            formAction: @js(old('_form_action', route('clientes.store'))),
            formMethod: @js(old('_form_method', 'POST')),
            clientFormData: {
                id_persona: @js(old('id_persona', '')),
                documento: @js(old('documento', '')),
                nombre: @js(old('nombre', '')),
                apellido1: @js(old('apellido1', '')),
                apellido2: @js(old('apellido2', '')),
                telefono: @js(old('telefono', '')),
                email: @js(old('email', '')),
                direccion: @js(old('direccion', '')),
            },
            openCreate() {
                this.isEditing = false;
                this.modalTitle = 'Nuevo Propietario / Cliente';
                this.formAction = '{{ route('clientes.store') }}';
                this.formMethod = 'POST';
                this.clientFormData = {
                    id_persona: '',
                    documento: '',
                    nombre: '',
                    apellido1: '',
                    apellido2: '',
                    telefono: '',
                    email: '',
                    direccion: '',
                };
                this.clientModalOpen = true;
            },
            openEdit(prop) {
                this.isEditing = true;
                this.modalTitle = 'Editar Cliente: ' + (prop.persona ? prop.persona.nombre + ' ' + prop.persona.apellido1 : '');
                this.formAction = '/clientes/' + prop.id_persona;
                this.formMethod = 'PUT';
                this.clientFormData = {
                    id_persona: prop.id_persona,
                    documento: prop.persona ? prop.persona.documento : '',
                    nombre: prop.persona ? prop.persona.nombre : '',
                    apellido1: prop.persona ? prop.persona.apellido1 : '',
                    apellido2: prop.persona ? (prop.persona.apellido2 || '') : '',
                    telefono: prop.persona ? (prop.persona.telefono || '') : '',
                    email: prop.persona ? (prop.persona.email || '') : '',
                    direccion: prop.persona ? (prop.persona.direccion || '') : '',
                };
                this.clientModalOpen = true;
            },
            closeModal() {
                this.clientModalOpen = false;
            }
        }"
        @keydown.escape.window="if(clientModalOpen) closeModal()"
        class="space-y-6"
    >
        <!-- Encabezado con información del módulo y botón de acción principal -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-primary-600">
                    <i data-lucide="paw-print" class="size-4"></i>
                    <span>Recepción y Gestión de Pacientes</span>
                </div>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                    Clientes y Mascotas
                </h1>
                <p class="mt-1 text-sm text-slate-500 max-w-2xl">
                    Directorio de propietarios, fichas de mascotas asociadas, historial clínico vinculado y acceso a citas.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <a
                    href="{{ route('catalogo.especies-razas') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm font-medium text-slate-700 shadow-xs hover:bg-slate-50 hover:text-slate-900 transition-colors"
                >
                    <i data-lucide="dna" class="size-4 mr-2 text-slate-500"></i>
                    <span>Especies y Razas</span>
                </a>

                @if ($canWrite)
                    <x-button variant="primary" @click="openCreate()">
                        <i data-lucide="user-plus" class="size-4 mr-2"></i>
                        <span>Nuevo Propietario</span>
                    </x-button>
                @endif
            </div>
        </div>

        <!-- Fila de métricas rápidas del módulo -->
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-card">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Propietarios</span>
                    <div class="flex size-7 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                        <i data-lucide="users" class="size-4"></i>
                    </div>
                </div>
                <p class="mt-2 text-2xl font-bold text-slate-900">{{ $totalPropietarios }}</p>
                <p class="text-xs text-slate-500 mt-0.5">Clientes registrados</p>
            </div>

            <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-card">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Mascotas</span>
                    <div class="flex size-7 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                        <i data-lucide="heart" class="size-4"></i>
                    </div>
                </div>
                <p class="mt-2 text-2xl font-bold text-emerald-600">{{ $totalMascotas }}</p>
                <p class="text-xs text-slate-500 mt-0.5">Pacientes en sistema</p>
            </div>

            <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-card">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Mascotas Activas</span>
                    <div class="flex size-7 items-center justify-center rounded-lg bg-amber-50 text-amber-600">
                        <i data-lucide="activity" class="size-4"></i>
                    </div>
                </div>
                <p class="mt-2 text-2xl font-bold text-slate-900">{{ $mascotasActivas }}</p>
                <p class="text-xs text-slate-500 mt-0.5">En seguimiento regular</p>
            </div>

            <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-card">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Especies</span>
                    <div class="flex size-7 items-center justify-center rounded-lg bg-violet-50 text-violet-600">
                        <i data-lucide="tag" class="size-4"></i>
                    </div>
                </div>
                <p class="mt-2 text-2xl font-bold text-slate-900">{{ $totalEspecies }}</p>
                <p class="text-xs text-slate-500 mt-0.5">Catálogo registrado</p>
            </div>
        </div>

        <!-- Contenedor principal del listado -->
        <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-card space-y-5">
            <!-- Barra de Búsqueda y Filtros -->
            <form action="{{ route('clientes.index') }}" method="GET" class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex flex-1 flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    <!-- Buscador -->
                    <div class="relative flex-1">
                        <i data-lucide="search" class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 size-4 text-slate-400"></i>
                        <input
                            type="text"
                            name="search"
                            value="{{ $filters['search'] ?? '' }}"
                            placeholder="Buscar por nombre, apellido, CI, teléfono o nombre de mascota..."
                            class="block w-full rounded-xl border-0 py-2 pl-10 pr-4 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-200 placeholder:text-slate-400 focus-ring"
                        />
                    </div>

                    <!-- Filtro por posesión de mascotas -->
                    <div class="sm:w-52">
                        <select
                            name="con_mascotas"
                            onchange="this.form.submit()"
                            class="block w-full rounded-xl border-0 bg-white py-2 pl-3.5 pr-8 text-sm text-slate-700 shadow-sm ring-1 ring-inset ring-slate-200 focus-ring"
                        >
                            <option value="">Todas las cuentas</option>
                            <option value="si" {{ ($filters['con_mascotas'] ?? '') === 'si' ? 'selected' : '' }}>Con mascotas asociadas</option>
                            <option value="no" {{ ($filters['con_mascotas'] ?? '') === 'no' ? 'selected' : '' }}>Sin mascotas registradas</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <x-button variant="secondary" type="submit">
                        <i data-lucide="filter" class="size-4 mr-1.5"></i>
                        <span>Filtrar</span>
                    </x-button>

                    @if (!empty($filters['search']) || !empty($filters['con_mascotas']))
                        <a
                            href="{{ route('clientes.index') }}"
                            class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-500 hover:bg-slate-50 hover:text-slate-700 transition-colors"
                            title="Limpiar filtros"
                        >
                            <i data-lucide="rotate-ccw" class="size-4"></i>
                        </a>
                    @endif
                </div>
            </form>

            <!-- Tabla de Propietarios y Mascotas -->
            <div class="overflow-hidden rounded-xl border border-slate-200/80 bg-white shadow-card">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200/80 text-left text-sm">
                        <thead class="bg-slate-50/75 text-xs font-semibold uppercase tracking-wider text-slate-500">
                            <tr>
                                <th scope="col" class="px-5 py-3.5 whitespace-nowrap">Propietario / Cliente</th>
                                <th scope="col" class="px-5 py-3.5 whitespace-nowrap">Documento (CI)</th>
                                <th scope="col" class="px-5 py-3.5 whitespace-nowrap">Contacto</th>
                                <th scope="col" class="px-5 py-3.5 whitespace-nowrap">Mascotas Asociadas</th>
                                <th scope="col" class="px-5 py-3.5 whitespace-nowrap text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white text-slate-700">
                            @forelse ($propietarios as $prop)
                                @php
                                    $persona = $prop->persona;
                                    $nombreCompleto = $persona ? trim("{$persona->nombre} {$persona->apellido1} {$persona->apellido2}") : 'Sin datos';
                                    $initials = $persona ? strtoupper(substr($persona->nombre, 0, 1) . substr($persona->apellido1, 0, 1)) : 'CL';
                                    $mascotasCount = $prop->pacientes->count();
                                @endphp
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <!-- Propietario -->
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-primary-50 text-primary-700 font-bold text-xs ring-2 ring-primary-500/20">
                                                {{ $initials }}
                                            </div>
                                            <div>
                                                <a href="{{ route('clientes.show', $prop->id_persona) }}" class="font-bold text-slate-900 hover:text-primary-600 transition-colors">
                                                    {{ $nombreCompleto }}
                                                </a>
                                                <div class="text-xs text-slate-400">ID Cliente: #{{ $prop->id_persona }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- CI -->
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-1.5 font-medium text-slate-800 text-xs">
                                            <i data-lucide="id-card" class="size-3.5 text-slate-400"></i>
                                            <span>{{ $persona?->documento ?: 'S/D' }}</span>
                                        </div>
                                    </td>

                                    <!-- Contacto -->
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <div class="space-y-0.5 text-xs">
                                            @if ($persona?->telefono)
                                                <div class="flex items-center gap-1.5 text-slate-700 font-medium">
                                                    <i data-lucide="phone" class="size-3.5 text-slate-400"></i>
                                                    <span>{{ $persona->telefono }}</span>
                                                </div>
                                            @endif
                                            @if ($persona?->email)
                                                <div class="flex items-center gap-1.5 text-slate-500">
                                                    <i data-lucide="mail" class="size-3.5 text-slate-400"></i>
                                                    <span>{{ $persona->email }}</span>
                                                </div>
                                            @endif
                                            @if (!$persona?->telefono && !$persona?->email)
                                                <span class="text-slate-400 italic">Sin datos de contacto</span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Mascotas -->
                                    <td class="px-5 py-4">
                                        @if ($mascotasCount > 0)
                                            <div class="flex flex-wrap items-center gap-1.5">
                                                @foreach ($prop->pacientes as $paciente)
                                                    @php
                                                        $especieNombre = strtolower($paciente->raza?->especie?->nombre ?? 'otro');
                                                        $iconName = str_contains($especieNombre, 'gat') ? 'cat' : (str_contains($especieNombre, 'perr') ? 'dog' : 'paw-print');
                                                    @endphp
                                                    <a
                                                        href="{{ route('mascotas.show', $paciente->id_paciente) }}"
                                                        class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200/80 bg-slate-50/80 px-2.5 py-1 text-xs font-semibold text-slate-700 hover:border-primary-300 hover:bg-primary-50/50 hover:text-primary-700 transition-colors"
                                                        title="Ver ficha de {{ $paciente->nombre }} ({{ $paciente->raza?->nombre }})"
                                                    >
                                                        <i data-lucide="{{ $iconName }}" class="size-3 text-primary-600"></i>
                                                        <span>{{ $paciente->nombre }}</span>
                                                        <span class="text-[10px] text-slate-400 font-normal">({{ $paciente->raza?->nombre ?? 'Mestizo' }})</span>
                                                    </a>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-xs text-amber-600 font-medium">
                                                <i data-lucide="alert-circle" class="size-3.5"></i>
                                                <span>Sin mascotas registradas</span>
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Acciones -->
                                    <td class="px-5 py-4 whitespace-nowrap text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <!-- Ver Ficha Propietario -->
                                            <a
                                                href="{{ route('clientes.show', $prop->id_persona) }}"
                                                class="inline-flex size-8 items-center justify-center rounded-lg border border-slate-200 text-slate-600 hover:bg-primary-50 hover:border-primary-300 hover:text-primary-600 transition-colors"
                                                title="Ver Ficha y Mascotas"
                                            >
                                                <i data-lucide="eye" class="size-4"></i>
                                            </a>

                                            @if ($canWrite)
                                                <!-- Editar Propietario -->
                                                <button
                                                    type="button"
                                                    @click="openEdit(@js($prop))"
                                                    class="inline-flex size-8 items-center justify-center rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-colors"
                                                    title="Editar datos de contacto"
                                                >
                                                    <i data-lucide="pencil" class="size-4"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="p-8">
                                        <x-empty-state
                                            icon="users"
                                            title="No se encontraron propietarios"
                                            description="No hay clientes registrados que coincidan con los criterios de búsqueda aplicados."
                                        />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($propietarios->hasPages())
                    <div class="border-t border-slate-200/80 px-5 py-3.5 bg-slate-50/40">
                        {{ $propietarios->links() }}
                    </div>
                @endif
            </div>
        </div>

        <!-- MODAL DE ALTA / EDICIÓN DE PROPIETARIO -->
        <div
            x-show="clientModalOpen"
            x-cloak
            class="fixed inset-0 z-50 overflow-y-auto"
            x-transition.opacity
        >
            <div class="flex min-h-full items-center justify-center p-4">
                <!-- Fondo oscuro backdrop -->
                <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" @click="closeModal()"></div>

                <!-- Contenedor del Modal -->
                <div
                    class="relative w-full max-w-xl rounded-2xl bg-white shadow-pop overflow-hidden"
                    x-show="clientModalOpen"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                >
                    <!-- Cabecera del Modal -->
                    <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4 bg-slate-50/50">
                        <div class="flex items-center gap-3">
                            <div class="flex size-9 items-center justify-center rounded-xl bg-primary-50 text-primary-600">
                                <i data-lucide="user" class="size-5"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900" x-text="modalTitle"></h3>
                                <p class="text-xs text-slate-500">Información del titular y datos de contacto</p>
                            </div>
                        </div>

                        <button type="button" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600" @click="closeModal()">
                            <i data-lucide="x" class="size-5"></i>
                        </button>
                    </div>

                    <!-- Formulario de Guardado -->
                    <form :action="formAction" method="POST">
                        @csrf
                        <template x-if="isEditing">
                            <input type="hidden" name="_method" value="PUT">
                        </template>

                        <div class="p-6 space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <x-field label="Documento de Identidad (CI)" :error="$errors->first('documento')" required>
                                    <x-input
                                        type="text"
                                        name="documento"
                                        x-model="clientFormData.documento"
                                        placeholder="Ej. 12345678"
                                        required
                                        :invalid="$errors->has('documento')"
                                    />
                                </x-field>

                                <x-field label="Nombres" :error="$errors->first('nombre')" required>
                                    <x-input
                                        type="text"
                                        name="nombre"
                                        x-model="clientFormData.nombre"
                                        placeholder="Ej. Laura"
                                        required
                                        :invalid="$errors->has('nombre')"
                                    />
                                </x-field>

                                <x-field label="Primer Apellido" :error="$errors->first('apellido1')" required>
                                    <x-input
                                        type="text"
                                        name="apellido1"
                                        x-model="clientFormData.apellido1"
                                        placeholder="Ej. Gonzales"
                                        required
                                        :invalid="$errors->has('apellido1')"
                                    />
                                </x-field>

                                <x-field label="Segundo Apellido" :error="$errors->first('apellido2')">
                                    <x-input
                                        type="text"
                                        name="apellido2"
                                        x-model="clientFormData.apellido2"
                                        placeholder="Ej. Paredes"
                                        :invalid="$errors->has('apellido2')"
                                    />
                                </x-field>

                                <x-field label="Teléfono / Celular" :error="$errors->first('telefono')">
                                    <x-input
                                        type="text"
                                        name="telefono"
                                        x-model="clientFormData.telefono"
                                        placeholder="Ej. 71234567"
                                        :invalid="$errors->has('telefono')"
                                    />
                                </x-field>

                                <x-field label="Correo Electrónico" :error="$errors->first('email')">
                                    <x-input
                                        type="email"
                                        name="email"
                                        x-model="clientFormData.email"
                                        placeholder="cliente@email.com"
                                        :invalid="$errors->has('email')"
                                    />
                                </x-field>

                                <div class="sm:col-span-2">
                                    <x-field label="Dirección de Domicilio" :error="$errors->first('direccion')">
                                        <x-input
                                            type="text"
                                            name="direccion"
                                            x-model="clientFormData.direccion"
                                            placeholder="Ej. Calle Potosí #753, Zona Central"
                                            :invalid="$errors->has('direccion')"
                                        />
                                    </x-field>
                                </div>
                            </div>
                        </div>

                        <!-- Pie del Modal -->
                        <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-4 bg-slate-50/50">
                            <x-button variant="secondary" type="button" @click="closeModal()">
                                Cancelar
                            </x-button>

                            <x-button variant="primary" type="submit">
                                <span x-text="isEditing ? 'Guardar Cambios' : 'Registrar Cliente'"></span>
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-layout>
