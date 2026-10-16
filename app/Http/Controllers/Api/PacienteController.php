<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PacienteResource;
use App\Models\Paciente;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PacienteController extends Controller
{
    /**
     * Lista de pacientes con raza y propietario.
     */
    public function index(): AnonymousResourceCollection
    {
        $pacientes = Paciente::with('raza', 'propietario.persona')
            ->orderBy('nombre')
            ->paginate(15);

        return PacienteResource::collection($pacientes);
    }

    /**
     * Detalle de un paciente con su historial completo.
     */
    public function show(Paciente $paciente): PacienteResource
    {
        $paciente->load([
            'raza.especie',
            'propietario.persona',
            'fichaMedica',
            'citas.detalleCitas.servicio',
            'atenciones.atencionVeterinaria.diagnosticos',
            'atenciones.atencionEstetica',
        ]);

        return new PacienteResource($paciente);
    }

    /**
     * Registra un nuevo paciente.
     */
    public function store(Request $request): PacienteResource
    {
        $validated = $request->validate([
            'id_raza' => ['required', 'integer', 'exists:razas,id_raza'],
            'id_propietario' => ['required', 'integer', 'exists:propietarios,id_persona'],
            'nombre' => ['required', 'string', 'max:100'],
            'sexo' => ['required', 'in:MACHO,HEMBRA'],
            'fecha_nacimiento' => ['nullable', 'date'],
            'tamanio' => ['nullable', 'in:PEQUEÑO,MEDIANO,GRANDE'],
            'condicion_reproductiva' => ['nullable', 'in:INTACTO,CASTRADO'],
            'color' => ['nullable', 'string', 'max:50'],
            'microchip' => ['nullable', 'string', 'max:50', 'unique:pacientes,microchip'],
            'estado' => ['nullable', 'in:ACTIVO,INACTIVO'],
        ]);

        $paciente = Paciente::create($validated);
        $paciente->load('raza', 'propietario.persona');

        return new PacienteResource($paciente);
    }

    /**
     * Actualiza un paciente existente.
     */
    public function update(Request $request, Paciente $paciente): PacienteResource
    {
        $validated = $request->validate([
            'id_raza' => ['sometimes', 'integer', 'exists:razas,id_raza'],
            'id_propietario' => ['sometimes', 'integer', 'exists:propietarios,id_persona'],
            'nombre' => ['sometimes', 'string', 'max:100'],
            'sexo' => ['sometimes', 'in:MACHO,HEMBRA'],
            'fecha_nacimiento' => ['nullable', 'date'],
            'tamanio' => ['nullable', 'in:PEQUEÑO,MEDIANO,GRANDE'],
            'condicion_reproductiva' => ['nullable', 'in:INTACTO,CASTRADO'],
            'color' => ['nullable', 'string', 'max:50'],
            'microchip' => ['nullable', 'string', 'max:50', 'unique:pacientes,microchip,'.$paciente->id_paciente.',id_paciente'],
            'estado' => ['nullable', 'in:ACTIVO,INACTIVO'],
        ]);

        $paciente->update($validated);
        $paciente->load('raza', 'propietario.persona');

        return new PacienteResource($paciente);
    }

    /**
     * Elimina un paciente.
     */
    public function destroy(Paciente $paciente): JsonResponse
    {
        $paciente->delete();

        return response()->json(['message' => 'Paciente eliminado']);
    }
}
