<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AtencionEsteticaResource;
use App\Models\AtencionEstetica;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AtencionEsteticaController extends Controller
{
    /**
     * Lista de atencion_esteticas con sus relaciones principales.
     */
    public function index(): AnonymousResourceCollection
    {
        $atencionEstetica = AtencionEstetica::with('atencion')
            ->orderByDesc('id_atencion')
            ->paginate(15);

        return AtencionEsteticaResource::collection($atencionEstetica);
    }

    /**
     * Detalle de un registro.
     */
    public function show(AtencionEstetica $atencionEstetica): AtencionEsteticaResource
    {
        $atencionEstetica->load('atencion');

        return new AtencionEsteticaResource($atencionEstetica);
    }

    /**
     * Registra un nuevo registro.
     */
    public function store(Request $request): AtencionEsteticaResource
    {
        $validated = $request->validate([
            'id_atencion' => ['required', 'integer', 'exists:atenciones,id_atencion'],
            'modalidad' => ['nullable', 'string', 'max:50'],
            'estado_pelaje' => ['nullable', 'string', 'max:50'],
            'caracteristicas_observadas' => ['nullable', 'string'],
        ]);

        $atencionEstetica = AtencionEstetica::create($validated);

        return new AtencionEsteticaResource($atencionEstetica);
    }

    /**
     * Actualiza un registro existente.
     */
    public function update(Request $request, AtencionEstetica $atencionEstetica): AtencionEsteticaResource
    {
        $validated = $request->validate([
            'id_atencion' => ['sometimes', 'integer', 'exists:atenciones,id_atencion'],
            'modalidad' => ['nullable', 'string', 'max:50'],
            'estado_pelaje' => ['nullable', 'string', 'max:50'],
            'caracteristicas_observadas' => ['nullable', 'string'],
        ]);

        $atencionEstetica->update($validated);

        return new AtencionEsteticaResource($atencionEstetica);
    }

    /**
     * Elimina un registro.
     */
    public function destroy(AtencionEstetica $atencionEstetica): JsonResponse
    {
        $atencionEstetica->delete();

        return response()->json(['message' => 'Atencion estetica eliminado']);
    }
}
