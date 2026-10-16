<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UsoProductoAtencionResource;
use App\Models\UsoProductoAtencion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UsoProductoAtencionController extends Controller
{
    /**
     * Lista de uso_producto_atencions con sus relaciones principales.
     */
    public function index(): AnonymousResourceCollection
    {
        $usoProductoAtencion = UsoProductoAtencion::with('atencion', 'producto')
            ->orderByDesc('id_uso')
            ->paginate(15);

        return UsoProductoAtencionResource::collection($usoProductoAtencion);
    }

    /**
     * Detalle de un registro.
     */
    public function show(UsoProductoAtencion $usoProductoAtencion): UsoProductoAtencionResource
    {
        $usoProductoAtencion->load('atencion', 'producto', 'administracionMedicamento', 'movimientoInventarios');

        return new UsoProductoAtencionResource($usoProductoAtencion);
    }

    /**
     * Registra un nuevo registro.
     */
    public function store(Request $request): UsoProductoAtencionResource
    {
        $validated = $request->validate([
            'id_atencion' => ['required', 'integer', 'exists:atenciones,id_atencion'],
            'id_producto' => ['required', 'integer', 'exists:productos,id_producto'],
            'id_lote' => ['nullable', 'integer', 'exists:lotes,id_lote'],
            'cantidad' => ['nullable', 'numeric'],
            'observaciones' => ['nullable', 'string'],
        ]);

        $usoProductoAtencion = UsoProductoAtencion::create($validated);

        return new UsoProductoAtencionResource($usoProductoAtencion);
    }

    /**
     * Actualiza un registro existente.
     */
    public function update(Request $request, UsoProductoAtencion $usoProductoAtencion): UsoProductoAtencionResource
    {
        $validated = $request->validate([
            'id_atencion' => ['sometimes', 'integer', 'exists:atenciones,id_atencion'],
            'id_producto' => ['sometimes', 'integer', 'exists:productos,id_producto'],
            'id_lote' => ['nullable', 'integer', 'exists:lotes,id_lote'],
            'cantidad' => ['nullable', 'numeric'],
            'observaciones' => ['nullable', 'string'],
        ]);

        $usoProductoAtencion->update($validated);

        return new UsoProductoAtencionResource($usoProductoAtencion);
    }

    /**
     * Elimina un registro.
     */
    public function destroy(UsoProductoAtencion $usoProductoAtencion): JsonResponse
    {
        $usoProductoAtencion->delete();

        return response()->json(['message' => 'Uso producto atencion eliminado']);
    }
}
