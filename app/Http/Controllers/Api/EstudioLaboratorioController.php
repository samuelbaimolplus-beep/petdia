<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EstudioLaboratorioResource;
use App\Models\EstudioLaboratorio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class EstudioLaboratorioController extends Controller
{
    /**
     * Lista de estudio_laboratorios con sus relaciones principales.
     */
    public function index(): AnonymousResourceCollection
    {
        $estudioLaboratorio = EstudioLaboratorio::with('procedimiento')
            ->orderByDesc('id_procedimiento')
            ->paginate(15);

        return EstudioLaboratorioResource::collection($estudioLaboratorio);
    }

    /**
     * Detalle de un registro.
     */
    public function show(EstudioLaboratorio $estudioLaboratorio): EstudioLaboratorioResource
    {
        $estudioLaboratorio->load('procedimiento');

        return new EstudioLaboratorioResource($estudioLaboratorio);
    }

    /**
     * Registra un nuevo registro.
     */
    public function store(Request $request): EstudioLaboratorioResource
    {
        $validated = $request->validate([
            'id_procedimiento' => ['required', 'integer', 'exists:procedimientos,id_procedimiento'],
            'tipo_estudio' => ['nullable', 'string', 'max:50'],
            'fecha_resultado' => ['nullable', 'date'],
            'resultado' => ['nullable', 'string'],
            'interpretacion' => ['nullable', 'string'],
        ]);

        $estudioLaboratorio = EstudioLaboratorio::create($validated);

        return new EstudioLaboratorioResource($estudioLaboratorio);
    }

    /**
     * Actualiza un registro existente.
     */
    public function update(Request $request, EstudioLaboratorio $estudioLaboratorio): EstudioLaboratorioResource
    {
        $validated = $request->validate([
            'id_procedimiento' => ['sometimes', 'integer', 'exists:procedimientos,id_procedimiento'],
            'tipo_estudio' => ['nullable', 'string', 'max:50'],
            'fecha_resultado' => ['nullable', 'date'],
            'resultado' => ['nullable', 'string'],
            'interpretacion' => ['nullable', 'string'],
        ]);

        $estudioLaboratorio->update($validated);

        return new EstudioLaboratorioResource($estudioLaboratorio);
    }

    /**
     * Elimina un registro.
     */
    public function destroy(EstudioLaboratorio $estudioLaboratorio): JsonResponse
    {
        $estudioLaboratorio->delete();

        return response()->json(['message' => 'Estudio laboratorio eliminado']);
    }
}
