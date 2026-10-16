<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DetallePagoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_detalle_pago,
            'monto' => $this->monto,
            'referencia' => $this->referencia,
            'pago' => $this->whenLoaded('pago', fn () => ['id' => $this->pago?->id_pago, 'fecha_hora' => $this->pago?->fecha_hora]),
            'metodo_pago' => $this->whenLoaded('metodoPago', fn () => ['id' => $this->metodoPago?->id_metodo_pago, 'nombre' => $this->metodoPago?->nombre]),
        ];
    }
}
