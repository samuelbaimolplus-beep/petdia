<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PacienteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_paciente,
            'nombre' => $this->nombre,
            'sexo' => $this->sexo,
            'fecha_nacimiento' => $this->fecha_nacimiento,
            'tamanio' => $this->tamanio,
            'condicion_reproductiva' => $this->condicion_reproductiva,
            'estado' => $this->estado,
            'raza' => $this->whenLoaded('raza', fn () => $this->raza?->nombre),
            'especie' => $this->whenLoaded('raza', fn () => $this->raza?->especie?->nombre),
            'propietario' => $this->whenLoaded('propietario', fn () => [
                'id' => $this->propietario?->id_persona,
                'nombre' => $this->propietario?->persona?->nombre,
                'telefono' => $this->propietario?->persona?->telefono,
                'email' => $this->propietario?->persona?->email,
            ]),
            'ficha_medica' => $this->whenLoaded('fichaMedica', fn () => [
                'fecha_apertura' => $this->fichaMedica?->fecha_apertura,
                'alergias' => $this->fichaMedica?->alergias,
                'enfermedades_previas' => $this->fichaMedica?->enfermedades_previas,
            ]),
            'citas' => CitaResource::collection($this->whenLoaded('citas')),
            'atenciones' => $this->whenLoaded('atenciones', fn () => $this->atenciones->map(fn ($atencion) => [
                'id' => $atencion->id_atencion,
                'fecha_hora_inicio' => $atencion->fecha_hora_inicio,
                'estado' => $atencion->estado,
                'diagnosticos' => $atencion->atencionVeterinaria?->diagnosticos?->pluck('nombre'),
            ])),
        ];
    }
}
