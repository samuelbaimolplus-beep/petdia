<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AtencionVeterinariaResource;
use App\Models\AtencionVeterinaria;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AtencionVeterinariaController extends Controller
{
    /**
     * Lista de atencion_veterinarias con sus relaciones principales.
     */
    public function index(): AnonymousResourceCollection
    {
        $atencionVeterinaria = AtencionVeterinaria::with('atencion')
            ->orderByDesc('id_atencion')
            ->paginate(15);

        return AtencionVeterinariaResource::collection($atencionVeterinaria);
    }

    /**
     * Detalle de un registro.
     */
    public function show(AtencionVeterinaria $atencionVeterinaria): AtencionVeterinariaResource
    {
        $atencionVeterinaria->load('atencion', 'registrosSignosVitales', 'diagnosticos', 'procedimientos', 'internacion', 'recetas');

        return new AtencionVeterinariaResource($atencionVeterinaria);
    }

    /**
     * Registra un nuevo registro.
     */
    public function store(Request $request): AtencionVeterinariaResource
    {
        $validated = $request->validate([
            'id_atencion' => ['required', 'integer', 'exists:atenciones,id_atencion'],
            'motivo_consulta' => ['nullable', 'string'],
            'tipo' => ['nullable', 'string', 'max:50'],
            'anamnesis' => ['nullable', 'string'],
            'sintomas' => ['nullable', 'string'],
            'examen_fisico' => ['nullable', 'string'],
            'conclusion' => ['nullable', 'string'],
            'fecha_reconsulta' => ['nullable', 'date'],
            'observaciones_clinicas' => ['nullable', 'string'],
        ]);

        $atencionVeterinaria = AtencionVeterinaria::create($validated);

        return new AtencionVeterinariaResource($atencionVeterinaria);
    }

    /**
     * Actualiza un registro existente.
     */
    public function update(Request $request, AtencionVeterinaria $atencionVeterinaria): AtencionVeterinariaResource
    {
        $validated = $request->validate([
            'id_atencion' => ['sometimes', 'integer', 'exists:atenciones,id_atencion'],
            'motivo_consulta' => ['nullable', 'string'],
            'tipo' => ['nullable', 'string', 'max:50'],
            'anamnesis' => ['nullable', 'string'],
            'sintomas' => ['nullable', 'string'],
            'examen_fisico' => ['nullable', 'string'],
            'conclusion' => ['nullable', 'string'],
            'fecha_reconsulta' => ['nullable', 'date'],
            'observaciones_clinicas' => ['nullable', 'string'],
        ]);

        $atencionVeterinaria->update($validated);

        return new AtencionVeterinariaResource($atencionVeterinaria);
    }

    /**
     * Elimina un registro.
     */
    public function destroy(AtencionVeterinaria $atencionVeterinaria): JsonResponse
    {
        $atencionVeterinaria->delete();

        return response()->json(['message' => 'Atencion veterinaria eliminado']);
    }
}
