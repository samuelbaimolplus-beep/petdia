<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EgresoCajaResource;
use App\Models\EgresoCaja;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class EgresoCajaController extends Controller
{
    /**
     * Lista de egresos de caja chica.
     */
    public function index(): AnonymousResourceCollection
    {
        $egresos = EgresoCaja::with('usuario')
            ->orderByDesc('id_egreso')
            ->paginate(15);

        return EgresoCajaResource::collection($egresos);
    }

    /**
     * Detalle de un registro.
     */
    public function show(EgresoCaja $egresoCaja): EgresoCajaResource
    {
        $egresoCaja->load('usuario', 'cierre');

        return new EgresoCajaResource($egresoCaja);
    }

    /**
     * Registra un nuevo egreso de caja.
     */
    public function store(Request $request): EgresoCajaResource
    {
        $validated = $request->validate([
            'id_usuario' => ['required', 'integer', 'exists:usuarios,id_usuario'],
            'id_cierre' => ['nullable', 'integer', 'exists:cierre_cajas,id_cierre'],
            'monto' => ['required', 'numeric', 'min:0.01'],
            'concepto' => ['required', 'string', 'max:255'],
            'categoria' => ['required', 'string', 'max:100'],
            'comprobante' => ['nullable', 'string', 'max:100'],
            'fecha_hora' => ['nullable', 'date'],
        ]);

        if (empty($validated['fecha_hora'])) {
            $validated['fecha_hora'] = now();
        }

        $egreso = EgresoCaja::create($validated);

        return new EgresoCajaResource($egreso);
    }

    /**
     * Actualiza un registro existente.
     */
    public function update(Request $request, EgresoCaja $egresoCaja): EgresoCajaResource
    {
        $validated = $request->validate([
            'id_usuario' => ['sometimes', 'integer', 'exists:usuarios,id_usuario'],
            'id_cierre' => ['nullable', 'integer', 'exists:cierre_cajas,id_cierre'],
            'monto' => ['sometimes', 'numeric', 'min:0.01'],
            'concepto' => ['sometimes', 'string', 'max:255'],
            'categoria' => ['sometimes', 'string', 'max:100'],
            'comprobante' => ['nullable', 'string', 'max:100'],
            'fecha_hora' => ['sometimes', 'date'],
        ]);

        $egresoCaja->update($validated);

        return new EgresoCajaResource($egresoCaja);
    }

    /**
     * Elimina un registro.
     */
    public function destroy(EgresoCaja $egresoCaja): JsonResponse
    {
        $egresoCaja->delete();

        return response()->json([
            'message' => 'Egreso de caja eliminado correctamente.',
        ]);
    }
}
