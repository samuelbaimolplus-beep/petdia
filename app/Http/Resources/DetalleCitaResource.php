<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DetalleCitaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_detalle_cita,
            'costoEstimado' => $this->costo_estimado,
            'tiempoEstimadoMinutos' => $this->tiempo_estimado_minutos,
            'cita' => $this->whenLoaded('cita', fn () => ['id' => $this->cita?->id_cita, 'fecha_hora_inicio' => $this->cita?->fecha_hora_inicio]),
            'servicio' => $this->whenLoaded('servicio', fn () => ['id' => $this->servicio?->id_servicio, 'nombre' => $this->servicio?->nombre]),
        ];
    }
}
