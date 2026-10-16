<x-layout title="Catálogo de Especies y Razas — La Petdiatría">
    <div
        x-data="{
            especieModalOpen: false,
            isEditingEspecie: false,
            especieFormAction: '{{ route('catalogo.especies.store') }}',
            especieFormData: { id_especie: '', nombre: '' },
            openCreateEspecie() {
                this.isEditingEspecie = false;
                this.especieFormAction = '{{ route('catalogo.especies.store') }}';
                this.especieFormData = { id_especie: '', nombre: '' };
                this.especieModalOpen = true;
            },
            openEditEspecie(esp) {
                this.isEditingEspecie = true;
                this.especieFormAction = '/catalogo/especies/' + esp.id_especie;
                this.especieFormData = { id_especie: esp.id_especie, nombre: esp.nombre };
                this.especieModalOpen = true;
            },

            razaModalOpen: false,
            isEditingRaza: false,
            razaFormAction: '{{ route('catalogo.razas.store') }}',
            razaFormData: { id_raza: '', id_especie: '{{ $especies->first()?->id_especie }}', nombre: '' },
            openCreateRaza(idEspecie = null) {
                this.isEditingRaza = false;
                this.razaFormAction = '{{ route('catalogo.razas.store') }}';
                this.razaFormData = {
                    id_raza: '',
                    id_especie: idEspecie || '{{ $especies->first()?->id_especie }}',
                    nombre: ''
                };
                this.razaModalOpen = true;
            },
            openEditRaza(raza) {
                this.isEditingRaza = true;
                this.razaFormAction = '/catalogo/razas/' + raza.id_raza;
                this.razaFormData = {
                    id_raza: raza.id_raza,
                    id_especie: raza.id_especie,
                    nombre: raza.nombre
                };
                this.razaModalOpen = true;
            }
        }"
        @keydown.escape.window="if(especieModalOpen) especieModalOpen = false; if(razaModalOpen) razaModalOpen = false;"
        class="space-y-6"
    >
        <!-- Encabezado -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <a
                    href="{{ route('clientes.index') }}"
                    class="inline-flex size-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 hover:bg-slate-50 hover:text-slate-800 transition-colors shadow-2xs"
                    title="Volver a Clientes y Mascotas"
                >
                    <i data-lucide="arrow-left" class="size-4"></i>
                </a>
                <div>
                    <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-slate-400">
                        <span>Configuración de Catálogo</span>
                        <span>·</span>
                        <span>Taxonomía Veterinaria</span>
                    </div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                        Especies y Razas
                    </h1>
                </div>
            </div>

            @if ($canWrite)
                <div class="flex items-center gap-3">
                    <x-button variant="secondary" @click="openCreateEspecie()">
                        <i data-lucide="folder-plus" class="size-4 mr-1.5"></i>
                        <span>Nueva Especie</span>
                    </x-button>

                    <x-button variant="primary" @click="openCreateRaza()">
                        <i data-lucide="tag" class="size-4 mr-1.5"></i>
                        <span>Nueva Raza</span>
                    </x-button>
                </div>
            @endif
        </div>

        <!-- Banner informativo -->
        <div class="flex items-start gap-3.5 rounded-2xl border border-indigo-100 bg-indigo-50/50 p-4">
            <div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-primary-100 text-primary-700">
                <i data-lucide="dna" class="size-5"></i>
            </div>
            <div class="text-xs text-slate-600 leading-relaxed">
                <strong class="font-bold text-slate-900">Catálogo taxonómico del sistema:</strong>
                Las especies y razas aquí registradas alimentan de manera dinámica los formularios de alta y edición de pacientes veterinarios, así como los tiempos estimados de atención clínica y estética.
            </div>
        </div>

        <!-- Grid de Especies y sus Razas Asociadas -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($especies as $esp)
                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card flex flex-col justify-between">
                    <div>
                        <!-- Cabecera de la Especie -->
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                            <div class="flex items-center gap-2.5">
                                <div class="flex size-9 items-center justify-center rounded-xl bg-primary-50 text-primary-600 font-bold">
                                    <i data-lucide="paw-print" class="size-4.5"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-slate-900 text-base">{{ $esp->nombre }}</h3>
                                    <span class="text-xs text-slate-400">{{ $esp->razas->count() }} razas configuradas</span>
                                </div>
                            </div>

                            @if ($canWrite)
                                <div class="flex items-center gap-1">
                                    <button
                                        type="button"
                                        @click="openEditEspecie(@js($esp))"
                                        class="inline-flex size-7 items-center justify-center rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-100 hover:text-slate-800 transition-colors"
                                        title="Editar nombre de especie"
                                    >
                                        <i data-lucide="pencil" class="size-3.5"></i>
                                    </button>
                                    <button
                                        type="button"
                                        @click="openCreateRaza({{ $esp->id_especie }})"
                                        class="inline-flex size-7 items-center justify-center rounded-lg bg-primary-50 text-primary-700 hover:bg-primary-100 transition-colors"
                                        title="Agregar raza a esta especie"
                                    >
                                        <i data-lucide="plus" class="size-3.5"></i>
                                    </button>
                                </div>
                            @endif
                        </div>

                        <!-- Lista de Razas -->
                        <div class="mt-4 space-y-1.5 max-h-72 overflow-y-auto pr-1">
                            @forelse ($esp->razas as $raza)
                                <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50/60 px-3 py-2 text-xs hover:bg-slate-100/70 transition-colors group">
                                    <div class="flex items-center gap-2">
                                        <span class="size-1.5 rounded-full bg-primary-500"></span>
                                        <span class="font-semibold text-slate-800">{{ $raza->nombre }}</span>
                                        <span class="text-[10px] text-slate-400">({{ $raza->pacientes_count ?? 0 }} pacientes)</span>
                                    </div>

                                    @if ($canWrite)
                                        <button
                                            type="button"
                                            @click="openEditRaza(@js($raza))"
                                            class="opacity-0 group-hover:opacity-100 inline-flex size-5 items-center justify-center text-slate-400 hover:text-primary-600 transition-all"
                                            title="Editar raza"
                                        >
                                            <i data-lucide="pencil" class="size-3"></i>
                                        </button>
                                    @endif
                                </div>
                            @empty
                                <div class="py-6 text-center text-xs text-slate-400 italic">
                                    Sin razas asociadas aún.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    @if ($canWrite)
                        <div class="mt-4 pt-3 border-t border-slate-100">
                            <button
                                type="button"
                                @click="openCreateRaza({{ $esp->id_especie }})"
                                class="w-full inline-flex items-center justify-center gap-1.5 rounded-xl border border-dashed border-slate-300 py-1.5 text-xs font-semibold text-slate-600 hover:border-primary-400 hover:text-primary-600 hover:bg-primary-50/30 transition-all"
                            >
                                <i data-lucide="plus" class="size-3.5"></i>
                                <span>Añadir Raza a {{ $esp->nombre }}</span>
                            </button>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <!-- MODAL ESPECIE -->
        <div
            x-show="especieModalOpen"
            x-cloak
            class="fixed inset-0 z-50 overflow-y-auto"
            x-transition.opacity
        >
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" @click="especieModalOpen = false"></div>

                <div
                    class="relative w-full max-w-md rounded-2xl bg-white shadow-pop overflow-hidden"
                    x-show="especieModalOpen"
                    x-transition.scale.95
                >
                    <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4 bg-slate-50/50">
                        <div class="flex items-center gap-3">
                            <div class="flex size-9 items-center justify-center rounded-xl bg-primary-50 text-primary-600">
                                <i data-lucide="paw-print" class="size-5"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900" x-text="isEditingEspecie ? 'Editar Especie' : 'Nueva Especie'"></h3>
                                <p class="text-xs text-slate-500">Clasificación taxonómica principal</p>
                            </div>
                        </div>

                        <button type="button" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600" @click="especieModalOpen = false">
                            <i data-lucide="x" class="size-5"></i>
                        </button>
                    </div>

                    <form :action="especieFormAction" method="POST">
                        @csrf
                        <template x-if="isEditingEspecie">
                            <input type="hidden" name="_method" value="PUT">
                        </template>

                        <div class="p-6">
                            <x-field label="Nombre de la Especie" required hint="Ej. Canino, Felino, Conejo, Ave">
                                <x-input type="text" name="nombre" x-model="especieFormData.nombre" placeholder="Ej. Equino" required />
                            </x-field>
                        </div>

                        <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-4 bg-slate-50/50">
                            <x-button variant="secondary" type="button" @click="especieModalOpen = false">
                                Cancelar
                            </x-button>

                            <x-button variant="primary" type="submit">
                                <span x-text="isEditingEspecie ? 'Guardar Cambios' : 'Crear Especie'"></span>
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- MODAL RAZA -->
        <div
            x-show="razaModalOpen"
            x-cloak
            class="fixed inset-0 z-50 overflow-y-auto"
            x-transition.opacity
        >
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" @click="razaModalOpen = false"></div>

                <div
                    class="relative w-full max-w-md rounded-2xl bg-white shadow-pop overflow-hidden"
                    x-show="razaModalOpen"
                    x-transition.scale.95
                >
                    <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4 bg-slate-50/50">
                        <div class="flex items-center gap-3">
                            <div class="flex size-9 items-center justify-center rounded-xl bg-primary-50 text-primary-600">
                                <i data-lucide="tag" class="size-5"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900" x-text="isEditingRaza ? 'Editar Raza' : 'Nueva Raza'"></h3>
                                <p class="text-xs text-slate-500">Subdivisión de raza por especie</p>
                            </div>
                        </div>

                        <button type="button" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600" @click="razaModalOpen = false">
                            <i data-lucide="x" class="size-5"></i>
                        </button>
                    </div>

                    <form :action="razaFormAction" method="POST">
                        @csrf
                        <template x-if="isEditingRaza">
                            <input type="hidden" name="_method" value="PUT">
                        </template>

                        <div class="p-6 space-y-4">
                            <x-field label="Especie Perteneciente" required>
                                <select
                                    name="id_especie"
                                    x-model="razaFormData.id_especie"
                                    class="block w-full rounded-xl border-0 bg-white py-2 pl-3.5 pr-8 text-sm text-slate-700 shadow-sm ring-1 ring-inset ring-slate-200 focus-ring"
                                    required
                                >
                                    @foreach ($especies as $esp)
                                        <option value="{{ $esp->id_especie }}">{{ $esp->nombre }}</option>
                                    @endforeach
                                </select>
                            </x-field>

                            <x-field label="Nombre de la Raza" required hint="Ej. Golden Retriever, Siamés, Mestizo">
                                <x-input type="text" name="nombre" x-model="razaFormData.nombre" placeholder="Ej. Beagle" required />
                            </x-field>
                        </div>

                        <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-4 bg-slate-50/50">
                            <x-button variant="secondary" type="button" @click="razaModalOpen = false">
                                Cancelar
                            </x-button>

                            <x-button variant="primary" type="submit">
                                <span x-text="isEditingRaza ? 'Guardar Cambios' : 'Crear Raza'"></span>
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-layout>
