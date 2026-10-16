<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DesparasitacionResource;
use App\Models\Desparasitacion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DesparasitacionController extends Controller
{
    /**
     * Lista de desparasitaciones con sus relaciones principales.
     */
    public function index(): AnonymousResourceCollection
    {
        $desparasitacion = Desparasitacion::with('procedimiento', 'administracionMedicamento')
            ->orderByDesc('id_procedimiento')
            ->paginate(15);

        return DesparasitacionResource::collection($desparasitacion);
    }

    /**
     * Detalle de un registro.
     */
    public function show(Desparasitacion $desparasitacion): DesparasitacionResource
    {
        $desparasitacion->load('procedimiento', 'administracionMedicamento');

        return new DesparasitacionResource($desparasitacion);
    }

    /**
     * Registra un nuevo registro.
     */
    public function store(Request $request): DesparasitacionResource
    {
        $validated = $request->validate([
            'id_procedimiento' => ['required', 'integer', 'exists:procedimientos,id_procedimiento'],
            'id_uso' => ['required', 'integer', 'exists:administracion_medicamentos,id_uso'],
            'tipo' => ['nullable', 'string', 'max:50'],
            'proxima_dosis' => ['nullable', 'date'],
        ]);

        $desparasitacion = Desparasitacion::create($validated);

        return new DesparasitacionResource($desparasitacion);
    }

    /**
     * Actualiza un registro existente.
     */
    public function update(Request $request, Desparasitacion $desparasitacion): DesparasitacionResource
    {
        $validated = $request->validate([
            'id_procedimiento' => ['sometimes', 'integer', 'exists:procedimientos,id_procedimiento'],
            'id_uso' => ['sometimes', 'integer', 'exists:administracion_medicamentos,id_uso'],
            'tipo' => ['nullable', 'string', 'max:50'],
            'proxima_dosis' => ['nullable', 'date'],
        ]);

        $desparasitacion->update($validated);

        return new DesparasitacionResource($desparasitacion);
    }

    /**
     * Elimina un registro.
     */
    public function destroy(Desparasitacion $desparasitacion): JsonResponse
    {
        $desparasitacion->delete();

        return response()->json(['message' => 'Desparasitacion eliminado']);
    }
}
