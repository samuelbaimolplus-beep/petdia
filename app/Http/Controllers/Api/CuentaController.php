<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CuentaResource;
use App\Models\Cuenta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class CuentaController extends Controller
{
    /**
     * Lista de cuentas con sus relaciones principales.
     */
    public function index(): AnonymousResourceCollection
    {
        $cuenta = Cuenta::with('propietario')
            ->orderByDesc('id_cuenta')
            ->paginate(15);

        return CuentaResource::collection($cuenta);
    }

    /**
     * Detalle de un registro.
     */
    public function show(Cuenta $cuenta): CuentaResource
    {
        $cuenta->load('propietario', 'cargos', 'pagos');

        return new CuentaResource($cuenta);
    }

    /**
     * Registra un nuevo registro.
     */
    public function store(Request $request): CuentaResource
    {
        $validated = $request->validate([
            'id_propietario' => ['required', 'integer', 'exists:propietarios,id_persona', 'unique:cuentas,id_propietario'],
            'fecha_apertura' => ['nullable', 'date'],
            'estado' => ['nullable', 'string', 'max:20'],
        ]);

        $cuenta = Cuenta::create($validated);

        return new CuentaResource($cuenta);
    }

    /**
     * Actualiza un registro existente.
     */
    public function update(Request $request, Cuenta $cuenta): CuentaResource
    {
        $validated = $request->validate([
            'id_propietario' => ['sometimes', 'integer', 'exists:propietarios,id_persona', Rule::unique('cuentas', 'id_propietario')->ignore($cuenta->id_cuenta, 'id_cuenta')],
            'fecha_apertura' => ['nullable', 'date'],
            'estado' => ['nullable', 'string', 'max:20'],
        ]);

        $cuenta->update($validated);

        return new CuentaResource($cuenta);
    }

    /**
     * Elimina un registro.
     */
    public function destroy(Cuenta $cuenta): JsonResponse
    {
        $cuenta->delete();

        return response()->json(['message' => 'Cuenta eliminado']);
    }
}
