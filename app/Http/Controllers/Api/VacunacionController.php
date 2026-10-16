<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\VacunacionResource;
use App\Models\Vacunacion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class VacunacionController extends Controller
{
    /**
     * Lista de vacunaciones con sus relaciones principales.
     */
    public function index(): AnonymousResourceCollection
    {
        $vacunacion = Vacunacion::with('procedimiento', 'administracionMedicamento')
            ->orderByDesc('id_procedimiento')
            ->paginate(15);

        return VacunacionResource::collection($vacunacion);
    }

    /**
     * Detalle de un registro.
     */
    public function show(Vacunacion $vacunacion): VacunacionResource
    {
        $vacunacion->load('procedimiento', 'administracionMedicamento');

        return new VacunacionResource($vacunacion);
    }

    /**
     * Registra un nuevo registro.
     */
    public function store(Request $request): VacunacionResource
    {
        $validated = $request->validate([
            'id_procedimiento' => ['required', 'integer', 'exists:procedimientos,id_procedimiento'],
            'id_uso' => ['required', 'integer', 'exists:administracion_medicamentos,id_uso'],
            'proxima_dosis' => ['nullable', 'date'],
            'sitio_aplicacion' => ['nullable', 'string', 'max:100'],
        ]);

        $vacunacion = Vacunacion::create($validated);

        return new VacunacionResource($vacunacion);
    }

    /**
     * Actualiza un registro existente.
     */
    public function update(Request $request, Vacunacion $vacunacion): VacunacionResource
    {
        $validated = $request->validate([
            'id_procedimiento' => ['sometimes', 'integer', 'exists:procedimientos,id_procedimiento'],
            'id_uso' => ['sometimes', 'integer', 'exists:administracion_medicamentos,id_uso'],
            'proxima_dosis' => ['nullable', 'date'],
            'sitio_aplicacion' => ['nullable', 'string', 'max:100'],
        ]);

        $vacunacion->update($validated);

        return new VacunacionResource($vacunacion);
    }

    /**
     * Elimina un registro.
     */
    public function destroy(Vacunacion $vacunacion): JsonResponse
    {
        $vacunacion->delete();

        return response()->json(['message' => 'Vacunacion eliminado']);
    }
}
