<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Atencion;
use App\Models\Internacion;
use App\Models\Profesional;
use App\Models\SeguimientoInternacion;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class InternacionWebController extends Controller
{
    /**
     * Lista predefinida de jaulas/caniles de la clínica.
     */
    public const CANILES = [
        'Canil C-1 (Caninos Gdes)',
        'Canil C-2 (Caninos Gdes)',
        'Canil C-3 (Caninos Med)',
        'Canil C-4 (Caninos Med)',
        'Canil C-5 (Caninos Peq)',
        'Canil C-6 (Caninos Peq)',
        'UCI-1 (Cuidados Críticos)',
        'UCI-2 (Cuidados Críticos)',
        'Felinos F-1 (Área Gatos)',
        'Felinos F-2 (Área Gatos)',
        'Aislamiento A-1 (Infecciosos)',
        'Aislamiento A-2 (Infecciosos)',
    ];

    /**
     * Tablero interactivo de caniles, pacientes internados y KPIs.
     */
    public function index(Request $request): View
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('internaciones')) {
            abort(403, 'No tienes permisos para acceder al módulo de hospitalización.');
        }

        $query = Internacion::with([
            'atencionVeterinaria.atencion.paciente.propietario.persona',
            'atencionVeterinaria.atencion.paciente.raza.especie',
            'seguimientos.profesional.persona',
        ]);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('motivo', 'ilike', "%{$search}%")
                    ->orWhere('jaula_numero', 'ilike', "%{$search}%")
                    ->orWhereHas('atencionVeterinaria.atencion.paciente', function ($qp) use ($search) {
                        $qp->where('nombre', 'ilike', "%{$search}%")
                            ->orWhereHas('propietario.persona', function ($qprop) use ($search) {
                                $qprop->where('nombre', 'ilike', "%{$search}%")
                                    ->orWhere('apellido1', 'ilike', "%{$search}%");
                            });
                    });
            });
        }

        $estado = $request->input('estado', 'ACTIVA');
        if ($estado !== 'TODOS') {
            $query->where('estado', $estado);
        }

        $internaciones = $query->orderByRaw("CASE WHEN estado = 'ACTIVA' THEN 0 ELSE 1 END")
            ->orderByDesc('fecha_hora_ingreso')
            ->paginate(15)
            ->withQueryString();

        // Mapeo del tablero visual de caniles
        $internacionesActivas = Internacion::with([
            'atencionVeterinaria.atencion.paciente.propietario.persona',
            'atencionVeterinaria.atencion.paciente.raza.especie',
            'seguimientos' => fn ($q) => $q->orderByDesc('fecha_hora')->limit(1),
        ])->where('estado', 'ACTIVA')->get()->keyBy('jaula_numero');

        $tableroCaniles = [];
        foreach (self::CANILES as $nombreCanil) {
            $ocupante = $internacionesActivas->get($nombreCanil);
            $tableroCaniles[] = [
                'nombre' => $nombreCanil,
                'ocupado' => (bool) $ocupante,
                'internacion' => $ocupante,
            ];
        }

        // KPIs
        $totalActivas = $internacionesActivas->count();
        $totalCaniles = count(self::CANILES);
        $canilesLibres = max(0, $totalCaniles - $totalActivas);
        $porcentajeOcupacion = $totalCaniles > 0 ? round(($totalActivas / $totalCaniles) * 100) : 0;
        $altasSemana = Internacion::where('estado', 'ALTA')
            ->where('fecha_hora_alta', '>=', Carbon::now()->subDays(7))
            ->count();

        // Atenciones veterinarias recientes que pueden ser ingresadas a hospitalización
        $atencionesDisponibles = Atencion::whereHas('atencionVeterinaria')
            ->whereDoesntHave('atencionVeterinaria.internacion', function ($q) {
                $q->where('estado', 'ACTIVA');
            })
            ->with(['paciente.propietario.persona', 'atencionVeterinaria'])
            ->orderByDesc('id_atencion')
            ->limit(25)
            ->get();

        $canilesDisponibles = array_values(array_filter(self::CANILES, fn ($c) => ! $internacionesActivas->has($c)));

        return view('internacion.index', compact(
            'internaciones',
            'tableroCaniles',
            'totalActivas',
            'canilesLibres',
            'porcentajeOcupacion',
            'altasSemana',
            'atencionesDisponibles',
            'canilesDisponibles'
        ));
    }

    /**
     * Registra un nuevo ingreso hospitalario.
     */
    public function store(Request $request): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('internaciones')) {
            abort(403, 'No tienes permisos para registrar internaciones.');
        }

        $validated = $request->validate([
            'id_atencion_ingreso' => ['required', 'integer', 'exists:atencion_veterinarias,id_atencion', 'unique:internaciones,id_atencion_ingreso'],
            'jaula_numero' => ['required', 'string', 'max:50'],
            'motivo' => ['required', 'string', 'max:500'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
        ], [
            'id_atencion_ingreso.unique' => 'Esta atención veterinaria ya se encuentra registrada en hospitalización.',
            'jaula_numero.required' => 'Debes asignar un canil o jaula al paciente.',
        ]);

        $internacion = Internacion::create([
            'id_atencion_ingreso' => $validated['id_atencion_ingreso'],
            'fecha_hora_ingreso' => Carbon::now(),
            'motivo' => $validated['motivo'],
            'estado' => 'ACTIVA',
            'jaula_numero' => $validated['jaula_numero'],
            'observaciones' => $validated['observaciones'] ?? null,
        ]);

        return redirect()->route('internaciones.show', $internacion->id_internacion)
            ->with('success', "Paciente ingresado exitosamente a {$internacion->jaula_numero}.");
    }

    /**
     * Ficha detallada de la internación: bitácora médica de turnos, signos vitales y evolución.
     */
    public function show(Internacion $internacion): View
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('internaciones')) {
            abort(403, 'No tienes permisos para ver hospitalizaciones.');
        }

        $internacion->load([
            'atencionVeterinaria.atencion.paciente.propietario.persona',
            'atencionVeterinaria.atencion.paciente.raza.especie',
            'atencionVeterinaria.atencion.participaciones.profesional.persona',
            'seguimientos.profesional.persona',
        ]);

        $profesionales = Profesional::with('persona')->where('estado', 'ACTIVO')->get();
        $ultimoSeguimiento = $internacion->seguimientos->first();

        // Calcular duración de la internación
        $ingreso = Carbon::parse($internacion->fecha_hora_ingreso);
        $fin = $internacion->fecha_hora_alta ? Carbon::parse($internacion->fecha_hora_alta) : Carbon::now();
        $diasInternado = max(1, (int) ceil($ingreso->diffInHours($fin) / 24));

        $canilesDisponibles = self::CANILES;

        return view('internacion.show', compact(
            'internacion',
            'profesionales',
            'ultimoSeguimiento',
            'diasInternado',
            'canilesDisponibles'
        ));
    }

    /**
     * Registra un nuevo chequeo de turno y evolución clínica del paciente.
     */
    public function storeSeguimiento(Request $request, Internacion $internacion): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('internaciones')) {
            abort(403, 'No tienes permisos para registrar evoluciones.');
        }

        if ($internacion->estado !== 'ACTIVA') {
            return back()->with('warning', 'No se pueden agregar evoluciones a un paciente dado de alta.');
        }

        $validated = $request->validate([
            'id_profesional' => ['nullable', 'integer', 'exists:profesionales,id_persona'],
            'temperatura' => ['nullable', 'numeric', 'min:30', 'max:45'],
            'frecuencia_cardiaca' => ['nullable', 'integer', 'min:30', 'max:300'],
            'frecuencia_respiratoria' => ['nullable', 'integer', 'min:5', 'max:150'],
            'peso' => ['nullable', 'numeric', 'min:0.1', 'max:120'],
            'estado_animo' => ['nullable', 'string', 'max:50'],
            'alimentacion' => ['nullable', 'string', 'max:50'],
            'hidratacion' => ['nullable', 'string', 'max:50'],
            'deposiciones' => ['nullable', 'string', 'max:50'],
            'orina' => ['nullable', 'string', 'max:50'],
            'cuidados' => ['nullable', 'string', 'max:1000'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
        ]);

        // Si no se seleccionó profesional, buscar si el usuario autenticado tiene registro en profesionales
        $idProfesional = $validated['id_profesional'] ?? null;
        if (! $idProfesional) {
            $prof = Profesional::find($currentUser->id_persona);
            $idProfesional = $prof?->id_persona ?? Profesional::first()?->id_persona;
        }

        SeguimientoInternacion::create([
            'id_internacion' => $internacion->id_internacion,
            'id_profesional' => $idProfesional,
            'fecha_hora' => Carbon::now(),
            'temperatura' => $validated['temperatura'] ?? null,
            'frecuencia_cardiaca' => $validated['frecuencia_cardiaca'] ?? null,
            'frecuencia_respiratoria' => $validated['frecuencia_respiratoria'] ?? null,
            'peso' => $validated['peso'] ?? null,
            'estado_animo' => $validated['estado_animo'] ?? null,
            'alimentacion' => $validated['alimentacion'] ?? null,
            'hidratacion' => $validated['hidratacion'] ?? null,
            'deposiciones' => $validated['deposiciones'] ?? null,
            'orina' => $validated['orina'] ?? null,
            'cuidados' => $validated['cuidados'] ?? null,
            'observaciones' => $validated['observaciones'] ?? null,
        ]);

        return redirect()->route('internaciones.show', $internacion->id_internacion)
            ->with('success', 'Evolución de turno registrada correctamente.');
    }

    /**
     * Da el alta médica al paciente y libera el canil.
     */
    public function darAlta(Request $request, Internacion $internacion): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('internaciones')) {
            abort(403, 'No tienes permisos para otorgar altas médicas.');
        }

        $validated = $request->validate([
            'observaciones_alta' => ['required', 'string', 'max:1000'],
        ], [
            'observaciones_alta.required' => 'Es necesario ingresar un resumen clínico o indicaciones del alta.',
        ]);

        $observacionesFinales = trim(
            ($internacion->observaciones ? $internacion->observaciones."\n\n" : '').
            '[ALTA MÉDICA '.Carbon::now()->format('d/m/Y H:i').']: '.$validated['observaciones_alta']
        );

        $internacion->update([
            'estado' => 'ALTA',
            'fecha_hora_alta' => Carbon::now(),
            'observaciones' => $observacionesFinales,
        ]);

        return redirect()->route('internaciones.show', $internacion->id_internacion)
            ->with('success', "Alta médica registrada con éxito. Canil {$internacion->jaula_numero} liberado.");
    }

    /**
     * Permite reubicar al paciente a otro canil o jaula.
     */
    public function cambiarJaula(Request $request, Internacion $internacion): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('internaciones')) {
            abort(403, 'No tienes permisos para modificar jaulas.');
        }

        $validated = $request->validate([
            'jaula_numero' => ['required', 'string', 'max:50'],
        ]);

        $internacion->update(['jaula_numero' => $validated['jaula_numero']]);

        return back()->with('success', "Paciente reubicado a {$internacion->jaula_numero}.");
    }
}
