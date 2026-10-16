<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DetallePaqueteResource;
use App\Models\DetallePaquete;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DetallePaqueteController extends Controller
{
    /**
     * Lista de detalle_paquetes con sus relaciones principales.
     */
    public function index(): AnonymousResourceCollection
    {
        $detallePaquete = DetallePaquete::with('paquete', 'servicio')
            ->orderByDesc('id_detalle_paquete')
            ->paginate(15);

        return DetallePaqueteResource::collection($detallePaquete);
    }

    /**
     * Detalle de un registro.
     */
    public function show(DetallePaquete $detallePaquete): DetallePaqueteResource
    {
        $detallePaquete->load('paquete', 'servicio');

        return new DetallePaqueteResource($detallePaquete);
    }

    /**
     * Registra un nuevo registro.
     */
    public function store(Request $request): DetallePaqueteResource
    {
        $validated = $request->validate([
            'id_paquete' => ['required', 'integer', 'exists:paquetes,id_paquete'],
            'id_servicio' => ['required', 'integer', 'exists:servicios,id_servicio'],
            'cantidad_incluida' => ['nullable', 'integer'],
        ]);

        $detallePaquete = DetallePaquete::create($validated);

        return new DetallePaqueteResource($detallePaquete);
    }

    /**
     * Actualiza un registro existente.
     */
    public function update(Request $request, DetallePaquete $detallePaquete): DetallePaqueteResource
    {
        $validated = $request->validate([
            'id_paquete' => ['sometimes', 'integer', 'exists:paquetes,id_paquete'],
            'id_servicio' => ['sometimes', 'integer', 'exists:servicios,id_servicio'],
            'cantidad_incluida' => ['nullable', 'integer'],
        ]);

        $detallePaquete->update($validated);

        return new DetallePaqueteResource($detallePaquete);
    }

    /**
     * Elimina un registro.
     */
    public function destroy(DetallePaquete $detallePaquete): JsonResponse
    {
        $detallePaquete->delete();

        return response()->json(['message' => 'Detalle paquete eliminado']);
    }
}
