<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EstudioLaboratorioResource extends JsonResource
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
            'tipoEstudio' => $this->tipo_estudio,
            'fechaResultado' => $this->fecha_resultado,
            'resultado' => $this->resultado,
            'interpretacion' => $this->interpretacion,
            'procedimiento' => $this->whenLoaded('procedimiento', fn () => ['id' => $this->procedimiento?->id_procedimiento, 'descripcion' => $this->procedimiento?->descripcion]),
        ];
    }
}
