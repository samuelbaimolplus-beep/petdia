<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MetodoPagoResource;
use App\Models\MetodoPago;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class MetodoPagoController extends Controller
{
    /**
     * Lista de metodos_pago con sus relaciones principales.
     */
    public function index(): AnonymousResourceCollection
    {
        $metodoPago = MetodoPago::query()
            ->orderByDesc('id_metodo_pago')
            ->paginate(15);

        return MetodoPagoResource::collection($metodoPago);
    }

    /**
     * Detalle de un registro.
     */
    public function show(MetodoPago $metodoPago): MetodoPagoResource
    {
        $metodoPago->load('detallePagos');

        return new MetodoPagoResource($metodoPago);
    }

    /**
     * Registra un nuevo registro.
     */
    public function store(Request $request): MetodoPagoResource
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:50', 'unique:metodos_pago,nombre'],
            'estado' => ['nullable', 'string', 'max:20'],
        ]);

        $metodoPago = MetodoPago::create($validated);

        return new MetodoPagoResource($metodoPago);
    }

    /**
     * Actualiza un registro existente.
     */
    public function update(Request $request, MetodoPago $metodoPago): MetodoPagoResource
    {
        $validated = $request->validate([
            'nombre' => ['sometimes', 'string', 'max:50', Rule::unique('metodos_pago', 'nombre')->ignore($metodoPago->id_metodo_pago, 'id_metodo_pago')],
            'estado' => ['nullable', 'string', 'max:20'],
        ]);

        $metodoPago->update($validated);

        return new MetodoPagoResource($metodoPago);
    }

    /**
     * Elimina un registro.
     */
    public function destroy(MetodoPago $metodoPago): JsonResponse
    {
        $metodoPago->delete();

        return response()->json(['message' => 'Metodo pago eliminado']);
    }
}
