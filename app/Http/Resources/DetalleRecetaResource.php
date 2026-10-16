<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DetalleRecetaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_detalle_receta,
            'dosis' => $this->dosis,
            'unidadDosis' => $this->unidad_dosis,
            'frecuencia' => $this->frecuencia,
            'duracion' => $this->duracion,
            'viaAdministracion' => $this->via_administracion,
            'indicaciones' => $this->indicaciones,
            'receta' => $this->whenLoaded('receta', fn () => ['id' => $this->receta?->id_receta, 'fecha_hora' => $this->receta?->fecha_hora]),
            'medicamento' => $this->whenLoaded('medicamento', fn () => ['id' => $this->medicamento?->id_producto, 'principio_activo' => $this->medicamento?->principio_activo]),
        ];
    }
}
