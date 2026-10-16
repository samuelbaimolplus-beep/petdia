<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RecetaResource;
use App\Models\Receta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RecetaController extends Controller
{
    /**
     * Lista de recetas con sus relaciones principales.
     */
    public function index(): AnonymousResourceCollection
    {
        $receta = Receta::with('atencionVeterinaria')
            ->orderByDesc('id_receta')
            ->paginate(15);

        return RecetaResource::collection($receta);
    }

    /**
     * Detalle de un registro.
     */
    public function show(Receta $receta): RecetaResource
    {
        $receta->load('atencionVeterinaria', 'detalleRecetas');

        return new RecetaResource($receta);
    }

    /**
     * Registra un nuevo registro.
     */
    public function store(Request $request): RecetaResource
    {
        $validated = $request->validate([
            'id_atencion' => ['required', 'integer', 'exists:atencion_veterinarias,id_atencion'],
            'fecha_hora' => ['nullable', 'date'],
            'indicaciones_generales' => ['nullable', 'string'],
        ]);

        $receta = Receta::create($validated);

        return new RecetaResource($receta);
    }

    /**
     * Actualiza un registro existente.
     */
    public function update(Request $request, Receta $receta): RecetaResource
    {
        $validated = $request->validate([
            'id_atencion' => ['sometimes', 'integer', 'exists:atencion_veterinarias,id_atencion'],
            'fecha_hora' => ['nullable', 'date'],
            'indicaciones_generales' => ['nullable', 'string'],
        ]);

        $receta->update($validated);

        return new RecetaResource($receta);
    }

    /**
     * Elimina un registro.
     */
    public function destroy(Receta $receta): JsonResponse
    {
        $receta->delete();

        return response()->json(['message' => 'Receta eliminado']);
    }
}
