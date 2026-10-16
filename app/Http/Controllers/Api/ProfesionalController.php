<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProfesionalResource;
use App\Models\Profesional;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class ProfesionalController extends Controller
{
    /**
     * Lista de profesionales con sus relaciones principales.
     */
    public function index(): AnonymousResourceCollection
    {
        $profesional = Profesional::with('persona')
            ->orderByDesc('id_persona')
            ->paginate(15);

        return ProfesionalResource::collection($profesional);
    }

    /**
     * Detalle de un registro.
     */
    public function show(Profesional $profesional): ProfesionalResource
    {
        $profesional->load('persona', 'citas', 'horarios', 'bloqueosAgenda', 'participacionesAtencion', 'seguimientosInternacion');

        return new ProfesionalResource($profesional);
    }

    /**
     * Registra un nuevo registro.
     */
    public function store(Request $request): ProfesionalResource
    {
        $validated = $request->validate([
            'id_persona' => ['required', 'integer', 'exists:personas,id_persona'],
            'especialidad' => ['nullable', 'string', 'max:100'],
            'registro_profesional' => ['nullable', 'string', 'max:50', 'unique:profesionales,registro_profesional'],
            'estado' => ['nullable', 'string', 'max:20'],
        ]);

        $profesional = Profesional::create($validated);

        return new ProfesionalResource($profesional);
    }

    /**
     * Actualiza un registro existente.
     */
    public function update(Request $request, Profesional $profesional): ProfesionalResource
    {
        $validated = $request->validate([
            'id_persona' => ['sometimes', 'integer', 'exists:personas,id_persona'],
            'especialidad' => ['nullable', 'string', 'max:100'],
            'registro_profesional' => ['nullable', 'string', 'max:50', Rule::unique('profesionales', 'registro_profesional')->ignore($profesional->id_persona, 'id_persona')],
            'estado' => ['nullable', 'string', 'max:20'],
        ]);

        $profesional->update($validated);

        return new ProfesionalResource($profesional);
    }

    /**
     * Elimina un registro.
     */
    public function destroy(Profesional $profesional): JsonResponse
    {
        $profesional->delete();

        return response()->json(['message' => 'Profesional eliminado']);
    }
}
