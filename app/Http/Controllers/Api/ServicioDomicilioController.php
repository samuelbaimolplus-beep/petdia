<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServicioDomicilioResource;
use App\Models\ServicioDomicilio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class ServicioDomicilioController extends Controller
{
    /**
     * Lista de servicio_domicilios con sus relaciones principales.
     */
    public function index(): AnonymousResourceCollection
    {
        $servicioDomicilio = ServicioDomicilio::with('cita')
            ->orderByDesc('id_servicio_domicilio')
            ->paginate(15);

        return ServicioDomicilioResource::collection($servicioDomicilio);
    }

    /**
     * Detalle de un registro.
     */
    public function show(ServicioDomicilio $servicioDomicilio): ServicioDomicilioResource
    {
        $servicioDomicilio->load('cita');

        return new ServicioDomicilioResource($servicioDomicilio);
    }

    /**
     * Registra un nuevo registro.
     */
    public function store(Request $request): ServicioDomicilioResource
    {
        $validated = $request->validate([
            'id_cita' => ['required', 'integer', 'exists:citas,id_cita', 'unique:servicio_domicilios,id_cita'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'referencia' => ['nullable', 'string', 'max:255'],
            'distancia_estimada' => ['nullable', 'numeric'],
            'costo_domicilio' => ['nullable', 'numeric'],
            'estado' => ['nullable', 'string', 'max:20'],
        ]);

        $servicioDomicilio = ServicioDomicilio::create($validated);

        return new ServicioDomicilioResource($servicioDomicilio);
    }

    /**
     * Actualiza un registro existente.
     */
    public function update(Request $request, ServicioDomicilio $servicioDomicilio): ServicioDomicilioResource
    {
        $validated = $request->validate([
            'id_cita' => ['sometimes', 'integer', 'exists:citas,id_cita', Rule::unique('servicio_domicilios', 'id_cita')->ignore($servicioDomicilio->id_servicio_domicilio, 'id_servicio_domicilio')],
            'direccion' => ['nullable', 'string', 'max:255'],
            'referencia' => ['nullable', 'string', 'max:255'],
            'distancia_estimada' => ['nullable', 'numeric'],
            'costo_domicilio' => ['nullable', 'numeric'],
            'estado' => ['nullable', 'string', 'max:20'],
        ]);

        $servicioDomicilio->update($validated);

        return new ServicioDomicilioResource($servicioDomicilio);
    }

    /**
     * Elimina un registro.
     */
    public function destroy(ServicioDomicilio $servicioDomicilio): JsonResponse
    {
        $servicioDomicilio->delete();

        return response()->json(['message' => 'Servicio domicilio eliminado']);
    }
}
