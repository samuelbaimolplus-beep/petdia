<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecordatorioResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_recordatorio,
            'tipo' => $this->tipo,
            'fechaHoraProgramada' => $this->fecha_hora_programada,
            'fechaHoraEnvio' => $this->fecha_hora_envio,
            'medioEnvio' => $this->medio_envio,
            'mensaje' => $this->mensaje,
            'estado' => $this->estado,
            'paciente' => $this->whenLoaded('paciente', fn () => ['id' => $this->paciente?->id_paciente, 'nombre' => $this->paciente?->nombre]),
            'servicio' => $this->whenLoaded('servicio', fn () => ['id' => $this->servicio?->id_servicio, 'nombre' => $this->servicio?->nombre]),
            'atencion_origen' => $this->whenLoaded('atencionOrigen', fn () => ['id' => $this->atencionOrigen?->id_atencion, 'fecha_hora_inicio' => $this->atencionOrigen?->fecha_hora_inicio]),
            'cita' => $this->whenLoaded('cita', fn () => ['id' => $this->cita?->id_cita, 'fecha_hora_inicio' => $this->cita?->fecha_hora_inicio]),
        ];
    }
}
