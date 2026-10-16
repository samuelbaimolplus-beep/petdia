<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompraResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_compra,
            'fechaHora' => $this->fecha_hora,
            'estado' => $this->estado,
            'total' => $this->total,
            'observaciones' => $this->observaciones,
            'proveedor' => $this->whenLoaded('proveedor', fn () => ['id' => $this->proveedor?->id_proveedor, 'nombre' => $this->proveedor?->nombre]),
        ];
    }
}
