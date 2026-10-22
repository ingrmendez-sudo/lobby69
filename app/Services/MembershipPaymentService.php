<?php

namespace App\Services;

use App\Enums\MembershipPaymentStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Cobro por SPEI — línea A2.
 *
 * Responsabilidad única: mover un pedido de pago entre estados y registrar
 * cada paso. NO aplica la membresía: al aprobar delega en
 * Membership::activateForUser(), que es quien conoce los tiers y las fechas.
 *
 * Sigue las convenciones del proyecto: métodos estáticos, DB::table / DB::statement
 * con casts ::uuid explícitos, y caché invalidada a través del servicio.
 */
class MembershipPaymentService
{
    /** Ventana de pago en horas. */
    public const PAYMENT_WINDOW_HOURS = 48;

    /** Cupo de membresías Fundador (slug 'vitalicio'), según la promesa comercial. */
    public const FOUNDER_SEATS = 20;

    // ─────────────────────────────────────────────────────────────────────
    // Creación del pedido
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Crea un pedido pendiente con monto exacto y referencia única.
     *
     * El monto añade centavos derivados del código público: dos clientes que
     * transfieren el mismo plan el mismo día quedan distinguibles sin depender
     * de que escriban bien la referencia.
     *
     * @param  string      $userId  UUID del usuario (o null si el pedido es de un visitante)
     * @param  string      $slug    Slug del plan en membership_plans
     * @param  string      $email   Contacto para recuperación
     * @param  string|null $referralCode
     * @return object  El pedido creado
     */
    public static function createOrder(
        ?string $userId,
        string  $slug,
        string  $email,
        ?string $referralCode = null,
        string  $paymentMethod = 'spei'
    ): object {
        $plan = DB::table('membership_plans')
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if (! $plan) {
            throw new InvalidArgumentException("Plan inexistente o inactivo: {$slug}");
        }

        // GUARDA 1: un plan sin costo no se puede pedir desde el checkout.
        // Sin esto, un POST manipulado permite crear pedidos de 'invitado'
        // (con duration_days null) y obtener acceso permanente por $0.
        $priceCheck = ($plan->promo_active && $plan->price_promo !== null)
            ? (float) $plan->price_promo
            : (float) $plan->price_normal;

        if ($priceCheck <= 0) {
            throw new InvalidArgumentException(
                "El plan '{$slug}' no se vende por checkout (precio cero)."
            );
        }

        // GUARDA 2: cupo de Fundadores. La tarjeta promete 20 membresías;
        // sin esta validación se pueden vender ilimitadas.
        if ($slug === 'vitalicio') {
            $vendidos = DB::table('memberships')
                ->where('tier', 'vitalicio')
                ->where('status', 'active')
                ->count();

            if ($vendidos >= self::FOUNDER_SEATS) {
                throw new \RuntimeException(
                    'El cupo de Fundadores está agotado (' . self::FOUNDER_SEATS . ' de ' . self::FOUNDER_SEATS . ').'
                );
            }
        }

        // Importe vigente: promo si está activa, si no el normal.
        $base = ($plan->promo_active && $plan->price_promo !== null)
            ? (float) $plan->price_promo
            : (float) $plan->price_normal;

        // Centavos discriminadores: 1 a 99, derivados de un código aleatorio.
        $code    = self::generatePublicCode($slug);
        $cents   = self::discriminatorCents($code);
        $amount  = round($base + ($cents / 100), 2);

        $now = now();

        $id = DB::table('membership_payments')->insertGetId([
            'created_at'          => $now,
            'updated_at'          => $now,
            'user_id'             => $userId ?? '',
            'requested_membership'=> $slug,
            'current_membership'  => null,
            'amount'              => $amount,
            'currency'            => 'MXN',
            'payment_method'      => $paymentMethod,
            'payment_reference'   => null,
            'receipt_path'        => null,
            'status'              => MembershipPaymentStatus::Pending->value,
            'public_code'         => $code,
            'email'               => $email,
            'expires_at'          => $now->copy()->addHours(self::PAYMENT_WINDOW_HOURS),
            'plan_snapshot'       => json_encode([
                'slug'          => $plan->slug,
                'name'          => $plan->name,
                'base_price'    => $base,
                'duration_days' => $plan->duration_days,
                'is_lifetime'   => (bool) $plan->is_lifetime,
            ]),
            'referral_code'       => $referralCode,
            'metadata'            => json_encode([]),
        ], 'id');

        $order = self::find($id);
        self::logEvent($id, 'created', 'customer', $userId, [
            'slug'   => $slug,
            'amount' => $amount,
        ]);

        return $order;
    }

