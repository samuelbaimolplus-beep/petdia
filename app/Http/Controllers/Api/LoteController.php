<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\LoteResource;
use App\Models\Lote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LoteController extends Controller
{
    /**
     * Lista de lotes con sus relaciones principales.
     */
    public function index(): AnonymousResourceCollection
    {
        $lote = Lote::with('producto', 'detalleCompra')
            ->orderByDesc('id_lote')
            ->paginate(15);

        return LoteResource::collection($lote);
    }

    /**
     * Detalle de un registro.
     */
    public function show(Lote $lote): LoteResource
    {
        $lote->load('producto', 'detalleCompra', 'movimientoInventarios');

        return new LoteResource($lote);
    }

    /**
     * Registra un nuevo registro.
     */
    public function store(Request $request): LoteResource
    {
        $validated = $request->validate([
            'id_producto' => ['required', 'integer', 'exists:productos,id_producto'],
            'id_detalle_compra' => ['nullable', 'integer', 'exists:detalle_compras,id_detalle_compra'],
            'numero_lote' => ['nullable', 'string', 'max:50'],
            'fecha_vencimiento' => ['nullable', 'date'],
            'estado' => ['nullable', 'string', 'max:20'],
        ]);

        $lote = Lote::create($validated);

        return new LoteResource($lote);
    }

    /**
     * Actualiza un registro existente.
     */
    public function update(Request $request, Lote $lote): LoteResource
    {
        $validated = $request->validate([
            'id_producto' => ['sometimes', 'integer', 'exists:productos,id_producto'],
            'id_detalle_compra' => ['nullable', 'integer', 'exists:detalle_compras,id_detalle_compra'],
            'numero_lote' => ['nullable', 'string', 'max:50'],
            'fecha_vencimiento' => ['nullable', 'date'],
            'estado' => ['nullable', 'string', 'max:20'],
        ]);

        $lote->update($validated);

        return new LoteResource($lote);
    }

    /**
     * Elimina un registro.
     */
    public function destroy(Lote $lote): JsonResponse
    {
        $lote->delete();

        return response()->json(['message' => 'Lote eliminado']);
    }
}
