<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EspecieResource;
use App\Models\Especie;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class EspecieController extends Controller
{
    /**
     * Lista de especies con sus relaciones principales.
     */
    public function index(): AnonymousResourceCollection
    {
        $especie = Especie::query()
            ->orderByDesc('id_especie')
            ->paginate(15);

        return EspecieResource::collection($especie);
    }

    /**
     * Detalle de un registro.
     */
    public function show(Especie $especie): EspecieResource
    {
        $especie->load('razas', 'tiemposEstimados');

        return new EspecieResource($especie);
    }

    /**
     * Registra un nuevo registro.
     */
    public function store(Request $request): EspecieResource
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:50', 'unique:especies,nombre'],
        ]);

        $especie = Especie::create($validated);

        return new EspecieResource($especie);
    }

    /**
     * Actualiza un registro existente.
     */
    public function update(Request $request, Especie $especie): EspecieResource
    {
        $validated = $request->validate([
            'nombre' => ['sometimes', 'string', 'max:50', Rule::unique('especies', 'nombre')->ignore($especie->id_especie, 'id_especie')],
        ]);

        $especie->update($validated);

        return new EspecieResource($especie);
    }

    /**
     * Elimina un registro.
     */
    public function destroy(Especie $especie): JsonResponse
    {
        $especie->delete();

        return response()->json(['message' => 'Especie eliminado']);
    }
}
