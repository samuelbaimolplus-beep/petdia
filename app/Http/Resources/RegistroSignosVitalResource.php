<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RegistroSignosVitalResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_registro_signos,
            'fechaHora' => $this->fecha_hora,
            'peso' => $this->peso,
            'temperatura' => $this->temperatura,
            'frecuenciaCardiaca' => $this->frecuencia_cardiaca,
            'frecuenciaRespiratoria' => $this->frecuencia_respiratoria,
            'atencion_veterinaria' => $this->whenLoaded('atencionVeterinaria', fn () => ['id' => $this->atencionVeterinaria?->id_atencion, 'tipo' => $this->atencionVeterinaria?->tipo]),
        ];
    }
}
