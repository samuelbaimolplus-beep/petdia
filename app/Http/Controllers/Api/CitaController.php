<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CitaResource;
use App\Models\Cita;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CitaController extends Controller
{
    /**
     * Lista de citas con paciente, profesional y servicios.
     */
    public function index(): AnonymousResourceCollection
    {
        $citas = Cita::with('paciente', 'profesional.persona', 'detalleCitas.servicio')
            ->orderBy('fecha_hora_inicio', 'desc')
            ->paginate(15);

        return CitaResource::collection($citas);
    }

    /**
     * Detalle de una cita con sus atenciones asociadas.
     */
    public function show(Cita $cita): CitaResource
    {
        $cita->load([
            'paciente',
            'profesional.persona',
            'detalleCitas.servicio',
            'servicioDomicilio',
            'atenciones.atencionVeterinaria',
            'atenciones.atencionEstetica',
        ]);

        return new CitaResource($cita);
    }

    /**
     * Programa una nueva cita.
     */
    public function store(Request $request): CitaResource
    {
        $validated = $request->validate([
            'id_paciente' => ['required', 'integer', 'exists:pacientes,id_paciente'],
            'id_profesional' => ['required', 'integer', 'exists:profesionales,id_persona'],
            'fecha_hora_inicio' => ['required', 'date'],
            'fecha_hora_fin' => ['required', 'date', 'after:fecha_hora_inicio'],
            'estado' => ['nullable', 'in:PROGRAMADA,CONFIRMADA,COMPLETADA,CANCELADA'],
            'origen' => ['nullable', 'in:RECEPCION,WEB'],
        ]);

        $cita = Cita::create($validated);
        $cita->load('paciente', 'profesional.persona');

        return new CitaResource($cita);
    }

    /**
     * Actualiza una cita existente.
     */
    public function update(Request $request, Cita $cita): CitaResource
    {
        $validated = $request->validate([
            'id_paciente' => ['sometimes', 'integer', 'exists:pacientes,id_paciente'],
            'id_profesional' => ['sometimes', 'integer', 'exists:profesionales,id_persona'],
            'fecha_hora_inicio' => ['sometimes', 'date'],
            'fecha_hora_fin' => ['sometimes', 'date', 'after:fecha_hora_inicio'],
            'estado' => ['sometimes', 'in:PROGRAMADA,CONFIRMADA,COMPLETADA,CANCELADA'],
            'origen' => ['sometimes', 'in:RECEPCION,WEB'],
        ]);

        $cita->update($validated);
        $cita->load('paciente', 'profesional.persona');

        return new CitaResource($cita);
    }

    /**
     * Elimina una cita.
     */
    public function destroy(Cita $cita): JsonResponse
    {
        $cita->delete();

        return response()->json(['message' => 'Cita eliminada']);
    }
}
