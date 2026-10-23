<?php

namespace App\Mail;

use App\Models\MembershipPayment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Aviso interno: un socio subió su comprobante y la orden espera revisión.
 *
 * Es una notificación operativa para el administrador, no para el socio.
 */
class AvisoComprobanteSubido extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param array<string,mixed> $datos
     */
    public function __construct(
        public MembershipPayment $payment,
        public array $datos = [],
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: sprintf(
                'Comprobante por revisar: %s pidió %s ($%s)',
                $this->datos['username'] ?? 'un socio',
                $this->payment->requested_membership,
                number_format((float) $this->payment->amount, 2)
            ),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.aviso-comprobante',
        );
    }
}
