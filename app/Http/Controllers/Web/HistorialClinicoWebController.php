<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Atencion;
use App\Models\AtencionVeterinaria;
use App\Models\Diagnostico;
use App\Models\EstudioLaboratorio;
use App\Models\Paciente;
use App\Models\Procedimiento;
use App\Models\Profesional;
use App\Models\RegistroSignosVital;
use App\Models\Tratamiento;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class HistorialClinicoWebController extends Controller
{
    /**
     * Muestra el buscador y directorio general de expedientes clínicos.
     */
    public function index(Request $request): View
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();

        // Control de acceso estricto RBAC
        if (! $currentUser->canAccessModule('historial_clinico')) {
            return view('historial.restringido', [
                'motivo' => 'Tu rol actual no tiene privilegios para visualizar expedientes médicos clínicos.',
            ]);
        }

        $query = Paciente::with([
            'raza.especie',
            'propietario.persona',
            'atenciones' => fn ($q) => $q->has('atencionVeterinaria')->latest('fecha_hora_inicio'),
        ])->orderBy('nombre');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'ilike', "%{$search}%")
                    ->orWhere('microchip', 'ilike', "%{$search}%")
                    ->orWhereHas('propietario.persona', function ($qp) use ($search) {
                        $qp->where('nombre', 'ilike', "%{$search}%")
                            ->orWhere('apellido1', 'ilike', "%{$search}%")
                            ->orWhere('documento', 'ilike', "%{$search}%");
                    })
                    ->orWhereHas('raza', function ($qr) use ($search) {
                        $qr->where('nombre', 'ilike', "%{$search}%");
                    });
            });
        }

        $pacientes = $query->paginate(10)->withQueryString();
        $totalPacientes = Paciente::count();
        $totalAtencionesClinicas = AtencionVeterinaria::count();
        $totalDiagnosticos = Diagnostico::count();
        $totalTratamientosActivos = Tratamiento::where('estado', 'ACTIVO')->count();

        return view('historial.index', [
            'pacientes' => $pacientes,
            'totalPacientes' => $totalPacientes,
            'totalAtencionesClinicas' => $totalAtencionesClinicas,
            'totalDiagnosticos' => $totalDiagnosticos,
            'totalTratamientosActivos' => $totalTratamientosActivos,
            'filters' => [
                'search' => $request->input('search'),
            ],
        ]);
    }

    /**
     * Muestra el expediente clínico completo y cronológico de una mascota.
     */
    public function show(int $id, Request $request): View
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();

        $paciente = Paciente::with([
            'raza.especie',
            'propietario.persona',
            'fichaMedica',
            'atenciones' => function ($q) {
                $q->has('atencionVeterinaria')
                    ->with([
                        'atencionVeterinaria.registrosSignosVitales',
                        'atencionVeterinaria.diagnosticos',
                        'atencionVeterinaria.tratamientos',
                        'atencionVeterinaria.procedimientos.estudioLaboratorio',
                        'atencionVeterinaria.procedimientos.cirugia',
                        'atencionVeterinaria.procedimientos.vacunacion',
                        'atencionVeterinaria.procedimientos.desparasitacion',
                        'atencionVeterinaria.internacion',
                        'atencionVeterinaria.recetas',
                        'participaciones.profesional.persona',
                    ])
                    ->orderBy('fecha_hora_inicio', 'desc');
            },
        ])->findOrFail($id);

        // Indicador visual claro de información médica restringida para roles sin acceso
        if (! $currentUser->canAccessModule('historial_clinico')) {
            return view('historial.restringido', [
                'paciente' => $paciente,
                'motivo' => 'Los registros clínicos, diagnósticos, tratamientos y notas médicas están restringidos exclusivamente al personal Veterinario y Super-Admin.',
            ]);
        }

        // Extracción de colecciones para las pestañas especializadas
        $atenciones = $paciente->atenciones;
        $diagnosticos = $atenciones->flatMap(fn ($a) => $a->atencionVeterinaria?->diagnosticos ?? collect());
        $tratamientos = $atenciones->flatMap(fn ($a) => $a->atencionVeterinaria?->tratamientos ?? collect());
        $procedimientos = $atenciones->flatMap(fn ($a) => $a->atencionVeterinaria?->procedimientos ?? collect());
        $estudios = $procedimientos->filter(fn ($p) => $p->estudioLaboratorio !== null);
        $cirugias = $procedimientos->filter(fn ($p) => $p->cirugia !== null);
        $vacunaciones = $procedimientos->filter(fn ($p) => $p->vacunacion !== null);
        $desparasitaciones = $procedimientos->filter(fn ($p) => $p->desparasitacion !== null);
        $recetas = $atenciones->flatMap(fn ($a) => $a->atencionVeterinaria?->recetas ?? collect());
        $internaciones = $atenciones->map(fn ($a) => $a->atencionVeterinaria?->internacion)->filter();

        // Solo el rol Veterinario (o Super-Admin) puede escribir
        $canWrite = $currentUser->hasRole(['VETERINARIO', 'SUPER_ADMIN']);

        return view('historial.show', [
            'paciente' => $paciente,
            'atenciones' => $atenciones,
            'diagnosticos' => $diagnosticos,
            'tratamientos' => $tratamientos,
            'estudios' => $estudios,
            'cirugias' => $cirugias,
            'vacunaciones' => $vacunaciones,
            'desparasitaciones' => $desparasitaciones,
            'recetas' => $recetas,
            'internaciones' => $internaciones,
            'canWrite' => $canWrite,
            'activeTab' => $request->input('tab', 'timeline'),
        ]);
    }

    /**
     * Registra una nueva entrada clínica completa para el paciente.
     */
    public function storeEntradaClinica(Request $request, int $id): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();

        // Solo Veterinario o Super-Admin pueden crear entradas clínicas
        if (! $currentUser->hasRole(['VETERINARIO', 'SUPER_ADMIN'])) {
            abort(403, 'Solo el personal médico veterinario tiene permiso para redactar notas clínicas.');
        }

        $paciente = Paciente::findOrFail($id);

        $validated = $request->validate([
            'motivo_consulta' => ['required', 'string', 'max:200'],
            'tipo' => ['required', 'string', 'in:CONSULTA,EMERGENCIA,CONTROL,CIRUGIA,INTERNACION'],
            'anamnesis' => ['required', 'string'],
            'sintomas' => ['nullable', 'string'],
            'examen_fisico' => ['nullable', 'string'],
            'conclusion' => ['nullable', 'string'],
            'fecha_reconsulta' => ['nullable', 'date'],
            'observaciones_clinicas' => ['nullable', 'string'],
            // Signos vitales
            'peso' => ['nullable', 'numeric', 'min:0'],
            'temperatura' => ['nullable', 'numeric', 'min:30', 'max:45'],
            'frecuencia_cardiaca' => ['nullable', 'integer', 'min:0'],
            'frecuencia_respiratoria' => ['nullable', 'integer', 'min:0'],
            // Diagnóstico inicial
            'diagnostico_nombre' => ['nullable', 'string', 'max:100'],
            'diagnostico_tipo' => ['nullable', 'string', 'in:PRESUNTIVO,DEFINITIVO'],
            'diagnostico_descripcion' => ['nullable', 'string'],
            // Tratamiento
            'tratamiento_descripcion' => ['nullable', 'string'],
            'tratamiento_indicaciones' => ['nullable', 'string'],
        ], [
            'motivo_consulta.required' => 'El motivo de consulta es obligatorio.',
            'anamnesis.required' => 'La anamnesis / antecedentes actuales son obligatorios.',
        ]);

        DB::transaction(function () use ($validated, $paciente, $currentUser) {
            // 1. Crear registro maestro de atención
            $atencion = Atencion::create([
                'id_paciente' => $paciente->id_paciente,
                'fecha_hora_inicio' => now(),
                'fecha_hora_fin' => now(),
                'estado' => 'COMPLETADA',
                'observaciones' => $validated['observaciones_clinicas'] ?? null,
                'recomendaciones' => $validated['conclusion'] ?? null,
            ]);

            // 2. Asociar el profesional médico logueado si existe
            $profesional = Profesional::where('id_persona', $currentUser->id_persona)->first();
            if ($profesional) {
                DB::table('participacion_atencions')->insert([
                    'id_atencion' => $atencion->id_atencion,
                    'id_profesional' => $profesional->id_persona,
                    'rol_atencion' => 'VETERINARIO',
                    'observaciones' => 'Médico tratante principal',
                ]);
            }

            // 3. Crear registro de atención veterinaria
            $atencionVet = AtencionVeterinaria::create([
                'id_atencion' => $atencion->id_atencion,
                'motivo_consulta' => $validated['motivo_consulta'],
                'tipo' => $validated['tipo'],
                'anamnesis' => $validated['anamnesis'],
                'sintomas' => $validated['sintomas'] ?? null,
                'examen_fisico' => $validated['examen_fisico'] ?? null,
                'conclusion' => $validated['conclusion'] ?? null,
                'fecha_reconsulta' => $validated['fecha_reconsulta'] ?? null,
                'observaciones_clinicas' => $validated['observaciones_clinicas'] ?? null,
            ]);

            // 4. Registrar signos vitales si fueron ingresados
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

            // 5. Registrar diagnóstico si fue ingresado
            if (! empty($validated['diagnostico_nombre'])) {
                Diagnostico::create([
                    'id_atencion' => $atencion->id_atencion,
                    'nombre' => $validated['diagnostico_nombre'],
                    'tipo' => $validated['diagnostico_tipo'] ?? 'PRESUNTIVO',
                    'descripcion' => $validated['diagnostico_descripcion'] ?? null,
                ]);
            }

            // 6. Registrar tratamiento si fue ingresado
            if (! empty($validated['tratamiento_descripcion'])) {
                $tratamiento = Tratamiento::create([
                    'id_paciente' => $paciente->id_paciente,
                    'descripcion' => $validated['tratamiento_descripcion'],
                    'indicaciones' => $validated['tratamiento_indicaciones'] ?? null,
                    'fecha_inicio' => now()->toDateString(),
                    'estado' => 'ACTIVO',
                ]);

                DB::table('atencion_tratamiento')->insert([
                    'id_atencion' => $atencion->id_atencion,
                    'id_tratamiento' => $tratamiento->id_tratamiento,
                    'tipo_relacion' => 'INICIO',
                ]);
            }
        });

        return redirect()->route('historial.show', $paciente->id_paciente)
            ->with('success', 'Entrada clínica registrada exitosamente en el expediente.');
    }

    /**
     * Retorna entradas clínicas paginadas para el timeline con scroll infinito (Mejora 6.1).
     */
    public function entradas(int $id, Request $request): JsonResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('historial_clinico')) {
            abort(403, 'Acceso restringido.');
        }

        $paciente = Paciente::findOrFail($id);

        $atenciones = $paciente->atenciones()
            ->has('atencionVeterinaria')
            ->with([
                'atencionVeterinaria.registrosSignosVitales',
                'atencionVeterinaria.diagnosticos',
                'atencionVeterinaria.tratamientos',
                'atencionVeterinaria.procedimientos.estudioLaboratorio',
                'atencionVeterinaria.procedimientos.cirugia',
                'atencionVeterinaria.procedimientos.vacunacion',
                'atencionVeterinaria.procedimientos.desparasitacion',
                'atencionVeterinaria.internacion',
                'atencionVeterinaria.recetas',
                'participaciones.profesional.persona',
            ])
            ->orderBy('fecha_hora_inicio', 'desc')
            ->paginate(5);

        $html = view('historial._timeline_items', ['atenciones' => $atenciones])->render();

        return response()->json([
            'html' => $html,
            'has_more' => $atenciones->hasMorePages(),
            'current_page' => $atenciones->currentPage(),
            'total' => $atenciones->total(),
        ]);
    }

    /**
     * Sube y registra un estudio de laboratorio o imagen médica complementaria.
     */
    public function storeEstudio(Request $request, int $id): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();

        if (! $currentUser->hasRole(['VETERINARIO', 'SUPER_ADMIN'])) {
            abort(403, 'Solo el personal médico veterinario tiene permiso para adjuntar estudios clínicos.');
        }

        $paciente = Paciente::findOrFail($id);

        $validated = $request->validate([
            'tipo_estudio' => ['required', 'string', 'max:100'],
            'fecha_resultado' => ['nullable', 'date'],
            'resultado' => ['nullable', 'string', 'max:500'],
            'interpretacion' => ['nullable', 'string'],
            'id_atencion' => ['nullable', 'integer'],
            'archivo' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
        ], [
            'tipo_estudio.required' => 'El tipo de estudio es obligatorio.',
            'archivo.required' => 'Debes adjuntar un archivo (PDF o Imagen).',
            'archivo.mimes' => 'El formato del archivo debe ser PDF, JPG, PNG o WEBP.',
            'archivo.max' => 'El archivo no debe pesar más de 10 MB.',
        ]);

        DB::transaction(function () use ($validated, $paciente, $request) {
            // Buscar o crear la atención asociada
            $atencion = null;
            if (! empty($validated['id_atencion'])) {
                $atencion = Atencion::where('id_paciente', $paciente->id_paciente)->find($validated['id_atencion']);
            }
            if (! $atencion) {
                $atencion = $paciente->atenciones()->has('atencionVeterinaria')->latest('fecha_hora_inicio')->first();
            }

            if (! $atencion) {
                $atencion = Atencion::create([
                    'id_paciente' => $paciente->id_paciente,
                    'fecha_hora_inicio' => now(),
                    'fecha_hora_fin' => now(),
                    'estado' => 'COMPLETADA',
                    'observaciones' => 'Registro de estudios complementarios de laboratorio e imágenes diagnósticas.',
                ]);
                AtencionVeterinaria::create([
                    'id_atencion' => $atencion->id_atencion,
                    'motivo_consulta' => 'Estudios Auxiliares / Laboratorio',
                    'tipo' => 'CONTROL',
                    'anamnesis' => 'Incorporación y archivo de exámenes complementarios de diagnóstico.',
                ]);
            }

            $fechaResultado = ! empty($validated['fecha_resultado']) ? $validated['fecha_resultado'].' 12:00:00' : now();

            // Crear Procedimiento general
            $procedimiento = Procedimiento::create([
                'id_atencion' => $atencion->id_atencion,
                'fecha_hora' => $fechaResultado,
                'descripcion' => 'Estudio de Laboratorio: '.$validated['tipo_estudio'],
                'observaciones' => $validated['interpretacion'] ?? $validated['resultado'] ?? null,
            ]);

            // Almacenar archivo en storage public
            $file = $request->file('archivo');
            $originalName = $file->getClientOriginalName();
            $path = $file->store("estudios/{$paciente->id_paciente}", 'public');

            // Crear EstudioLaboratorio vinculado al procedimiento
            EstudioLaboratorio::create([
                'id_procedimiento' => $procedimiento->id_procedimiento,
                'tipo_estudio' => $validated['tipo_estudio'],
                'fecha_resultado' => $fechaResultado,
                'resultado' => $validated['resultado'] ?? null,
                'interpretacion' => $validated['interpretacion'] ?? null,
                'archivo_adjunto' => $path,
                'nombre_archivo_original' => $originalName,
            ]);
        });

        return redirect()->route('historial.show', ['id' => $paciente->id_paciente, 'tab' => 'estudios'])
            ->with('success', 'Estudio de laboratorio / imagen diagnóstica adjuntado exitosamente al expediente.');
    }

    /**
     * Elimina un estudio adjunto.
     */
    public function destroyEstudio(int $id, int $idProcedimiento): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();

        if (! $currentUser->hasRole(['VETERINARIO', 'SUPER_ADMIN'])) {
            abort(403, 'No tienes privilegios para eliminar estudios médicos.');
        }

        $paciente = Paciente::findOrFail($id);
        $estudio = EstudioLaboratorio::where('id_procedimiento', $idProcedimiento)->firstOrFail();

        if ($estudio->archivo_adjunto) {
            Storage::disk('public')->delete($estudio->archivo_adjunto);
        }

        $estudio->delete();
        Procedimiento::where('id_procedimiento', $idProcedimiento)->delete();

        return redirect()->route('historial.show', ['id' => $paciente->id_paciente, 'tab' => 'estudios'])
            ->with('success', 'Estudio eliminado correctamente.');
    }
}
