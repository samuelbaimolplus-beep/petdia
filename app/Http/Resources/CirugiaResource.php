<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CirugiaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_procedimiento,
            'tipoCirugia' => $this->tipo_cirugia,
            'diagnosticoPreoperatorio' => $this->diagnostico_preoperatorio,
            'complicaciones' => $this->complicaciones,
            'indicacionesPostoperatorias' => $this->indicaciones_postoperatorias,
            'resultado' => $this->resultado,
            'procedimiento' => $this->whenLoaded('procedimiento', fn () => ['id' => $this->procedimiento?->id_procedimiento, 'descripcion' => $this->procedimiento?->descripcion]),
        ];
    }
}
