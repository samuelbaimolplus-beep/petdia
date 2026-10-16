<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PersonaResource;
use App\Models\Persona;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class PersonaController extends Controller
{
    /**
     * Lista de personas con sus relaciones principales.
     */
    public function index(): AnonymousResourceCollection
    {
        $persona = Persona::query()
            ->orderByDesc('id_persona')
            ->paginate(15);

        return PersonaResource::collection($persona);
    }

    /**
     * Detalle de un registro.
     */
    public function show(Persona $persona): PersonaResource
    {
        $persona->load('propietario', 'profesional', 'usuarios');

        return new PersonaResource($persona);
    }

    /**
     * Registra un nuevo registro.
     */
    public function store(Request $request): PersonaResource
    {
        $validated = $request->validate([
            'documento' => ['required', 'string', 'max:50', 'unique:personas,documento'],
            'nombre' => ['required', 'string', 'max:100'],
            'apellido1' => ['required', 'string', 'max:100'],
            'apellido2' => ['nullable', 'string', 'max:100'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'string', 'max:100'],
            'direccion' => ['nullable', 'string', 'max:255'],
        ]);

        $persona = Persona::create($validated);

        return new PersonaResource($persona);
    }

    /**
     * Actualiza un registro existente.
     */
    public function update(Request $request, Persona $persona): PersonaResource
    {
        $validated = $request->validate([
            'documento' => ['sometimes', 'string', 'max:50', Rule::unique('personas', 'documento')->ignore($persona->id_persona, 'id_persona')],
            'nombre' => ['sometimes', 'string', 'max:100'],
            'apellido1' => ['sometimes', 'string', 'max:100'],
            'apellido2' => ['nullable', 'string', 'max:100'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'string', 'max:100'],
            'direccion' => ['nullable', 'string', 'max:255'],
        ]);

        $persona->update($validated);

        return new PersonaResource($persona);
    }

    /**
     * Elimina un registro.
     */
    public function destroy(Persona $persona): JsonResponse
    {
        $persona->delete();

        return response()->json(['message' => 'Persona eliminado']);
    }
}
