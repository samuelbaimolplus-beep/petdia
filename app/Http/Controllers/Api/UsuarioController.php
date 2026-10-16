<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UsuarioResource;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class UsuarioController extends Controller
{
    /**
     * Lista de usuarios con sus relaciones principales.
     */
    public function index(): AnonymousResourceCollection
    {
        $usuario = Usuario::with('persona')
            ->orderByDesc('id_usuario')
            ->paginate(15);

        return UsuarioResource::collection($usuario);
    }

    /**
     * Detalle de un registro.
     */
    public function show(Usuario $usuario): UsuarioResource
    {
        $usuario->load('persona', 'cierresCaja', 'ventas', 'movimientoInventarios', 'devolucionesPago');

        return new UsuarioResource($usuario);
    }

    /**
     * Registra un nuevo registro.
     */
    public function store(Request $request): UsuarioResource
    {
        $validated = $request->validate([
            'id_persona' => ['required', 'integer', 'exists:personas,id_persona', 'unique:usuarios,id_persona'],
            'nombre_usuario' => ['required', 'string', 'max:50', 'unique:usuarios,nombre_usuario'],
            'password_hash' => ['required', 'string', 'max:255'],
            'estado' => ['nullable', 'string', 'max:20'],
            'ultimo_acceso' => ['nullable', 'date'],
        ]);

        $usuario = Usuario::create($validated);

        return new UsuarioResource($usuario);
    }

    /**
     * Actualiza un registro existente.
     */
    public function update(Request $request, Usuario $usuario): UsuarioResource
    {
        $validated = $request->validate([
            'id_persona' => ['sometimes', 'integer', 'exists:personas,id_persona', Rule::unique('usuarios', 'id_persona')->ignore($usuario->id_usuario, 'id_usuario')],
            'nombre_usuario' => ['sometimes', 'string', 'max:50', Rule::unique('usuarios', 'nombre_usuario')->ignore($usuario->id_usuario, 'id_usuario')],
            'password_hash' => ['sometimes', 'string', 'max:255'],
            'estado' => ['nullable', 'string', 'max:20'],
            'ultimo_acceso' => ['nullable', 'date'],
        ]);

        $usuario->update($validated);

        return new UsuarioResource($usuario);
    }

    /**
     * Elimina un registro.
     */
    public function destroy(Usuario $usuario): JsonResponse
    {
        $usuario->delete();

        return response()->json(['message' => 'Usuario eliminado']);
    }
}
