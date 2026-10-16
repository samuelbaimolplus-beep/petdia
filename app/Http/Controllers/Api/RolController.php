<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RolResource;
use App\Models\Rol;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class RolController extends Controller
{
    /**
     * Lista de roles con sus relaciones principales.
     */
    public function index(): AnonymousResourceCollection
    {
        $rol = Rol::query()
            ->orderByDesc('id_rol')
            ->paginate(15);

        return RolResource::collection($rol);
    }

    /**
     * Detalle de un registro.
     */
    public function show(Rol $rol): RolResource
    {
        return new RolResource($rol);
    }

    /**
     * Registra un nuevo registro.
     */
    public function store(Request $request): RolResource
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:50', 'unique:roles,nombre'],
            'descripcion' => ['nullable', 'string', 'max:255'],
        ]);

        $rol = Rol::create($validated);

        return new RolResource($rol);
    }

    /**
     * Actualiza un registro existente.
     */
    public function update(Request $request, Rol $rol): RolResource
    {
        $validated = $request->validate([
            'nombre' => ['sometimes', 'string', 'max:50', Rule::unique('roles', 'nombre')->ignore($rol->id_rol, 'id_rol')],
            'descripcion' => ['nullable', 'string', 'max:255'],
        ]);

        $rol->update($validated);

        return new RolResource($rol);
    }

    /**
     * Elimina un registro.
     */
    public function destroy(Rol $rol): JsonResponse
    {
        $rol->delete();

        return response()->json(['message' => 'Rol eliminado']);
    }
}