    // ─────────────────────────────────────────────────────────────────────
    // Transiciones
    // ─────────────────────────────────────────────────────────────────────

    /** El cliente sube comprobante o declara el folio. No activa nada. */
    public static function submitProof(int $orderId, ?string $receiptPath, ?string $declaredRef = null): void
    {
        $order = self::requireOrder($orderId);
        self::assertTransition($order, MembershipPaymentStatus::UnderReview, [MembershipPaymentStatus::Pending]);

        DB::table('membership_payments')->where('id', $orderId)->update([
            'receipt_path'   => $receiptPath ?? $order->receipt_path,
            'status'         => MembershipPaymentStatus::UnderReview->value,
            'updated_at'     => now(),
        ]);

        self::logEvent($orderId, 'proof_uploaded', 'customer', $order->user_id ?: null, [
            'receipt_path'  => $receiptPath,
            'declared_ref'  => $declaredRef,
        ]);
    }

    /**
     * Aprueba el pago y aplica la membresía.
     *
     * Exige folio bancario: sin él, la fila no puede llegar a 'approved'
     * (lo impone membership_payments_approved_check en la base).
     *
     * Todo dentro de una transacción: si la activación falla, el pedido
     * no queda aprobado a medias.
     */
    public static function approve(int $orderId, string $bankTrackingKey, string $adminId): object
    {
        return DB::transaction(function () use ($orderId, $bankTrackingKey, $adminId) {
            // Bloqueo de fila: dos administradores a la vez no pueden aprobar dos veces.
            $order = DB::table('membership_payments')
                ->where('id', $orderId)
                ->lockForUpdate()
                ->first();

            if (! $order) {
                throw new InvalidArgumentException("Pedido inexistente: {$orderId}");
            }

            if ($order->status === MembershipPaymentStatus::Approved->value) {
                // Idempotencia: ya estaba aprobado, no se duplica nada.
                return $order;
            }

            if ($order->status !== MembershipPaymentStatus::UnderReview->value
                && $order->status !== MembershipPaymentStatus::Pending->value) {
                throw new InvalidArgumentException(
                    "No se puede aprobar un pedido en estado '{$order->status}'."
                );
            }

            if ($order->user_id === '' || $order->user_id === null) {
                throw new InvalidArgumentException(
                    'El pedido no tiene usuario asociado: asígnelo antes de aprobar.'
                );
            }

            $snapshot = json_decode($order->plan_snapshot ?? '{}', true) ?: [];
            $tier     = $snapshot['slug'] ?? $order->requested_membership;
            $days     = ! empty($snapshot['is_lifetime']) ? null : ($snapshot['duration_days'] ?? null);

            // Aplicar la membresía con el servicio existente del proyecto.
            \App\Models\Membership::activateForUser(
                userId:        $order->user_id,
                tier:          $tier,
                durationDays:  $days,
                price:         (float) $order->amount,
                paymentMethod: $order->payment_method ?: 'spei',
                transactionId: $bankTrackingKey,
            );

            DB::table('membership_payments')->where('id', $orderId)->update([
                'status'              => MembershipPaymentStatus::Approved->value,
                'bank_tracking_key'   => $bankTrackingKey,
                'reviewed_by'         => $adminId,
                'reviewed_at'         => now(),
                'updated_at'          => now(),
            ]);

            self::logEvent($orderId, 'approved', 'admin', $adminId, [
                'tier'      => $tier,
                'days'      => $days,
                'tracking'  => $bankTrackingKey,
            ]);

            return self::find($orderId);
        });
    }

    /** Rechaza con motivo obligatorio. */
    public static function reject(int $orderId, string $reason, string $adminId): void
    {
        $order = self::requireOrder($orderId);

        if ($order->status === MembershipPaymentStatus::Rejected->value) {
            return;
        }

        DB::table('membership_payments')->where('id', $orderId)->update([
            'status'         => MembershipPaymentStatus::Rejected->value,
            'admin_note'     => $reason,
            'reviewed_by'    => $adminId,
            'reviewed_at'    => now(),
            'updated_at'     => now(),
        ]);

        self::logEvent($orderId, 'rejected', 'admin', $adminId, ['reason' => $reason]);
    }

