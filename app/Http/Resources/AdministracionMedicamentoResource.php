<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdministracionMedicamentoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_uso,
            'fechaHora' => $this->fecha_hora,
            'dosis' => $this->dosis,
            'unidadDosis' => $this->unidad_dosis,
            'viaAdministracion' => $this->via_administracion,
            'observacionesClinicas' => $this->observaciones_clinicas,
            'uso_producto_atencion' => $this->whenLoaded('usoProductoAtencion', fn () => ['id' => $this->usoProductoAtencion?->id_uso, 'observaciones' => $this->usoProductoAtencion?->observaciones]),
        ];
    }
}
