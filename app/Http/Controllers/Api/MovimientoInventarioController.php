<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MovimientoInventarioResource;
use App\Models\MovimientoInventario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MovimientoInventarioController extends Controller
{
    /**
     * Lista de movimiento_inventarios con sus relaciones principales.
     */
    public function index(): AnonymousResourceCollection
    {
        $movimientoInventario = MovimientoInventario::with('producto', 'lote', 'usuario', 'detalleCompra', 'usoProductoAtencion', 'detalleVenta')
            ->orderByDesc('id_movimiento')
            ->paginate(15);

        return MovimientoInventarioResource::collection($movimientoInventario);
    }

    /**
     * Detalle de un registro.
     */
    public function show(MovimientoInventario $movimientoInventario): MovimientoInventarioResource
    {
        $movimientoInventario->load('producto', 'lote', 'usuario', 'detalleCompra', 'usoProductoAtencion', 'detalleVenta');

        return new MovimientoInventarioResource($movimientoInventario);
    }

    /**
     * Registra un nuevo registro.
     */
    public function store(Request $request): MovimientoInventarioResource
    {
        $validated = $request->validate([
            'id_producto' => ['required', 'integer', 'exists:productos,id_producto'],
            'id_lote' => ['nullable', 'integer', 'exists:lotes,id_lote'],
            'id_usuario' => ['required', 'integer', 'exists:usuarios,id_usuario'],
            'id_detalle_compra' => ['nullable', 'integer', 'exists:detalle_compras,id_detalle_compra'],
            'id_uso' => ['nullable', 'integer', 'exists:uso_producto_atencions,id_uso'],
            'id_detalle_venta' => ['nullable', 'integer', 'exists:detalle_ventas,id_detalle_venta'],
            'fecha_hora' => ['nullable', 'date'],
            'tipo_movimiento' => ['nullable', 'string', 'max:20'],
            'cantidad' => ['nullable', 'numeric'],
            'motivo' => ['nullable', 'string', 'max:50'],
        ]);

        $movimientoInventario = MovimientoInventario::create($validated);

        return new MovimientoInventarioResource($movimientoInventario);
    }

    /**
     * Actualiza un registro existente.
     */
    public function update(Request $request, MovimientoInventario $movimientoInventario): MovimientoInventarioResource
    {
        $validated = $request->validate([
            'id_producto' => ['sometimes', 'integer', 'exists:productos,id_producto'],
            'id_lote' => ['nullable', 'integer', 'exists:lotes,id_lote'],
            'id_usuario' => ['sometimes', 'integer', 'exists:usuarios,id_usuario'],
            'id_detalle_compra' => ['nullable', 'integer', 'exists:detalle_compras,id_detalle_compra'],
            'id_uso' => ['nullable', 'integer', 'exists:uso_producto_atencions,id_uso'],
            'id_detalle_venta' => ['nullable', 'integer', 'exists:detalle_ventas,id_detalle_venta'],
            'fecha_hora' => ['nullable', 'date'],
            'tipo_movimiento' => ['nullable', 'string', 'max:20'],
            'cantidad' => ['nullable', 'numeric'],
            'motivo' => ['nullable', 'string', 'max:50'],
        ]);

        $movimientoInventario->update($validated);

        return new MovimientoInventarioResource($movimientoInventario);
    }

    /**
     * Elimina un registro.
     */
    public function destroy(MovimientoInventario $movimientoInventario): JsonResponse
    {
        $movimientoInventario->delete();

        return response()->json(['message' => 'Movimiento inventario eliminado']);
    }
}
