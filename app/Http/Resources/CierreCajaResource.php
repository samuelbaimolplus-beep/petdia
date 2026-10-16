<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CierreCajaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_cierre,
            'fechaHoraApertura' => $this->fecha_hora_apertura,
            'fechaHoraCierre' => $this->fecha_hora_cierre,
            'totalRegistrado' => $this->total_registrado,
            'observaciones' => $this->observaciones,
            'usuario' => $this->whenLoaded('usuario', fn () => ['id' => $this->usuario?->id_usuario, 'estado' => $this->usuario?->estado]),
        ];
    }
}
