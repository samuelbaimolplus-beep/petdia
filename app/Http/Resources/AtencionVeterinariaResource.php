<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AtencionVeterinariaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_atencion,
            'motivoConsulta' => $this->motivo_consulta,
            'tipo' => $this->tipo,
            'anamnesis' => $this->anamnesis,
            'sintomas' => $this->sintomas,
            'examenFisico' => $this->examen_fisico,
            'conclusion' => $this->conclusion,
            'fechaReconsulta' => $this->fecha_reconsulta,
            'observacionesClinicas' => $this->observaciones_clinicas,
            'atencion' => $this->whenLoaded('atencion', fn () => ['id' => $this->atencion?->id_atencion, 'fecha_hora_inicio' => $this->atencion?->fecha_hora_inicio]),
        ];
    }
}
