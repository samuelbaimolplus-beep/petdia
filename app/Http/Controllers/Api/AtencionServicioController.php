<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AtencionServicioResource;
use App\Models\AtencionServicio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AtencionServicioController extends Controller
{
    /**
     * Lista de atencion_servicios con sus relaciones principales.
     */
    public function index(): AnonymousResourceCollection
    {
        $atencionServicio = AtencionServicio::with('atencion', 'servicio')
            ->orderByDesc('id_atencion_servicio')
            ->paginate(15);

        return AtencionServicioResource::collection($atencionServicio);
    }

    /**
     * Detalle de un registro.
     */
    public function show(AtencionServicio $atencionServicio): AtencionServicioResource
    {
        $atencionServicio->load('atencion', 'servicio');

        return new AtencionServicioResource($atencionServicio);
    }

    /**
     * Registra un nuevo registro.
     */
    public function store(Request $request): AtencionServicioResource
    {
        $validated = $request->validate([
            'id_atencion' => ['required', 'integer', 'exists:atenciones,id_atencion'],
            'id_servicio' => ['required', 'integer', 'exists:servicios,id_servicio'],
            'cantidad' => ['nullable', 'integer'],
            'precio_unitario' => ['nullable', 'numeric'],
            'descuento' => ['nullable', 'numeric'],
        ]);

        $atencionServicio = AtencionServicio::create($validated);

        return new AtencionServicioResource($atencionServicio);
    }

    /**
     * Actualiza un registro existente.
     */
    public function update(Request $request, AtencionServicio $atencionServicio): AtencionServicioResource
    {
        $validated = $request->validate([
            'id_atencion' => ['sometimes', 'integer', 'exists:atenciones,id_atencion'],
            'id_servicio' => ['sometimes', 'integer', 'exists:servicios,id_servicio'],
            'cantidad' => ['nullable', 'integer'],
            'precio_unitario' => ['nullable', 'numeric'],
            'descuento' => ['nullable', 'numeric'],
        ]);

        $atencionServicio->update($validated);

        return new AtencionServicioResource($atencionServicio);
    }

    /**
     * Elimina un registro.
     */
    public function destroy(AtencionServicio $atencionServicio): JsonResponse
    {
        $atencionServicio->delete();

        return response()->json(['message' => 'Atencion servicio eliminado']);
    }
}
