<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotificacionAlarma extends Mailable
{
    use Queueable, SerializesModels;

    public $mensaje;
    public $alarma;
    public $evento;

    public function __construct($mensaje, $alarma, $evento)
    {
        $this->mensaje = $mensaje;
        $this->alarma = $alarma;
        $this->evento = $evento;
    }

    public function build()
    {
        return $this->subject('Notificación de Alarma')
                    ->view('emails.notificacion_alarma');
    }
}