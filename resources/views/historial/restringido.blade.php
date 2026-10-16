<x-layout title="Información Médica Restringida — La Petdiatría">
    <div class="max-w-2xl mx-auto my-12 text-center space-y-6">
        <div class="mx-auto flex size-20 items-center justify-center rounded-3xl bg-rose-50 text-rose-600 shadow-sm ring-1 ring-rose-500/20">
            <i data-lucide="lock" class="size-10"></i>
        </div>

        <div class="space-y-2">
            <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-600/20">
                <i data-lucide="shield-alert" class="size-3.5"></i>
                <span>Acceso Restringido por Rol (RBAC)</span>
            </span>
            <h1 class="text-3xl font-extrabold tracking-tight text-slate-900">
                Expediente Clínico Reservado
            </h1>
            <p class="text-sm text-slate-500 max-w-lg mx-auto leading-relaxed">
                {{ $motivo ?? 'Los diagnósticos, tratamientos médicos, anamnesis y cirugías forman parte de la información clínica confidencial reservada exclusivamente para el personal Médico Veterinario y Super-Administrador.' }}
            </p>
        </div>

        @if (isset($paciente))
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card text-left flex items-center justify-between">
                <div class="flex items-center gap-3.5">
                    <div class="flex size-11 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 font-bold">
                        <i data-lucide="paw-print" class="size-6"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-900 text-sm">{{ $paciente->nombre }}</h4>
                        <p class="text-xs text-slate-400">
                            {{ $paciente->raza?->especie?->nombre ?? 'Especie S/D' }} · {{ $paciente->raza?->nombre ?? 'Mestizo' }}
                        </p>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Propietario: {{ $paciente->propietario?->persona?->nombre }} {{ $paciente->propietario?->persona?->apellido1 }}
                        </p>
                    </div>
                </div>

                <a
                    href="{{ route('mascotas.show', $paciente->id_paciente) }}"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors"
                >
                    <span>Ver Ficha Básica</span>
                </a>
            </div>
        @endif

        <div class="pt-4 flex items-center justify-center gap-3">
            <a
                href="{{ route('dashboard') }}"
                class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-slate-800 transition-colors shadow-sm"
            >
                <i data-lucide="arrow-left" class="size-4 mr-2"></i>
                <span>Volver al Dashboard</span>
            </a>

            @if (isset($paciente))
                <a
                    href="{{ route('clientes.show', $paciente->id_propietario) }}"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors"
                >
                    <span>Ficha del Propietario</span>
                </a>
            @endif
        </div>
    </div>
</x-layout>
