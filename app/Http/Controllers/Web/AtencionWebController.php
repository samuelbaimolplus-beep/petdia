<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Atencion;
use App\Models\AtencionEstetica;
use App\Models\AtencionServicio;
use App\Models\AtencionVeterinaria;
use App\Models\Cita;
use App\Models\Diagnostico;
use App\Models\ObservacionRegistro;
use App\Models\Paciente;
use App\Models\ParticipacionAtencion;
use App\Models\Profesional;
use App\Models\RegistroSignosVital;
use App\Models\Servicio;
use App\Models\Tratamiento;
use App\Models\Usuario;
use App\Services\AuditService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AtencionWebController extends Controller
{
    /**
     * Listado general de atenciones con filtros, estados y KPIs.
     */
    public function index(Request $request): View
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('atenciones')) {
            abort(403, 'No tienes permisos para acceder al módulo de atenciones.');
        }

        $query = Atencion::with([
            'paciente.propietario.persona',
            'cita',
            'atencionVeterinaria',
            'atencionEstetica',
            'profesionales.persona',
            'atencionServicios.servicio',
        ]);

        // Filtro por búsqueda (mascota, propietario o ID)
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('id_atencion', 'ilike', "%{$search}%")
                    ->orWhere('observaciones', 'ilike', "%{$search}%")
                    ->orWhereHas('paciente', function ($qp) use ($search) {
                        $qp->where('nombre', 'ilike', "%{$search}%")
                            ->orWhereHas('propietario.persona', function ($qprop) use ($search) {
                                $qprop->where('nombre', 'ilike', "%{$search}%")
                                    ->orWhere('apellido1', 'ilike', "%{$search}%")
                                    ->orWhere('documento', 'ilike', "%{$search}%");
                            });
                    });
            });
        }

        // Filtro por tipo de atención
        if ($tipo = $request->input('tipo')) {
            if ($tipo === 'VETERINARIA') {
                $query->has('atencionVeterinaria');
            } elseif ($tipo === 'ESTETICA') {
                $query->has('atencionEstetica');
            } elseif ($tipo === 'URGENCIA') {
                $query->where(function ($q) {
                    $q->whereHas('cita', fn ($qc) => $qc->where('origen', 'URGENCIA'))
                        ->orWhereHas('atencionVeterinaria', fn ($qv) => $qv->where('tipo', 'EMERGENCIA'));
                });
            }
        }

        // Filtro por estado (EN_CURSO, FINALIZADA, CANCELADA)
        if ($estado = $request->input('estado')) {
            $query->where('estado', $estado);
        }

        // Filtro por fecha (por defecto hoy si se solicita)
        if ($fecha = $request->input('fecha')) {
            $query->whereDate('fecha_hora_inicio', $fecha);
        }

        $atenciones = $query->orderBy('fecha_hora_inicio', 'desc')
            ->paginate(12)
            ->withQueryString();

        // Métricas
        $hoy = Carbon::today()->toDateString();
        $totalHoy = Atencion::whereDate('fecha_hora_inicio', $hoy)->count();
        $enCursoCount = Atencion::where('estado', 'EN_CURSO')->count();
        $finalizadasHoy = Atencion::whereDate('fecha_hora_inicio', $hoy)->where('estado', 'FINALIZADA')->count();
        $esteticasCount = Atencion::has('atencionEstetica')->whereDate('fecha_hora_inicio', $hoy)->count();

        // Control de permisos de solo lectura
        $normalize = fn ($str) => strtoupper(str_replace(['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú', '-', ' '], ['a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U', '_', '_'], $str));
        $userRoles = $currentUser->roles->pluck('nombre')->map($normalize)->all();
        $isReadOnly = in_array('ADMIN', $userRoles, true) && ! in_array('SUPER_ADMIN', $userRoles, true) && ! in_array('VETERINARIO', $userRoles, true) && ! in_array('SPA', $userRoles, true);

        return view('atenciones.index', [
            'atenciones' => $atenciones,
            'isReadOnly' => $isReadOnly,
            'metrics' => [
                'total_hoy' => $totalHoy,
                'en_curso' => $enCursoCount,
                'finalizadas_hoy' => $finalizadasHoy,
                'esteticas_hoy' => $esteticasCount,
            ],
            'filters' => [
                'search' => $search,
                'tipo' => $tipo,
                'estado' => $estado,
                'fecha' => $fecha,
            ],
        ]);
    }

    /**
     * Formulario para iniciar una nueva atención regular (con o sin cita).
     */
    public function create(Request $request): View
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('atenciones')) {
            abort(403);
        }

        $citaId = $request->query('cita_id');
        $cita = null;
        if ($citaId) {
            $cita = Cita::with(['paciente.propietario.persona', 'profesional.persona', 'servicios'])->find($citaId);
        }

        $pacientes = Paciente::with(['propietario.persona', 'raza.especie'])
            ->where('estado', 'ACTIVO')
            ->orderBy('nombre')
            ->get();

        $profesionales = Profesional::with('persona')
            ->where('estado', 'ACTIVO')
            ->get();

        $servicios = Servicio::where('estado', 'ACTIVO')
            ->orderBy('nombre')
            ->get();

        return view('atenciones.create', [
            'cita' => $cita,
            'pacientes' => $pacientes,
            'profesionales' => $profesionales,
            'servicios' => $servicios,
            'esUrgencia' => false,
        ]);
    }

    /**
     * CU15: Formulario exclusivo para registrar una atención urgente sin cita previa.
     */
    public function createUrgente(): View
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('atenciones')) {
            abort(403);
        }

        $pacientes = Paciente::with(['propietario.persona', 'raza.especie'])
            ->where('estado', 'ACTIVO')
            ->orderBy('nombre')
            ->get();

        $profesionales = Profesional::with('persona')
            ->where('estado', 'ACTIVO')
            ->get();

        $servicios = Servicio::where('estado', 'ACTIVO')
            ->orderBy('nombre')
            ->get();

        return view('atenciones.create', [
            'cita' => null,
            'pacientes' => $pacientes,
            'profesionales' => $profesionales,
            'servicios' => $servicios,
            'esUrgencia' => true,
        ]);
    }

    /**
     * Guarda la nueva atención clínica o estética (CU31).
     */
    public function store(Request $request): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('atenciones')) {
            abort(403);
        }

        $validated = $request->validate([
            'id_paciente' => ['required', 'integer', 'exists:pacientes,id_paciente'],
            'id_cita' => ['nullable', 'integer', 'exists:citas,id_cita'],
            'id_profesional' => ['required', 'integer', 'exists:profesionales,id_persona'],
            'tipo_atencion' => ['required', 'string', 'in:VETERINARIA,ESTETICA'],
            'fecha_hora_inicio' => ['required', 'date'],
            'estado' => ['required', 'string', 'in:EN_CURSO,FINALIZADA'],
            'observaciones' => ['nullable', 'string', 'max:500'],
            'recomendaciones' => ['nullable', 'string', 'max:500'],

            // Variante Veterinaria
            'motivo_consulta' => ['nullable', 'string', 'max:255'],
            'anamnesis' => ['nullable', 'string'],
            'sintomas' => ['nullable', 'string'],
            'conclusion' => ['nullable', 'string'],
            'fecha_reconsulta' => ['nullable', 'date'],

            // Signos vitales iniciales
            'peso' => ['nullable', 'numeric', 'min:0'],
            'temperatura' => ['nullable', 'numeric', 'min:30', 'max:45'],
            'frecuencia_cardiaca' => ['nullable', 'integer', 'min:0'],
            'frecuencia_respiratoria' => ['nullable', 'integer', 'min:0'],

            // Variante Estética
            'modalidad' => ['nullable', 'string', 'max:100'],
            'estado_pelaje' => ['nullable', 'string', 'max:100'],
            'caracteristicas_observadas' => ['nullable', 'string'],

            // Servicios seleccionados
            'servicios' => ['nullable', 'array'],
            'servicios.*.id_servicio' => ['required_with:servicios', 'integer', 'exists:servicios,id_servicio'],
            'servicios.*.cantidad' => ['required_with:servicios', 'numeric', 'min:1'],
            'servicios.*.descuento' => ['nullable', 'numeric', 'min:0'],
        ]);

        $atencion = DB::transaction(function () use ($validated) {
            $totalFinal = 0.0;

            // Calcular total de servicios si existen
            $serviciosData = [];
            if (! empty($validated['servicios'])) {
                foreach ($validated['servicios'] as $srvInput) {
                    $servicioModel = Servicio::findOrFail($srvInput['id_servicio']);
                    $cantidad = (float) $srvInput['cantidad'];
                    $descuento = (float) ($srvInput['descuento'] ?? 0);
                    $precioUnitario = (float) $servicioModel->precio_base;
                    $subtotal = ($precioUnitario * $cantidad) - $descuento;
                    $totalFinal += max(0, $subtotal);

                    $serviciosData[] = [
                        'id_servicio' => $servicioModel->id_servicio,
                        'cantidad' => $cantidad,
                        'precio_unitario' => $precioUnitario,
                        'descuento' => $descuento,
                    ];
                }
            }

            $atencion = Atencion::create([
                'id_paciente' => $validated['id_paciente'],
                'id_cita' => $validated['id_cita'] ?? null,
                'fecha_hora_inicio' => $validated['fecha_hora_inicio'],
                'fecha_hora_fin' => $validated['estado'] === 'FINALIZADA' ? Carbon::now() : null,
                'estado' => $validated['estado'],
                'observaciones' => $validated['observaciones'] ?? null,
                'recomendaciones' => $validated['recomendaciones'] ?? null,
                'total_final' => $totalFinal,
            ]);

            // Asignar profesional principal (CU33)
            ParticipacionAtencion::create([
                'id_atencion' => $atencion->id_atencion,
                'id_profesional' => $validated['id_profesional'],
                'rol_atencion' => 'PRINCIPAL',
            ]);

            // Crear registros de servicios realizados (CU32)
            foreach ($serviciosData as $srv) {
                AtencionServicio::create(array_merge($srv, ['id_atencion' => $atencion->id_atencion]));
            }

            // Datos específicos según el tipo
            if ($validated['tipo_atencion'] === 'VETERINARIA') {
                $atencionVet = AtencionVeterinaria::create([
                    'id_atencion' => $atencion->id_atencion,
                    'motivo_consulta' => $validated['motivo_consulta'] ?? 'Consulta general',
                    'anamnesis' => $validated['anamnesis'] ?? null,
                    'sintomas' => $validated['sintomas'] ?? null,
                    'conclusion' => $validated['conclusion'] ?? null,
                    'fecha_reconsulta' => $validated['fecha_reconsulta'] ?? null,
                ]);

                // Registrar signos vitales si se ingresaron
                if (! empty($validated['peso']) || ! empty($validated['temperatura']) || ! empty($validated['frecuencia_cardiaca'])) {
                    RegistroSignosVital::create([
                        'id_atencion' => $atencion->id_atencion,
                        'fecha_hora' => now(),
                        'peso' => $validated['peso'] ?? null,
                        'temperatura' => $validated['temperatura'] ?? null,
                        'frecuencia_cardiaca' => $validated['frecuencia_cardiaca'] ?? null,
                        'frecuencia_respiratoria' => $validated['frecuencia_respiratoria'] ?? null,
                    ]);
                }
            } else {
                AtencionEstetica::create([
                    'id_atencion' => $atencion->id_atencion,
                    'modalidad' => $validated['modalidad'] ?? 'BAÑO Y CORTE',
                    'estado_pelaje' => $validated['estado_pelaje'] ?? 'NORMAL',
                    'caracteristicas_observadas' => $validated['caracteristicas_observadas'] ?? null,
                ]);
            }

            // Si venía de una cita, marcar la cita como ATENDIDA
            if (! empty($validated['id_cita'])) {
                Cita::where('id_cita', $validated['id_cita'])->update(['estado' => 'ATENDIDA']);
            }

            return $atencion;
        });

        AuditService::log(
            'REGISTRAR_ATENCION',
            'ATENCIONES',
            [
                'id_atencion' => $atencion->id_atencion,
                'id_paciente' => $atencion->id_paciente,
                'tipo' => $validated['tipo_atencion'],
                'estado' => $validated['estado'],
            ],
            $atencion,
            null,
            'EXITO',
            "Atención #{$atencion->id_atencion} ({$validated['tipo_atencion']}) registrada para paciente #{$atencion->id_paciente}"
        );

        return redirect()->route('atenciones.show', $atencion->id_atencion)
            ->with('success', 'Atención registrada exitosamente.');
    }

    /**
     * CU15: Guarda una atención urgente inmediata sin exigir cita previa.
     */
    public function storeUrgente(Request $request): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('atenciones')) {
            abort(403);
        }

        $validated = $request->validate([
            'id_paciente' => ['required', 'integer', 'exists:pacientes,id_paciente'],
            'id_profesional' => ['required', 'integer', 'exists:profesionales,id_persona'],
            'motivo_urgencia' => ['required', 'string', 'max:255'],
            'anamnesis' => ['nullable', 'string'],
            'sintomas' => ['nullable', 'string'],
            'peso' => ['nullable', 'numeric', 'min:0'],
            'temperatura' => ['nullable', 'numeric', 'min:30', 'max:45'],
            'frecuencia_cardiaca' => ['nullable', 'integer'],
            'observaciones' => ['nullable', 'string'],
        ], [
            'id_paciente.required' => 'Debe identificar el paciente para la atención urgente.',
            'id_profesional.required' => 'Debe asignar el profesional veterinario a cargo.',
            'motivo_urgencia.required' => 'El motivo de la urgencia es obligatorio.',
        ]);

        $atencion = DB::transaction(function () use ($validated) {
            $atencion = Atencion::create([
                'id_paciente' => $validated['id_paciente'],
                'id_cita' => null, // Urgencia sin cita previa
                'fecha_hora_inicio' => now(),
                'estado' => 'EN_CURSO',
                'observaciones' => $validated['observaciones'] ?? 'Atención médica de urgencia sin cita previa.',
            ]);

            ParticipacionAtencion::create([
                'id_atencion' => $atencion->id_atencion,
                'id_profesional' => $validated['id_profesional'],
                'rol_atencion' => 'PRINCIPAL',
                'observaciones' => 'Atención médica de emergencia',
            ]);

            AtencionVeterinaria::create([
                'id_atencion' => $atencion->id_atencion,
                'motivo_consulta' => '[URGENCIA] '.$validated['motivo_urgencia'],
                'tipo' => 'EMERGENCIA',
                'anamnesis' => $validated['anamnesis'] ?? 'Ingreso urgente a guardia clínica.',
                'sintomas' => $validated['sintomas'] ?? null,
            ]);

            if (! empty($validated['peso']) || ! empty($validated['temperatura']) || ! empty($validated['frecuencia_cardiaca'])) {
                RegistroSignosVital::create([
                    'id_atencion' => $atencion->id_atencion,
                    'fecha_hora' => now(),
                    'peso' => $validated['peso'] ?? null,
                    'temperatura' => $validated['temperatura'] ?? null,
                    'frecuencia_cardiaca' => $validated['frecuencia_cardiaca'] ?? null,
                ]);
            }

            return $atencion;
        });

        AuditService::log(
            'REGISTRAR_ATENCION_URGENTE',
            'ATENCIONES',
            [
                'id_atencion' => $atencion->id_atencion,
                'id_paciente' => $validated['id_paciente'],
                'motivo' => $validated['motivo_urgencia'],
            ],
            $atencion,
            null,
            'EXITO',
            "Atención urgente #{$atencion->id_atencion} iniciada para paciente #{$validated['id_paciente']}"
        );

        return redirect()->route('atenciones.show', $atencion->id_atencion)
            ->with('success', 'Atención urgente iniciada correctamente. Puede agregar diagnósticos, tratamientos y procedimientos.');
    }

    /**
     * Muestra la ficha completa y resumen de la atención con todos sus componentes clínicos.
     */
    public function show(int $id): View
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('atenciones')) {
            abort(403);
        }

        $atencion = Atencion::with([
            'paciente.propietario.persona',
            'paciente.raza.especie',
            'cita',
            'atencionVeterinaria.diagnosticos',
            'atencionVeterinaria.tratamientos',
            'atencionVeterinaria.procedimientos.estudioLaboratorio',
            'atencionVeterinaria.procedimientos.cirugia',
            'atencionVeterinaria.registrosSignosVitales',
            'atencionEstetica',
            'participaciones.profesional.persona',
            'atencionServicios.servicio',
            'observacionesRegistros.usuario.persona',
        ])->findOrFail($id);

        $profesionales = Profesional::with('persona')->where('estado', 'ACTIVO')->get();
        $servicios = Servicio::where('estado', 'ACTIVO')->orderBy('nombre')->get();

        $normalize = fn ($str) => strtoupper(str_replace(['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú', '-', ' '], ['a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U', '_', '_'], $str));
        $userRoles = $currentUser->roles->pluck('nombre')->map($normalize)->all();
        $isReadOnly = in_array('ADMIN', $userRoles, true) && ! in_array('SUPER_ADMIN', $userRoles, true) && ! in_array('VETERINARIO', $userRoles, true) && ! in_array('SPA', $userRoles, true);
        $canClinical = in_array('VETERINARIO', $userRoles, true) || in_array('SUPER_ADMIN', $userRoles, true);

        return view('atenciones.show', [
            'atencion' => $atencion,
            'profesionales' => $profesionales,
            'servicios' => $servicios,
            'isReadOnly' => $isReadOnly,
            'canClinical' => $canClinical,
        ]);
    }

    /**
     * CU9: Registra un diagnóstico asociado a la atención veterinaria.
     */
    public function storeDiagnostico(Request $request, int $id): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->hasRole(['VETERINARIO', 'SUPER_ADMIN'])) {
            abort(403, 'Solo el personal médico veterinario puede registrar diagnósticos.');
        }

        $atencion = Atencion::with('atencionVeterinaria')->findOrFail($id);
        if (! $atencion->atencionVeterinaria) {
            return back()->with('error', 'Solo las atenciones de tipo veterinario admiten diagnósticos clínicos.');
        }

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'tipo' => ['required', 'string', 'in:PRESUNTIVO,DEFINITIVO'],
            'descripcion' => ['nullable', 'string'],
            'observaciones' => ['nullable', 'string'],
        ], [
            'nombre.required' => 'El nombre del diagnóstico es obligatorio.',
        ]);

        $diag = Diagnostico::create([
            'id_atencion' => $atencion->id_atencion,
            'nombre' => $validated['nombre'],
            'tipo' => $validated['tipo'],
            'descripcion' => $validated['descripcion'] ?? null,
            'observaciones' => $validated['observaciones'] ?? null,
        ]);

        AuditService::log(
            'REGISTRAR_DIAGNOSTICO',
            'HISTORIAL',
            [
                'id_atencion' => $atencion->id_atencion,
                'id_diagnostico' => $diag->id_diagnostico,
                'nombre' => $diag->nombre,
                'tipo' => $diag->tipo,
            ],
            $diag,
            null,
            'EXITO',
            "Diagnóstico '{$diag->nombre}' registrado en atención #{$atencion->id_atencion}"
        );

        return back()->with('success', 'Diagnóstico registrado exitosamente.');
    }

    /**
     * CU9: Actualiza un diagnóstico existente.
     */
    public function updateDiagnostico(Request $request, int $id, int $idDiagnostico): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->hasRole(['VETERINARIO', 'SUPER_ADMIN'])) {
            abort(403, 'Solo el personal médico veterinario puede modificar diagnósticos.');
        }

        $diag = Diagnostico::where('id_atencion', $id)->findOrFail($idDiagnostico);

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'tipo' => ['required', 'string', 'in:PRESUNTIVO,DEFINITIVO'],
            'descripcion' => ['nullable', 'string'],
            'observaciones' => ['nullable', 'string'],
        ]);

        $diag->update($validated);

        AuditService::log(
            'MODIFICAR_DIAGNOSTICO',
            'HISTORIAL',
            ['id_diagnostico' => $diag->id_diagnostico, 'nombre' => $diag->nombre],
            $diag,
            null,
            'EXITO',
            "Diagnóstico #{$diag->id_diagnostico} actualizado en atención #{$id}"
        );

        return back()->with('success', 'Diagnóstico actualizado correctamente.');
    }

    /**
     * CU9: Registra un tratamiento asociado al paciente y a la atención.
     */
    public function storeTratamiento(Request $request, int $id): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->hasRole(['VETERINARIO', 'SUPER_ADMIN'])) {
            abort(403, 'Solo el personal médico veterinario puede indicar tratamientos.');
        }

        $atencion = Atencion::findOrFail($id);

        $medicamento = $request->input('medicamento');
        $descripcion = $request->input('descripcion');
        if (empty($descripcion) && ! empty($medicamento)) {
            $descripcion = trim($medicamento.' '.($request->input('dosis') ? 'Dosis: '.$request->input('dosis') : '').' '.($request->input('frecuencia') ? 'Frecuencia: '.$request->input('frecuencia') : ''));
        }
        $request->merge([
            'descripcion' => $descripcion,
            'fecha_inicio' => $request->input('fecha_inicio') ?: now()->toDateString(),
            'estado' => $request->input('estado') ?: 'ACTIVO',
            'indicaciones' => $request->input('indicaciones') ?: $request->input('instrucciones'),
        ]);

        $validated = $request->validate([
            'descripcion' => ['required', 'string'],
            'indicaciones' => ['nullable', 'string'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['nullable', 'date'],
            'estado' => ['required', 'string', 'in:ACTIVO,SUSPENDIDO,COMPLETADO'],
            'observaciones' => ['nullable', 'string'],
        ], [
            'descripcion.required' => 'La descripción del tratamiento es obligatoria.',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
        ]);

        $tratamiento = DB::transaction(function () use ($validated, $atencion) {
            $trat = Tratamiento::create([
                'id_paciente' => $atencion->id_paciente,
                'descripcion' => $validated['descripcion'],
                'indicaciones' => $validated['indicaciones'] ?? null,
                'fecha_inicio' => $validated['fecha_inicio'],
                'fecha_fin' => $validated['fecha_fin'] ?? null,
                'estado' => $validated['estado'],
                'observaciones' => $validated['observaciones'] ?? null,
            ]);

            DB::table('atencion_tratamiento')->insert([
                'id_atencion' => $atencion->id_atencion,
                'id_tratamiento' => $trat->id_tratamiento,
                'tipo_relacion' => 'PRESCRIPCION',
            ]);

            return $trat;
        });

        AuditService::log(
            'REGISTRAR_TRATAMIENTO',
            'HISTORIAL',
            [
                'id_atencion' => $atencion->id_atencion,
                'id_tratamiento' => $tratamiento->id_tratamiento,
                'id_paciente' => $atencion->id_paciente,
            ],
            $tratamiento,
            null,
            'EXITO',
            "Tratamiento indicado en atención #{$atencion->id_atencion} para paciente #{$atencion->id_paciente}"
        );

        return back()->with('success', 'Tratamiento registrado exitosamente.');
    }

    /**
     * CU9: Actualiza un tratamiento indicado.
     */
    public function updateTratamiento(Request $request, int $id, int $idTratamiento): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->hasRole(['VETERINARIO', 'SUPER_ADMIN'])) {
            abort(403, 'Solo el personal médico veterinario puede modificar tratamientos.');
        }

        $tratamiento = Tratamiento::findOrFail($idTratamiento);

        $validated = $request->validate([
            'descripcion' => ['required', 'string'],
            'indicaciones' => ['nullable', 'string'],
            'fecha_fin' => ['nullable', 'date'],
            'estado' => ['required', 'string', 'in:ACTIVO,SUSPENDIDO,COMPLETADO'],
            'observaciones' => ['nullable', 'string'],
        ]);

        $tratamiento->update($validated);

        AuditService::log(
            'MODIFICAR_TRATAMIENTO',
            'HISTORIAL',
            ['id_tratamiento' => $tratamiento->id_tratamiento, 'estado' => $tratamiento->estado],
            $tratamiento,
            null,
            'EXITO',
            "Tratamiento #{$tratamiento->id_tratamiento} actualizado"
        );

        return back()->with('success', 'Tratamiento actualizado correctamente.');
    }

    /**
     * CU32: Registra un servicio efectivamente prestado durante la atención.
     */
    public function storeServicio(Request $request, int $id): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('atenciones')) {
            abort(403);
        }

        $atencion = Atencion::findOrFail($id);

        $validated = $request->validate([
            'id_servicio' => ['required', 'integer', 'exists:servicios,id_servicio'],
            'cantidad' => ['required', 'numeric', 'min:1'],
            'descuento' => ['nullable', 'numeric', 'min:0'],
        ]);

        $servicioModel = Servicio::findOrFail($validated['id_servicio']);
        $cantidad = (float) $validated['cantidad'];
        $descuento = (float) ($validated['descuento'] ?? 0);
        $precioUnitario = (float) $servicioModel->precio_base;

        AtencionServicio::create([
            'id_atencion' => $atencion->id_atencion,
            'id_servicio' => $servicioModel->id_servicio,
            'cantidad' => $cantidad,
            'precio_unitario' => $precioUnitario,
            'descuento' => $descuento,
        ]);

        // Recalcular total de la atención
        $recalculo = AtencionServicio::where('id_atencion', $atencion->id_atencion)->get()->sum(function ($item) {
            return ($item->cantidad * $item->precio_unitario) - ($item->descuento ?? 0);
        });
        $atencion->update(['total_final' => max(0, $recalculo)]);

        AuditService::log(
            'REGISTRAR_SERVICIO_ATENCION',
            'ATENCIONES',
            [
                'id_atencion' => $atencion->id_atencion,
                'id_servicio' => $servicioModel->id_servicio,
                'servicio' => $servicioModel->nombre,
                'cantidad' => $cantidad,
            ],
            $atencion,
            null,
            'EXITO',
            "Servicio '{$servicioModel->nombre}' agregado a atención #{$atencion->id_atencion}"
        );

        return back()->with('success', "Servicio '{$servicioModel->nombre}' añadido a la atención.");
    }

    /**
     * CU32: Elimina un servicio de la atención.
     */
    public function deleteServicio(int $id, int $idServicio): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('atenciones')) {
            abort(403);
        }

        $atencion = Atencion::findOrFail($id);
        $item = AtencionServicio::where('id_atencion', $id)
            ->where(function ($q) use ($idServicio) {
                $q->where('id_atencion_servicio', $idServicio)
                    ->orWhere('id_servicio', $idServicio);
            })->firstOrFail();
        $item->delete();

        // Recalcular total
        $recalculo = AtencionServicio::where('id_atencion', $atencion->id_atencion)->get()->sum(function ($it) {
            return ($it->cantidad * $it->precio_unitario) - ($it->descuento ?? 0);
        });
        $atencion->update(['total_final' => max(0, $recalculo)]);

        AuditService::log(
            'ELIMINAR_SERVICIO_ATENCION',
            'ATENCIONES',
            ['id_atencion' => $id, 'id_atencion_servicio' => $idServicio],
            $atencion,
            null,
            'EXITO',
            "Servicio removido de atención #{$id}"
        );

        return back()->with('success', 'Servicio removido de la atención.');
    }

    /**
     * CU33: Asocia un profesional participante a la atención.
     */
    public function storeParticipante(Request $request, int $id): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('atenciones')) {
            abort(403);
        }

        $atencion = Atencion::findOrFail($id);

        $validated = $request->validate([
            'id_profesional' => ['required', 'integer', 'exists:profesionales,id_persona'],
            'rol_atencion' => ['required', 'string', 'in:PRINCIPAL,ASISTENTE,CIRUJANO,ANESTESISTA,OBSERVADOR,GROOMER'],
            'observaciones' => ['nullable', 'string', 'max:255'],
        ]);

        $profesional = Profesional::with('persona')->findOrFail($validated['id_profesional']);
        if ($profesional->estado !== 'ACTIVO') {
            return back()->withErrors(['id_profesional' => 'El profesional seleccionado no se encuentra habilitado/activo.']);
        }

        ParticipacionAtencion::create([
            'id_atencion' => $atencion->id_atencion,
            'id_profesional' => $profesional->id_persona,
            'rol_atencion' => $validated['rol_atencion'],
            'observaciones' => $validated['observaciones'] ?? null,
        ]);

        AuditService::log(
            'AGREGAR_PROFESIONAL_PARTICIPANTE',
            'ATENCIONES',
            [
                'id_atencion' => $atencion->id_atencion,
                'id_profesional' => $profesional->id_persona,
                'rol_atencion' => $validated['rol_atencion'],
            ],
            $atencion,
            null,
            'EXITO',
            "Profesional {$profesional->persona->nombre} agregado como {$validated['rol_atencion']} en atención #{$atencion->id_atencion}"
        );

        return back()->with('success', 'Profesional participante registrado en la atención.');
    }

    /**
     * CU33: Remueve un profesional participante de la atención.
     */
    public function deleteParticipante(int $id, int $idParticipacion): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('atenciones')) {
            abort(403);
        }

        $participacion = ParticipacionAtencion::where('id_atencion', $id)->findOrFail($idParticipacion);
        $participacion->delete();

        return back()->with('success', 'Profesional participante removido.');
    }

    /**
     * CU34: Registra una observación clínica o administrativa vinculada a la atención y mascota.
     */
    public function storeObservacion(Request $request, int $id): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();

        $validated = $request->validate([
            'tipo' => ['required', 'string', 'in:CLINICA,ADMINISTRATIVA'],
            'categoria' => ['nullable', 'string', 'max:50'],
            'observacion' => ['required', 'string'],
        ], [
            'observacion.required' => 'Debe ingresar el texto de la observación.',
        ]);

        // Restricción: solo personal clínico puede registrar observaciones clínicas
        if ($validated['tipo'] === 'CLINICA' && ! $currentUser->hasRole(['VETERINARIO', 'SUPER_ADMIN'])) {
            abort(403, 'No tienes privilegios para registrar observaciones de índole clínica.');
        }

        $atencion = Atencion::findOrFail($id);

        $obs = ObservacionRegistro::create([
            'id_paciente' => $atencion->id_paciente,
            'id_atencion' => $atencion->id_atencion,
            'id_usuario' => $currentUser->id_usuario,
            'tipo' => $validated['tipo'],
            'categoria' => $validated['categoria'] ?? 'GENERAL',
            'observacion' => $validated['observacion'],
            'fecha_hora' => now(),
        ]);

        AuditService::log(
            'REGISTRAR_OBSERVACION',
            'ATENCIONES',
            [
                'id_atencion' => $atencion->id_atencion,
                'id_observacion' => $obs->id_observacion,
                'tipo' => $obs->tipo,
            ],
            $obs,
            null,
            'EXITO',
            "Observación {$obs->tipo} registrada por {$currentUser->nombre_usuario} en atención #{$atencion->id_atencion}"
        );

        return back()->with('success', "Observación {$validated['tipo']} agregada correctamente.");
    }

    /**
     * Finaliza una atención en curso y calcula resumen para pase a Caja.
     */
    public function finalizar(Request $request, int $id): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('atenciones')) {
            abort(403);
        }

        $atencion = Atencion::with('atencionServicios')->findOrFail($id);

        $recalculoTotal = $atencion->atencionServicios->sum(function ($item) {
            return ($item->cantidad * $item->precio_unitario) - ($item->descuento ?? 0);
        });

        $atencion->update([
            'estado' => 'FINALIZADA',
            'fecha_hora_fin' => Carbon::now(),
            'total_final' => max(0, $recalculoTotal),
            'observaciones' => $request->input('observaciones', $atencion->observaciones),
            'recomendaciones' => $request->input('recomendaciones', $atencion->recomendaciones),
        ]);

        AuditService::log(
            'FINALIZAR_ATENCION',
            'ATENCIONES',
            ['id_atencion' => $atencion->id_atencion, 'total_final' => $recalculoTotal],
            $atencion,
            null,
            'EXITO',
            "Atención #{$atencion->id_atencion} finalizada con total de Bs. {$recalculoTotal}"
        );

        return redirect()->route('atenciones.show', $atencion->id_atencion)
            ->with('success', 'Atención finalizada con éxito. Puede proceder al cobro en Caja.');
    }

    /**
     * Cancela una atención.
     */
    public function cancelar(Request $request, int $id): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('atenciones')) {
            abort(403);
        }

        $atencion = Atencion::findOrFail($id);
        $motivo = $request->input('motivo', 'Cancelada por el profesional.');

        $atencion->update([
            'estado' => 'CANCELADA',
            'observaciones' => trim(($atencion->observaciones ?? '')." [Cancelada: {$motivo}]"),
        ]);

        AuditService::log(
            'CANCELAR_ATENCION',
            'ATENCIONES',
            ['id_atencion' => $atencion->id_atencion, 'motivo' => $motivo],
            $atencion,
            null,
            'EXITO',
            "Atención #{$atencion->id_atencion} cancelada: {$motivo}"
        );

        return redirect()->route('atenciones.show', $atencion->id_atencion)
            ->with('success', 'Atención cancelada correctamente.');
    }
}
