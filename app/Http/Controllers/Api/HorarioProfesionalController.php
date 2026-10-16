<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\HorarioProfesionalResource;
use App\Models\HorarioProfesional;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class HorarioProfesionalController extends Controller
{
    /**
     * Lista de horario_profesionales con sus relaciones principales.
     */
    public function index(): AnonymousResourceCollection
    {
        $horarioProfesional = HorarioProfesional::with('profesional')
            ->orderByDesc('id_horario')
            ->paginate(15);

        return HorarioProfesionalResource::collection($horarioProfesional);
    }

    /**
     * Detalle de un registro.
     */
    public function show(HorarioProfesional $horarioProfesional): HorarioProfesionalResource
    {
        $horarioProfesional->load('profesional');

        return new HorarioProfesionalResource($horarioProfesional);
    }

    /**
     * Registra un nuevo registro.
     */
    public function store(Request $request): HorarioProfesionalResource
    {
        $validated = $request->validate([
            'id_profesional' => ['required', 'integer', 'exists:profesionales,id_persona'],
            'dia_semana' => ['nullable', 'integer'],
            'hora_inicio' => ['nullable', 'date_format:H:i:s'],
            'hora_fin' => ['nullable', 'date_format:H:i:s'],
            'estado' => ['nullable', 'string', 'max:20'],
        ]);

        $horarioProfesional = HorarioProfesional::create($validated);

        return new HorarioProfesionalResource($horarioProfesional);
    }

    /**
     * Actualiza un registro existente.
     */
    public function update(Request $request, HorarioProfesional $horarioProfesional): HorarioProfesionalResource
    {
        $validated = $request->validate([
            'id_profesional' => ['sometimes', 'integer', 'exists:profesionales,id_persona'],
            'dia_semana' => ['nullable', 'integer'],
            'hora_inicio' => ['nullable', 'date_format:H:i:s'],
            'hora_fin' => ['nullable', 'date_format:H:i:s'],
            'estado' => ['nullable', 'string', 'max:20'],
        ]);

        $horarioProfesional->update($validated);

        return new HorarioProfesionalResource($horarioProfesional);
    }

    /**
     * Elimina un registro.
     */
    public function destroy(HorarioProfesional $horarioProfesional): JsonResponse
    {
        $horarioProfesional->delete();

        return response()->json(['message' => 'Horario profesional eliminado']);
    }
}
