<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AtencionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_atencion,
            'fecha_hora_inicio' => $this->fecha_hora_inicio,
            'fecha_hora_fin' => $this->fecha_hora_fin,
            'estado' => $this->estado,
            'observaciones' => $this->observaciones,
            'recomendaciones' => $this->recomendaciones,
            'total_final' => $this->total_final,
            'paciente' => $this->whenLoaded('paciente', fn () => [
                'id' => $this->paciente?->id_paciente,
                'nombre' => $this->paciente?->nombre,
            ]),
            'atencion_veterinaria' => $this->whenLoaded('atencionVeterinaria', fn () => $this->atencionVeterinaria ? [
                'tipo' => $this->atencionVeterinaria->tipo,
                'motivo_consulta' => $this->atencionVeterinaria->motivo_consulta,
                'anamnesis' => $this->atencionVeterinaria->anamnesis,
                'sintomas' => $this->atencionVeterinaria->sintomas,
                'examen_fisico' => $this->atencionVeterinaria->examen_fisico,
                'conclusion' => $this->atencionVeterinaria->conclusion,
                'diagnosticos' => $this->atencionVeterinaria->diagnosticos?->pluck('nombre')->values(),
                'recetas' => $this->atencionVeterinaria->recetas?->map(fn ($receta) => [
                    'fecha_hora' => $receta->fecha_hora,
                    'medicamentos' => $receta->detalleRecetas?->map(
                        fn ($detalle) => $detalle->medicamento?->producto?->nombre
                    )->values(),
                ])->values(),
            ] : null),
            'atencion_estetica' => $this->whenLoaded('atencionEstetica', fn () => $this->atencionEstetica ? [
                'modalidad' => $this->atencionEstetica->modalidad,
                'estado_pelaje' => $this->atencionEstetica->estado_pelaje,
                'caracteristicas' => $this->atencionEstetica->caracteristicas_observadas,
            ] : null),
            'servicios' => $this->whenLoaded('atencionServicios', fn () => $this->atencionServicios->map(fn ($detalle) => [
                'servicio' => $detalle->servicio?->nombre,
                'cantidad' => $detalle->cantidad,
                'precio_unitario' => $detalle->precio_unitario,
                'descuento' => $detalle->descuento,
            ])->values()),
            'participantes' => $this->whenLoaded('participaciones', fn () => $this->participaciones->map(fn ($p) => [
                'rol' => $p->rol_atencion,
                'profesional' => $p->profesional?->persona?->nombre,
            ])->values()),
        ];
    }
}
