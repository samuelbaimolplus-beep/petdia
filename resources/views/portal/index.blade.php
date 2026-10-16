<x-layout title="Portal del Propietario — La Petdiatría">
    <div x-data="{ modalSolicitarCita: false }" class="space-y-6">
        <x-breadcrumb :items="[
            ['label' => 'Inicio', 'url' => route('dashboard')],
            ['label' => 'Portal del Propietario', 'url' => null],
        ]" />

        <!-- Banner de Bienvenida -->
        <div class="relative overflow-hidden rounded-3xl bg-linear-to-r from-primary-600 via-primary-700 to-teal-700 p-6 sm:p-8 text-white shadow-lg">
            <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div class="max-w-xl space-y-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-white/20 backdrop-blur-md text-white border border-white/30">
                        <i data-lucide="sparkles" class="size-3.5"></i>
                        Portal de Autoservicio
                    </span>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                        ¡Hola, {{ $currentUser->name }}!
                    </h1>
                    <p class="text-xs sm:text-sm text-primary-100 leading-relaxed">
                        Bienvenido a tu espacio personal. Consulta la salud de tus consentidos, descarga sus carnets de vacunación y recetas médicas, o agenda tu próxima visita veterinaria en un instante.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <button
                        type="button"
                        @click="modalSolicitarCita = true"
                        class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-white text-primary-700 hover:bg-primary-50 text-xs sm:text-sm font-bold shadow-md transition-all hover:scale-105 active:scale-95"
                    >
                        <i data-lucide="calendar-plus" class="size-4 text-primary-600"></i>
                        <span>Solicitar Cita Online</span>
                    </button>
                </div>
            </div>
            <!-- Círculos decorativos de fondo -->
            <div class="absolute -right-12 -bottom-12 w-64 h-64 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
            <div class="absolute right-48 -top-12 w-48 h-48 bg-teal-400/20 rounded-full blur-xl pointer-events-none"></div>
        </div>

        @if(session('success'))
            <div class="flex items-center gap-3 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium">
                <i data-lucide="check-circle" class="size-5 text-emerald-600 shrink-0"></i>
                <p>{{ session('success') }}</p>
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-medium space-y-1">
                <div class="flex items-center gap-2 font-semibold">
                    <i data-lucide="alert-circle" class="size-4 text-rose-600"></i>
                    <span>Por favor corrige los siguientes errores:</span>
                </div>
                <ul class="list-disc pl-5 space-y-0.5">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- SECCIÓN 1: MIS MASCOTAS -->
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="p-2 rounded-xl bg-primary-100 text-primary-700">
                        <i data-lucide="paw-print" class="size-5"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Mis Mascotas</h2>
                        <p class="text-xs text-slate-500">Historial de salud y carnet digital de tus compañeros</p>
                    </div>
                </div>
                <span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-semibold">
                    {{ $pacientes->count() }} {{ Str::plural('mascota', $pacientes->count()) }}
                </span>
            </div>

            @if($pacientes->isEmpty())
                <div class="p-8 text-center rounded-3xl border border-dashed border-slate-300 bg-white">
                    <i data-lucide="heart-handshake" class="size-12 mx-auto text-slate-300 mb-3"></i>
                    <h3 class="text-sm font-bold text-slate-700">Aún no tienes mascotas registradas</h3>
                    <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                        Cuando visites la veterinaria o el personal registre a tus mascotas a tu nombre, podrás ver aquí todo su historial clínico.
                    </p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach($pacientes as $paciente)
                        <div class="relative flex flex-col justify-between rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm hover:shadow-md transition-all group">
                            <div>
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        <div class="size-14 rounded-2xl bg-linear-to-tr from-primary-50 to-teal-50 border border-primary-200/60 flex items-center justify-center text-primary-700 shadow-inner group-hover:scale-105 transition-transform">
                                            @if(strtolower($paciente->raza?->especie?->nombre ?? '') === 'felino' || strtolower($paciente->raza?->especie?->nombre ?? '') === 'gato')
                                                <i data-lucide="cat" class="size-7"></i>
                                            @else
                                                <i data-lucide="dog" class="size-7"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <h3 class="text-base font-bold text-slate-900 group-hover:text-primary-600 transition-colors">
                                                {{ $paciente->nombre }}
                                            </h3>
                                            <p class="text-xs font-medium text-slate-500">
                                                {{ $paciente->raza?->nombre ?? 'Raza no especificada' }}
                                                <span class="text-slate-300">·</span>
                                                <span class="text-slate-400">{{ $paciente->raza?->especie?->nombre ?? 'Especie' }}</span>
                                            </p>
                                        </div>
                                    </div>
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $paciente->sexo === 'M' ? 'bg-blue-50 text-blue-700 border border-blue-200/60' : 'bg-pink-50 text-pink-700 border border-pink-200/60' }}">
                                        {{ $paciente->sexo === 'M' ? 'Macho' : 'Hembra' }}
                                    </span>
                                </div>

                                <div class="grid grid-cols-2 gap-2 mt-4 pt-4 border-t border-slate-100 text-xs">
                                    <div class="bg-slate-50 p-2.5 rounded-xl">
                                        <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block">Edad</span>
                                        <span class="font-bold text-slate-700">
                                            @if($paciente->fecha_nacimiento)
                                                {{ \Carbon\Carbon::parse($paciente->fecha_nacimiento)->diffForHumans(null, true) }}
                                            @else
                                                No especificada
                                            @endif
                                        </span>
                                    </div>
                                    <div class="bg-slate-50 p-2.5 rounded-xl">
                                        <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block">Estado</span>
                                        <span class="font-bold text-emerald-600 flex items-center gap-1">
                                            <span class="size-1.5 rounded-full bg-emerald-500"></span>
                                            {{ $paciente->estado ?? 'ACTIVO' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-5 pt-3 border-t border-slate-100 flex items-center gap-2">
                                <a
                                    href="{{ route('portal.mascota', $paciente->id_paciente) }}"
                                    class="flex-1 inline-flex items-center justify-center gap-1.5 py-2.5 px-3 rounded-xl bg-primary-50 hover:bg-primary-100 text-primary-700 text-xs font-bold transition-all text-center"
                                >
                                    <i data-lucide="clipboard-plus" class="size-3.5"></i>
                                    <span>Ficha de Salud</span>
                                </a>
                                <a
                                    href="{{ route('mascotas.carnet.pdf', $paciente->id_paciente) }}"
                                    target="_blank"
                                    title="Descargar Carnet de Vacunación Oficial PDF"
                                    class="inline-flex items-center justify-center size-9 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition-all"
                                >
                                    <i data-lucide="file-badge" class="size-4 text-primary-600"></i>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- SECCIÓN 2 Y 3 EN DOS COLUMNAS -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Columna Izquierda: Próximas Citas -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="p-2 rounded-xl bg-indigo-100 text-indigo-700">
                            <i data-lucide="calendar" class="size-5"></i>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-900">Mis Próximas Citas</h2>
                            <p class="text-xs text-slate-500">Horarios y estados de tus visitas veterinarias</p>
                        </div>
                    </div>
                </div>

                @if($citas->isEmpty())
                    <div class="p-6 text-center rounded-3xl border border-slate-200 bg-white">
                        <i data-lucide="calendar-check-2" class="size-8 mx-auto text-slate-300 mb-2"></i>
                        <p class="text-xs font-semibold text-slate-600">No tienes citas agendadas próximamente</p>
                        <button
                            type="button"
                            @click="modalSolicitarCita = true"
                            class="mt-3 inline-flex items-center gap-1.5 text-xs font-bold text-primary-600 hover:text-primary-700"
                        >
                            <span>+ Agendar una cita ahora</span>
                        </button>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($citas as $cita)
                            <div class="p-4 rounded-2xl border border-slate-200/80 bg-white flex items-center justify-between gap-3 shadow-2xs">
                                <div class="flex items-center gap-3">
                                    <div class="p-2.5 rounded-xl bg-indigo-50 text-indigo-700 shrink-0 text-center min-w-14">
                                        <span class="block text-[10px] font-bold uppercase tracking-wider">{{ \Carbon\Carbon::parse($cita->fecha_hora_inicio)->isoFormat('MMM') }}</span>
                                        <span class="block text-lg font-extrabold leading-none">{{ \Carbon\Carbon::parse($cita->fecha_hora_inicio)->format('d') }}</span>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-bold text-slate-900">{{ $cita->paciente?->nombre ?? 'Mascota' }}</span>
                                            <span class="text-xs text-slate-400">·</span>
                                            <span class="text-xs font-semibold text-primary-700">{{ \Carbon\Carbon::parse($cita->fecha_hora_inicio)->format('h:i A') }}</span>
                                        </div>
                                        <p class="text-xs text-slate-500 mt-0.5">
                                            {{ $cita->detalles->first()?->servicio?->nombre ?? 'Consulta General' }}
                                        </p>
                                        @if($cita->profesional)
                                            <p class="text-[11px] text-slate-400">
                                                Dr(a). {{ $cita->profesional->persona?->nombre }} {{ $cita->profesional->persona?->apellido }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider
                                    @if($cita->estado === 'CONFIRMADA') bg-emerald-50 text-emerald-700 border border-emerald-200
                                    @elseif($cita->estado === 'PENDIENTE') bg-amber-50 text-amber-700 border border-amber-200
                                    @elseif($cita->estado === 'EN_ATENCION') bg-blue-50 text-blue-700 border border-blue-200
                                    @else bg-slate-100 text-slate-600
                                    @endif">
                                    {{ $cita->estado }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Columna Derecha: Recetas Médicas Descargables -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="p-2 rounded-xl bg-teal-100 text-teal-700">
                            <i data-lucide="file-text" class="size-5"></i>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-900">Recetas Médicas Emitidas</h2>
                            <p class="text-xs text-slate-500">Tratamientos farmacológicos y dosis prescritas</p>
                        </div>
                    </div>
                </div>

                @if($recetas->isEmpty())
                    <div class="p-6 text-center rounded-3xl border border-slate-200 bg-white">
                        <i data-lucide="pill" class="size-8 mx-auto text-slate-300 mb-2"></i>
                        <p class="text-xs font-semibold text-slate-600">No hay recetas emitidas recientemente</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">Tus prescripciones veterinarias aparecerán aquí al finalizar las consultas</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($recetas as $receta)
                            <div class="p-4 rounded-2xl border border-slate-200/80 bg-white flex items-center justify-between gap-3 shadow-2xs">
                                <div class="flex items-center gap-3">
                                    <div class="p-2.5 rounded-xl bg-teal-50 text-teal-700 shrink-0">
                                        <i data-lucide="file-badge-2" class="size-5"></i>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-bold text-slate-900">
                                                {{ $receta->atencionVeterinaria?->atencion?->paciente?->nombre ?? 'Mascota' }}
                                            </span>
                                            <span class="text-xs text-slate-400">·</span>
                                            <span class="text-xs text-slate-500">
                                                {{ \Carbon\Carbon::parse($receta->fecha_hora)->format('d/m/Y') }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-slate-500 mt-0.5">
                                            {{ $receta->detalleRecetas->count() }} {{ Str::plural('medicamento', $receta->detalleRecetas->count()) }} prescrito(s)
                                        </p>
                                    </div>
                                </div>
                                <a
                                    href="{{ route('historial.receta.pdf', $receta->id_receta) }}"
                                    target="_blank"
                                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-teal-50 hover:bg-teal-100 text-teal-700 text-xs font-bold transition-all"
                                >
                                    <i data-lucide="download" class="size-3.5"></i>
                                    <span>Descargar PDF</span>
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- MODAL: SOLICITAR CITA ONLINE -->
        <div
            x-show="modalSolicitarCita"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
            @keydown.escape.window="modalSolicitarCita = false"
        >
            <div
                @click.away="modalSolicitarCita = false"
                class="w-full max-w-lg rounded-3xl bg-white p-6 shadow-2xl space-y-5"
            >
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="p-2 rounded-xl bg-primary-50 text-primary-600">
                            <i data-lucide="calendar-plus" class="size-5"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Solicitar Cita Veterinaria</h3>
                            <p class="text-xs text-slate-500">Ingresa los datos para reservar tu horario</p>
                        </div>
                    </div>
                    <button
                        type="button"
                        @click="modalSolicitarCita = false"
                        class="p-2 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors"
                    >
                        <i data-lucide="x" class="size-5"></i>
                    </button>
                </div>

                <form action="{{ route('portal.solicitar-cita') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label for="id_paciente" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            ¿Para cuál de tus mascotas es la cita? <span class="text-rose-500">*</span>
                        </label>
                        <select
                            name="id_paciente"
                            id="id_paciente"
                            required
                            class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                        >
                            <option value="">-- Selecciona una mascota --</option>
                            @foreach($pacientes as $paciente)
                                <option value="{{ $paciente->id_paciente }}">{{ $paciente->nombre }} ({{ $paciente->raza?->nombre ?? 'Raza' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="id_servicio" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Servicio Requerido <span class="text-rose-500">*</span>
                        </label>
                        <select
                            name="id_servicio"
                            id="id_servicio"
                            required
                            class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                        >
                            <option value="">-- Selecciona un servicio --</option>
                            @foreach($servicios as $servicio)
                                <option value="{{ $servicio->id_servicio }}">
                                    {{ $servicio->nombre }} — Bs {{ number_format($servicio->precio_base, 2) }} ({{ $servicio->duracion_estimada_minutos ?: 30 }} min)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="fecha_hora_inicio" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Fecha y Hora Deseada <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="datetime-local"
                            name="fecha_hora_inicio"
                            id="fecha_hora_inicio"
                            required
                            min="{{ now()->addHours(1)->format('Y-m-d\TH:i') }}"
                            class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                        />
                        <p class="text-[11px] text-slate-400 mt-1">Horario referencial sujeto a confirmación por el equipo de recepción.</p>
                    </div>

                    <div>
                        <label for="motivo" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Motivo o Síntomas Notados (Opcional)
                        </label>
                        <textarea
                            name="motivo"
                            id="motivo"
                            rows="2"
                            placeholder="Ej. Control de vacunas, decaimiento, tos desde hace 2 días..."
                            class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs text-slate-800 focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                        ></textarea>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button
                            type="button"
                            @click="modalSolicitarCita = false"
                            class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 transition-colors"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold shadow-sm transition-all"
                        >
                            <i data-lucide="send" class="size-3.5"></i>
                            <span>Enviar Solicitud</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layout>
