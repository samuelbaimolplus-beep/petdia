<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PagoVentaResource;
use App\Models\PagoVenta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PagoVentaController extends Controller
{
    /**
     * Lista de pago_ventas con sus relaciones principales.
     */
    public function index(): AnonymousResourceCollection
    {
        $pagoVenta = PagoVenta::with('pago', 'venta')
            ->orderByDesc('id_pago_venta')
            ->paginate(15);

        return PagoVentaResource::collection($pagoVenta);
    }

    /**
     * Detalle de un registro.
     */
    public function show(PagoVenta $pagoVenta): PagoVentaResource
    {
        $pagoVenta->load('pago', 'venta');

        return new PagoVentaResource($pagoVenta);
    }

    /**
     * Registra un nuevo registro.
     */
    public function store(Request $request): PagoVentaResource
    {
        $validated = $request->validate([
            'id_pago' => ['required', 'integer', 'exists:pagos,id_pago'],
            'id_venta' => ['required', 'integer', 'exists:ventas,id_venta'],
            'monto_aplicado' => ['nullable', 'numeric'],
        ]);

        $pagoVenta = PagoVenta::create($validated);

        return new PagoVentaResource($pagoVenta);
    }

    /**
     * Actualiza un registro existente.
     */
    public function update(Request $request, PagoVenta $pagoVenta): PagoVentaResource
    {
        $validated = $request->validate([
            'id_pago' => ['sometimes', 'integer', 'exists:pagos,id_pago'],
            'id_venta' => ['sometimes', 'integer', 'exists:ventas,id_venta'],
            'monto_aplicado' => ['nullable', 'numeric'],
        ]);

        $pagoVenta->update($validated);

        return new PagoVentaResource($pagoVenta);
    }

    /**
     * Elimina un registro.
     */
    public function destroy(PagoVenta $pagoVenta): JsonResponse
    {
        $pagoVenta->delete();

        return response()->json(['message' => 'Pago venta eliminado']);
    }
}
