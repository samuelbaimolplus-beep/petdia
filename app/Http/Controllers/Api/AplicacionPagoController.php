<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AplicacionPagoResource;
use App\Models\AplicacionPago;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AplicacionPagoController extends Controller
{
    /**
     * Lista de aplicacion_pagos con sus relaciones principales.
     */
    public function index(): AnonymousResourceCollection
    {
        $aplicacionPago = AplicacionPago::with('pago', 'cargo')
            ->orderByDesc('id_aplicacion')
            ->paginate(15);

        return AplicacionPagoResource::collection($aplicacionPago);
    }

    /**
     * Detalle de un registro.
     */
    public function show(AplicacionPago $aplicacionPago): AplicacionPagoResource
    {
        $aplicacionPago->load('pago', 'cargo');

        return new AplicacionPagoResource($aplicacionPago);
    }

    /**
     * Registra un nuevo registro.
     */
    public function store(Request $request): AplicacionPagoResource
    {
        $validated = $request->validate([
            'id_pago' => ['required', 'integer', 'exists:pagos,id_pago'],
            'id_cargo' => ['required', 'integer', 'exists:cargos,id_cargo'],
            'monto_aplicado' => ['nullable', 'numeric'],
        ]);

        $aplicacionPago = AplicacionPago::create($validated);

        return new AplicacionPagoResource($aplicacionPago);
    }

    /**
     * Actualiza un registro existente.
     */
    public function update(Request $request, AplicacionPago $aplicacionPago): AplicacionPagoResource
    {
        $validated = $request->validate([
            'id_pago' => ['sometimes', 'integer', 'exists:pagos,id_pago'],
            'id_cargo' => ['sometimes', 'integer', 'exists:cargos,id_cargo'],
            'monto_aplicado' => ['nullable', 'numeric'],
        ]);

        $aplicacionPago->update($validated);

        return new AplicacionPagoResource($aplicacionPago);
    }

    /**
     * Elimina un registro.
     */
    public function destroy(AplicacionPago $aplicacionPago): JsonResponse
    {
        $aplicacionPago->delete();

        return response()->json(['message' => 'Aplicacion pago eliminado']);
    }
}
