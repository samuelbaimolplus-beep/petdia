<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FichaMedicaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_ficha,
            'fechaApertura' => $this->fecha_apertura,
            'antecedentes' => $this->antecedentes,
            'alergias' => $this->alergias,
            'enfermedadesPrevias' => $this->enfermedades_previas,
            'cirugiasPrevias' => $this->cirugias_previas,
            'medicamentosHabituales' => $this->medicamentos_habituales,
            'observaciones' => $this->observaciones,
            'paciente' => $this->whenLoaded('paciente', fn () => ['id' => $this->paciente?->id_paciente, 'nombre' => $this->paciente?->nombre]),
        ];
    }
}
