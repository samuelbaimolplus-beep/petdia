<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdministracionMedicamentoResource;
use App\Models\AdministracionMedicamento;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AdministracionMedicamentoController extends Controller
{
    /**
     * Lista de administracion_medicamentos con sus relaciones principales.
     */
    public function index(): AnonymousResourceCollection
    {
        $administracionMedicamento = AdministracionMedicamento::with('usoProductoAtencion')
            ->orderByDesc('id_uso')
            ->paginate(15);

        return AdministracionMedicamentoResource::collection($administracionMedicamento);
    }

    /**
     * Detalle de un registro.
     */
    public function show(AdministracionMedicamento $administracionMedicamento): AdministracionMedicamentoResource
    {
        $administracionMedicamento->load('usoProductoAtencion', 'vacunacion', 'desparasitacion');

        return new AdministracionMedicamentoResource($administracionMedicamento);
    }

    /**
     * Registra un nuevo registro.
     */
    public function store(Request $request): AdministracionMedicamentoResource
    {
        $validated = $request->validate([
            'id_uso' => ['required', 'integer', 'exists:uso_producto_atencions,id_uso'],
            'fecha_hora' => ['nullable', 'date'],
            'dosis' => ['nullable', 'numeric'],
            'unidad_dosis' => ['nullable', 'string', 'max:20'],
            'via_administracion' => ['nullable', 'string', 'max:50'],
            'observaciones_clinicas' => ['nullable', 'string'],
        ]);

        $administracionMedicamento = AdministracionMedicamento::create($validated);

        return new AdministracionMedicamentoResource($administracionMedicamento);
    }

    /**
     * Actualiza un registro existente.
     */
    public function update(Request $request, AdministracionMedicamento $administracionMedicamento): AdministracionMedicamentoResource
    {
        $validated = $request->validate([
            'id_uso' => ['sometimes', 'integer', 'exists:uso_producto_atencions,id_uso'],
            'fecha_hora' => ['nullable', 'date'],
            'dosis' => ['nullable', 'numeric'],
            'unidad_dosis' => ['nullable', 'string', 'max:20'],
            'via_administracion' => ['nullable', 'string', 'max:50'],
            'observaciones_clinicas' => ['nullable', 'string'],
        ]);

        $administracionMedicamento->update($validated);

        return new AdministracionMedicamentoResource($administracionMedicamento);
    }

    /**
     * Elimina un registro.
     */
    public function destroy(AdministracionMedicamento $administracionMedicamento): JsonResponse
    {
        $administracionMedicamento->delete();

        return response()->json(['message' => 'Administracion medicamento eliminado']);
    }
}
