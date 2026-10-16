<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductoResource;
use App\Models\Producto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductoController extends Controller
{
    /**
     * Lista de productos con sus medicamentos asociados.
     */
    public function index(): AnonymousResourceCollection
    {
        $productos = Producto::with('medicamento')
            ->orderBy('nombre')
            ->paginate(15);

        return ProductoResource::collection($productos);
    }

    /**
     * Detalle de un producto con sus lotes.
     */
    public function show(Producto $producto): ProductoResource
    {
        $producto->load('medicamento', 'lotes');

        return new ProductoResource($producto);
    }

    /**
     * Registra un nuevo producto.
     */
    public function store(Request $request): ProductoResource
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'tipo' => ['required', 'in:MEDICAMENTO,INSUMO,HERRAMIENTA,ACCESORIO'],
            'categoria' => ['required', 'in:VETERINARIA,ESTETICA'],
            'marca' => ['nullable', 'string', 'max:100'],
            'unidad_medida' => ['nullable', 'string', 'max:50'],
            'descripcion' => ['nullable', 'string'],
            'precio_venta' => ['required', 'numeric', 'min:0'],
            'stock_minimo' => ['nullable', 'numeric', 'min:0'],
            'control_lotes' => ['nullable', 'boolean'],
            'estado' => ['nullable', 'in:ACTIVO,INACTIVO'],
        ]);

        $producto = Producto::create($validated);

        return new ProductoResource($producto);
    }

    /**
     * Actualiza un producto existente.
     */
    public function update(Request $request, Producto $producto): ProductoResource
    {
        $validated = $request->validate([
            'nombre' => ['sometimes', 'string', 'max:150'],
            'tipo' => ['sometimes', 'in:MEDICAMENTO,INSUMO,HERRAMIENTA,ACCESORIO'],
            'categoria' => ['sometimes', 'in:VETERINARIA,ESTETICA'],
            'marca' => ['nullable', 'string', 'max:100'],
            'unidad_medida' => ['nullable', 'string', 'max:50'],
            'descripcion' => ['nullable', 'string'],
            'precio_venta' => ['sometimes', 'numeric', 'min:0'],
            'stock_minimo' => ['nullable', 'numeric', 'min:0'],
            'control_lotes' => ['nullable', 'boolean'],
            'estado' => ['sometimes', 'in:ACTIVO,INACTIVO'],
        ]);

        $producto->update($validated);

        return new ProductoResource($producto);
    }

    /**
     * Elimina un producto.
     */
    public function destroy(Producto $producto): JsonResponse
    {
        $producto->delete();

        return response()->json(['message' => 'Producto eliminado']);
    }
}
