<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Mail\RecordatorioMailable;
use App\Models\Configuracion;
use App\Models\Paciente;
use App\Models\Profesional;
use App\Models\Recordatorio;
use App\Models\Servicio;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class RecordatorioWebController extends Controller
{
    /**
     * Listado general de recordatorios con KPIs y filtros por tipo y estado.
     */
    public function index(Request $request): View
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('recordatorios')) {
            abort(403, 'No tienes permisos para acceder al módulo de recordatorios.');
        }

        $query = Recordatorio::with([
            'paciente.propietario.persona',
            'servicio',
            'profesional.persona',
            'cita.profesional.persona',
        ])->orderBy('fecha_hora_programada', 'asc');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('mensaje', 'ilike', "%{$search}%")
                    ->orWhereHas('paciente', function ($qp) use ($search) {
                        $qp->where('nombre', 'ilike', "%{$search}%")
                            ->orWhereHas('propietario.persona', function ($qpp) use ($search) {
                                $qpp->where('nombre', 'ilike', "%{$search}%")
                                    ->orWhere('apellido1', 'ilike', "%{$search}%")
                                    ->orWhere('telefono', 'ilike', "%{$search}%");
                            });
                    })
                    ->orWhereHas('profesional.persona', function ($qprof) use ($search) {
                        $qprof->where('nombre', 'ilike', "%{$search}%")
                            ->orWhere('apellido1', 'ilike', "%{$search}%");
                    });
            });
        }

        if ($tipo = $request->input('tipo')) {
            $query->where('tipo', $tipo);
        }

        if ($estado = $request->input('estado')) {
            $query->where('estado', $estado);
        } else {
            // Por defecto mostrar pendientes primero si no se especifica
            if (! $request->has('search')) {
                $query->whereIn('estado', ['PENDIENTE', 'PROGRAMADO']);
            }
        }

        if ($fecha = $request->input('fecha')) {
            $query->whereDate('fecha_hora_programada', $fecha);
        }

        $recordatorios = $query->paginate(15)->withQueryString();

        // KPIs
        $hoy = Carbon::today();
        $pendientesHoy = Recordatorio::whereIn('estado', ['PENDIENTE', 'PROGRAMADO'])
            ->whereDate('fecha_hora_programada', $hoy)
            ->count();

        $proximosSieteDias = Recordatorio::whereIn('estado', ['PENDIENTE', 'PROGRAMADO'])
            ->whereBetween('fecha_hora_programada', [$hoy, Carbon::today()->addDays(7)])
            ->count();

        $enviadosMes = Recordatorio::where('estado', 'ENVIADO')
            ->whereMonth('fecha_hora_envio', Carbon::now()->month)
            ->whereYear('fecha_hora_envio', Carbon::now()->year)
            ->count();

        $totalPendientes = Recordatorio::whereIn('estado', ['PENDIENTE', 'PROGRAMADO'])->count();

        return view('recordatorios.index', compact(
            'recordatorios',
            'pendientesHoy',
            'proximosSieteDias',
            'enviadosMes',
            'totalPendientes'
        ));
    }

    /**
     * Formulario para programar un nuevo recordatorio preventivo.
     */
    public function create(Request $request): View
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('recordatorios')) {
            abort(403, 'No tienes permisos para programar recordatorios.');
        }

        $pacientes = Paciente::with(['propietario.persona', 'raza.especie'])
            ->where('estado', 'VIVO')
            ->orderBy('nombre')
            ->get();

        $servicios = Servicio::where('estado', 'ACTIVO')->orderBy('nombre')->get();
        $profesionales = Profesional::with('persona')->where('estado', 'ACTIVO')->get();
        $pacientePreseleccionado = $request->input('id_paciente');

        return view('recordatorios.create', compact('pacientes', 'servicios', 'profesionales', 'pacientePreseleccionado'));
    }

    /**
     * Guarda el recordatorio programado.
     */
    public function store(Request $request): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('recordatorios')) {
            abort(403, 'No tienes permisos para programar recordatorios.');
        }

        $validated = $request->validate([
            'id_paciente' => ['required', 'integer', 'exists:pacientes,id_paciente'],
            'id_profesional' => ['nullable', 'integer', 'exists:profesionales,id_persona'],
            'tipo' => ['required', 'string', 'in:VACUNA,DESPARASITACION,CONTROL,CITA,OTRO'],
            'id_servicio' => ['nullable', 'integer', 'exists:servicios,id_servicio'],
            'fecha_programada' => ['required', 'date'],
            'hora_programada' => ['nullable', 'string'],
            'medio_envio' => ['required', 'string', 'in:WHATSAPP,SMS,EMAIL'],
            'mensaje' => ['nullable', 'string', 'max:500'],
        ]);

        $hora = $validated['hora_programada'] ?: '09:00';
        $fechaHora = Carbon::parse("{$validated['fecha_programada']} {$hora}");

        // Si no hay mensaje, generar una nota clínica preventiva
        $paciente = Paciente::with('propietario.persona')->find($validated['id_paciente']);
        $mensaje = $validated['mensaje'];
        if (empty($mensaje)) {
            $nombreMascota = $paciente->nombre;
            $tipoTexto = strtolower($validated['tipo']);
            $mensaje = "Alerta clínica: Seguimiento preventivo ({$tipoTexto}) programado para {$nombreMascota}. Coordinar preparación y atención oportuna.";
        }

        Recordatorio::create([
            'id_paciente' => $validated['id_paciente'],
            'id_profesional' => $validated['id_profesional'] ?? null,
            'tipo' => $validated['tipo'],
            'id_servicio' => $validated['id_servicio'] ?? null,
            'fecha_hora_programada' => $fechaHora,
            'medio_envio' => $validated['medio_envio'],
            'mensaje' => $mensaje,
            'estado' => 'PENDIENTE',
        ]);

        return redirect()->route('recordatorios.index')->with('success', 'Recordatorio programado con éxito para el equipo veterinario.');
    }

    /**
     * Marca el recordatorio como enviado y despacha el correo al veterinario / trabajador responsable.
     */
    public function marcarEnviado(Recordatorio $recordatorio): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('recordatorios')) {
            abort(403, 'No tienes permisos para actualizar recordatorios.');
        }

        $recordatorio->loadMissing([
            'paciente.propietario.persona',
            'servicio',
            'profesional.persona',
            'cita.profesional.persona',
        ]);

        $profesional = $recordatorio->profesional_responsable;
        $emailDestino = $profesional?->persona?->email;
        $nombreDestinatario = $profesional?->persona
            ? 'Dr./a '.trim("{$profesional->persona->nombre} {$profesional->persona->apellido1}")
            : null;

        // Si el profesional no tiene correo configurado, usar el correo de operaciones/notificaciones de la clínica
        if (empty($emailDestino) || ! filter_var($emailDestino, FILTER_VALIDATE_EMAIL)) {
            $emailDestino = Configuracion::get('clinica_email', config('mail.from.address'));
            $nombreDestinatario = $nombreDestinatario ?? 'Equipo Clínico';
        }

        $emailEnviado = false;

        if (! empty($emailDestino) && filter_var($emailDestino, FILTER_VALIDATE_EMAIL)) {
            try {
                Mail::to($emailDestino)->send(new RecordatorioMailable($recordatorio));
                $emailEnviado = true;
            } catch (\Throwable $e) {
                Log::error("Error al enviar recordatorio ID {$recordatorio->id_recordatorio} por correo al trabajador: ".$e->getMessage());
            }
        }

        $recordatorio->update([
            'estado' => 'ENVIADO',
            'fecha_hora_envio' => Carbon::now(),
        ]);

        $mensaje = $emailEnviado
            ? "Recordatorio marcado como enviado y notificado al correo de {$nombreDestinatario} ({$emailDestino})."
            : 'Recordatorio marcado como enviado con éxito.';

        return redirect()->route('recordatorios.index')->with('success', $mensaje);
    }

    /**
     * Cancela o descarta un recordatorio.
     */
    public function cancelar(Recordatorio $recordatorio): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('recordatorios')) {
            abort(403, 'No tienes permisos para cancelar recordatorios.');
        }

        $recordatorio->update([
            'estado' => 'CANCELADO',
        ]);

        return redirect()->route('recordatorios.index')->with('success', 'Recordatorio cancelado.');
    }
}
