<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InternacionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_internacion,
            'fechaHoraIngreso' => $this->fecha_hora_ingreso,
            'fechaHoraAlta' => $this->fecha_hora_alta,
            'motivo' => $this->motivo,
            'estado' => $this->estado,
            'observaciones' => $this->observaciones,
            'atencion_veterinaria' => $this->whenLoaded('atencionVeterinaria', fn () => ['id' => $this->atencionVeterinaria?->id_atencion, 'tipo' => $this->atencionVeterinaria?->tipo]),
        ];
    }
}
