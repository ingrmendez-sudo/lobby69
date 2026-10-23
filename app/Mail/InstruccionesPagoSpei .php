<?php

namespace App\Mail;

use App\Models\MembershipPayment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Instrucciones de transferencia SPEI.
 *
 * Se envía en cuanto se crea la orden, para que el socio conserve la CLABE y
 * el monto exacto aunque cierre la pestaña.
 */
class InstruccionesPagoSpei extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param array<string,mixed> $spei  Salida de SpeiService::instructions()
     */
    public function __construct(
        public MembershipPayment $payment,
        public array $spei,
        public ?string $planName = null,
        public ?string $userName = null,
    ) {}

    public function envelope(): Envelope
    {
        $monto = number_format((float) ($this->spei['amount'] ?? 0), 2);
        $plan  = $this->planName ? ' — ' . $this->planName : '';

        return new Envelope(
            subject: 'Transfiere $' . $monto . ' para activar tu membresía' . $plan,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.instrucciones-spei',
        );
    }
}