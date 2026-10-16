<?php

namespace App\Mail;

use App\Models\Configuracion;
use App\Models\Recordatorio;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RecordatorioMailable extends Mailable
{
    use Queueable, SerializesModels;

    public string $cuerpoMensaje;

    public string $nombreMascota;

    public string $especieRaza;

    public string $nombrePropietario;

    public string $telefonoPropietario;

    public string $enlaceWhatsapp;

    public string $nombreProfesional;

    public string $fechaCita;

    public string $tipoRecordatorio;

    public ?string $servicioNombre;

    public string $clinicaNombre;

    public string $clinicaTelefono;

    public string $clinicaDireccion;

    /**
     * Create a new message instance.
     */
    public function __construct(public Recordatorio $recordatorio)
    {
        $this->recordatorio->loadMissing([
            'paciente.propietario.persona',
            'paciente.raza.especie',
            'servicio',
            'profesional.persona',
            'cita.profesional.persona',
        ]);

        $paciente = $this->recordatorio->paciente;
        $personaPropietario = $paciente?->propietario?->persona;
        $profesional = $this->recordatorio->profesional_responsable;

        $this->clinicaNombre = Configuracion::get('clinica_nombre', 'Veterinaria La Petdiatría');
        $this->clinicaTelefono = Configuracion::get('clinica_telefono', '+595 21 123 456');
        $this->clinicaDireccion = Configuracion::get('clinica_direccion', 'Avda. Principal 123, Asunción');

        $this->nombreMascota = $paciente?->nombre ?? 'Mascota';
        $especie = $paciente?->raza?->especie?->nombre ?? 'Mascota';
        $raza = $paciente?->raza?->nombre ?? 'Mestizo';
        $this->especieRaza = "{$especie} - {$raza}";

        $this->nombrePropietario = $personaPropietario
            ? trim("{$personaPropietario->nombre} {$personaPropietario->apellido1}")
            : 'Propietario no registrado';

        $telefono = $personaPropietario?->telefono ?? '';
        $this->telefonoPropietario = $telefono ?: 'Sin teléfono registrado';
        $telLimpio = preg_replace('/[^0-9]/', '', $telefono);
        $this->enlaceWhatsapp = ! empty($telLimpio)
            ? "https://wa.me/{$telLimpio}?text=".urlencode("Hola {$this->nombrePropietario}, nos comunicamos desde {$this->clinicaNombre} respecto a {$this->nombreMascota}.")
            : '';

        $this->nombreProfesional = $profesional?->persona
            ? 'Dr./a '.trim("{$profesional->persona->nombre} {$profesional->persona->apellido1}")
            : 'Equipo Veterinario';

        $fecha = $this->recordatorio->fecha_hora_programada
            ? Carbon::parse($this->recordatorio->fecha_hora_programada)->format('d/m/Y H:i')
            : 'Por coordinar';
        $this->fechaCita = $fecha;

        $this->tipoRecordatorio = ucfirst(strtolower($this->recordatorio->tipo ?? 'Atención'));
        $this->servicioNombre = $this->recordatorio->servicio?->nombre;

        $this->cuerpoMensaje = $this->recordatorio->mensaje
            ?: "Tarea de {$this->tipoRecordatorio} programada para {$this->nombreMascota}. Coordinar preparación y atención requerida.";
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "🔔 [Tarea Clínica] {$this->tipoRecordatorio} — Paciente: {$this->nombreMascota} ({$this->clinicaNombre})",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.recordatorio',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
