<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CargoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_cargo,
            'fechaHora' => $this->fecha_hora,
            'concepto' => $this->concepto,
            'monto' => $this->monto,
            'descuento' => $this->descuento,
            'estado' => $this->estado,
            'observaciones' => $this->observaciones,
            'cuenta' => $this->whenLoaded('cuenta', fn () => ['id' => $this->cuenta?->id_cuenta, 'estado' => $this->cuenta?->estado]),
            'tratamiento' => $this->whenLoaded('tratamiento', fn () => ['id' => $this->tratamiento?->id_tratamiento, 'descripcion' => $this->tratamiento?->descripcion]),
        ];
    }
}
