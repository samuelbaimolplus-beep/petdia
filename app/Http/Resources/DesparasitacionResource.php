<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DesparasitacionResource extends JsonResource
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
            'tipo' => $this->tipo,
            'proximaDosis' => $this->proxima_dosis,
            'procedimiento' => $this->whenLoaded('procedimiento', fn () => ['id' => $this->procedimiento?->id_procedimiento, 'descripcion' => $this->procedimiento?->descripcion]),
            'administracion_medicamento' => $this->whenLoaded('administracionMedicamento', fn () => ['id' => $this->administracionMedicamento?->id_uso, 'fecha_hora' => $this->administracionMedicamento?->fecha_hora]),
        ];
    }
}
