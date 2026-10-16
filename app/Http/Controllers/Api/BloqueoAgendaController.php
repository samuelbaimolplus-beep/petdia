<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BloqueoAgendaResource;
use App\Models\BloqueoAgenda;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BloqueoAgendaController extends Controller
{
    /**
     * Lista de bloqueo_agendas con sus relaciones principales.
     */
    public function index(): AnonymousResourceCollection
    {
        $bloqueoAgenda = BloqueoAgenda::with('profesional')
            ->orderByDesc('id_bloqueo')
            ->paginate(15);

        return BloqueoAgendaResource::collection($bloqueoAgenda);
    }

    /**
     * Detalle de un registro.
     */
    public function show(BloqueoAgenda $bloqueoAgenda): BloqueoAgendaResource
    {
        $bloqueoAgenda->load('profesional');

        return new BloqueoAgendaResource($bloqueoAgenda);
    }

    /**
     * Registra un nuevo registro.
     */
    public function store(Request $request): BloqueoAgendaResource
    {
        $validated = $request->validate([
            'id_profesional' => ['required', 'integer', 'exists:profesionales,id_persona'],
            'fecha_hora_inicio' => ['nullable', 'date'],
            'fecha_hora_fin' => ['nullable', 'date'],
            'motivo' => ['nullable', 'string', 'max:255'],
        ]);

        $bloqueoAgenda = BloqueoAgenda::create($validated);

        return new BloqueoAgendaResource($bloqueoAgenda);
    }

    /**
     * Actualiza un registro existente.
     */
    public function update(Request $request, BloqueoAgenda $bloqueoAgenda): BloqueoAgendaResource
    {
        $validated = $request->validate([
            'id_profesional' => ['sometimes', 'integer', 'exists:profesionales,id_persona'],
            'fecha_hora_inicio' => ['nullable', 'date'],
            'fecha_hora_fin' => ['nullable', 'date'],
            'motivo' => ['nullable', 'string', 'max:255'],
        ]);

        $bloqueoAgenda->update($validated);

        return new BloqueoAgendaResource($bloqueoAgenda);
    }

    /**
     * Elimina un registro.
     */
    public function destroy(BloqueoAgenda $bloqueoAgenda): JsonResponse
    {
        $bloqueoAgenda->delete();

        return response()->json(['message' => 'Bloqueo agenda eliminado']);
    }
}
