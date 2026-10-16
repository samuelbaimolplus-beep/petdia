<?php

namespace App\Mail;

use App\Models\Configuracion;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $clinicaNombre;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public string $otp,
        public string $nombreUsuario,
        public string $nombrePersona,
        public int $minutosExpiracion = 15
    ) {
        $this->clinicaNombre = Configuracion::get('clinica_nombre', 'Veterinaria La Petdiatría');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Código de recuperación de contraseña: {$this->otp} — {$this->clinicaNombre}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.password-reset-otp',
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
