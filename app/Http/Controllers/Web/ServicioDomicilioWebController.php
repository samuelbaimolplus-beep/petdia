<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\DetalleCita;
use App\Models\Paciente;
use App\Models\Profesional;
use App\Models\Servicio;
use App\Models\ServicioDomicilio;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ServicioDomicilioWebController extends Controller
{
    /**
     * Listado e itinerario de servicios a domicilio y transporte.
     */
    public function index(Request $request): View
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('servicio_domicilio')) {
            abort(403, 'No tienes permisos para acceder a servicios a domicilio.');
        }

        $query = ServicioDomicilio::with([
            'cita.paciente.propietario.persona',
            'cita.profesional.persona',
            'cita.detalleCitas.servicio',
        ]);

        // Si es rol TRANSPORTE, por defecto prioriza traslados de hoy
        $activeRole = ((app()->isLocal() || app()->runningUnitTests()) && session()->has('demo_role'))
            ? strtoupper(session('demo_role'))
            : strtoupper($currentUser->rol_principal ?? '');
        $esTransporte = ($activeRole === 'TRANSPORTE');

        // Búsqueda por dirección, referencia, paciente o propietario
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('direccion', 'ilike', "%{$search}%")
                    ->orWhere('referencia', 'ilike', "%{$search}%")
                    ->orWhereHas('cita.paciente', function ($qp) use ($search) {
                        $qp->where('nombre', 'ilike', "%{$search}%")
                            ->orWhereHas('propietario.persona', function ($qprop) use ($search) {
                                $qprop->where('nombre', 'ilike', "%{$search}%")
                                    ->orWhere('apellido1', 'ilike', "%{$search}%")
                                    ->orWhere('documento', 'ilike', "%{$search}%");
                            });
                    });
            });
        }

        // Filtro por Estado
        if ($estado = $request->input('estado')) {
            $query->where('estado', $estado);
        }

        // Filtro por Fecha de la Cita
        if ($fecha = $request->input('fecha')) {
            $query->whereHas('cita', function ($q) use ($fecha) {
                $q->whereDate('fecha_hora_inicio', $fecha);
            });
        } elseif ($esTransporte && ! $request->has('todos')) {
            // Conductor ve hoy por defecto
            $query->whereHas('cita', function ($q) {
                $q->whereDate('fecha_hora_inicio', Carbon::today());
            });
        }

        $solicitudes = $query->orderByDesc('id_servicio_domicilio')->paginate(15)->withQueryString();

        // KPIs de Traslado
        $trasladosHoy = ServicioDomicilio::whereHas('cita', function ($q) {
            $q->whereDate('fecha_hora_inicio', Carbon::today());
        })->count();

        $enRuta = ServicioDomicilio::where('estado', 'EN_RUTA')->count();
        $completadosHoy = ServicioDomicilio::where('estado', 'COMPLETADO')
            ->whereHas('cita', function ($q) {
                $q->whereDate('fecha_hora_inicio', Carbon::today());
            })->count();
        $pendientes = ServicioDomicilio::where(function ($q) {
            $q->where('estado', 'PENDIENTE')->orWhereNull('estado');
        })->count();

        return view('domicilios.index', compact(
            'solicitudes',
            'trasladosHoy',
            'enRuta',
            'completadosHoy',
            'pendientes',
            'esTransporte'
        ));
    }

    /**
     * Formulario para solicitar una visita o traslado a domicilio.
     */
    public function create(): View
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('servicio_domicilio')) {
            abort(403, 'No tienes permisos para registrar servicios a domicilio.');
        }

        $pacientes = Paciente::with('propietario.persona')->where('estado', 'ACTIVO')->get();
        $profesionales = Profesional::with('persona')->where('estado', 'ACTIVO')->get();
        $serviciosDomicilio = Servicio::where('estado', 'ACTIVO')
            ->where('disponible_domicilio', true)
            ->get();

        if ($serviciosDomicilio->isEmpty()) {
            $serviciosDomicilio = Servicio::where('estado', 'ACTIVO')->get();
        }

        return view('domicilios.create', compact('pacientes', 'profesionales', 'serviciosDomicilio'));
    }

    /**
     * Almacena una nueva solicitud de servicio a domicilio vinculada a una cita.
     */
    public function store(Request $request): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('servicio_domicilio')) {
            abort(403, 'No tienes permisos para registrar servicios a domicilio.');
        }

        $validated = $request->validate([
            'id_paciente' => ['required', 'integer', 'exists:pacientes,id_paciente'],
            'id_profesional' => ['nullable', 'integer', 'exists:profesionales,id_persona'],
            'fecha' => ['required', 'date'],
            'hora' => ['required', 'string'],
            'direccion' => ['required', 'string', 'max:255'],
            'referencia' => ['nullable', 'string', 'max:255'],
            'distancia_estimada' => ['nullable', 'numeric', 'min:0'],
            'costo_domicilio' => ['nullable', 'numeric', 'min:0'],
            'servicios' => ['required', 'array', 'min:1'],
            'servicios.*' => ['integer', 'exists:servicios,id_servicio'],
            'observaciones' => ['nullable', 'string', 'max:500'],
        ]);

        return DB::transaction(function () use ($validated) {
            $inicio = Carbon::parse($validated['fecha'].' '.$validated['hora']);
            $fin = (clone $inicio)->addMinutes(60);

            // 1. Crear Cita con origen DOMICILIO
            $cita = Cita::create([
                'id_paciente' => $validated['id_paciente'],
                'id_profesional' => $validated['id_profesional'] ?? null,
                'fecha_hora_inicio' => $inicio,
                'fecha_hora_fin' => $fin,
                'estado' => 'CONFIRMADA',
                'origen' => 'DOMICILIO',
            ]);

            // 2. Asociar Servicios a la Cita
            foreach ($validated['servicios'] as $idServicio) {
                $serv = Servicio::find($idServicio);
                DetalleCita::create([
                    'id_cita' => $cita->id_cita,
                    'id_servicio' => $idServicio,
                    'costo_estimado' => $serv?->precio_base ?? 0,
                    'tiempo_estimado_minutos' => 45,
                ]);
            }

            // 3. Crear Registro de Servicio a Domicilio
            $domicilio = ServicioDomicilio::create([
                'id_cita' => $cita->id_cita,
                'direccion' => $validated['direccion'],
                'referencia' => $validated['referencia'] ?? null,
                'distancia_estimada' => $validated['distancia_estimada'] ?? 5.0,
                'costo_domicilio' => $validated['costo_domicilio'] ?? 30.00,
                'estado' => 'PENDIENTE',
            ]);

            return redirect()->route('domicilios.index')->with('success', "Servicio a domicilio #{$domicilio->id_servicio_domicilio} programado con éxito.");
        });
    }

    /**
     * Actualiza el estado de la ruta o visita a domicilio.
     */
    public function cambiarEstado(Request $request, ServicioDomicilio $domicilio): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('servicio_domicilio')) {
            abort(403, 'No tienes permisos para modificar servicios a domicilio.');
        }

        $validated = $request->validate([
            'estado' => ['required', 'string', 'in:PENDIENTE,EN_RUTA,COMPLETADO,CANCELADO'],
        ]);

        $domicilio->update(['estado' => $validated['estado']]);

        // Si se completa o cancela el traslado, reflejar en la cita si aplica
        if ($validated['estado'] === 'COMPLETADO' && $domicilio->cita) {
            $domicilio->cita->update(['estado' => 'COMPLETADA']);
        } elseif ($validated['estado'] === 'CANCELADO' && $domicilio->cita) {
            $domicilio->cita->update(['estado' => 'CANCELADA']);
        }

        return redirect()->back()->with('success', "Estado del traslado actualizado a {$validated['estado']}.");
    }
}
