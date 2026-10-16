<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CuentaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_cuenta,
            'fechaApertura' => $this->fecha_apertura,
            'estado' => $this->estado,
            'propietario' => $this->whenLoaded('propietario', fn () => ['id' => $this->propietario?->id_persona]),
        ];
    }
}
