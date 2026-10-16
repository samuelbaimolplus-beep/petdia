<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServicioResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_servicio,
            'nombre' => $this->nombre,
            'categoria' => $this->categoria,
            'descripcion' => $this->descripcion,
            'observaciones' => $this->observaciones,
            'precio_base' => $this->precio_base,
            'disponible_domicilio' => $this->disponible_domicilio,
            'estado' => $this->estado,
            'tiempos_estimados' => $this->whenLoaded('tiemposEstimados', fn () => $this->tiemposEstimados->map(
                fn ($t) => [
                    'especie' => $t->especie?->nombre,
                    'peso_min' => $t->peso_min,
                    'peso_max' => $t->peso_max,
                    'duracion_minutos' => $t->duracion_minutos,
                ]
            )->values()),
        ];
    }
}
