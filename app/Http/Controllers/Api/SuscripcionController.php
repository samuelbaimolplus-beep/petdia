<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SuscripcionResource;
use App\Models\Suscripcion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SuscripcionController extends Controller
{
    /**
     * Lista de suscripciones con sus relaciones principales.
     */
    public function index(): AnonymousResourceCollection
    {
        $suscripcion = Suscripcion::with('paciente', 'paquete')
            ->orderByDesc('id_suscripcion')
            ->paginate(15);

        return SuscripcionResource::collection($suscripcion);
    }

    /**
     * Detalle de un registro.
     */
    public function show(Suscripcion $suscripcion): SuscripcionResource
    {
        $suscripcion->load('paciente', 'paquete');

        return new SuscripcionResource($suscripcion);
    }

    /**
     * Registra un nuevo registro.
     */
    public function store(Request $request): SuscripcionResource
    {
        $validated = $request->validate([
            'id_paciente' => ['required', 'integer', 'exists:pacientes,id_paciente'],
            'id_paquete' => ['required', 'integer', 'exists:paquetes,id_paquete'],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date'],
            'estado' => ['nullable', 'string', 'max:20'],
        ]);

        $suscripcion = Suscripcion::create($validated);

        return new SuscripcionResource($suscripcion);
    }

    /**
     * Actualiza un registro existente.
     */
    public function update(Request $request, Suscripcion $suscripcion): SuscripcionResource
    {
        $validated = $request->validate([
            'id_paciente' => ['sometimes', 'integer', 'exists:pacientes,id_paciente'],
            'id_paquete' => ['sometimes', 'integer', 'exists:paquetes,id_paquete'],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date'],
            'estado' => ['nullable', 'string', 'max:20'],
        ]);

        $suscripcion->update($validated);

        return new SuscripcionResource($suscripcion);
    }

    /**
     * Elimina un registro.
     */
    public function destroy(Suscripcion $suscripcion): JsonResponse
    {
        $suscripcion->delete();

        return response()->json(['message' => 'Suscripcion eliminado']);
    }
}
