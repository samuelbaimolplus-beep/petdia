<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\DetalleCita;
use App\Models\Paciente;
use App\Models\Profesional;
use App\Models\Propietario;
use App\Models\Receta;
use App\Models\Servicio;
use App\Models\Usuario;
use App\Services\AuditService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PortalWebController extends Controller
{
    /**
     * Tablero principal de autoservicio para propietarios de mascotas.
     */
    public function index(Request $request): View
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();

        // Obtener o asociar perfil de propietario
        $idPersona = $currentUser->id_persona;

        $pacientes = Paciente::with(['raza.especie'])
            ->where('id_propietario', $idPersona)
            ->where('estado', 'ACTIVO')
            ->orderBy('nombre')
            ->get();

        // Si es staff administrativo/veterinario probando el portal y no tiene mascotas asignadas,
        // cargar mascotas demo para previsualizar la experiencia
        if ($pacientes->isEmpty() && $currentUser->hasRole(['SUPER_ADMIN', 'ADMIN', 'VETERINARIO'])) {
            $pacientes = Paciente::with(['raza.especie'])->where('estado', 'ACTIVO')->take(3)->get();
        }

        $pacienteIds = $pacientes->pluck('id_paciente')->toArray();

        // Citas agendadas o pendientes
        $citas = Cita::with(['paciente', 'profesional.persona', 'detalles.servicio'])
            ->whereIn('id_paciente', $pacienteIds)
            ->where('fecha_hora_inicio', '>=', now()->subDay())
            ->orderBy('fecha_hora_inicio', 'asc')
            ->get();

        // Recetas médicas recientes emitidas
        $recetas = Receta::whereHas('atencionVeterinaria.atencion', function ($q) use ($pacienteIds) {
            $q->whereIn('id_paciente', $pacienteIds);
        })
            ->with(['atencionVeterinaria.atencion.paciente', 'detalleRecetas.medicamento'])
            ->latest('fecha_hora')
            ->take(6)
            ->get();

        // Catálogo de servicios disponibles para reserva online
        $servicios = Servicio::where('estado', 'ACTIVO')->orWhereNull('estado')->orderBy('nombre')->get();

        return view('portal.index', [
            'currentUser' => $currentUser,
            'pacientes' => $pacientes,
            'citas' => $citas,
            'recetas' => $recetas,
            'servicios' => $servicios,
        ]);
    }

    /**
     * Ficha de salud y carnet digital de la mascota para el propietario.
     */
    public function mascota(int $id): View
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();

        $paciente = Paciente::with([
            'raza.especie',
            'propietario.persona',
            'atenciones' => function ($q) {
                $q->has('atencionVeterinaria')
                    ->with([
                        'atencionVeterinaria.registrosSignosVitales',
                        'atencionVeterinaria.procedimientos.vacunacion',
                        'atencionVeterinaria.procedimientos.desparasitacion',
                        'atencionVeterinaria.recetas.detalleRecetas.medicamento',
                    ])
                    ->latest('fecha_hora_inicio');
            },
        ])->findOrFail($id);

        // Control de privacidad: El tutor solo puede consultar sus propias mascotas
        $isOwner = $paciente->id_propietario === $currentUser->id_persona;
        $isStaff = $currentUser->hasRole(['SUPER_ADMIN', 'ADMIN', 'VETERINARIO']);

        if (! $isOwner && ! $isStaff) {
            abort(403, 'Acceso denegado: solo puedes consultar las mascotas registradas a tu nombre.');
        }

        // Extraer historial preventivo
        $vacunas = collect();
        $desparasitaciones = collect();
        $recetas = collect();
        $pesos = collect();

        foreach ($paciente->atenciones as $atencion) {
            if ($vet = $atencion->atencionVeterinaria) {
                foreach ($vet->procedimientos as $proc) {
                    if ($proc->vacunacion) {
                        $vacunas->push([
                            'fecha' => $proc->fecha_hora,
                            'vacuna' => $proc->descripcion ?: 'Vacuna',
                            'proxima' => $proc->vacunacion->proxima_dosis,
                            'sitio' => $proc->vacunacion->sitio_aplicacion,
                        ]);
                    }
                    if ($proc->desparasitacion) {
                        $desparasitaciones->push([
                            'fecha' => $proc->fecha_hora,
                            'producto' => $proc->descripcion ?: 'Antiparasitario',
                            'tipo' => $proc->desparasitacion->tipo ?? 'Interno / Externo',
                            'proxima' => $proc->desparasitacion->proxima_dosis,
                        ]);
                    }
                }
                foreach ($vet->recetas as $rec) {
                    $recetas->push($rec);
                }
                foreach ($vet->registrosSignosVitales as $signo) {
                    if ($signo->peso) {
                        $pesos->push([
                            'fecha' => $signo->fecha_hora,
                            'peso' => $signo->peso,
                        ]);
                    }
                }
            }
        }

        return view('portal.mascota', [
            'paciente' => $paciente,
            'vacunas' => $vacunas,
            'desparasitaciones' => $desparasitaciones,
            'recetas' => $recetas,
            'pesos' => $pesos,
        ]);
    }

    /**
     * Permite al cliente solicitar una reserva de cita en línea.
     */
    public function solicitarCita(Request $request): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();

        $validated = $request->validate([
            'id_paciente' => ['required', 'exists:pacientes,id_paciente'],
            'id_servicio' => ['required', 'exists:servicios,id_servicio'],
            'fecha_hora_inicio' => ['required', 'date', 'after:now'],
            'motivo' => ['nullable', 'string', 'max:255'],
        ], [
            'id_paciente.required' => 'Debes seleccionar una de tus mascotas.',
            'id_servicio.required' => 'Debes seleccionar el servicio que requieres.',
            'fecha_hora_inicio.required' => 'Debes indicar la fecha y hora deseada.',
            'fecha_hora_inicio.after' => 'La fecha de la cita debe ser posterior al momento actual.',
        ]);

        $paciente = Paciente::findOrFail($validated['id_paciente']);

        // Validar propiedad de la mascota
        $isOwner = $paciente->id_propietario === $currentUser->id_persona;
        $isStaff = $currentUser->hasRole(['SUPER_ADMIN', 'ADMIN', 'VETERINARIO']);
        if (! $isOwner && ! $isStaff) {
            abort(403, 'Solo puedes solicitar citas para tus propias mascotas.');
        }

        $inicio = Carbon::parse($validated['fecha_hora_inicio']);
        $servicio = Servicio::findOrFail($validated['id_servicio']);
        $duracion = $servicio->duracion_estimada_minutos ?: 30;
        $fin = $inicio->copy()->addMinutes($duracion);

        // Asignar al primer veterinario activo disponible por defecto
        $profesional = Profesional::first();

        DB::transaction(function () use ($paciente, $profesional, $inicio, $fin, $servicio, $duracion) {
            $cita = Cita::create([
                'id_paciente' => $paciente->id_paciente,
                'id_profesional' => $profesional?->id_persona,
                'fecha_hora_inicio' => $inicio,
                'fecha_hora_fin' => $fin,
                'estado' => 'PENDIENTE',
                'origen' => 'ONLINE_PORTAL',
            ]);

            DetalleCita::create([
                'id_cita' => $cita->id_cita,
                'id_servicio' => $servicio->id_servicio,
                'costo_estimado' => $servicio->precio_base,
                'tiempo_estimado_minutos' => $duracion,
            ]);

            AuditService::log('SOLICITAR_CITA_PORTAL', 'CITAS', [
                'id_cita' => $cita->id_cita,
                'paciente' => $paciente->nombre,
                'servicio' => $servicio->nombre,
                'fecha_solicitada' => $inicio->toDateTimeString(),
            ], $cita);
        });

        return redirect()->route('portal.index')
            ->with('success', "¡Solicitud de cita para {$paciente->nombre} enviada exitosamente! El equipo de recepción revisará tu reserva.");
    }
}
