<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MovimientoInventarioResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_movimiento,
            'fechaHora' => $this->fecha_hora,
            'tipoMovimiento' => $this->tipo_movimiento,
            'cantidad' => $this->cantidad,
            'motivo' => $this->motivo,
            'producto' => $this->whenLoaded('producto', fn () => ['id' => $this->producto?->id_producto, 'nombre' => $this->producto?->nombre]),
            'lote' => $this->whenLoaded('lote', fn () => ['id' => $this->lote?->id_lote, 'numero_lote' => $this->lote?->numero_lote]),
            'usuario' => $this->whenLoaded('usuario', fn () => ['id' => $this->usuario?->id_usuario, 'estado' => $this->usuario?->estado]),
            'detalle_compra' => $this->whenLoaded('detalleCompra', fn () => ['id' => $this->detalleCompra?->id_detalle_compra]),
            'uso_producto_atencion' => $this->whenLoaded('usoProductoAtencion', fn () => ['id' => $this->usoProductoAtencion?->id_uso, 'observaciones' => $this->usoProductoAtencion?->observaciones]),
            'detalle_venta' => $this->whenLoaded('detalleVenta', fn () => ['id' => $this->detalleVenta?->id_detalle_venta]),
        ];
    }
}
