<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UsoProductoAtencionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_uso,
            'cantidad' => $this->cantidad,
            'observaciones' => $this->observaciones,
            'atencion' => $this->whenLoaded('atencion', fn () => ['id' => $this->atencion?->id_atencion, 'fecha_hora_inicio' => $this->atencion?->fecha_hora_inicio]),
            'producto' => $this->whenLoaded('producto', fn () => ['id' => $this->producto?->id_producto, 'nombre' => $this->producto?->nombre]),
        ];
    }
}
