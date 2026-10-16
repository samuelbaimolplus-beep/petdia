<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PagoAtencionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_pago_atencion,
            'montoAplicado' => $this->monto_aplicado,
            'pago' => $this->whenLoaded('pago', fn () => ['id' => $this->pago?->id_pago, 'fecha_hora' => $this->pago?->fecha_hora]),
            'atencion' => $this->whenLoaded('atencion', fn () => ['id' => $this->atencion?->id_atencion, 'fecha_hora_inicio' => $this->atencion?->fecha_hora_inicio]),
        ];
    }
}
