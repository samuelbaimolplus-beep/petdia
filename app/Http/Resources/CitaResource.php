<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CitaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_cita,
            'fecha_hora_inicio' => $this->fecha_hora_inicio,
            'fecha_hora_fin' => $this->fecha_hora_fin,
            'estado' => $this->estado,
            'origen' => $this->origen,
            'paciente' => $this->whenLoaded('paciente', fn () => [
                'id' => $this->paciente?->id_paciente,
                'nombre' => $this->paciente?->nombre,
            ]),
            'profesional' => $this->whenLoaded('profesional', fn () => [
                'id' => $this->profesional?->id_persona,
                'nombre' => $this->profesional?->persona?->nombre,
            ]),
            'servicios' => $this->whenLoaded('detalleCitas', fn () => $this->detalleCitas
                ->map(fn ($detalle) => [
                    'servicio' => $detalle->servicio?->nombre,
                    'costo_estimado' => $detalle->costo_estimado,
                ])
                ->values()),
            'servicio_domicilio' => $this->whenLoaded('servicioDomicilio', fn () => [
                'direccion' => $this->servicioDomicilio?->direccion,
                'costo_domicilio' => $this->servicioDomicilio?->costo_domicilio,
                'estado' => $this->servicioDomicilio?->estado,
            ]),
            'atenciones' => $this->whenLoaded('atenciones', fn () => $this->atenciones->map(fn ($atencion) => [
                'id' => $atencion->id_atencion,
                'fecha_hora_inicio' => $atencion->fecha_hora_inicio,
                'estado' => $atencion->estado,
                'tipo' => $atencion->atencionVeterinaria?->tipo ?? $atencion->atencionEstetica?->modalidad,
                'total_final' => $atencion->total_final,
            ])),
        ];
    }
}
