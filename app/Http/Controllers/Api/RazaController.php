<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RazaResource;
use App\Models\Raza;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RazaController extends Controller
{
    /**
     * Lista de razas con sus relaciones principales.
     */
    public function index(): AnonymousResourceCollection
    {
        $raza = Raza::with('especie')
            ->orderByDesc('id_raza')
            ->paginate(15);

        return RazaResource::collection($raza);
    }

    /**
     * Detalle de un registro.
     */
    public function show(Raza $raza): RazaResource
    {
        $raza->load('especie', 'pacientes', 'ajustesTiempoRaza');

        return new RazaResource($raza);
    }

    /**
     * Registra un nuevo registro.
     */
    public function store(Request $request): RazaResource
    {
        $validated = $request->validate([
            'id_especie' => ['required', 'integer', 'exists:especies,id_especie'],
            'nombre' => ['required', 'string', 'max:50'],
        ]);

        $raza = Raza::create($validated);

        return new RazaResource($raza);
    }

    /**
     * Actualiza un registro existente.
     */
    public function update(Request $request, Raza $raza): RazaResource
    {
        $validated = $request->validate([
            'id_especie' => ['sometimes', 'integer', 'exists:especies,id_especie'],
            'nombre' => ['sometimes', 'string', 'max:50'],
        ]);

        $raza->update($validated);

        return new RazaResource($raza);
    }

    /**
     * Elimina un registro.
     */
    public function destroy(Raza $raza): JsonResponse
    {
        $raza->delete();

        return response()->json(['message' => 'Raza eliminado']);
    }
}
