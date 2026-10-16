<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UsuarioResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_usuario,
            'nombreUsuario' => $this->nombre_usuario,

            'estado' => $this->estado,
            'ultimoAcceso' => $this->ultimo_acceso,
            'persona' => $this->whenLoaded('persona', fn () => ['id' => $this->persona?->id_persona, 'nombre' => $this->persona?->nombre]),
        ];
    }
}
