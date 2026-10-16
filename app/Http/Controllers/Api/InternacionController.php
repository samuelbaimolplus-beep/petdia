<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\InternacionResource;
use App\Models\Internacion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class InternacionController extends Controller
{
    /**
     * Lista de internaciones con sus relaciones principales.
     */
    public function index(): AnonymousResourceCollection
    {
        $internacion = Internacion::with('atencionVeterinaria')
            ->orderByDesc('id_internacion')
            ->paginate(15);

        return InternacionResource::collection($internacion);
    }

    /**
     * Detalle de un registro.
     */
    public function show(Internacion $internacion): InternacionResource
    {
        $internacion->load('atencionVeterinaria', 'seguimientos');

        return new InternacionResource($internacion);
    }

    /**
     * Registra un nuevo registro.
     */
    public function store(Request $request): InternacionResource
    {
        $validated = $request->validate([
            'id_atencion_ingreso' => ['required', 'integer', 'exists:atencion_veterinarias,id_atencion', 'unique:internaciones,id_atencion_ingreso'],
            'fecha_hora_ingreso' => ['nullable', 'date'],
            'fecha_hora_alta' => ['nullable', 'date'],
            'motivo' => ['nullable', 'string'],
            'estado' => ['nullable', 'string', 'max:20'],
            'observaciones' => ['nullable', 'string'],
        ]);

        $internacion = Internacion::create($validated);

        return new InternacionResource($internacion);
    }

    /**
     * Actualiza un registro existente.
     */
    public function update(Request $request, Internacion $internacion): InternacionResource
    {
        $validated = $request->validate([
            'id_atencion_ingreso' => ['sometimes', 'integer', 'exists:atencion_veterinarias,id_atencion', Rule::unique('internaciones', 'id_atencion_ingreso')->ignore($internacion->id_internacion, 'id_internacion')],
            'fecha_hora_ingreso' => ['nullable', 'date'],
            'fecha_hora_alta' => ['nullable', 'date'],
            'motivo' => ['nullable', 'string'],
            'estado' => ['nullable', 'string', 'max:20'],
            'observaciones' => ['nullable', 'string'],
        ]);

        $internacion->update($validated);

        return new InternacionResource($internacion);
    }

    /**
     * Elimina un registro.
     */
    public function destroy(Internacion $internacion): JsonResponse
    {
        $internacion->delete();

        return response()->json(['message' => 'Internacion eliminado']);
    }
}
