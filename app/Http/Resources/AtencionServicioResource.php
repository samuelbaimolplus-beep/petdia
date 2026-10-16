<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AtencionServicioResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_atencion_servicio,
            'cantidad' => $this->cantidad,
            'precioUnitario' => $this->precio_unitario,
            'descuento' => $this->descuento,
            'atencion' => $this->whenLoaded('atencion', fn () => ['id' => $this->atencion?->id_atencion, 'fecha_hora_inicio' => $this->atencion?->fecha_hora_inicio]),
            'servicio' => $this->whenLoaded('servicio', fn () => ['id' => $this->servicio?->id_servicio, 'nombre' => $this->servicio?->nombre]),
        ];
    }
}
