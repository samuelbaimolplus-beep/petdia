<?php

namespace App\Mail;

use App\Models\Configuracion;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VerifyEmailMailable extends Mailable
{
    use Queueable, SerializesModels;

    public string $clinicaNombre;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public string $otpCode,
        public string $nombreUsuario,
        public string $nombrePersona,
        public int $horasExpiracion = 24
    ) {
        $this->clinicaNombre = Configuracion::get('clinica_nombre', 'Veterinaria La Petdiatría');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Tu código de activación de cuenta ({$this->otpCode}) — {$this->clinicaNombre}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.verify-email',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, mixed>
     */
    public function attachments(): array
    {
        return [];
    }
}
