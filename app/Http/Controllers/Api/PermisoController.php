<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PermisoResource;
use App\Models\Permiso;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class PermisoController extends Controller
{
    /**
     * Lista de permisos con sus relaciones principales.
     */
    public function index(): AnonymousResourceCollection
    {
        $permiso = Permiso::query()
            ->orderByDesc('id_permiso')
            ->paginate(15);

        return PermisoResource::collection($permiso);
    }

    /**
     * Detalle de un registro.
     */
    public function show(Permiso $permiso): PermisoResource
    {
        return new PermisoResource($permiso);
    }

    /**
     * Registra un nuevo registro.
     */
    public function store(Request $request): PermisoResource
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:50', 'unique:permisos,nombre'],
            'descripcion' => ['nullable', 'string', 'max:255'],
        ]);

        $permiso = Permiso::create($validated);

        return new PermisoResource($permiso);
    }

    /**
     * Actualiza un registro existente.
     */
    public function update(Request $request, Permiso $permiso): PermisoResource
    {
        $validated = $request->validate([
            'nombre' => ['sometimes', 'string', 'max:50', Rule::unique('permisos', 'nombre')->ignore($permiso->id_permiso, 'id_permiso')],
            'descripcion' => ['nullable', 'string', 'max:255'],
        ]);

        $permiso->update($validated);

        return new PermisoResource($permiso);
    }

    /**
     * Elimina un registro.
     */
    public function destroy(Permiso $permiso): JsonResponse
    {
        $permiso->delete();

        return response()->json(['message' => 'Permiso eliminado']);
    }
}
