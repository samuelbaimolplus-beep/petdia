<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DevolucionPagoResource;
use App\Models\DevolucionPago;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DevolucionPagoController extends Controller
{
    /**
     * Lista de devolucion_pagos con sus relaciones principales.
     */
    public function index(): AnonymousResourceCollection
    {
        $devolucionPago = DevolucionPago::with('pago', 'usuario')
            ->orderByDesc('id_devolucion')
            ->paginate(15);

        return DevolucionPagoResource::collection($devolucionPago);
    }

    /**
     * Detalle de un registro.
     */
    public function show(DevolucionPago $devolucionPago): DevolucionPagoResource
    {
        $devolucionPago->load('pago', 'usuario');

        return new DevolucionPagoResource($devolucionPago);
    }

    /**
     * Registra un nuevo registro.
     */
    public function store(Request $request): DevolucionPagoResource
    {
        $validated = $request->validate([
            'id_pago' => ['required', 'integer', 'exists:pagos,id_pago'],
            'id_usuario' => ['required', 'integer', 'exists:usuarios,id_usuario'],
            'fecha_hora' => ['nullable', 'date'],
            'monto' => ['nullable', 'numeric'],
            'motivo' => ['nullable', 'string'],
            'estado' => ['nullable', 'string', 'max:20'],
        ]);

        $devolucionPago = DevolucionPago::create($validated);

        return new DevolucionPagoResource($devolucionPago);
    }

    /**
     * Actualiza un registro existente.
     */
    public function update(Request $request, DevolucionPago $devolucionPago): DevolucionPagoResource
    {
        $validated = $request->validate([
            'id_pago' => ['sometimes', 'integer', 'exists:pagos,id_pago'],
            'id_usuario' => ['sometimes', 'integer', 'exists:usuarios,id_usuario'],
            'fecha_hora' => ['nullable', 'date'],
            'monto' => ['nullable', 'numeric'],
            'motivo' => ['nullable', 'string'],
            'estado' => ['nullable', 'string', 'max:20'],
        ]);

        $devolucionPago->update($validated);

        return new DevolucionPagoResource($devolucionPago);
    }

    /**
     * Elimina un registro.
     */
    public function destroy(DevolucionPago $devolucionPago): JsonResponse
    {
        $devolucionPago->delete();

        return response()->json(['message' => 'Devolucion pago eliminado']);
    }
}
