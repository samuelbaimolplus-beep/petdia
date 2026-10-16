<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ParticipacionAtencionResource;
use App\Models\ParticipacionAtencion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ParticipacionAtencionController extends Controller
{
    /**
     * Lista de participacion_atencions con sus relaciones principales.
     */
    public function index(): AnonymousResourceCollection
    {
        $participacionAtencion = ParticipacionAtencion::with('atencion', 'profesional')
            ->orderByDesc('id_participacion')
            ->paginate(15);

        return ParticipacionAtencionResource::collection($participacionAtencion);
    }

    /**
     * Detalle de un registro.
     */
    public function show(ParticipacionAtencion $participacionAtencion): ParticipacionAtencionResource
    {
        $participacionAtencion->load('atencion', 'profesional');

        return new ParticipacionAtencionResource($participacionAtencion);
    }

    /**
     * Registra un nuevo registro.
     */
    public function store(Request $request): ParticipacionAtencionResource
    {
        $validated = $request->validate([
            'id_atencion' => ['required', 'integer', 'exists:atenciones,id_atencion'],
            'id_profesional' => ['required', 'integer', 'exists:profesionales,id_persona'],
            'rol_atencion' => ['nullable', 'string', 'max:50'],
            'observaciones' => ['nullable', 'string'],
        ]);

        $participacionAtencion = ParticipacionAtencion::create($validated);

        return new ParticipacionAtencionResource($participacionAtencion);
    }

    /**
     * Actualiza un registro existente.
     */
    public function update(Request $request, ParticipacionAtencion $participacionAtencion): ParticipacionAtencionResource
    {
        $validated = $request->validate([
            'id_atencion' => ['sometimes', 'integer', 'exists:atenciones,id_atencion'],
            'id_profesional' => ['sometimes', 'integer', 'exists:profesionales,id_persona'],
            'rol_atencion' => ['nullable', 'string', 'max:50'],
            'observaciones' => ['nullable', 'string'],
        ]);

        $participacionAtencion->update($validated);

        return new ParticipacionAtencionResource($participacionAtencion);
    }

    /**
     * Elimina un registro.
     */
    public function destroy(ParticipacionAtencion $participacionAtencion): JsonResponse
    {
        $participacionAtencion->delete();

        return response()->json(['message' => 'Participacion atencion eliminado']);
    }
}
