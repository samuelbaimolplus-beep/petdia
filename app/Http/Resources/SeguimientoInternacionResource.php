<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SeguimientoInternacionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_seguimiento,
            'fechaHora' => $this->fecha_hora,
            'cuidados' => $this->cuidados,
            'observaciones' => $this->observaciones,
            'internacion' => $this->whenLoaded('internacion', fn () => ['id' => $this->internacion?->id_internacion, 'motivo' => $this->internacion?->motivo]),
            'profesional' => $this->whenLoaded('profesional', fn () => ['id' => $this->profesional?->id_persona, 'especialidad' => $this->profesional?->especialidad]),
        ];
    }
}
