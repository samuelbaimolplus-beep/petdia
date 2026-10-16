<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProcedimientoResource;
use App\Models\Procedimiento;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProcedimientoController extends Controller
{
    /**
     * Lista de procedimientos con sus relaciones principales.
     */
    public function index(): AnonymousResourceCollection
    {
        $procedimiento = Procedimiento::with('atencionVeterinaria')
            ->orderByDesc('id_procedimiento')
            ->paginate(15);

        return ProcedimientoResource::collection($procedimiento);
    }

    /**
     * Detalle de un registro.
     */
    public function show(Procedimiento $procedimiento): ProcedimientoResource
    {
        $procedimiento->load('atencionVeterinaria', 'cirugia', 'vacunacion', 'desparasitacion', 'estudioLaboratorio');

        return new ProcedimientoResource($procedimiento);
    }

    /**
     * Registra un nuevo registro.
     */
    public function store(Request $request): ProcedimientoResource
    {
        $validated = $request->validate([
            'id_atencion' => ['required', 'integer', 'exists:atencion_veterinarias,id_atencion'],
            'fecha_hora' => ['nullable', 'date'],
            'descripcion' => ['nullable', 'string'],
            'observaciones' => ['nullable', 'string'],
        ]);

        $procedimiento = Procedimiento::create($validated);

        return new ProcedimientoResource($procedimiento);
    }

    /**
     * Actualiza un registro existente.
     */
    public function update(Request $request, Procedimiento $procedimiento): ProcedimientoResource
    {
        $validated = $request->validate([
            'id_atencion' => ['sometimes', 'integer', 'exists:atencion_veterinarias,id_atencion'],
            'fecha_hora' => ['nullable', 'date'],
            'descripcion' => ['nullable', 'string'],
            'observaciones' => ['nullable', 'string'],
        ]);

        $procedimiento->update($validated);

        return new ProcedimientoResource($procedimiento);
    }

    /**
     * Elimina un registro.
     */
    public function destroy(Procedimiento $procedimiento): JsonResponse
    {
        $procedimiento->delete();

        return response()->json(['message' => 'Procedimiento eliminado']);
    }
}
