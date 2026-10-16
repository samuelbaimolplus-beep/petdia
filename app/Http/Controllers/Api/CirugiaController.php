<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CirugiaResource;
use App\Models\Cirugia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CirugiaController extends Controller
{
    /**
     * Lista de cirugias con sus relaciones principales.
     */
    public function index(): AnonymousResourceCollection
    {
        $cirugia = Cirugia::with('procedimiento')
            ->orderByDesc('id_procedimiento')
            ->paginate(15);

        return CirugiaResource::collection($cirugia);
    }

    /**
     * Detalle de un registro.
     */
    public function show(Cirugia $cirugia): CirugiaResource
    {
        $cirugia->load('procedimiento');

        return new CirugiaResource($cirugia);
    }

    /**
     * Registra un nuevo registro.
     */
    public function store(Request $request): CirugiaResource
    {
        $validated = $request->validate([
            'id_procedimiento' => ['required', 'integer', 'exists:procedimientos,id_procedimiento'],
            'tipo_cirugia' => ['nullable', 'string', 'max:50'],
            'diagnostico_preoperatorio' => ['nullable', 'string'],
            'complicaciones' => ['nullable', 'string'],
            'indicaciones_postoperatorias' => ['nullable', 'string'],
            'resultado' => ['nullable', 'string', 'max:50'],
        ]);

        $cirugia = Cirugia::create($validated);

        return new CirugiaResource($cirugia);
    }

    /**
     * Actualiza un registro existente.
     */
    public function update(Request $request, Cirugia $cirugia): CirugiaResource
    {
        $validated = $request->validate([
            'id_procedimiento' => ['sometimes', 'integer', 'exists:procedimientos,id_procedimiento'],
            'tipo_cirugia' => ['nullable', 'string', 'max:50'],
            'diagnostico_preoperatorio' => ['nullable', 'string'],
            'complicaciones' => ['nullable', 'string'],
            'indicaciones_postoperatorias' => ['nullable', 'string'],
            'resultado' => ['nullable', 'string', 'max:50'],
        ]);

        $cirugia->update($validated);

        return new CirugiaResource($cirugia);
    }

    /**
     * Elimina un registro.
     */
    public function destroy(Cirugia $cirugia): JsonResponse
    {
        $cirugia->delete();

        return response()->json(['message' => 'Cirugia eliminado']);
    }
}
