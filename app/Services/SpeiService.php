<?php

namespace App\Services;

use App\Models\MembershipPayment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Reglas de cobro por transferencia SPEI.
 *
 * Genera la referencia que el usuario cita en su transferencia, el monto
 * exacto (precio + centavos discriminadores) y la ventana de pago.
 */
class SpeiService
{
    public function clabe(): string
    {
        return (string) config('spei.clabe');
    }

    public function beneficiary(): string
    {
        return (string) config('spei.beneficiary');
    }

    public function bank(): string
    {
        return (string) config('spei.bank');
    }

    /** ¿Ya se configuró la CLABE en .env? */
    public function isConfigured(): bool
    {
        return $this->clabe() !== '';
    }

    public function windowHours(): int
    {
        return max(1, (int) config('spei.window_hours', 24));
    }

    public function expiresAt(): Carbon
    {
        return now()->addHours($this->windowHours());
    }

    /**
     * Referencia pública del pedido. Es la que el usuario escribe en el
     * concepto de su transferencia, así que debe ser corta y tecleable.
     * Cabe en public_code varchar(24).
     */
    public function publicCode(int $orderId): string
    {
        return sprintf('L69-%05d-%s', $orderId, strtoupper(Str::random(4)));
    }

    /**
     * Centavos derivados del id del pedido (1..99).
     * Determinista: el mismo pedido siempre produce el mismo monto, así que
     * el usuario puede reconsultar la pantalla sin que cambie la cifra.
     */
    public function discriminatorCents(int $orderId): int
    {
        if (! config('spei.discriminator.enabled', true)) {
            return 0;
        }

        $min  = max(1, (int) config('spei.discriminator.min', 1));
        $max  = max($min, (int) config('spei.discriminator.max', 99));
        $span = $max - $min + 1;

        return $min + ((($orderId * 37) + 11) % $span);
    }

    /**
     * Desglose del monto a transferir.
     *
     * @return array{base: float, cents: int, total: float}
     */
    public function exactAmount(float $base, int $orderId): array
    {
        $base  = round($base, 2);
        $cents = $this->discriminatorCents($orderId);

        return [
            'base'  => $base,
            'cents' => $cents,
            'total' => round($base + ($cents / 100), 2),
        ];
    }

    /** Concepto que se teclea en la banca en línea. */
    public function concept(MembershipPayment $payment): string
    {
        $prefix = trim((string) config('spei.concept_prefix', 'LOBBY69'));

        return trim($prefix . ' ' . ($payment->public_code ?? ''));
    }

    /**
     * Todo lo que la pantalla de instrucciones necesita, ya resuelto.
     */
    public function instructions(MembershipPayment $payment): array
    {
        $metadata = is_array($payment->metadata) ? $payment->metadata : [];

        return [
            'configured'   => $this->isConfigured(),
            'clabe'        => $this->clabe(),
            'beneficiary'  => $this->beneficiary(),
            'bank'         => $this->bank(),
            'amount'       => (float) $payment->amount,
            'base_amount'  => (float) ($metadata['base_amount'] ?? $payment->amount),
            'cents'        => (int) ($metadata['discriminator_cents'] ?? 0),
            'reference'    => (string) $payment->public_code,
            'concept'      => $this->concept($payment),
            'expires_at'   => $payment->expires_at,
            'window_hours' => $this->windowHours(),
        ];
    }
}
