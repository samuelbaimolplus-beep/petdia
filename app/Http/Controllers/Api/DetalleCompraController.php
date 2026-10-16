<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DetalleCompraResource;
use App\Models\DetalleCompra;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DetalleCompraController extends Controller
{
    /**
     * Lista de detalle_compras con sus relaciones principales.
     */
    public function index(): AnonymousResourceCollection
    {
        $detalleCompra = DetalleCompra::with('compra', 'producto')
            ->orderByDesc('id_detalle_compra')
            ->paginate(15);

        return DetalleCompraResource::collection($detalleCompra);
    }

    /**
     * Detalle de un registro.
     */
    public function show(DetalleCompra $detalleCompra): DetalleCompraResource
    {
        $detalleCompra->load('compra', 'producto', 'lotes', 'movimientoInventarios');

        return new DetalleCompraResource($detalleCompra);
    }

    /**
     * Registra un nuevo registro.
     */
    public function store(Request $request): DetalleCompraResource
    {
        $validated = $request->validate([
            'id_compra' => ['required', 'integer', 'exists:compras,id_compra'],
            'id_producto' => ['required', 'integer', 'exists:productos,id_producto'],
            'cantidad' => ['nullable', 'numeric'],
            'precio_unitario' => ['nullable', 'numeric'],
        ]);

        $detalleCompra = DetalleCompra::create($validated);

        return new DetalleCompraResource($detalleCompra);
    }

    /**
     * Actualiza un registro existente.
     */
    public function update(Request $request, DetalleCompra $detalleCompra): DetalleCompraResource
    {
        $validated = $request->validate([
            'id_compra' => ['sometimes', 'integer', 'exists:compras,id_compra'],
            'id_producto' => ['sometimes', 'integer', 'exists:productos,id_producto'],
            'cantidad' => ['nullable', 'numeric'],
            'precio_unitario' => ['nullable', 'numeric'],
        ]);

        $detalleCompra->update($validated);

        return new DetalleCompraResource($detalleCompra);
    }

    /**
     * Elimina un registro.
     */
    public function destroy(DetalleCompra $detalleCompra): JsonResponse
    {
        $detalleCompra->delete();

        return response()->json(['message' => 'Detalle compra eliminado']);
    }
}
