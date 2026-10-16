<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RecordatorioResource;
use App\Models\Recordatorio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RecordatorioController extends Controller
{
    /**
     * Lista de recordatorios con sus relaciones principales.
     */
    public function index(): AnonymousResourceCollection
    {
        $recordatorio = Recordatorio::with('paciente', 'servicio', 'atencionOrigen', 'cita')
            ->orderByDesc('id_recordatorio')
            ->paginate(15);

        return RecordatorioResource::collection($recordatorio);
    }

    /**
     * Detalle de un registro.
     */
    public function show(Recordatorio $recordatorio): RecordatorioResource
    {
        $recordatorio->load('paciente', 'servicio', 'atencionOrigen', 'cita');

        return new RecordatorioResource($recordatorio);
    }

    /**
     * Registra un nuevo registro.
     */
    public function store(Request $request): RecordatorioResource
    {
        $validated = $request->validate([
            'id_paciente' => ['required', 'integer', 'exists:pacientes,id_paciente'],
            'id_servicio' => ['nullable', 'integer', 'exists:servicios,id_servicio'],
            'id_atencion_origen' => ['nullable', 'integer', 'exists:atenciones,id_atencion'],
            'id_cita' => ['nullable', 'integer', 'exists:citas,id_cita'],
            'tipo' => ['nullable', 'string', 'max:50'],
            'fecha_hora_programada' => ['nullable', 'date'],
            'fecha_hora_envio' => ['nullable', 'date'],
            'medio_envio' => ['nullable', 'string', 'max:50'],
            'mensaje' => ['nullable', 'string'],
            'estado' => ['nullable', 'string', 'max:20'],
        ]);

        $recordatorio = Recordatorio::create($validated);

        return new RecordatorioResource($recordatorio);
    }

    /**
     * Actualiza un registro existente.
     */
    public function update(Request $request, Recordatorio $recordatorio): RecordatorioResource
    {
        $validated = $request->validate([
            'id_paciente' => ['sometimes', 'integer', 'exists:pacientes,id_paciente'],
            'id_servicio' => ['nullable', 'integer', 'exists:servicios,id_servicio'],
            'id_atencion_origen' => ['nullable', 'integer', 'exists:atenciones,id_atencion'],
            'id_cita' => ['nullable', 'integer', 'exists:citas,id_cita'],
            'tipo' => ['nullable', 'string', 'max:50'],
            'fecha_hora_programada' => ['nullable', 'date'],
            'fecha_hora_envio' => ['nullable', 'date'],
            'medio_envio' => ['nullable', 'string', 'max:50'],
            'mensaje' => ['nullable', 'string'],
            'estado' => ['nullable', 'string', 'max:20'],
        ]);

        $recordatorio->update($validated);

        return new RecordatorioResource($recordatorio);
    }

    /**
     * Elimina un registro.
     */
    public function destroy(Recordatorio $recordatorio): JsonResponse
    {
        $recordatorio->delete();

        return response()->json(['message' => 'Recordatorio eliminado']);
    }
}
