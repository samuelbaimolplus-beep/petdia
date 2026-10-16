<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ParticipacionAtencionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_participacion,
            'rolAtencion' => $this->rol_atencion,
            'observaciones' => $this->observaciones,
            'atencion' => $this->whenLoaded('atencion', fn () => ['id' => $this->atencion?->id_atencion, 'fecha_hora_inicio' => $this->atencion?->fecha_hora_inicio]),
            'profesional' => $this->whenLoaded('profesional', fn () => ['id' => $this->profesional?->id_persona, 'especialidad' => $this->profesional?->especialidad]),
        ];
    }
}
