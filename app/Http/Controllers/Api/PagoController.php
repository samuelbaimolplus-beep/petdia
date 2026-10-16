<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PagoResource;
use App\Models\Pago;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PagoController extends Controller
{
    /**
     * Lista de pagos con sus relaciones principales.
     */
    public function index(): AnonymousResourceCollection
    {
        $pago = Pago::with('cuenta', 'cierreCaja')
            ->orderByDesc('id_pago')
            ->paginate(15);

        return PagoResource::collection($pago);
    }

    /**
     * Detalle de un registro.
     */
    public function show(Pago $pago): PagoResource
    {
        $pago->load('cuenta', 'cierreCaja', 'detallePagos', 'aplicaciones', 'pagoAtencions', 'pagoVentas', 'devoluciones');

        return new PagoResource($pago);
    }

    /**
     * Registra un nuevo registro.
     */
    public function store(Request $request): PagoResource
    {
        $validated = $request->validate([
            'id_cuenta' => ['nullable', 'integer', 'exists:cuentas,id_cuenta'],
            'id_cierre' => ['nullable', 'integer', 'exists:cierre_cajas,id_cierre'],
            'fecha_hora' => ['nullable', 'date'],
            'estado' => ['nullable', 'string', 'max:20'],
            'observaciones' => ['nullable', 'string'],
        ]);

        $pago = Pago::create($validated);

        return new PagoResource($pago);
    }

    /**
     * Actualiza un registro existente.
     */
    public function update(Request $request, Pago $pago): PagoResource
    {
        $validated = $request->validate([
            'id_cuenta' => ['nullable', 'integer', 'exists:cuentas,id_cuenta'],
            'id_cierre' => ['nullable', 'integer', 'exists:cierre_cajas,id_cierre'],
            'fecha_hora' => ['nullable', 'date'],
            'estado' => ['nullable', 'string', 'max:20'],
            'observaciones' => ['nullable', 'string'],
        ]);

        $pago->update($validated);

        return new PagoResource($pago);
    }

    /**
     * Elimina un registro.
     */
    public function destroy(Pago $pago): JsonResponse
    {
        $pago->delete();

        return response()->json(['message' => 'Pago eliminado']);
    }
}
