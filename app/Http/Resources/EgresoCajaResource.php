<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EgresoCajaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_egreso,
            'id_usuario' => $this->id_usuario,
            'id_cierre' => $this->id_cierre,
            'monto' => $this->monto,
            'concepto' => $this->concepto,
            'categoria' => $this->categoria,
            'comprobante' => $this->comprobante,
            'fechaHora' => $this->fecha_hora,
            'usuario' => $this->whenLoaded('usuario', fn () => ['id' => $this->usuario?->id_usuario, 'nombre' => $this->usuario?->persona?->nombre ?? $this->usuario?->nombre_usuario]),
        ];
    }
}
