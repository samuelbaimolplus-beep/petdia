<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RegistroSignosVitalResource;
use App\Models\RegistroSignosVital;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RegistroSignosVitalController extends Controller
{
    /**
     * Lista de registro_signos_vitales con sus relaciones principales.
     */
    public function index(): AnonymousResourceCollection
    {
        $registroSignosVital = RegistroSignosVital::with('atencionVeterinaria')
            ->orderByDesc('id_registro_signos')
            ->paginate(15);

        return RegistroSignosVitalResource::collection($registroSignosVital);
    }

    /**
     * Detalle de un registro.
     */
    public function show(RegistroSignosVital $registroSignosVital): RegistroSignosVitalResource
    {
        $registroSignosVital->load('atencionVeterinaria');

        return new RegistroSignosVitalResource($registroSignosVital);
    }

    /**
     * Registra un nuevo registro.
     */
    public function store(Request $request): RegistroSignosVitalResource
    {
        $validated = $request->validate([
            'id_atencion' => ['required', 'integer', 'exists:atencion_veterinarias,id_atencion'],
            'fecha_hora' => ['nullable', 'date'],
            'peso' => ['nullable', 'numeric'],
            'temperatura' => ['nullable', 'numeric'],
            'frecuencia_cardiaca' => ['nullable', 'integer'],
            'frecuencia_respiratoria' => ['nullable', 'integer'],
        ]);

        $registroSignosVital = RegistroSignosVital::create($validated);

        return new RegistroSignosVitalResource($registroSignosVital);
    }

    /**
     * Actualiza un registro existente.
     */
    public function update(Request $request, RegistroSignosVital $registroSignosVital): RegistroSignosVitalResource
    {
        $validated = $request->validate([
            'id_atencion' => ['sometimes', 'integer', 'exists:atencion_veterinarias,id_atencion'],
            'fecha_hora' => ['nullable', 'date'],
            'peso' => ['nullable', 'numeric'],
            'temperatura' => ['nullable', 'numeric'],
            'frecuencia_cardiaca' => ['nullable', 'integer'],
            'frecuencia_respiratoria' => ['nullable', 'integer'],
        ]);

        $registroSignosVital->update($validated);

        return new RegistroSignosVitalResource($registroSignosVital);
    }

    /**
     * Elimina un registro.
     */
    public function destroy(RegistroSignosVital $registroSignosVital): JsonResponse
    {
        $registroSignosVital->delete();

        return response()->json(['message' => 'Registro signos vital eliminado']);
    }
}
