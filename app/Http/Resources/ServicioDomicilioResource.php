<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServicioDomicilioResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_servicio_domicilio,
            'direccion' => $this->direccion,
            'referencia' => $this->referencia,
            'distanciaEstimada' => $this->distancia_estimada,
            'costoDomicilio' => $this->costo_domicilio,
            'estado' => $this->estado,
            'cita' => $this->whenLoaded('cita', fn () => ['id' => $this->cita?->id_cita, 'fecha_hora_inicio' => $this->cita?->fecha_hora_inicio]),
        ];
    }
}
