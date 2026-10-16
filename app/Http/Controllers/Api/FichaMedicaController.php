<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FichaMedicaResource;
use App\Models\FichaMedica;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class FichaMedicaController extends Controller
{
    /**
     * Lista de ficha_medicas con sus relaciones principales.
     */
    public function index(): AnonymousResourceCollection
    {
        $fichaMedica = FichaMedica::with('paciente')
            ->orderByDesc('id_ficha')
            ->paginate(15);

        return FichaMedicaResource::collection($fichaMedica);
    }

    /**
     * Detalle de un registro.
     */
    public function show(FichaMedica $fichaMedica): FichaMedicaResource
    {
        $fichaMedica->load('paciente');

        return new FichaMedicaResource($fichaMedica);
    }

    /**
     * Registra un nuevo registro.
     */
    public function store(Request $request): FichaMedicaResource
    {
        $validated = $request->validate([
            'id_paciente' => ['required', 'integer', 'exists:pacientes,id_paciente', 'unique:ficha_medicas,id_paciente'],
            'fecha_apertura' => ['nullable', 'date'],
            'antecedentes' => ['nullable', 'string'],
            'alergias' => ['nullable', 'string'],
            'enfermedades_previas' => ['nullable', 'string'],
            'cirugias_previas' => ['nullable', 'string'],
            'medicamentos_habituales' => ['nullable', 'string'],
            'observaciones' => ['nullable', 'string'],
        ]);

        $fichaMedica = FichaMedica::create($validated);

        return new FichaMedicaResource($fichaMedica);
    }

    /**
     * Actualiza un registro existente.
     */
    public function update(Request $request, FichaMedica $fichaMedica): FichaMedicaResource
    {
        $validated = $request->validate([
            'id_paciente' => ['sometimes', 'integer', 'exists:pacientes,id_paciente', Rule::unique('ficha_medicas', 'id_paciente')->ignore($fichaMedica->id_ficha, 'id_ficha')],
            'fecha_apertura' => ['nullable', 'date'],
            'antecedentes' => ['nullable', 'string'],
            'alergias' => ['nullable', 'string'],
            'enfermedades_previas' => ['nullable', 'string'],
            'cirugias_previas' => ['nullable', 'string'],
            'medicamentos_habituales' => ['nullable', 'string'],
            'observaciones' => ['nullable', 'string'],
        ]);

        $fichaMedica->update($validated);

        return new FichaMedicaResource($fichaMedica);
    }

    /**
     * Elimina un registro.
     */
    public function destroy(FichaMedica $fichaMedica): JsonResponse
    {
        $fichaMedica->delete();

        return response()->json(['message' => 'Ficha medica eliminado']);
    }
}
