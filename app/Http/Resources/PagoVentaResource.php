<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PagoVentaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_pago_venta,
            'montoAplicado' => $this->monto_aplicado,
            'pago' => $this->whenLoaded('pago', fn () => ['id' => $this->pago?->id_pago, 'fecha_hora' => $this->pago?->fecha_hora]),
            'venta' => $this->whenLoaded('venta', fn () => ['id' => $this->venta?->id_venta, 'fecha_hora' => $this->venta?->fecha_hora]),
        ];
    }
}
