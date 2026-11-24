<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $link;

    /**
     * Create a new message instance.
     */
    public function __construct($link)
    {
        $this->link = $link;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Recuperación de Contraseña',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            htmlString: "<h3>Hola, estás recibiendo este correo porque solicitaste restablecer tu contraseña.</h3>
                        <p>Haz clic en el siguiente enlace para continuar (Este enlace expira en 30 minutos):</p>
                        <p><a href='{$this->link}' style='background: #111; color: #fff; padding: 10px 15px; text-decoration: none; border-radius: 5px; display: inline-block;'>Restablecer Contraseña</a></p>",
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}