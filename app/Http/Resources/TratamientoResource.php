<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TratamientoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_tratamiento,
            'descripcion' => $this->descripcion,
            'indicaciones' => $this->indicaciones,
            'fechaInicio' => $this->fecha_inicio,
            'fechaFin' => $this->fecha_fin,
            'estado' => $this->estado,
            'observaciones' => $this->observaciones,
            'paciente' => $this->whenLoaded('paciente', fn () => ['id' => $this->paciente?->id_paciente, 'nombre' => $this->paciente?->nombre]),
        ];
    }
}
