<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DiagnosticoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_diagnostico,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'tipo' => $this->tipo,
            'observaciones' => $this->observaciones,
            'atencion_veterinaria' => $this->whenLoaded('atencionVeterinaria', fn () => ['id' => $this->atencionVeterinaria?->id_atencion, 'tipo' => $this->atencionVeterinaria?->tipo]),
        ];
    }
}
