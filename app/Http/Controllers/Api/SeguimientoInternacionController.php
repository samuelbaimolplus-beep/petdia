<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SeguimientoInternacionResource;
use App\Models\SeguimientoInternacion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SeguimientoInternacionController extends Controller
{
    /**
     * Lista de seguimiento_internaciones con sus relaciones principales.
     */
    public function index(): AnonymousResourceCollection
    {
        $seguimientoInternacion = SeguimientoInternacion::with('internacion', 'profesional')
            ->orderByDesc('id_seguimiento')
            ->paginate(15);

        return SeguimientoInternacionResource::collection($seguimientoInternacion);
    }

    /**
     * Detalle de un registro.
     */
    public function show(SeguimientoInternacion $seguimientoInternacion): SeguimientoInternacionResource
    {
        $seguimientoInternacion->load('internacion', 'profesional');

        return new SeguimientoInternacionResource($seguimientoInternacion);
    }

    /**
     * Registra un nuevo registro.
     */
    public function store(Request $request): SeguimientoInternacionResource
    {
        $validated = $request->validate([
            'id_internacion' => ['required', 'integer', 'exists:internaciones,id_internacion'],
            'id_profesional' => ['required', 'integer', 'exists:profesionales,id_persona'],
            'fecha_hora' => ['nullable', 'date'],
            'cuidados' => ['nullable', 'string'],
            'observaciones' => ['nullable', 'string'],
        ]);

        $seguimientoInternacion = SeguimientoInternacion::create($validated);

        return new SeguimientoInternacionResource($seguimientoInternacion);
    }

    /**
     * Actualiza un registro existente.
     */
    public function update(Request $request, SeguimientoInternacion $seguimientoInternacion): SeguimientoInternacionResource
    {
        $validated = $request->validate([
            'id_internacion' => ['sometimes', 'integer', 'exists:internaciones,id_internacion'],
            'id_profesional' => ['sometimes', 'integer', 'exists:profesionales,id_persona'],
            'fecha_hora' => ['nullable', 'date'],
            'cuidados' => ['nullable', 'string'],
            'observaciones' => ['nullable', 'string'],
        ]);

        $seguimientoInternacion->update($validated);

        return new SeguimientoInternacionResource($seguimientoInternacion);
    }

    /**
     * Elimina un registro.
     */
    public function destroy(SeguimientoInternacion $seguimientoInternacion): JsonResponse
    {
        $seguimientoInternacion->delete();

        return response()->json(['message' => 'Seguimiento internacion eliminado']);
    }
}
