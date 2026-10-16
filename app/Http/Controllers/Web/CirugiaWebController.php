<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Atencion;
use App\Models\AtencionVeterinaria;
use App\Models\Cirugia;
use App\Models\ControlCirugia;
use App\Models\Paciente;
use App\Models\Procedimiento;
use App\Models\Profesional;
use App\Models\Usuario;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CirugiaWebController extends Controller
{
    /**
     * Tablero general de quirófano y cirugías programadas.
     */
    public function index(Request $request): View
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();

        if (! $currentUser->canAccessModule('cirugias')) {
            abort(403, 'Acceso denegado al módulo de quirófano y cirugías.');
        }

        $query = Cirugia::with([
            'procedimiento.atencionVeterinaria.atencion.paciente.propietario.persona',
            'procedimiento.atencionVeterinaria.atencion.paciente.raza.especie',
            'cirujano.persona',
            'anestesista.persona',
            'controles.profesional.persona',
        ])->orderBy('fecha_programada', 'desc');

        // Filtro por estado
        $estado = $request->input('estado');
        if ($estado && in_array($estado, ['PROGRAMADA', 'EN_QUIROFANO', 'COMPLETADA', 'CANCELADA'], true)) {
            $query->where('estado', $estado);
        }

        // Búsqueda por nombre de mascota o tipo de cirugía
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('tipo_cirugia', 'ilike', "%{$search}%")
                    ->orWhere('diagnostico_preoperatorio', 'ilike', "%{$search}%")
                    ->orWhereHas('procedimiento.atencionVeterinaria.atencion.paciente', function ($qp) use ($search) {
                        $qp->where('nombre', 'ilike', "%{$search}%")
                            ->orWhereHas('propietario.persona', function ($qprop) use ($search) {
                                $qprop->where('nombre', 'ilike', "%{$search}%")
                                    ->orWhere('apellido1', 'ilike', "%{$search}%");
                            });
                    });
            });
        }

        $cirugias = $query->paginate(12)->withQueryString();

        // KPIs de Quirófano
        $totalHoy = Cirugia::whereDate('fecha_programada', today())->count();
        $totalEnQuirofano = Cirugia::where('estado', 'EN_QUIROFANO')->count();
        $totalProgramadas = Cirugia::where('estado', 'PROGRAMADA')->count();
        $totalCompletadas = Cirugia::where('estado', 'COMPLETADA')->count();

        // Catálogos para el modal de programación
        $pacientes = Paciente::with(['raza.especie', 'propietario.persona'])->where('estado', 'ACTIVO')->orderBy('nombre')->get();
        $profesionales = Profesional::with('persona')->orderBy('id_persona')->get();

        return view('cirugia.index', [
            'cirugias' => $cirugias,
            'totalHoy' => $totalHoy,
            'totalEnQuirofano' => $totalEnQuirofano,
            'totalProgramadas' => $totalProgramadas,
            'totalCompletadas' => $totalCompletadas,
            'pacientes' => $pacientes,
            'profesionales' => $profesionales,
            'filters' => [
                'estado' => $estado,
                'search' => $search,
            ],
        ]);
    }

    /**
     * Registra y programa una nueva intervención quirúrgica.
     */
    public function store(Request $request): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();

        if (! $currentUser->canAccessModule('cirugias')) {
            abort(403, 'No tienes privilegios para programar intervenciones quirúrgicas.');
        }

        $validated = $request->validate([
            'id_paciente' => ['required', 'exists:pacientes,id_paciente'],
            'tipo_cirugia' => ['required', 'string', 'max:100'],
            'fecha_programada' => ['required', 'date'],
            'diagnostico_preoperatorio' => ['nullable', 'string', 'max:500'],
            'riesgo_asa' => ['required', 'string', 'in:ASA I,ASA II,ASA III,ASA IV,ASA V'],
            'tipo_anestesia' => ['nullable', 'string', 'max:80'],
            'ayuno_solidos_horas' => ['nullable', 'integer', 'min:0', 'max:48'],
            'ayuno_liquidos_horas' => ['nullable', 'integer', 'min:0', 'max:24'],
            'id_cirujano' => ['nullable', 'exists:profesionales,id_persona'],
            'id_anestesista' => ['nullable', 'exists:profesionales,id_persona'],
            'observaciones_prequirurgicas' => ['nullable', 'string'],
        ], [
            'id_paciente.required' => 'Debes seleccionar un paciente.',
            'tipo_cirugia.required' => 'El tipo de cirugía es obligatorio.',
            'fecha_programada.required' => 'La fecha y hora programada es obligatoria.',
            'riesgo_asa.required' => 'La valoración ASA es obligatoria.',
        ]);

        $cirugia = DB::transaction(function () use ($validated, $currentUser) {
            $paciente = Paciente::findOrFail($validated['id_paciente']);

            // Crear registro maestro de atención quirúrgica
            $atencion = Atencion::create([
                'id_paciente' => $paciente->id_paciente,
                'fecha_hora_inicio' => $validated['fecha_programada'],
                'estado' => 'EN_PROCESO',
                'observaciones' => 'Intervención quirúrgica programada: '.$validated['tipo_cirugia'],
            ]);

            $atencionVet = AtencionVeterinaria::create([
                'id_atencion' => $atencion->id_atencion,
                'motivo_consulta' => 'Cirugía: '.$validated['tipo_cirugia'],
                'tipo' => 'CIRUGIA',
                'anamnesis' => $validated['diagnostico_preoperatorio'] ?? 'Paciente derivado a quirófano.',
            ]);

            // Procedimiento
            $procedimiento = Procedimiento::create([
                'id_atencion' => $atencionVet->id_atencion,
                'fecha_hora' => $validated['fecha_programada'],
                'descripcion' => 'Cirugía: '.$validated['tipo_cirugia'],
                'observaciones' => $validated['observaciones_prequirurgicas'] ?? null,
            ]);

            // Cirugía
            $nuevaCirugia = Cirugia::create([
                'id_procedimiento' => $procedimiento->id_procedimiento,
                'tipo_cirugia' => $validated['tipo_cirugia'],
                'diagnostico_preoperatorio' => $validated['diagnostico_preoperatorio'] ?? null,
                'fecha_programada' => $validated['fecha_programada'],
                'estado' => 'PROGRAMADA',
                'riesgo_asa' => $validated['riesgo_asa'],
                'tipo_anestesia' => $validated['tipo_anestesia'] ?? 'Inhalatoria (Isoflurano)',
                'ayuno_solidos_horas' => $validated['ayuno_solidos_horas'] ?? 8,
                'ayuno_liquidos_horas' => $validated['ayuno_liquidos_horas'] ?? 2,
                'id_cirujano' => $validated['id_cirujano'] ?? null,
                'id_anestesista' => $validated['id_anestesista'] ?? null,
                'observaciones_prequirurgicas' => $validated['observaciones_prequirurgicas'] ?? null,
            ]);

            AuditService::log(
                'PROGRAMAR_CIRUGIA',
                'CIRUGIAS',
                [
                    'tipo_cirugia' => $validated['tipo_cirugia'],
                    'id_paciente' => $paciente->id_paciente,
                    'riesgo_asa' => $validated['riesgo_asa'],
                ],
                (string) $nuevaCirugia->id_procedimiento,
                $currentUser->id_usuario,
                'EXITO',
                "Cirugía programada: {$validated['tipo_cirugia']} para paciente ID {$paciente->id_paciente}"
            );

            return $nuevaCirugia;
        });

        return redirect()->route('cirugias.show', $cirugia->id_procedimiento)
            ->with('success', 'Cirugía programada exitosamente en el calendario de quirófano.');
    }

    /**
     * Muestra la ficha quirúrgica integral con preoperatorio, consentimiento, controles y quirófano.
     */
    public function show(int $id): View
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();

        if (! $currentUser->canAccessModule('cirugias')) {
            abort(403, 'Acceso denegado a la ficha de cirugía.');
        }

        $cirugia = Cirugia::with([
            'procedimiento.atencionVeterinaria.atencion.paciente.propietario.persona',
            'procedimiento.atencionVeterinaria.atencion.paciente.raza.especie',
            'cirujano.persona',
            'anestesista.persona',
            'controles.profesional.persona',
        ])->findOrFail($id);

        $paciente = $cirugia->paciente;
        $propietario = $paciente?->propietario?->persona;
        $profesionales = Profesional::with('persona')->where('estado', 'ACTIVO')->orderBy('id_persona')->get();

        return view('cirugia.show', [
            'cirugia' => $cirugia,
            'paciente' => $paciente,
            'propietario' => $propietario,
            'profesionales' => $profesionales,
        ]);
    }

    /**
     * Actualiza el estado de la cirugía (EN_QUIROFANO, COMPLETADA, CANCELADA).
     */
    public function cambiarEstado(Request $request, int $id): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();

        if (! $currentUser->canAccessModule('cirugias')) {
            abort(403, 'No tienes privilegios para modificar el estado quirúrgico.');
        }

        $cirugia = Cirugia::findOrFail($id);

        $validated = $request->validate([
            'estado' => ['required', 'string', 'in:PROGRAMADA,EN_QUIROFANO,COMPLETADA,CANCELADA'],
            'resultado' => ['nullable', 'string', 'max:50'],
            'complicaciones' => ['nullable', 'string'],
            'indicaciones_postoperatorias' => ['nullable', 'string'],
        ]);

        $cirugia->update([
            'estado' => $validated['estado'],
            'resultado' => $validated['resultado'] ?? $cirugia->resultado,
            'complicaciones' => $validated['complicaciones'] ?? $cirugia->complicaciones,
            'indicaciones_postoperatorias' => $validated['indicaciones_postoperatorias'] ?? $cirugia->indicaciones_postoperatorias,
        ]);

        // Si se completa, actualizar atención vinculada
        if ($validated['estado'] === 'COMPLETADA') {
            $atencion = $cirugia->procedimiento?->atencionVeterinaria?->atencion;
            if ($atencion) {
                $atencion->update([
                    'estado' => 'COMPLETADA',
                    'fecha_hora_fin' => now(),
                    'recomendaciones' => $validated['indicaciones_postoperatorias'] ?? $atencion->recomendaciones,
                ]);
            }
        }

        AuditService::log(
            'CAMBIAR_ESTADO_CIRUGIA',
            'CIRUGIAS',
            [
                'estado' => $validated['estado'],
                'resultado' => $validated['resultado'] ?? null,
            ],
            (string) $cirugia->id_procedimiento,
            $currentUser->id_usuario,
            'EXITO',
            "Estado de cirugía #{$cirugia->id_procedimiento} actualizado a {$validated['estado']}"
        );

        return redirect()->route('cirugias.show', $cirugia->id_procedimiento)
            ->with('success', 'Estado de la intervención quirúrgica actualizado a '.$validated['estado'].'.');
    }

    /**
     * Registra la confirmación del consentimiento informado firmado.
     */
    public function confirmarConsentimiento(Request $request, int $id): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();

        if (! $currentUser->canAccessModule('cirugias')) {
            abort(403, 'Acceso denegado.');
        }

        $cirugia = Cirugia::findOrFail($id);

        $validated = $request->validate([
            'firma_propietario_nombre' => ['required', 'string', 'max:150'],
            'firma_propietario_documento' => ['required', 'string', 'max:50'],
        ], [
            'firma_propietario_nombre.required' => 'El nombre del tutor firmante es obligatorio.',
            'firma_propietario_documento.required' => 'El documento de identidad es obligatorio.',
        ]);

        $cirugia->update([
            'autorizacion_firmada' => true,
            'firma_propietario_nombre' => $validated['firma_propietario_nombre'],
            'firma_propietario_documento' => $validated['firma_propietario_documento'],
        ]);

        AuditService::log(
            'REGISTRAR_CONSENTIMIENTO_CIRUGIA',
            'CIRUGIAS',
            [
                'tutor' => $validated['firma_propietario_nombre'],
                'documento' => $validated['firma_propietario_documento'],
            ],
            (string) $cirugia->id_procedimiento,
            $currentUser->id_usuario,
            'EXITO',
            "Consentimiento informado validado para cirugía #{$cirugia->id_procedimiento} por {$validated['firma_propietario_nombre']}"
        );

        return redirect()->route('cirugias.show', $cirugia->id_procedimiento)
            ->with('success', 'Consentimiento informado registrado y validado.');
    }

    /**
     * Registra un control postquirúrgico y evolución del paciente (CU11).
     */
    public function storeControl(Request $request, int $id): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();

        if (! $currentUser->hasRole(['VETERINARIO', 'SUPER_ADMIN', 'ADMIN'])) {
            abort(403, 'Solo el personal médico autorizado puede registrar controles postquirúrgicos.');
        }

        $cirugia = Cirugia::findOrFail($id);

        $validated = $request->validate([
            'fecha_control' => ['required', 'date'],
            'estado_herida' => ['required', 'string', 'max:50'],
            'temperatura' => ['nullable', 'numeric', 'min:30', 'max:45'],
            'peso' => ['nullable', 'numeric', 'min:0', 'max:200'],
            'evolucion' => ['required', 'string', 'max:1000'],
            'indicaciones' => ['nullable', 'string', 'max:1000'],
            'proximo_control' => ['nullable', 'date'],
            'id_profesional' => ['nullable', 'exists:profesionales,id_persona'],
        ], [
            'fecha_control.required' => 'La fecha del control es obligatoria.',
            'estado_herida.required' => 'Debes registrar el estado de la herida/suturas.',
            'evolucion.required' => 'Debes describir la evolución clínica del paciente.',
        ]);

        $control = ControlCirugia::create([
            'id_cirugia' => $cirugia->id_procedimiento,
            'id_profesional' => $validated['id_profesional'] ?? $currentUser->id_persona,
            'fecha_control' => $validated['fecha_control'],
            'estado_herida' => $validated['estado_herida'],
            'temperatura' => $validated['temperatura'] ?? null,
            'peso' => $validated['peso'] ?? null,
            'evolucion' => $validated['evolucion'],
            'indicaciones' => $validated['indicaciones'] ?? null,
            'proximo_control' => $validated['proximo_control'] ?? null,
            'created_at' => now(),
        ]);

        AuditService::log(
            'REGISTRAR_CONTROL_CIRUGIA',
            'CIRUGIAS',
            [
                'id_control' => $control->id_control,
                'estado_herida' => $control->estado_herida,
                'proximo_control' => $control->proximo_control?->toDateString(),
            ],
            (string) $cirugia->id_procedimiento,
            $currentUser->id_usuario,
            'EXITO',
            "Control postquirúrgico registrado para cirugía #{$cirugia->id_procedimiento} con herida: {$control->estado_herida}"
        );

        return redirect()->route('cirugias.show', $cirugia->id_procedimiento)
            ->with('success', 'Control postoperatorio registrado exitosamente.');
    }

    /**
     * Elimina un control postoperatorio si se cuenta con privilegios médicos.
     */
    public function deleteControl(int $id, int $idControl): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();

        if (! $currentUser->hasRole(['SUPER_ADMIN', 'ADMIN'])) {
            abort(403, 'Solo administradores pueden eliminar registros de controles quirúrgicos.');
        }

        $control = ControlCirugia::where('id_cirugia', $id)->where('id_control', $idControl)->firstOrFail();
        $control->delete();

        AuditService::log(
            'ELIMINAR_CONTROL_CIRUGIA',
            'CIRUGIAS',
            ['id_control' => $idControl],
            (string) $id,
            $currentUser->id_usuario,
            'EXITO',
            "Control postoperatorio #{$idControl} de cirugía #{$id} eliminado por administración"
        );

        return redirect()->route('cirugias.show', $id)
            ->with('success', 'Control postquirúrgico eliminado.');
    }
}
