<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServicioResource;
use App\Models\Servicio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ServicioController extends Controller
{
    /**
     * Lista de servicios con sus tiempos estimados por especie.
     */
    public function index(): AnonymousResourceCollection
    {
        $servicios = Servicio::with('tiemposEstimados.especie')
            ->orderBy('nombre')
            ->paginate(15);

        return ServicioResource::collection($servicios);
    }

    /**
     * Detalle de un servicio.
     */
    public function show(Servicio $servicio): ServicioResource
    {
        $servicio->load('tiemposEstimados.especie', 'profesionales.persona');

        return new ServicioResource($servicio);
    }

    /**
     * Registra un nuevo servicio.
     */
    public function store(Request $request): ServicioResource
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:150', 'unique:servicios,nombre'],
            'categoria' => ['required', 'in:ESTETICA,VETERINARIA'],
            'descripcion' => ['nullable', 'string'],
            'observaciones' => ['nullable', 'string'],
            'precio_base' => ['required', 'numeric', 'min:0'],
            'disponible_domicilio' => ['nullable', 'boolean'],
            'estado' => ['nullable', 'in:ACTIVO,INACTIVO'],
        ]);

        $servicio = Servicio::create($validated);

        return new ServicioResource($servicio);
    }

    /**
     * Actualiza un servicio existente.
     */
    public function update(Request $request, Servicio $servicio): ServicioResource
    {
        $validated = $request->validate([
            'nombre' => ['sometimes', 'string', 'max:150', 'unique:servicios,nombre,'.$servicio->id_servicio.',id_servicio'],
            'categoria' => ['sometimes', 'in:ESTETICA,VETERINARIA'],
            'descripcion' => ['nullable', 'string'],
            'observaciones' => ['nullable', 'string'],
            'precio_base' => ['sometimes', 'numeric', 'min:0'],
            'disponible_domicilio' => ['nullable', 'boolean'],
            'estado' => ['sometimes', 'in:ACTIVO,INACTIVO'],
        ]);

        $servicio->update($validated);

        return new ServicioResource($servicio);
    }

    /**
     * Elimina un servicio.
     */
    public function destroy(Servicio $servicio): JsonResponse
    {
        $servicio->delete();

        return response()->json(['message' => 'Servicio eliminado']);
    }
}
