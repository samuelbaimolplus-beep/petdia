<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AplicacionPagoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_aplicacion,
            'montoAplicado' => $this->monto_aplicado,
            'pago' => $this->whenLoaded('pago', fn () => ['id' => $this->pago?->id_pago, 'fecha_hora' => $this->pago?->fecha_hora]),
            'cargo' => $this->whenLoaded('cargo', fn () => ['id' => $this->cargo?->id_cargo, 'concepto' => $this->cargo?->concepto]),
        ];
    }
}
