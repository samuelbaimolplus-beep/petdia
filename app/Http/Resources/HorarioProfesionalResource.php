<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HorarioProfesionalResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_horario,
            'diaSemana' => $this->dia_semana,
            'horaInicio' => $this->hora_inicio,
            'horaFin' => $this->hora_fin,
            'estado' => $this->estado,
            'profesional' => $this->whenLoaded('profesional', fn () => ['id' => $this->profesional?->id_persona, 'especialidad' => $this->profesional?->especialidad]),
        ];
    }
}
