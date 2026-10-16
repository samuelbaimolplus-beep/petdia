<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_lote,
            'numeroLote' => $this->numero_lote,
            'fechaVencimiento' => $this->fecha_vencimiento,
            'estado' => $this->estado,
            'producto' => $this->whenLoaded('producto', fn () => ['id' => $this->producto?->id_producto, 'nombre' => $this->producto?->nombre]),
            'detalle_compra' => $this->whenLoaded('detalleCompra', fn () => ['id' => $this->detalleCompra?->id_detalle_compra]),
        ];
    }
}
