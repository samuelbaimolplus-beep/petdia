<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DetallePaqueteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_detalle_paquete,
            'cantidadIncluida' => $this->cantidad_incluida,
            'paquete' => $this->whenLoaded('paquete', fn () => ['id' => $this->paquete?->id_paquete, 'nombre' => $this->paquete?->nombre]),
            'servicio' => $this->whenLoaded('servicio', fn () => ['id' => $this->servicio?->id_servicio, 'nombre' => $this->servicio?->nombre]),
        ];
    }
}
