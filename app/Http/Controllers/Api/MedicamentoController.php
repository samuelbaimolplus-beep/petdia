<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MedicamentoResource;
use App\Models\Medicamento;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MedicamentoController extends Controller
{
    /**
     * Lista de medicamentos con sus relaciones principales.
     */
    public function index(): AnonymousResourceCollection
    {
        $medicamento = Medicamento::with('producto')
            ->orderByDesc('id_producto')
            ->paginate(15);

        return MedicamentoResource::collection($medicamento);
    }

    /**
     * Detalle de un registro.
     */
    public function show(Medicamento $medicamento): MedicamentoResource
    {
        $medicamento->load('producto', 'detalleRecetas');

        return new MedicamentoResource($medicamento);
    }

    /**
     * Registra un nuevo registro.
     */
    public function store(Request $request): MedicamentoResource
    {
        $validated = $request->validate([
            'id_producto' => ['required', 'integer', 'exists:productos,id_producto'],
            'principio_activo' => ['nullable', 'string', 'max:100'],
            'presentacion' => ['nullable', 'string', 'max:50'],
            'concentracion' => ['nullable', 'numeric'],
            'unidad_concentracion' => ['nullable', 'string', 'max:20'],
        ]);

        $medicamento = Medicamento::create($validated);

        return new MedicamentoResource($medicamento);
    }

    /**
     * Actualiza un registro existente.
     */
    public function update(Request $request, Medicamento $medicamento): MedicamentoResource
    {
        $validated = $request->validate([
            'id_producto' => ['sometimes', 'integer', 'exists:productos,id_producto'],
            'principio_activo' => ['nullable', 'string', 'max:100'],
            'presentacion' => ['nullable', 'string', 'max:50'],
            'concentracion' => ['nullable', 'numeric'],
            'unidad_concentracion' => ['nullable', 'string', 'max:20'],
        ]);

        $medicamento->update($validated);

        return new MedicamentoResource($medicamento);
    }

    /**
     * Elimina un registro.
     */
    public function destroy(Medicamento $medicamento): JsonResponse
    {
        $medicamento->delete();

        return response()->json(['message' => 'Medicamento eliminado']);
    }
}
