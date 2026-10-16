<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DetalleVentaResource;
use App\Models\DetalleVenta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DetalleVentaController extends Controller
{
    /**
     * Lista de detalle_ventas con sus relaciones principales.
     */
    public function index(): AnonymousResourceCollection
    {
        $detalleVenta = DetalleVenta::with('venta', 'producto')
            ->orderByDesc('id_detalle_venta')
            ->paginate(15);

        return DetalleVentaResource::collection($detalleVenta);
    }

    /**
     * Detalle de un registro.
     */
    public function show(DetalleVenta $detalleVenta): DetalleVentaResource
    {
        $detalleVenta->load('venta', 'producto', 'movimientoInventarios');

        return new DetalleVentaResource($detalleVenta);
    }

    /**
     * Registra un nuevo registro.
     */
    public function store(Request $request): DetalleVentaResource
    {
        $validated = $request->validate([
            'id_venta' => ['required', 'integer', 'exists:ventas,id_venta'],
            'id_producto' => ['required', 'integer', 'exists:productos,id_producto'],
            'cantidad' => ['nullable', 'numeric'],
            'precio_unitario' => ['nullable', 'numeric'],
            'descuento' => ['nullable', 'numeric'],
        ]);

        $detalleVenta = DetalleVenta::create($validated);

        return new DetalleVentaResource($detalleVenta);
    }

    /**
     * Actualiza un registro existente.
     */
    public function update(Request $request, DetalleVenta $detalleVenta): DetalleVentaResource
    {
        $validated = $request->validate([
            'id_venta' => ['sometimes', 'integer', 'exists:ventas,id_venta'],
            'id_producto' => ['sometimes', 'integer', 'exists:productos,id_producto'],
            'cantidad' => ['nullable', 'numeric'],
            'precio_unitario' => ['nullable', 'numeric'],
            'descuento' => ['nullable', 'numeric'],
        ]);

        $detalleVenta->update($validated);

        return new DetalleVentaResource($detalleVenta);
    }

    /**
     * Elimina un registro.
     */
    public function destroy(DetalleVenta $detalleVenta): JsonResponse
    {
        $detalleVenta->delete();

        return response()->json(['message' => 'Detalle venta eliminado']);
    }
}
