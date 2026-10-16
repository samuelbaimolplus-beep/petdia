<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Atencion;
use App\Models\DetallePaquete;
use App\Models\Paciente;
use App\Models\Paquete;
use App\Models\Servicio;
use App\Models\Suscripcion;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PaqueteWebController extends Controller
{
    /**
     * Catálogo de paquetes con servicios incluidos, precios y KPIs.
     */
    public function index(Request $request): View
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('paquetes')) {
            abort(403, 'No tienes permisos para acceder a paquetes.');
        }

        $query = Paquete::with(['detallePaquetes.servicio', 'suscripciones'])->orderByDesc('id_paquete');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'ilike', "%{$search}%")
                    ->orWhere('descripcion', 'ilike', "%{$search}%");
            });
        }

        if ($estado = $request->input('estado')) {
            $query->where('estado', $estado);
        }

        $paquetes = $query->paginate(12)->withQueryString();

        // KPIs
        $totalPaquetes = Paquete::count();
        $paquetesActivos = Paquete::where('estado', 'ACTIVO')->count();
        $suscripcionesActivas = Suscripcion::where('estado', 'ACTIVO')->count();
        $totalPacientesSuscritos = Suscripcion::where('estado', 'ACTIVO')->distinct('id_paciente')->count('id_paciente');

        return view('paquetes.index', compact(
            'paquetes',
            'totalPaquetes',
            'paquetesActivos',
            'suscripcionesActivas',
            'totalPacientesSuscritos'
        ));
    }

    /**
     * Formulario de creación de paquete.
     */
    public function create(): View
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('paquetes')) {
            abort(403, 'No tienes permisos para crear paquetes.');
        }

        $servicios = Servicio::where('estado', 'ACTIVO')->orderBy('nombre')->get();

        return view('paquetes.create', compact('servicios'));
    }

    /**
     * Almacena un paquete con sus servicios incluidos y cantidades.
     */
    public function store(Request $request): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('paquetes')) {
            abort(403, 'No tienes permisos para crear paquetes.');
        }

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'precio' => ['required', 'numeric', 'min:0'],
            'estado' => ['required', 'string', 'in:ACTIVO,INACTIVO'],
            'servicios' => ['required', 'array', 'min:1'],
            'servicios.*.id_servicio' => ['required', 'integer', 'exists:servicios,id_servicio'],
            'servicios.*.cantidad_incluida' => ['required', 'integer', 'min:1'],
        ]);

        DB::transaction(function () use ($validated) {
            $paquete = Paquete::create([
                'nombre' => $validated['nombre'],
                'descripcion' => $validated['descripcion'] ?? null,
                'precio' => $validated['precio'],
                'estado' => $validated['estado'],
            ]);

            foreach ($validated['servicios'] as $srv) {
                DetallePaquete::create([
                    'id_paquete' => $paquete->id_paquete,
                    'id_servicio' => $srv['id_servicio'],
                    'cantidad_incluida' => $srv['cantidad_incluida'],
                ]);
            }
        });

        return redirect()->route('paquetes.index')->with('success', 'Paquete creado exitosamente con sus servicios incluidos.');
    }

    /**
     * Formulario de edición de un paquete.
     */
    public function edit(Paquete $paquete): View
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('paquetes')) {
            abort(403, 'No tienes permisos para editar paquetes.');
        }

        $paquete->load('detallePaquetes.servicio');
        $servicios = Servicio::where('estado', 'ACTIVO')->orderBy('nombre')->get();

        return view('paquetes.edit', compact('paquete', 'servicios'));
    }

    /**
     * Actualiza el paquete y sincroniza los servicios incluidos.
     */
    public function update(Request $request, Paquete $paquete): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('paquetes')) {
            abort(403, 'No tienes permisos para editar paquetes.');
        }

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'precio' => ['required', 'numeric', 'min:0'],
            'estado' => ['required', 'string', 'in:ACTIVO,INACTIVO'],
            'servicios' => ['required', 'array', 'min:1'],
            'servicios.*.id_servicio' => ['required', 'integer', 'exists:servicios,id_servicio'],
            'servicios.*.cantidad_incluida' => ['required', 'integer', 'min:1'],
        ]);

        DB::transaction(function () use ($validated, $paquete) {
            $paquete->update([
                'nombre' => $validated['nombre'],
                'descripcion' => $validated['descripcion'] ?? null,
                'precio' => $validated['precio'],
                'estado' => $validated['estado'],
            ]);

            // Reemplazar detalles
            $paquete->detallePaquetes()->delete();

            foreach ($validated['servicios'] as $srv) {
                DetallePaquete::create([
                    'id_paquete' => $paquete->id_paquete,
                    'id_servicio' => $srv['id_servicio'],
                    'cantidad_incluida' => $srv['cantidad_incluida'],
                ]);
            }
        });

        return redirect()->route('paquetes.index')->with('success', 'Paquete actualizado correctamente.');
    }

    /**
     * Alterna el estado ACTIVO / INACTIVO de un paquete.
     */
    public function toggleEstado(Paquete $paquete): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('paquetes')) {
            abort(403, 'No tienes permisos para modificar paquetes.');
        }

        $nuevoEstado = $paquete->estado === 'ACTIVO' ? 'INACTIVO' : 'ACTIVO';
        $paquete->update(['estado' => $nuevoEstado]);

        return redirect()->route('paquetes.index')->with('success', "Estado del paquete cambiado a {$nuevoEstado}.");
    }

    /**
     * Listado y gestión de suscripciones de pacientes a planes y paquetes.
     */
    public function suscripciones(Request $request): View
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('paquetes')) {
            abort(403, 'No tienes permisos para ver suscripciones.');
        }

        $query = Suscripcion::with([
            'paciente.propietario.persona',
            'paquete.detallePaquetes.servicio',
        ])->orderByDesc('id_suscripcion');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('paciente', function ($qp) use ($search) {
                    $qp->where('nombre', 'ilike', "%{$search}%")
                        ->orWhereHas('propietario.persona', function ($qpp) use ($search) {
                            $qpp->where('nombre', 'ilike', "%{$search}%")
                                ->orWhere('apellido1', 'ilike', "%{$search}%")
                                ->orWhere('documento', 'ilike', "%{$search}%");
                        });
                })->orWhereHas('paquete', function ($qp) use ($search) {
                    $qp->where('nombre', 'ilike', "%{$search}%");
                });
            });
        }

        if ($idPaquete = $request->input('id_paquete')) {
            $query->where('id_paquete', $idPaquete);
        }

        if ($estado = $request->input('estado')) {
            $query->where('estado', $estado);
        }

        $suscripciones = $query->paginate(15)->withQueryString();

        // Calcular asistencias consumidas vs total del paquete por suscripción
        $suscripciones->getCollection()->transform(function ($suscripcion) {
            $totalAsistencias = (int) $suscripcion->paquete->detallePaquetes->sum('cantidad_incluida');

            // Calcular atenciones realizadas al paciente durante la vigencia de la suscripción
            $serviciosIds = $suscripcion->paquete->detallePaquetes->pluck('id_servicio')->all();

            $atencionesConsumidas = Atencion::where('id_paciente', $suscripcion->id_paciente)
                ->where('estado', 'COMPLETADA')
                ->whereDate('fecha_hora_inicio', '>=', $suscripcion->fecha_inicio)
                ->where(function ($q) use ($suscripcion) {
                    if ($suscripcion->fecha_fin) {
                        $q->whereDate('fecha_hora_inicio', '<=', $suscripcion->fecha_fin);
                    }
                })
                ->whereHas('serviciosRealizados', function ($q) use ($serviciosIds) {
                    $q->whereIn('atencion_servicios.id_servicio', $serviciosIds);
                })
                ->count();

            $suscripcion->total_asistencias = max($totalAsistencias, 1);
            $suscripcion->asistencias_consumidas = min($atencionesConsumidas, $suscripcion->total_asistencias);
            $suscripcion->porcentaje_consumido = (int) min(100, round(($suscripcion->asistencias_consumidas / $suscripcion->total_asistencias) * 100));

            return $suscripcion;
        });

        $paquetesDisponibles = Paquete::where('estado', 'ACTIVO')->orderBy('nombre')->get();
        $pacientesDisponibles = Paciente::with(['propietario.persona', 'raza.especie'])
            ->where('estado', 'VIVO')
            ->orderBy('nombre')
            ->get();

        return view('paquetes.suscripciones', compact(
            'suscripciones',
            'paquetesDisponibles',
            'pacientesDisponibles'
        ));
    }

    /**
     * Registra una nueva suscripción de un paciente a un paquete.
     */
    public function storeSuscripcion(Request $request): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('paquetes')) {
            abort(403, 'No tienes permisos para registrar suscripciones.');
        }

        $validated = $request->validate([
            'id_paciente' => ['required', 'integer', 'exists:pacientes,id_paciente'],
            'id_paquete' => ['required', 'integer', 'exists:paquetes,id_paquete'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
        ]);

        $validated['estado'] = 'ACTIVO';
        Suscripcion::create($validated);

        return redirect()->route('paquetes.suscripciones')->with('success', 'Suscripción asignada al paciente exitosamente.');
    }

    /**
     * Cancela o finaliza una suscripción de cliente.
     */
    public function cancelarSuscripcion(Suscripcion $suscripcion): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('paquetes')) {
            abort(403, 'No tienes permisos para modificar suscripciones.');
        }

        $suscripcion->update(['estado' => 'CANCELADO']);

        return redirect()->route('paquetes.suscripciones')->with('success', 'Suscripción cancelada.');
    }
}
