<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DiagnosticoResource;
use App\Models\Diagnostico;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DiagnosticoController extends Controller
{
    /**
     * Lista de diagnosticos con sus relaciones principales.
     */
    public function index(): AnonymousResourceCollection
    {
        $diagnostico = Diagnostico::with('atencionVeterinaria')
            ->orderByDesc('id_diagnostico')
            ->paginate(15);

        return DiagnosticoResource::collection($diagnostico);
    }

    /**
     * Detalle de un registro.
     */
    public function show(Diagnostico $diagnostico): DiagnosticoResource
    {
        $diagnostico->load('atencionVeterinaria');

        return new DiagnosticoResource($diagnostico);
    }

    /**
     * Registra un nuevo registro.
     */
    public function store(Request $request): DiagnosticoResource
    {
        $validated = $request->validate([
            'id_atencion' => ['required', 'integer', 'exists:atencion_veterinarias,id_atencion'],
            'nombre' => ['nullable', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string'],
            'tipo' => ['nullable', 'string', 'max:50'],
            'observaciones' => ['nullable', 'string'],
        ]);

        $diagnostico = Diagnostico::create($validated);

        return new DiagnosticoResource($diagnostico);
    }

    /**
     * Actualiza un registro existente.
     */
    public function update(Request $request, Diagnostico $diagnostico): DiagnosticoResource
    {
        $validated = $request->validate([
            'id_atencion' => ['sometimes', 'integer', 'exists:atencion_veterinarias,id_atencion'],
            'nombre' => ['nullable', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string'],
            'tipo' => ['nullable', 'string', 'max:50'],
            'observaciones' => ['nullable', 'string'],
        ]);

        $diagnostico->update($validated);

        return new DiagnosticoResource($diagnostico);
    }

    /**
     * Elimina un registro.
     */
    public function destroy(Diagnostico $diagnostico): JsonResponse
    {
        $diagnostico->delete();

        return response()->json(['message' => 'Diagnostico eliminado']);
    }
}
