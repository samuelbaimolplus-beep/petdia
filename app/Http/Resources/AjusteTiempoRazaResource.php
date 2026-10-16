<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AjusteTiempoRazaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_ajuste,
            'duracionMinutos' => $this->duracion_minutos,
            'tiempo_estimado' => $this->whenLoaded('tiempoEstimado', fn () => ['id' => $this->tiempoEstimado?->id_tiempo_estimado]),
            'raza' => $this->whenLoaded('raza', fn () => ['id' => $this->raza?->id_raza, 'nombre' => $this->raza?->nombre]),
        ];
    }
}
