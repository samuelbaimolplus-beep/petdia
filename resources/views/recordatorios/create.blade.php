@extends('layouts.app')

@section('title', 'Programar Recordatorio — La Petdiatría')

@section('content')
<div class="max-w-3xl mx-auto space-y-6" x-data="{
    tipo: 'VACUNA',
    pacientes: {{ Js::from($pacientes->map(fn($p) => [
        'id' => $p->id_paciente,
        'nombre' => $p->nombre,
        'propietario' => ($p->propietario->persona->nombre ?? '') . ' ' . ($p->propietario->persona->apellido1 ?? '')
    ])) }},
    idPaciente: '{{ $pacientePreseleccionado ?? $pacientes->first()?->id_paciente ?? "" }}',
    mensaje: '',
    
    actualizarPlantilla() {
        const p = this.pacientes.find(pac => pac.id == this.idPaciente);
        const nom = p ? p.nombre : 'la mascota';
        if (this.tipo === 'VACUNA') {
            this.mensaje = 'Tarea clínica: Refuerzo de vacuna programado para ' + nom + '. Alistar dosis de biológico y contactar al tutor si requiere preparación previa.';
        } else if (this.tipo === 'DESPARASITACION') {
            this.mensaje = 'Tarea preventiva: Desparasitación periódica programada para ' + nom + '. Coordinar peso y antiparasitario adecuado con el tutor.';
        } else if (this.tipo === 'CONTROL') {
            this.mensaje = 'Seguimiento médico: Control post-tratamiento de ' + nom + '. Evaluar evolución clínica y responder dudas del tutor.';
        } else if (this.tipo === 'CITA') {
            this.mensaje = 'Cita agendada: Preparar box de atención para ' + nom + '. Verificar historial de atenciones previas.';
        } else {
            this.mensaje = 'Aviso de seguimiento clínico para ' + nom + '.';
        }
    }
}" x-init="actualizarPlantilla()">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400">
                <a href="{{ route('recordatorios.index') }}" class="hover:text-indigo-600 transition-colors">Recordatorios</a>
                <span>/</span>
                <span class="text-slate-600 font-medium">Programar</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 mt-1">Programar Recordatorio Preventivo — Tarea para el Personal</h1>
            <p class="text-sm text-slate-500">Agendar alertas de trabajo preventivo para el personal y veterinarios.</p>
        </div>
        <a href="{{ route('recordatorios.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-200 bg-white text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 transition-colors">
            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Volver
        </a>
    </div>

    <!-- Formulario -->
    <form action="{{ route('recordatorios.store') }}" method="POST" class="bg-white p-6 rounded-2xl border border-slate-100 shadow-xs space-y-5">
        @csrf

        <div class="space-y-4">
            <!-- Paciente -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Paciente / Mascota *</label>
                <select name="id_paciente" x-model="idPaciente" @change="actualizarPlantilla()" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600">
                    <option value="">Seleccionar Mascota...</option>
                    @foreach($pacientes as $pac)
                        <option value="{{ $pac->id_paciente }}" {{ ($pacientePreseleccionado == $pac->id_paciente) ? 'selected' : '' }}>
                            {{ $pac->nombre }} — {{ $pac->raza->nombre ?? 'Mestizo' }} (Dueño: {{ $pac->propietario->persona->nombre ?? '' }} {{ $pac->propietario->persona->apellido1 ?? '' }})
                        </option>
                    @endforeach
                </select>
                @error('id_paciente') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Veterinario / Profesional Responsable -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Veterinario / Trabajador Responsable</label>
                <select name="id_profesional" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600">
                    <option value="">Equipo Clínico General (Notificación al correo de la clínica)</option>
                    @foreach($profesionales as $prof)
                        <option value="{{ $prof->id_persona }}">
                            Dr./a {{ $prof->persona->nombre ?? '' }} {{ $prof->persona->apellido1 ?? '' }} ({{ $prof->especialidad ?? 'Veterinario' }}) — ✉ {{ $prof->persona->email ?? 'Sin correo registrado' }}
                        </option>
                    @endforeach
                </select>
                <p class="text-[11px] text-slate-400 mt-1">El recordatorio por correo llegará al buzón personal del profesional seleccionado.</p>
                @error('id_profesional') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Tipo de Recordatorio y Servicio -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Tipo de Tarea Clínica *</label>
                    <select name="tipo" x-model="tipo" @change="actualizarPlantilla()" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600">
                        <option value="VACUNA">Vacuna / Refuerzo</option>
                        <option value="DESPARASITACION">Desparasitación Periódica</option>
                        <option value="CONTROL">Control Post-Tratamiento</option>
                        <option value="CITA">Recordatorio de Cita Médica</option>
                        <option value="OTRO">Otro Aviso Clínico</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Servicio Relacionado (Opcional)</label>
                    <select name="id_servicio" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600">
                        <option value="">Ninguno / General</option>
                        @foreach($servicios as $srv)
                            <option value="{{ $srv->id_servicio }}">{{ $srv->nombre }} ({{ $srv->categoria }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Fecha y Hora Programada -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Fecha Programada de Atención *</label>
                    <input type="date" name="fecha_programada" value="{{ date('Y-m-d') }}" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Hora Estimada</label>
                    <input type="time" name="hora_programada" value="09:00" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600">
                </div>
            </div>

            <!-- Canal de Envío -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Canal de Alerta *</label>
                <div class="grid grid-cols-3 gap-3">
                    <label class="flex items-center gap-2 p-3 rounded-xl border border-slate-200 cursor-pointer hover:bg-slate-50 transition-colors">
                        <input type="radio" name="medio_envio" value="EMAIL" checked class="text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm font-medium text-slate-800">Correo (Personal)</span>
                    </label>
                    <label class="flex items-center gap-2 p-3 rounded-xl border border-slate-200 cursor-pointer hover:bg-slate-50 transition-colors">
                        <input type="radio" name="medio_envio" value="WHATSAPP" class="text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm font-medium text-slate-800">WhatsApp</span>
                    </label>
                    <label class="flex items-center gap-2 p-3 rounded-xl border border-slate-200 cursor-pointer hover:bg-slate-50 transition-colors">
                        <input type="radio" name="medio_envio" value="SMS" class="text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm font-medium text-slate-800">SMS</span>
                    </label>
                </div>
            </div>

            <!-- Mensaje / Notas Clínicas -->
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block text-xs font-semibold text-slate-700 uppercase">Instrucciones / Notas para el Trabajador</label>
                    <button type="button" @click="actualizarPlantilla()" class="text-xs text-indigo-600 hover:text-indigo-700 font-medium">
                        Regenerar sugerencia
                    </button>
                </div>
                <textarea name="mensaje" x-model="mensaje" rows="4" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600"></textarea>
            </div>
        </div>

        <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
            <a href="{{ route('recordatorios.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
                Cancelar
            </a>
            <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold shadow-xs hover:shadow transition-all">
                Guardar Tarea / Recordatorio
            </button>
        </div>
    </form>
</div>
@endsection
