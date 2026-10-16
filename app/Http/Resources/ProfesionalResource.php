<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfesionalResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_persona,
            'especialidad' => $this->especialidad,
            'registroProfesional' => $this->registro_profesional,
            'estado' => $this->estado,
            'persona' => $this->whenLoaded('persona', fn () => ['id' => $this->persona?->id_persona, 'nombre' => $this->persona?->nombre]),
        ];
    }
}
