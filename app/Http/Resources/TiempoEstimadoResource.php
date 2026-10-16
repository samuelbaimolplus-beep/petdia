<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TiempoEstimadoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_tiempo_estimado,
            'pesoMin' => $this->peso_min,
            'pesoMax' => $this->peso_max,
            'duracionMinutos' => $this->duracion_minutos,
            'servicio' => $this->whenLoaded('servicio', fn () => ['id' => $this->servicio?->id_servicio, 'nombre' => $this->servicio?->nombre]),
            'especie' => $this->whenLoaded('especie', fn () => ['id' => $this->especie?->id_especie, 'nombre' => $this->especie?->nombre]),
        ];
    }
}
