<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TiempoEstimadoResource;
use App\Models\TiempoEstimado;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TiempoEstimadoController extends Controller
{
    /**
     * Lista de tiempos_estimados con sus relaciones principales.
     */
    public function index(): AnonymousResourceCollection
    {
        $tiempoEstimado = TiempoEstimado::with('servicio', 'especie')
            ->orderByDesc('id_tiempo_estimado')
            ->paginate(15);

        return TiempoEstimadoResource::collection($tiempoEstimado);
    }

    /**
     * Detalle de un registro.
     */
    public function show(TiempoEstimado $tiempoEstimado): TiempoEstimadoResource
    {
        $tiempoEstimado->load('servicio', 'especie', 'ajustesTiempoRaza');

        return new TiempoEstimadoResource($tiempoEstimado);
    }

    /**
     * Registra un nuevo registro.
     */
    public function store(Request $request): TiempoEstimadoResource
    {
        $validated = $request->validate([
            'id_servicio' => ['required', 'integer', 'exists:servicios,id_servicio'],
            'id_especie' => ['required', 'integer', 'exists:especies,id_especie'],
            'peso_min' => ['nullable', 'numeric'],
            'peso_max' => ['nullable', 'numeric'],
            'duracion_minutos' => ['nullable', 'integer'],
        ]);

        $tiempoEstimado = TiempoEstimado::create($validated);

        return new TiempoEstimadoResource($tiempoEstimado);
    }

    /**
     * Actualiza un registro existente.
     */
    public function update(Request $request, TiempoEstimado $tiempoEstimado): TiempoEstimadoResource
    {
        $validated = $request->validate([
            'id_servicio' => ['sometimes', 'integer', 'exists:servicios,id_servicio'],
            'id_especie' => ['sometimes', 'integer', 'exists:especies,id_especie'],
            'peso_min' => ['nullable', 'numeric'],
            'peso_max' => ['nullable', 'numeric'],
            'duracion_minutos' => ['nullable', 'integer'],
        ]);

        $tiempoEstimado->update($validated);

        return new TiempoEstimadoResource($tiempoEstimado);
    }

    /**
     * Elimina un registro.
     */
    public function destroy(TiempoEstimado $tiempoEstimado): JsonResponse
    {
        $tiempoEstimado->delete();

        return response()->json(['message' => 'Tiempo estimado eliminado']);
    }
}
