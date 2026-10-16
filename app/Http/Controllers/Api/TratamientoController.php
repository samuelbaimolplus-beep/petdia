<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TratamientoResource;
use App\Models\Tratamiento;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TratamientoController extends Controller
{
    /**
     * Lista de tratamientos con sus relaciones principales.
     */
    public function index(): AnonymousResourceCollection
    {
        $tratamiento = Tratamiento::with('paciente')
            ->orderByDesc('id_tratamiento')
            ->paginate(15);

        return TratamientoResource::collection($tratamiento);
    }

    /**
     * Detalle de un registro.
     */
    public function show(Tratamiento $tratamiento): TratamientoResource
    {
        $tratamiento->load('paciente', 'cargos');

        return new TratamientoResource($tratamiento);
    }

    /**
     * Registra un nuevo registro.
     */
    public function store(Request $request): TratamientoResource
    {
        $validated = $request->validate([
            'id_paciente' => ['required', 'integer', 'exists:pacientes,id_paciente'],
            'descripcion' => ['nullable', 'string'],
            'indicaciones' => ['nullable', 'string'],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date'],
            'estado' => ['nullable', 'string', 'max:20'],
            'observaciones' => ['nullable', 'string'],
        ]);

        $tratamiento = Tratamiento::create($validated);

        return new TratamientoResource($tratamiento);
    }

    /**
     * Actualiza un registro existente.
     */
    public function update(Request $request, Tratamiento $tratamiento): TratamientoResource
    {
        $validated = $request->validate([
            'id_paciente' => ['sometimes', 'integer', 'exists:pacientes,id_paciente'],
            'descripcion' => ['nullable', 'string'],
            'indicaciones' => ['nullable', 'string'],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date'],
            'estado' => ['nullable', 'string', 'max:20'],
            'observaciones' => ['nullable', 'string'],
        ]);

        $tratamiento->update($validated);

        return new TratamientoResource($tratamiento);
    }

    /**
     * Elimina un registro.
     */
    public function destroy(Tratamiento $tratamiento): JsonResponse
    {
        $tratamiento->delete();

        return response()->json(['message' => 'Tratamiento eliminado']);
    }
}
