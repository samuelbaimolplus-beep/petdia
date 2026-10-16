<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DetallePagoResource;
use App\Models\DetallePago;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DetallePagoController extends Controller
{
    /**
     * Lista de detalle_pagos con sus relaciones principales.
     */
    public function index(): AnonymousResourceCollection
    {
        $detallePago = DetallePago::with('pago', 'metodoPago')
            ->orderByDesc('id_detalle_pago')
            ->paginate(15);

        return DetallePagoResource::collection($detallePago);
    }

    /**
     * Detalle de un registro.
     */
    public function show(DetallePago $detallePago): DetallePagoResource
    {
        $detallePago->load('pago', 'metodoPago');

        return new DetallePagoResource($detallePago);
    }

    /**
     * Registra un nuevo registro.
     */
    public function store(Request $request): DetallePagoResource
    {
        $validated = $request->validate([
            'id_pago' => ['required', 'integer', 'exists:pagos,id_pago'],
            'id_metodo_pago' => ['required', 'integer', 'exists:metodos_pago,id_metodo_pago'],
            'monto' => ['nullable', 'numeric'],
            'referencia' => ['nullable', 'string', 'max:100'],
        ]);

        $detallePago = DetallePago::create($validated);

        return new DetallePagoResource($detallePago);
    }

    /**
     * Actualiza un registro existente.
     */
    public function update(Request $request, DetallePago $detallePago): DetallePagoResource
    {
        $validated = $request->validate([
            'id_pago' => ['sometimes', 'integer', 'exists:pagos,id_pago'],
            'id_metodo_pago' => ['sometimes', 'integer', 'exists:metodos_pago,id_metodo_pago'],
            'monto' => ['nullable', 'numeric'],
            'referencia' => ['nullable', 'string', 'max:100'],
        ]);

        $detallePago->update($validated);

        return new DetallePagoResource($detallePago);
    }

    /**
     * Elimina un registro.
     */
    public function destroy(DetallePago $detallePago): JsonResponse
    {
        $detallePago->delete();

        return response()->json(['message' => 'Detalle pago eliminado']);
    }
}
