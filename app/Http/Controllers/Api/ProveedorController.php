<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProveedorResource;
use App\Models\Proveedor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProveedorController extends Controller
{
    /**
     * Lista de proveedores con sus relaciones principales.
     */
    public function index(): AnonymousResourceCollection
    {
        $proveedor = Proveedor::query()
            ->orderByDesc('id_proveedor')
            ->paginate(15);

        return ProveedorResource::collection($proveedor);
    }

    /**
     * Detalle de un registro.
     */
    public function show(Proveedor $proveedor): ProveedorResource
    {
        $proveedor->load('compras');

        return new ProveedorResource($proveedor);
    }

    /**
     * Registra un nuevo registro.
     */
    public function store(Request $request): ProveedorResource
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'nit' => ['nullable', 'string', 'max:50'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'string', 'max:100'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'estado' => ['nullable', 'string', 'max:20'],
        ]);

        $proveedor = Proveedor::create($validated);

        return new ProveedorResource($proveedor);
    }

    /**
     * Actualiza un registro existente.
     */
    public function update(Request $request, Proveedor $proveedor): ProveedorResource
    {
        $validated = $request->validate([
            'nombre' => ['sometimes', 'string', 'max:100'],
            'nit' => ['nullable', 'string', 'max:50'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'string', 'max:100'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'estado' => ['nullable', 'string', 'max:20'],
        ]);

        $proveedor->update($validated);

        return new ProveedorResource($proveedor);
    }

    /**
     * Elimina un registro.
     */
    public function destroy(Proveedor $proveedor): JsonResponse
    {
        $proveedor->delete();

        return response()->json(['message' => 'Proveedor eliminado']);
    }
}
