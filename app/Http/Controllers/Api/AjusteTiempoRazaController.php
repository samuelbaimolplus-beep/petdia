<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AjusteTiempoRazaResource;
use App\Models\AjusteTiempoRaza;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AjusteTiempoRazaController extends Controller
{
    /**
     * Lista de ajustes_tiempo_razas con sus relaciones principales.
     */
    public function index(): AnonymousResourceCollection
    {
        $ajusteTiempoRaza = AjusteTiempoRaza::with('tiempoEstimado', 'raza')
            ->orderByDesc('id_ajuste')
            ->paginate(15);

        return AjusteTiempoRazaResource::collection($ajusteTiempoRaza);
    }

    /**
     * Detalle de un registro.
     */
    public function show(AjusteTiempoRaza $ajusteTiempoRaza): AjusteTiempoRazaResource
    {
        $ajusteTiempoRaza->load('tiempoEstimado', 'raza');

        return new AjusteTiempoRazaResource($ajusteTiempoRaza);
    }

    /**
     * Registra un nuevo registro.
     */
    public function store(Request $request): AjusteTiempoRazaResource
    {
        $validated = $request->validate([
            'id_tiempo_estimado' => ['required', 'integer', 'exists:tiempos_estimados,id_tiempo_estimado'],
            'id_raza' => ['required', 'integer', 'exists:razas,id_raza'],
            'duracion_minutos' => ['nullable', 'integer'],
        ]);

        $ajusteTiempoRaza = AjusteTiempoRaza::create($validated);

        return new AjusteTiempoRazaResource($ajusteTiempoRaza);
    }

    /**
     * Actualiza un registro existente.
     */
    public function update(Request $request, AjusteTiempoRaza $ajusteTiempoRaza): AjusteTiempoRazaResource
    {
        $validated = $request->validate([
            'id_tiempo_estimado' => ['sometimes', 'integer', 'exists:tiempos_estimados,id_tiempo_estimado'],
            'id_raza' => ['sometimes', 'integer', 'exists:razas,id_raza'],
            'duracion_minutos' => ['nullable', 'integer'],
        ]);

        $ajusteTiempoRaza->update($validated);

        return new AjusteTiempoRazaResource($ajusteTiempoRaza);
    }

    /**
     * Elimina un registro.
     */
    public function destroy(AjusteTiempoRaza $ajusteTiempoRaza): JsonResponse
    {
        $ajusteTiempoRaza->delete();

        return response()->json(['message' => 'Ajuste tiempo raza eliminado']);
    }
}
