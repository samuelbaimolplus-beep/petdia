<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MedicamentoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_producto,
            'principioActivo' => $this->principio_activo,
            'presentacion' => $this->presentacion,
            'concentracion' => $this->concentracion,
            'unidadConcentracion' => $this->unidad_concentracion,
            'producto' => $this->whenLoaded('producto', fn () => ['id' => $this->producto?->id_producto, 'nombre' => $this->producto?->nombre]),
        ];
    }
}
