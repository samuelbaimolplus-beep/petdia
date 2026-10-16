<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_producto,
            'nombre' => $this->nombre,
            'tipo' => $this->tipo,
            'categoria' => $this->categoria,
            'marca' => $this->marca,
            'unidad_medida' => $this->unidad_medida,
            'descripcion' => $this->descripcion,
            'precio_venta' => $this->precio_venta,
            'stock_minimo' => $this->stock_minimo,
            'control_lotes' => $this->control_lotes,
            'estado' => $this->estado,
            'medicamento' => $this->whenLoaded('medicamento', fn () => $this->medicamento ? [
                'principio_activo' => $this->medicamento->principio_activo,
                'presentacion' => $this->medicamento->presentacion,
                'concentracion' => $this->medicamento->concentracion,
                'unidad_concentracion' => $this->medicamento->unidad_concentracion,
            ] : null),
            'lotes' => $this->whenLoaded('lotes', fn () => $this->lotes->map(fn ($lote) => [
                'numero_lote' => $lote->numero_lote,
                'fecha_vencimiento' => $lote->fecha_vencimiento,
                'estado' => $lote->estado,
            ])->values()),
        ];
    }
}
