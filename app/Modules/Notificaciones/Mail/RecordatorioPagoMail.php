<?php

namespace App\Modules\Notificaciones\Mail;

use App\Modules\Comprobantes\Models\Recibo;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RecordatorioPagoMail extends Mailable
{
    use Queueable, SerializesModels;

    public Recibo $recibo;

    public function __construct(Recibo $recibo)
    {
        $this->recibo = $recibo;
    }

    public function envelope(): Envelope
    {
        $empresa = config('isp.empresa.nombre', 'Admin ISP');
        return new Envelope(
            subject: 'Recordatorio: Recibo ' . $this->recibo->codigo . ' vence pronto - ' . $empresa,
            from: config('mail.from.address'),
            replyTo: [config('mail.from.address')]
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.recordatorio-pago');
    }
}
