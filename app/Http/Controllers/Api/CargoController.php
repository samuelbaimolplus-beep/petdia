<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CargoResource;
use App\Models\Cargo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CargoController extends Controller
{
    /**
     * Lista de cargos con sus relaciones principales.
     */
    public function index(): AnonymousResourceCollection
    {
        $cargo = Cargo::with('cuenta', 'tratamiento')
            ->orderByDesc('id_cargo')
            ->paginate(15);

        return CargoResource::collection($cargo);
    }

    /**
     * Detalle de un registro.
     */
    public function show(Cargo $cargo): CargoResource
    {
        $cargo->load('cuenta', 'tratamiento', 'aplicacionesPago');

        return new CargoResource($cargo);
    }

    /**
     * Registra un nuevo registro.
     */
    public function store(Request $request): CargoResource
    {
        $validated = $request->validate([
            'id_cuenta' => ['required', 'integer', 'exists:cuentas,id_cuenta'],
            'id_tratamiento' => ['nullable', 'integer', 'exists:tratamientos,id_tratamiento'],
            'fecha_hora' => ['nullable', 'date'],
            'concepto' => ['nullable', 'string', 'max:255'],
            'monto' => ['nullable', 'numeric'],
            'descuento' => ['nullable', 'numeric'],
            'estado' => ['nullable', 'string', 'max:20'],
            'observaciones' => ['nullable', 'string'],
        ]);

        $cargo = Cargo::create($validated);

        return new CargoResource($cargo);
    }

    /**
     * Actualiza un registro existente.
     */
    public function update(Request $request, Cargo $cargo): CargoResource
    {
        $validated = $request->validate([
            'id_cuenta' => ['sometimes', 'integer', 'exists:cuentas,id_cuenta'],
            'id_tratamiento' => ['nullable', 'integer', 'exists:tratamientos,id_tratamiento'],
            'fecha_hora' => ['nullable', 'date'],
            'concepto' => ['nullable', 'string', 'max:255'],
            'monto' => ['nullable', 'numeric'],
            'descuento' => ['nullable', 'numeric'],
            'estado' => ['nullable', 'string', 'max:20'],
            'observaciones' => ['nullable', 'string'],
        ]);

        $cargo->update($validated);

        return new CargoResource($cargo);
    }

    /**
     * Elimina un registro.
     */
    public function destroy(Cargo $cargo): JsonResponse
    {
        $cargo->delete();

        return response()->json(['message' => 'Cargo eliminado']);
    }
}
