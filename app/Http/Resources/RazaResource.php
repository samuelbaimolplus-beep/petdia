<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RazaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_raza,
            'nombre' => $this->nombre,
            'especie' => $this->whenLoaded('especie', fn () => ['id' => $this->especie?->id_especie, 'nombre' => $this->especie?->nombre]),
        ];
    }
}
