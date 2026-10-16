<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PagoAtencionResource;
use App\Models\PagoAtencion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PagoAtencionController extends Controller
{
    /**
     * Lista de pago_atencions con sus relaciones principales.
     */
    public function index(): AnonymousResourceCollection
    {
        $pagoAtencion = PagoAtencion::with('pago', 'atencion')
            ->orderByDesc('id_pago_atencion')
            ->paginate(15);

        return PagoAtencionResource::collection($pagoAtencion);
    }

    /**
     * Detalle de un registro.
     */
    public function show(PagoAtencion $pagoAtencion): PagoAtencionResource
    {
        $pagoAtencion->load('pago', 'atencion');

        return new PagoAtencionResource($pagoAtencion);
    }

    /**
     * Registra un nuevo registro.
     */
    public function store(Request $request): PagoAtencionResource
    {
        $validated = $request->validate([
            'id_pago' => ['required', 'integer', 'exists:pagos,id_pago'],
            'id_atencion' => ['required', 'integer', 'exists:atenciones,id_atencion'],
            'monto_aplicado' => ['nullable', 'numeric'],
        ]);

        $pagoAtencion = PagoAtencion::create($validated);

        return new PagoAtencionResource($pagoAtencion);
    }

    /**
     * Actualiza un registro existente.
     */
    public function update(Request $request, PagoAtencion $pagoAtencion): PagoAtencionResource
    {
        $validated = $request->validate([
            'id_pago' => ['sometimes', 'integer', 'exists:pagos,id_pago'],
            'id_atencion' => ['sometimes', 'integer', 'exists:atenciones,id_atencion'],
            'monto_aplicado' => ['nullable', 'numeric'],
        ]);

        $pagoAtencion->update($validated);

        return new PagoAtencionResource($pagoAtencion);
    }

    /**
     * Elimina un registro.
     */
    public function destroy(PagoAtencion $pagoAtencion): JsonResponse
    {
        $pagoAtencion->delete();

        return response()->json(['message' => 'Pago atencion eliminado']);
    }
}
