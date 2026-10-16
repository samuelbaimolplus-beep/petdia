<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BloqueoAgendaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_bloqueo,
            'fechaHoraInicio' => $this->fecha_hora_inicio,
            'fechaHoraFin' => $this->fecha_hora_fin,
            'motivo' => $this->motivo,
            'profesional' => $this->whenLoaded('profesional', fn () => ['id' => $this->profesional?->id_persona, 'especialidad' => $this->profesional?->especialidad]),
        ];
    }
}
