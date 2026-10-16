<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DetalleCompraResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_detalle_compra,
            'cantidad' => $this->cantidad,
            'precioUnitario' => $this->precio_unitario,
            'compra' => $this->whenLoaded('compra', fn () => ['id' => $this->compra?->id_compra, 'fecha_hora' => $this->compra?->fecha_hora]),
            'producto' => $this->whenLoaded('producto', fn () => ['id' => $this->producto?->id_producto, 'nombre' => $this->producto?->nombre]),
        ];
    }
}
