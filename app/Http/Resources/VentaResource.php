<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VentaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_venta,
            'fechaHora' => $this->fecha_hora,
            'estado' => $this->estado,
            'total' => $this->total,
            'observaciones' => $this->observaciones,
            'propietario' => $this->whenLoaded('propietario', fn () => ['id' => $this->propietario?->id_persona]),
            'usuario' => $this->whenLoaded('usuario', fn () => ['id' => $this->usuario?->id_usuario, 'estado' => $this->usuario?->estado]),
        ];
    }
}
