<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DevolucionPagoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_devolucion,
            'fechaHora' => $this->fecha_hora,
            'monto' => $this->monto,
            'motivo' => $this->motivo,
            'estado' => $this->estado,
            'pago' => $this->whenLoaded('pago', fn () => ['id' => $this->pago?->id_pago, 'fecha_hora' => $this->pago?->fecha_hora]),
            'usuario' => $this->whenLoaded('usuario', fn () => ['id' => $this->usuario?->id_usuario, 'estado' => $this->usuario?->estado]),
        ];
    }
}
