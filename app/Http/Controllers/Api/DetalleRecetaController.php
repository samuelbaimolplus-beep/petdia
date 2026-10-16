<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DetalleRecetaResource;
use App\Models\DetalleReceta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DetalleRecetaController extends Controller
{
    /**
     * Lista de detalle_recetas con sus relaciones principales.
     */
    public function index(): AnonymousResourceCollection
    {
        $detalleReceta = DetalleReceta::with('receta', 'medicamento')
            ->orderByDesc('id_detalle_receta')
            ->paginate(15);

        return DetalleRecetaResource::collection($detalleReceta);
    }

    /**
     * Detalle de un registro.
     */
    public function show(DetalleReceta $detalleReceta): DetalleRecetaResource
    {
        $detalleReceta->load('receta', 'medicamento');

        return new DetalleRecetaResource($detalleReceta);
    }

    /**
     * Registra un nuevo registro.
     */
    public function store(Request $request): DetalleRecetaResource
    {
        $validated = $request->validate([
            'id_receta' => ['required', 'integer', 'exists:recetas,id_receta'],
            'id_producto' => ['required', 'integer', 'exists:medicamentos,id_producto'],
            'dosis' => ['nullable', 'numeric'],
            'unidad_dosis' => ['nullable', 'string', 'max:20'],
            'frecuencia' => ['nullable', 'string', 'max:50'],
            'duracion' => ['nullable', 'string', 'max:50'],
            'via_administracion' => ['nullable', 'string', 'max:50'],
            'indicaciones' => ['nullable', 'string'],
        ]);

        $detalleReceta = DetalleReceta::create($validated);

        return new DetalleRecetaResource($detalleReceta);
    }

    /**
     * Actualiza un registro existente.
     */
    public function update(Request $request, DetalleReceta $detalleReceta): DetalleRecetaResource
    {
        $validated = $request->validate([
            'id_receta' => ['sometimes', 'integer', 'exists:recetas,id_receta'],
            'id_producto' => ['sometimes', 'integer', 'exists:medicamentos,id_producto'],
            'dosis' => ['nullable', 'numeric'],
            'unidad_dosis' => ['nullable', 'string', 'max:20'],
            'frecuencia' => ['nullable', 'string', 'max:50'],
            'duracion' => ['nullable', 'string', 'max:50'],
            'via_administracion' => ['nullable', 'string', 'max:50'],
            'indicaciones' => ['nullable', 'string'],
        ]);

        $detalleReceta->update($validated);

        return new DetalleRecetaResource($detalleReceta);
    }

    /**
     * Elimina un registro.
     */
    public function destroy(DetalleReceta $detalleReceta): JsonResponse
    {
        $detalleReceta->delete();

        return response()->json(['message' => 'Detalle receta eliminado']);
    }
}
