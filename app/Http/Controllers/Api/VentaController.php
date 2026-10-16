<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\VentaResource;
use App\Models\Venta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class VentaController extends Controller
{
    /**
     * Lista de ventas con sus relaciones principales.
     */
    public function index(): AnonymousResourceCollection
    {
        $venta = Venta::with('propietario', 'usuario')
            ->orderByDesc('id_venta')
            ->paginate(15);

        return VentaResource::collection($venta);
    }

    /**
     * Detalle de un registro.
     */
    public function show(Venta $venta): VentaResource
    {
        $venta->load('propietario', 'usuario', 'detalleVentas', 'pagoVentas');

        return new VentaResource($venta);
    }

    /**
     * Registra un nuevo registro.
     */
    public function store(Request $request): VentaResource
    {
        $validated = $request->validate([
            'id_propietario' => ['nullable', 'integer', 'exists:propietarios,id_persona'],
            'id_usuario' => ['required', 'integer', 'exists:usuarios,id_usuario'],
            'fecha_hora' => ['nullable', 'date'],
            'estado' => ['nullable', 'string', 'max:20'],
            'total' => ['nullable', 'numeric'],
            'observaciones' => ['nullable', 'string'],
        ]);

        $venta = Venta::create($validated);

        return new VentaResource($venta);
    }

    /**
     * Actualiza un registro existente.
     */
    public function update(Request $request, Venta $venta): VentaResource
    {
        $validated = $request->validate([
            'id_propietario' => ['nullable', 'integer', 'exists:propietarios,id_persona'],
            'id_usuario' => ['sometimes', 'integer', 'exists:usuarios,id_usuario'],
            'fecha_hora' => ['nullable', 'date'],
            'estado' => ['nullable', 'string', 'max:20'],
            'total' => ['nullable', 'numeric'],
            'observaciones' => ['nullable', 'string'],
        ]);

        $venta->update($validated);

        return new VentaResource($venta);
    }

    /**
     * Elimina un registro.
     */
    public function destroy(Venta $venta): JsonResponse
    {
        $venta->delete();

        return response()->json(['message' => 'Venta eliminado']);
    }
}
