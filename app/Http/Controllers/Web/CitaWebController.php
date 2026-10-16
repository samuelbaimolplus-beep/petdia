<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\BloqueoAgenda;
use App\Models\Cita;
use App\Models\DetalleCita;
use App\Models\Paciente;
use App\Models\Profesional;
use App\Models\Servicio;
use App\Models\Usuario;
use App\Services\AuditService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CitaWebController extends Controller
{
    /**
     * Muestra la vista de agenda/calendario y listado de citas.
     */
    public function index(Request $request): View
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();

        if (! $currentUser->canAccessModule('citas')) {
            abort(403, 'No tienes permisos para acceder a la agenda de citas.');
        }

        $fechaSeleccionada = $request->input('fecha', Carbon::today()->format('Y-m-d'));
        $fecha = Carbon::parse($fechaSeleccionada);
        $vista = $request->input('vista', 'dia'); // 'dia', 'semana', 'lista'
        $profesionalId = $request->input('profesional');
        $estado = $request->input('estado');
        $search = $request->input('search');

        // Consulta base con relaciones necesarias
        $query = Cita::with([
            'paciente.propietario.persona',
            'paciente.raza.especie',
            'profesional.persona',
            'detalleCitas.servicio',
            'servicioDomicilio',
        ]);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('paciente', function ($qp) use ($search) {
                    $qp->where('nombre', 'ilike', "%{$search}%");
                })->orWhereHas('paciente.propietario.persona', function ($qprop) use ($search) {
                    $qprop->where('nombre', 'ilike', "%{$search}%")
                        ->orWhere('apellido1', 'ilike', "%{$search}%")
                        ->orWhere('documento', 'ilike', "%{$search}%");
                });
            });
        }

        if ($profesionalId) {
            $query->where('id_profesional', $profesionalId);
        }

        if ($estado) {
            $query->where('estado', $estado);
        }

        // Rango de fechas según la vista
        if ($vista === 'semana') {
            $inicioSemana = $fecha->copy()->startOfWeek();
            $finSemana = $fecha->copy()->endOfWeek();
            $query->whereBetween('fecha_hora_inicio', [
                $inicioSemana->copy()->startOfDay(),
                $finSemana->copy()->endOfDay(),
            ]);
        } elseif ($vista === 'dia') {
            $query->whereDate('fecha_hora_inicio', $fecha->format('Y-m-d'));
        }

        $citas = $query->orderBy('fecha_hora_inicio', 'asc')->get();

        // Métricas rápidas de hoy
        $citasHoyCount = Cita::whereDate('fecha_hora_inicio', Carbon::today())->count();
        $confirmadasHoyCount = Cita::whereDate('fecha_hora_inicio', Carbon::today())->where('estado', 'CONFIRMADA')->count();
        $pendientesHoyCount = Cita::whereDate('fecha_hora_inicio', Carbon::today())->where('estado', 'PENDIENTE')->count();
        $enAtencionHoyCount = Cita::whereDate('fecha_hora_inicio', Carbon::today())->where('estado', 'EN_ATENCION')->count();

        // Catálogos para formularios y selects
        $profesionales = Profesional::with(['persona', 'horarios', 'bloqueosAgenda'])
            ->where('estado', 'ACTIVO')
            ->get();

        $servicios = Servicio::where('estado', 'ACTIVO')->orderBy('nombre')->get();

        $pacientes = Paciente::with(['propietario.persona', 'raza.especie'])
            ->where('estado', 'ACTIVO')
            ->orderBy('nombre')
            ->get();

        // Bloqueos de agenda para la fecha/semana seleccionada
        $bloqueosQuery = BloqueoAgenda::with('profesional.persona');
        if ($profesionalId) {
            $bloqueosQuery->where('id_profesional', $profesionalId);
        }
        $bloqueos = $bloqueosQuery->orderBy('fecha_hora_inicio')->get();

        // Permisos de escritura
        $userRoles = $currentUser->roles->pluck('nombre')->map(fn ($r) => strtoupper(str_replace(['-', ' '], '_', $r)))->all();
        $isReadOnly = in_array('VETERINARIO', $userRoles, true) || in_array('SPA', $userRoles, true);
        if (in_array('SUPER_ADMIN', $userRoles, true) || in_array('ADMIN', $userRoles, true) || in_array('RECEPCION', $userRoles, true)) {
            $isReadOnly = false;
        }

        return view('citas.index', [
            'citas' => $citas,
            'fecha' => $fecha,
            'vista' => $vista,
            'profesionales' => $profesionales,
            'servicios' => $servicios,
            'pacientes' => $pacientes,
            'bloqueos' => $bloqueos,
            'isReadOnly' => $isReadOnly,
            'metrics' => [
                'hoy' => $citasHoyCount,
                'confirmadas' => $confirmadasHoyCount,
                'pendientes' => $pendientesHoyCount,
                'en_atencion' => $enAtencionHoyCount,
            ],
            'filters' => [
                'fecha' => $fechaSeleccionada,
                'vista' => $vista,
                'profesional' => $profesionalId,
                'estado' => $estado,
                'search' => $search,
            ],
        ]);
    }

    /**
     * Registra una nueva cita programada con validación de choques.
     */
    public function store(Request $request): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('citas')) {
            abort(403);
        }

        $idServicio = $request->input('id_servicio') ?: $request->input('servicios', []);
        if (! is_array($idServicio)) {
            $idServicio = [$idServicio];
        }
        $request->merge(['id_servicio' => $idServicio]);

        if ($fechaHora = $request->input('fecha_hora')) {
            $parsed = Carbon::parse($fechaHora);
            $request->merge([
                'fecha' => $request->input('fecha') ?: $parsed->toDateString(),
                'hora' => $request->input('hora') ?: $parsed->format('H:i'),
            ]);
        }

        $validated = $request->validate([
            'id_paciente' => ['required', 'integer', 'exists:pacientes,id_paciente'],
            'id_profesional' => ['required', 'integer', 'exists:profesionales,id_persona'],
            'fecha' => ['required', 'date'],
            'hora' => ['required', 'string'],
            'duracion_minutos' => ['nullable', 'integer', 'min:10', 'max:240'],
            'id_servicio' => ['required', 'array', 'min:1'],
            'id_servicio.*' => ['integer', 'exists:servicios,id_servicio'],
            'origen' => ['nullable', 'string', 'in:PRESENCIAL,TELEFONO,WEB,URGENCIA'],
            'es_domicilio' => ['nullable', 'boolean'],
            'direccion_domicilio' => ['nullable', 'required_if:es_domicilio,1', 'string', 'max:255'],
        ], [
            'id_paciente.required' => 'Debe seleccionar un paciente.',
            'id_profesional.required' => 'Debe asignar un profesional para la cita.',
            'id_servicio.required' => 'Debe seleccionar al menos un servicio a realizar.',
        ]);

        $inicio = Carbon::parse($validated['fecha'].' '.$validated['hora']);
        $duracion = (int) ($validated['duracion_minutos'] ?? 30);
        $fin = $inicio->copy()->addMinutes($duracion);

        // Validación 1: Verificar si el profesional tiene choques con citas vigentes
        $solapamientoCita = Cita::where('id_profesional', $validated['id_profesional'])
            ->whereNotIn('estado', ['CANCELADA', 'INASISTENCIA'])
            ->where(function ($q) use ($inicio, $fin) {
                $q->where(function ($sub) use ($inicio, $fin) {
                    $sub->where('fecha_hora_inicio', '<', $fin)
                        ->where('fecha_hora_fin', '>', $inicio);
                });
            })
            ->exists();

        if ($solapamientoCita) {
            return back()->withInput()->withErrors([
                'hora' => 'El profesional seleccionado ya tiene una cita programada en ese rango horario.',
                'fecha_hora' => 'El profesional seleccionado ya tiene una cita programada en ese rango horario.',
            ]);
        }

        // Validación 2: Verificar si el profesional tiene un bloqueo de agenda
        $solapamientoBloqueo = BloqueoAgenda::where('id_profesional', $validated['id_profesional'])
            ->where(function ($q) use ($inicio, $fin) {
                $q->where('fecha_hora_inicio', '<', $fin)
                    ->where('fecha_hora_fin', '>', $inicio);
            })
            ->exists();

        if ($solapamientoBloqueo) {
            return back()->withInput()->withErrors([
                'hora' => 'El profesional tiene un bloqueo de agenda activo (ausencia, capacitación o descanso) en ese horario.',
                'fecha_hora' => 'El profesional tiene un bloqueo de agenda activo (ausencia, capacitación o descanso) en ese horario.',
            ]);
        }

        $cita = DB::transaction(function () use ($validated, $inicio, $fin, $duracion) {
            $cita = Cita::create([
                'id_paciente' => $validated['id_paciente'],
                'id_profesional' => $validated['id_profesional'],
                'fecha_hora_inicio' => $inicio,
                'fecha_hora_fin' => $fin,
                'estado' => 'CONFIRMADA',
                'origen' => $validated['origen'] ?? 'PRESENCIAL',
            ]);

            foreach ($validated['id_servicio'] as $servicioId) {
                $servicio = Servicio::find($servicioId);
                DetalleCita::create([
                    'id_cita' => $cita->id_cita,
                    'id_servicio' => $servicioId,
                    'costo_estimado' => $servicio?->precio_base ?? 0,
                    'tiempo_estimado_minutos' => $duracion,
                ]);
            }

            if (! empty($validated['es_domicilio'])) {
                DB::table('servicio_domicilios')->insert([
                    'id_cita' => $cita->id_cita,
                    'direccion' => $validated['direccion_domicilio'] ?? 'Dirección del propietario',
                    'estado' => 'PENDIENTE',
                ]);
            }

            return $cita;
        });

        AuditService::log('REGISTRAR_CITA', 'CITAS', [
            'id_cita' => $cita->id_cita,
            'id_paciente' => $cita->id_paciente,
            'id_profesional' => $cita->id_profesional,
            'fecha_hora' => $inicio->toDateTimeString(),
        ], $cita, null, 'EXITO', "Cita #{$cita->id_cita} agendada para paciente #{$cita->id_paciente}");

        return redirect()->route('citas.index', ['fecha' => $inicio->format('Y-m-d')])
            ->with('success', 'Cita agendada exitosamente.');
    }

    /**
     * Registra una atención urgente inmediata (sin cita previa).
     */
    public function urgente(Request $request): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('citas')) {
            abort(403);
        }

        $validated = $request->validate([
            'id_paciente' => ['required', 'integer', 'exists:pacientes,id_paciente'],
            'id_profesional' => ['required', 'integer', 'exists:profesionales,id_persona'],
            'id_servicio' => ['required', 'integer', 'exists:servicios,id_servicio'],
        ]);

        $ahora = Carbon::now();
        $fin = $ahora->copy()->addMinutes(45);

        $cita = DB::transaction(function () use ($validated, $ahora, $fin) {
            $cita = Cita::create([
                'id_paciente' => $validated['id_paciente'],
                'id_profesional' => $validated['id_profesional'],
                'fecha_hora_inicio' => $ahora,
                'fecha_hora_fin' => $fin,
                'estado' => 'EN_ATENCION',
                'origen' => 'URGENCIA',
            ]);

            $servicio = Servicio::find($validated['id_servicio']);
            DetalleCita::create([
                'id_cita' => $cita->id_cita,
                'id_servicio' => $validated['id_servicio'],
                'costo_estimado' => $servicio?->precio_base ?? 0,
                'tiempo_estimado_minutos' => 45,
            ]);

            return $cita;
        });

        AuditService::log('REGISTRAR_CITA_URGENTE', 'CITAS', [
            'id_cita' => $cita->id_cita,
            'id_paciente' => $cita->id_paciente,
            'id_profesional' => $cita->id_profesional,
        ], $cita, null, 'EXITO', "Atención urgente #{$cita->id_cita} ingresada para paciente #{$cita->id_paciente}");

        return redirect()->route('citas.index', ['fecha' => $ahora->format('Y-m-d')])
            ->with('success', 'Atención urgente ingresada directamente.');
    }

    /**
     * Reprograma una cita existente.
     */
    public function reprogramar(Request $request, int $id): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('citas')) {
            abort(403);
        }

        $cita = Cita::findOrFail($id);

        if ($request->filled('fecha_hora') && (! $request->filled('fecha') || ! $request->filled('hora'))) {
            $dt = Carbon::parse($request->input('fecha_hora'));
            $request->merge([
                'fecha' => $dt->toDateString(),
                'hora' => $dt->format('H:i'),
            ]);
        }
        if (! $request->filled('id_profesional') && $cita->id_profesional) {
            $request->merge(['id_profesional' => $cita->id_profesional]);
        }

        $validated = $request->validate([
            'id_profesional' => ['required', 'integer', 'exists:profesionales,id_persona'],
            'fecha' => ['required', 'date'],
            'hora' => ['required', 'string'],
            'duracion_minutos' => ['nullable', 'integer', 'min:10', 'max:240'],
        ]);

        $inicio = Carbon::parse($validated['fecha'].' '.$validated['hora']);
        $duracion = (int) ($validated['duracion_minutos'] ?? 30);
        $fin = $inicio->copy()->addMinutes($duracion);

        // Validar choque excluyendo la cita actual
        $solapamiento = Cita::where('id_profesional', $validated['id_profesional'])
            ->where('id_cita', '!=', $id)
            ->whereNotIn('estado', ['CANCELADA', 'INASISTENCIA'])
            ->where(function ($q) use ($inicio, $fin) {
                $q->where('fecha_hora_inicio', '<', $fin)
                    ->where('fecha_hora_fin', '>', $inicio);
            })
            ->exists();

        if ($solapamiento) {
            return back()->withErrors([
                'hora' => 'El profesional ya tiene otra cita en el nuevo horario seleccionado.',
                'fecha_hora' => 'El profesional ya tiene otra cita en el nuevo horario seleccionado.',
            ]);
        }

        $cita->update([
            'id_profesional' => $validated['id_profesional'],
            'fecha_hora_inicio' => $inicio,
            'fecha_hora_fin' => $fin,
            'estado' => 'CONFIRMADA',
        ]);

        AuditService::log('REPROGRAMAR_CITA', 'CITAS', [
            'id_cita' => $cita->id_cita,
            'nueva_fecha' => $inicio->toDateTimeString(),
            'id_profesional' => $validated['id_profesional'],
        ], $cita, null, 'EXITO', "Cita #{$cita->id_cita} reprogramada para {$inicio->toDateTimeString()}");

        return redirect()->route('citas.index', ['fecha' => $inicio->format('Y-m-d')])
            ->with('success', 'Cita reprogramada correctamente.');
    }

    /**
     * Cambia el estado de una cita (Confirmar, Inasistencia, En Atención).
     */
    public function cambiarEstado(Request $request, int $id): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('citas')) {
            abort(403);
        }

        $cita = Cita::findOrFail($id);

        $validated = $request->validate([
            'estado' => ['required', 'string', 'in:CONFIRMADA,EN_ATENCION,COMPLETADA,INASISTENCIA'],
        ]);

        $cita->update(['estado' => $validated['estado']]);

        AuditService::log('CAMBIO_ESTADO_CITA', 'CITAS', [
            'id_cita' => $cita->id_cita,
            'nuevo_estado' => $validated['estado'],
        ], $cita, null, 'EXITO', "Estado de cita #{$cita->id_cita} actualizado a {$validated['estado']}");

        return back()->with('success', "Estado de la cita actualizado a {$validated['estado']}.");
    }

    /**
     * Cancela una cita con motivo obligatorio.
     */
    public function cancelar(Request $request, int $id): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('citas')) {
            abort(403);
        }

        $cita = Cita::findOrFail($id);

        $validated = $request->validate([
            'motivo_cancelacion' => ['required', 'string', 'max:255'],
        ], [
            'motivo_cancelacion.required' => 'Debe ingresar un motivo para cancelar la cita.',
        ]);

        $cita->update([
            'estado' => 'CANCELADA',
            'motivo_cancelacion' => $validated['motivo_cancelacion'],
        ]);

        AuditService::log('CANCELAR_CITA', 'CITAS', [
            'id_cita' => $cita->id_cita,
            'motivo' => $validated['motivo_cancelacion'],
            'id_paciente' => $cita->id_paciente,
        ], $cita, null, 'EXITO', "Cita #{$cita->id_cita} cancelada: {$validated['motivo_cancelacion']}");

        return back()->with('success', 'Cita cancelada correctamente.');
    }

    /**
     * Registra un bloqueo de agenda para un profesional.
     */
    public function storeBloqueo(Request $request): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('citas')) {
            abort(403);
        }

        $profId = $request->input('id_profesional');
        $profesional = Profesional::where('id_persona', $profId)->firstOrFail();

        $inicioInput = $request->input('fecha_hora_inicio') ?: trim(($request->input('fecha_inicio') ?? '').' '.($request->input('hora_inicio') ?? ''));
        $finInput = $request->input('fecha_hora_fin') ?: trim(($request->input('fecha_fin') ?? '').' '.($request->input('hora_fin') ?? ''));

        $request->validate([
            'motivo' => ['required', 'string', 'max:255'],
        ]);

        if (empty($inicioInput) || empty($finInput)) {
            return back()->withErrors(['hora_inicio' => 'Debe especificar el rango de fechas y horas para el bloqueo.']);
        }

        $inicio = Carbon::parse($inicioInput);
        $fin = Carbon::parse($finInput);

        if ($fin->lessThanOrEqualTo($inicio)) {
            return back()->withErrors(['hora_fin' => 'La fecha/hora de fin debe ser posterior a la de inicio.']);
        }

        $bloqueo = BloqueoAgenda::create([
            'id_profesional' => $profesional->id_persona,
            'fecha_hora_inicio' => $inicio,
            'fecha_hora_fin' => $fin,
            'motivo' => $request->input('motivo'),
        ]);

        AuditService::log('CREAR_BLOQUEO_AGENDA', 'AGENDA', [
            'id_bloqueo' => $bloqueo->id_bloqueo,
            'id_profesional' => $profesional->id_persona,
            'motivo' => $request->input('motivo'),
        ], $bloqueo, null, 'EXITO', "Bloqueo de agenda creado para profesional #{$profesional->id_persona}");

        return back()->with('success', 'Bloqueo de agenda registrado exitosamente.');
    }

    /**
     * Elimina un bloqueo de agenda.
     */
    public function deleteBloqueo(int $id): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('citas')) {
            abort(403);
        }

        $bloqueo = BloqueoAgenda::findOrFail($id);
        $profId = $bloqueo->id_profesional;

        AuditService::log('ELIMINAR_BLOQUEO_AGENDA', 'AGENDA', [
            'id_bloqueo' => $bloqueo->id_bloqueo,
            'id_profesional' => $profId,
        ], $bloqueo, null, 'EXITO', "Bloqueo de agenda levantado para profesional #{$profId}");

        $bloqueo->delete();

        return back()->with('success', 'Bloqueo de agenda levantado correctamente.');
    }

    /**
     * CU14 & CU30: Consulta los horarios reales disponibles de un profesional.
     */
    public function consultarDisponibilidad(Request $request): JsonResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('citas')) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $profesionalId = $request->query('id_profesional');
        $fechaStr = $request->query('fecha', Carbon::today()->format('Y-m-d'));
        $duracion = (int) $request->query('duracion_minutos', 30);
        $fecha = Carbon::parse($fechaStr);

        if (! $profesionalId) {
            return response()->json(['error' => 'Debe especificar el profesional.'], 422);
        }

        $profesional = Profesional::with(['persona', 'horarios', 'bloqueosAgenda'])->findOrFail($profesionalId);
        $diaSemana = $fecha->dayOfWeekIso; // 1 (Lunes) a 7 (Domingo)

        // Horarios laborales del profesional para este día
        $horarios = $profesional->horarios
            ->where('dia_semana', $diaSemana)
            ->filter(fn ($h) => in_array(strtoupper((string) ($h->estado ?? 'ACTIVO')), ['ACTIVO', '1', 'TRUE'], true));

        if ($horarios->isEmpty()) {
            return response()->json([
                'fecha' => $fechaStr,
                'dia' => $fecha->translatedFormat('l'),
                'disponible' => false,
                'mensaje' => 'El profesional no atiende en el día seleccionado.',
                'horarios' => [],
                'slots' => [],
            ]);
        }

        // Citas existentes en esa fecha (excluyendo canceladas e inasistencias)
        $citasOcupadas = Cita::where('id_profesional', $profesionalId)
            ->whereDate('fecha_hora_inicio', $fechaStr)
            ->whereNotIn('estado', ['CANCELADA', 'INASISTENCIA'])
            ->get(['fecha_hora_inicio', 'fecha_hora_fin']);

        // Bloqueos de agenda
        $bloqueos = BloqueoAgenda::where('id_profesional', $profesionalId)
            ->where(function ($q) use ($fecha) {
                $q->whereDate('fecha_hora_inicio', '<=', $fecha)
                    ->whereDate('fecha_hora_fin', '>=', $fecha);
            })
            ->get();

        $slotsDisponibles = [];

        foreach ($horarios as $turno) {
            $inicioTurno = Carbon::parse($fechaStr.' '.$turno->hora_inicio);
            $finTurno = Carbon::parse($fechaStr.' '.$turno->hora_fin);

            $cursor = $inicioTurno->copy();
            while ($cursor->copy()->addMinutes($duracion)->lessThanOrEqualTo($finTurno)) {
                $slotFin = $cursor->copy()->addMinutes($duracion);

                // Verificar choque con cita
                $chocaCita = $citasOcupadas->some(function ($c) use ($cursor, $slotFin) {
                    return $cursor->lt($c->fecha_hora_fin) && $slotFin->gt($c->fecha_hora_inicio);
                });

                // Verificar choque con bloqueo
                $chocaBloqueo = $bloqueos->some(function ($b) use ($cursor, $slotFin) {
                    return $cursor->lt($b->fecha_hora_fin) && $slotFin->gt($b->fecha_hora_inicio);
                });

                if (! $chocaCita && ! $chocaBloqueo) {
                    $slotsDisponibles[] = [
                        'hora' => $cursor->format('H:i'),
                        'hora_fin' => $slotFin->format('H:i'),
                        'texto' => $cursor->format('H:i').' - '.$slotFin->format('H:i'),
                    ];
                }

                $cursor->addMinutes(30);
            }
        }

        return response()->json([
            'fecha' => $fechaStr,
            'id_profesional' => $profesionalId,
            'nombre_profesional' => $profesional->persona ? $profesional->persona->nombre.' '.$profesional->persona->apellido1 : 'Profesional',
            'disponible' => count($slotsDisponibles) > 0,
            'total_disponibles' => count($slotsDisponibles),
            'horarios' => $horarios->values(),
            'slots' => $slotsDisponibles,
        ]);
    }
}
