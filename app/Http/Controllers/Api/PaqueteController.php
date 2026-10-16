<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaqueteResource;
use App\Models\Paquete;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PaqueteController extends Controller
{
    /**
     * Lista de paquetes con sus relaciones principales.
     */
    public function index(): AnonymousResourceCollection
    {
        $paquete = Paquete::query()
            ->orderByDesc('id_paquete')
            ->paginate(15);

        return PaqueteResource::collection($paquete);
    }

    /**
     * Detalle de un registro.
     */
    public function show(Paquete $paquete): PaqueteResource
    {
        $paquete->load('detallePaquetes', 'suscripciones');

        return new PaqueteResource($paquete);
    }

    /**
     * Registra un nuevo registro.
     */
    public function store(Request $request): PaqueteResource
    {
        $validated = $request->validate([
            'nombre' => ['nullable', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string'],
            'precio' => ['nullable', 'numeric'],
            'estado' => ['nullable', 'string', 'max:20'],
        ]);

        $paquete = Paquete::create($validated);

        return new PaqueteResource($paquete);
    }

    /**
     * Actualiza un registro existente.
     */
    public function update(Request $request, Paquete $paquete): PaqueteResource
    {
        $validated = $request->validate([
            'nombre' => ['nullable', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string'],
            'precio' => ['nullable', 'numeric'],
            'estado' => ['nullable', 'string', 'max:20'],
        ]);

        $paquete->update($validated);

        return new PaqueteResource($paquete);
    }

    /**
     * Elimina un registro.
     */
    public function destroy(Paquete $paquete): JsonResponse
    {
        $paquete->delete();

        return response()->json(['message' => 'Paquete eliminado']);
    }
}