    /**
     * Expira pedidos fuera de ventana. Idempotente: la llama el job, no el usuario.
     * Devuelve cuántos cerró.
     */
    public static function expireOverdue(): int
    {
        $ids = DB::table('membership_payments')
            ->whereIn('status', [
                MembershipPaymentStatus::Pending->value,
                MembershipPaymentStatus::UnderReview->value,
            ])
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->pluck('id');

        $count = 0;
        foreach ($ids as $id) {
            $updated = DB::table('membership_payments')
                ->where('id', $id)
                ->whereIn('status', [
                    MembershipPaymentStatus::Pending->value,
                    MembershipPaymentStatus::UnderReview->value,
                ])
                ->update([
                    'status'     => MembershipPaymentStatus::Expired->value,
                    'updated_at' => now(),
                ]);

            if ($updated) {
                self::logEvent((int) $id, 'expired', 'system', null, []);
                $count++;
            }
        }

        return $count;
    }

    // ─────────────────────────────────────────────────────────────────────
    // Consultas
    // ─────────────────────────────────────────────────────────────────────

    public static function find(int $id): ?object
    {
        return DB::table('membership_payments')->where('id', $id)->first();
    }

    /** Consulta pública: por código, sin sesión (pantalla de estado). */
    public static function findByCode(string $publicCode): ?object
    {
        return DB::table('membership_payments')
            ->where('public_code', $publicCode)
            ->first();
    }

    /**
     * Bandeja de administración: lo que lleva más de X horas sin resolver.
     */
    public static function pendingReview(int $olderThanHours = 0): array
    {
        $q = DB::table('membership_payments')
            ->whereIn('status', [
                MembershipPaymentStatus::UnderReview->value,
                MembershipPaymentStatus::Pending->value,
            ])
            ->orderBy('updated_at');

        if ($olderThanHours > 0) {
            $q->where('updated_at', '<', now()->subHours($olderThanHours));
        }

        return $q->get()->all();
    }

    // ─────────────────────────────────────────────────────────────────────
    // Internos
    // ─────────────────────────────────────────────────────────────────────

    private static function requireOrder(int $id): object
    {
        $order = self::find($id);
        if (! $order) {
            throw new InvalidArgumentException("Pedido inexistente: {$id}");
        }
        return $order;
    }

    private static function assertTransition(object $order, MembershipPaymentStatus $to, array $from): void
    {
        if (! in_array($order->status, array_map(fn ($s) => $s->value, $from), true)) {
            throw new InvalidArgumentException(
                "Transición no permitida: '{$order->status}' → '{$to->value}'."
            );
        }
    }

    /**
     * Código público legible: L69-<PLAN>-<5 caracteres>.
     * Sin vocales ni caracteres ambiguos (0/O, 1/I/L).
     */
    private static function generatePublicCode(string $slug): string
    {
        $alphabet = '23456789ABCDEFGHJKMNPQRSTUVWXYZ';
        $suffix   = '';
        for ($i = 0; $i < 5; $i++) {
            $suffix .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }

        $tag = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $slug) ?: 'PLAN', 0, 4));

        return "L69-{$tag}-{$suffix}";
    }

    /** Centavos 1..99 deterministas a partir del código. */
    private static function discriminatorCents(string $code): int
    {
        return (crc32($code) % 99) + 1;
    }

    /**
     * Escribe en la bitácora. Nunca lanza: un fallo de auditoría no debe
     * tumbar un cobro ya aprobado.
     */
    private static function logEvent(
        int $orderId,
        string $type,
        string $actorType,
        ?string $actorId,
        array $payload
    ): void {
        try {
            DB::table('payment_events')->insert([
                'order_id'   => $orderId,
                'type'       => $type,
                'actor_type' => $actorType,
                'actor_id'   => $actorId,
                'payload'    => json_encode($payload),
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('payment_events: no se pudo registrar el evento', [
                'order_id' => $orderId,
                'type'     => $type,
                'error'    => $e->getMessage(),
            ]);
        }
    }
}
