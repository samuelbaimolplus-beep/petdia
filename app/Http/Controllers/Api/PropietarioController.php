<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PropietarioResource;
use App\Models\Propietario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PropietarioController extends Controller
{
    /**
     * Lista de propietarios con sus relaciones principales.
     */
    public function index(): AnonymousResourceCollection
    {
        $propietario = Propietario::with('persona')
            ->orderByDesc('id_persona')
            ->paginate(15);

        return PropietarioResource::collection($propietario);
    }

    /**
     * Detalle de un registro.
     */
    public function show(Propietario $propietario): PropietarioResource
    {
        $propietario->load('persona', 'pacientes', 'cuenta', 'ventas');

        return new PropietarioResource($propietario);
    }

    /**
     * Registra un nuevo registro.
     */
    public function store(Request $request): PropietarioResource
    {
        $validated = $request->validate([
            'id_persona' => ['required', 'integer', 'exists:personas,id_persona'],
        ]);

        $propietario = Propietario::create($validated);

        return new PropietarioResource($propietario);
    }

    /**
     * Actualiza un registro existente.
     */
    public function update(Request $request, Propietario $propietario): PropietarioResource
    {
        $validated = $request->validate([
            'id_persona' => ['sometimes', 'integer', 'exists:personas,id_persona'],
        ]);

        $propietario->update($validated);

        return new PropietarioResource($propietario);
    }

    /**
     * Elimina un registro.
     */
    public function destroy(Propietario $propietario): JsonResponse
    {
        $propietario->delete();

        return response()->json(['message' => 'Propietario eliminado']);
    }
}
