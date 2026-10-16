<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PagoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_pago,
            'fechaHora' => $this->fecha_hora,
            'estado' => $this->estado,
            'observaciones' => $this->observaciones,
            'cuenta' => $this->whenLoaded('cuenta', fn () => ['id' => $this->cuenta?->id_cuenta, 'estado' => $this->cuenta?->estado]),
            'cierre_caja' => $this->whenLoaded('cierreCaja', fn () => ['id' => $this->cierreCaja?->id_cierre, 'observaciones' => $this->cierreCaja?->observaciones]),
        ];
    }
}
