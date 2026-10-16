<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CierreCajaResource;
use App\Models\CierreCaja;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CierreCajaController extends Controller
{
    /**
     * Lista de cierre_cajas con sus relaciones principales.
     */
    public function index(): AnonymousResourceCollection
    {
        $cierreCaja = CierreCaja::with('usuario')
            ->orderByDesc('id_cierre')
            ->paginate(15);

        return CierreCajaResource::collection($cierreCaja);
    }

    /**
     * Detalle de un registro.
     */
    public function show(CierreCaja $cierreCaja): CierreCajaResource
    {
        $cierreCaja->load('usuario', 'pagos');

        return new CierreCajaResource($cierreCaja);
    }

    /**
     * Registra un nuevo registro.
     */
    public function store(Request $request): CierreCajaResource
    {
        $validated = $request->validate([
            'id_usuario' => ['required', 'integer', 'exists:usuarios,id_usuario'],
            'fecha_hora_apertura' => ['nullable', 'date'],
            'fecha_hora_cierre' => ['nullable', 'date'],
            'total_registrado' => ['nullable', 'numeric'],
            'observaciones' => ['nullable', 'string'],
        ]);

        $cierreCaja = CierreCaja::create($validated);

        return new CierreCajaResource($cierreCaja);
    }

    /**
     * Actualiza un registro existente.
     */
    public function update(Request $request, CierreCaja $cierreCaja): CierreCajaResource
    {
        $validated = $request->validate([
            'id_usuario' => ['sometimes', 'integer', 'exists:usuarios,id_usuario'],
            'fecha_hora_apertura' => ['nullable', 'date'],
            'fecha_hora_cierre' => ['nullable', 'date'],
            'total_registrado' => ['nullable', 'numeric'],
            'observaciones' => ['nullable', 'string'],
        ]);

        $cierreCaja->update($validated);

        return new CierreCajaResource($cierreCaja);
    }

    /**
     * Elimina un registro.
     */
    public function destroy(CierreCaja $cierreCaja): JsonResponse
    {
        $cierreCaja->delete();

        return response()->json(['message' => 'Cierre caja eliminado']);
    }
}
