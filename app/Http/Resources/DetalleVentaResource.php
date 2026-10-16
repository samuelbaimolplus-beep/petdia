<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DetalleVentaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_detalle_venta,
            'cantidad' => $this->cantidad,
            'precioUnitario' => $this->precio_unitario,
            'descuento' => $this->descuento,
            'venta' => $this->whenLoaded('venta', fn () => ['id' => $this->venta?->id_venta, 'fecha_hora' => $this->venta?->fecha_hora]),
            'producto' => $this->whenLoaded('producto', fn () => ['id' => $this->producto?->id_producto, 'nombre' => $this->producto?->nombre]),
        ];
    }
}
