<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CompraResource;
use App\Models\Compra;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CompraController extends Controller
{
    /**
     * Lista de compras con sus relaciones principales.
     */
    public function index(): AnonymousResourceCollection
    {
        $compra = Compra::with('proveedor')
            ->orderByDesc('id_compra')
            ->paginate(15);

        return CompraResource::collection($compra);
    }

    /**
     * Detalle de un registro.
     */
    public function show(Compra $compra): CompraResource
    {
        $compra->load('proveedor', 'detalleCompras');

        return new CompraResource($compra);
    }

    /**
     * Registra un nuevo registro.
     */
    public function store(Request $request): CompraResource
    {
        $validated = $request->validate([
            'id_proveedor' => ['required', 'integer', 'exists:proveedores,id_proveedor'],
            'fecha_hora' => ['nullable', 'date'],
            'estado' => ['nullable', 'string', 'max:20'],
            'total' => ['nullable', 'numeric'],
            'observaciones' => ['nullable', 'string'],
        ]);

        $compra = Compra::create($validated);

        return new CompraResource($compra);
    }

    /**
     * Actualiza un registro existente.
     */
    public function update(Request $request, Compra $compra): CompraResource
    {
        $validated = $request->validate([
            'id_proveedor' => ['sometimes', 'integer', 'exists:proveedores,id_proveedor'],
            'fecha_hora' => ['nullable', 'date'],
            'estado' => ['nullable', 'string', 'max:20'],
            'total' => ['nullable', 'numeric'],
            'observaciones' => ['nullable', 'string'],
        ]);

        $compra->update($validated);

        return new CompraResource($compra);
    }

    /**
     * Elimina un registro.
     */
    public function destroy(Compra $compra): JsonResponse
    {
        $compra->delete();

        return response()->json(['message' => 'Compra eliminado']);
    }
}
