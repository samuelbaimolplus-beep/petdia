<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DetalleCitaResource;
use App\Models\DetalleCita;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DetalleCitaController extends Controller
{
    /**
     * Lista de detalle_citas con sus relaciones principales.
     */
    public function index(): AnonymousResourceCollection
    {
        $detalleCita = DetalleCita::with('cita', 'servicio')
            ->orderByDesc('id_detalle_cita')
            ->paginate(15);

        return DetalleCitaResource::collection($detalleCita);
    }

    /**
     * Detalle de un registro.
     */
    public function show(DetalleCita $detalleCita): DetalleCitaResource
    {
        $detalleCita->load('cita', 'servicio');

        return new DetalleCitaResource($detalleCita);
    }

    /**
     * Registra un nuevo registro.
     */
    public function store(Request $request): DetalleCitaResource
    {
        $validated = $request->validate([
            'id_cita' => ['required', 'integer', 'exists:citas,id_cita'],
            'id_servicio' => ['required', 'integer', 'exists:servicios,id_servicio'],
            'costo_estimado' => ['nullable', 'numeric'],
            'tiempo_estimado_minutos' => ['nullable', 'integer'],
        ]);

        $detalleCita = DetalleCita::create($validated);

        return new DetalleCitaResource($detalleCita);
    }

    /**
     * Actualiza un registro existente.
     */
    public function update(Request $request, DetalleCita $detalleCita): DetalleCitaResource
    {
        $validated = $request->validate([
            'id_cita' => ['sometimes', 'integer', 'exists:citas,id_cita'],
            'id_servicio' => ['sometimes', 'integer', 'exists:servicios,id_servicio'],
            'costo_estimado' => ['nullable', 'numeric'],
            'tiempo_estimado_minutos' => ['nullable', 'integer'],
        ]);

        $detalleCita->update($validated);

        return new DetalleCitaResource($detalleCita);
    }

    /**
     * Elimina un registro.
     */
    public function destroy(DetalleCita $detalleCita): JsonResponse
    {
        $detalleCita->delete();

        return response()->json(['message' => 'Detalle cita eliminado']);
    }
}
