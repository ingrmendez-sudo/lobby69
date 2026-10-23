<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Bitácora de membership_payments.
 *
 * La tabla es append-only a nivel de base (triggers payment_events_no_update
 * y payment_events_no_delete): nunca se edita ni se borra un evento, se
 * inserta uno nuevo. Por eso aquí no se usa Eloquent save() sino insert().
 */
class PaymentEvent extends Model
{
    protected $table = 'payment_events';

    /** La tabla solo tiene created_at, no updated_at. */
    public $timestamps = false;

    protected $fillable = [
        'order_id', 'type', 'actor_type', 'actor_id',
        'payload', 'ip', 'user_agent', 'created_at',
    ];

    protected $casts = [
        'payload'    => 'array',
        'created_at' => 'datetime',
    ];

    /** Tipos permitidos por el CHECK payment_events_type_check. */
    public const TYPES = [
        'created', 'proof_uploaded', 'review_started', 'approved',
        'rejected', 'expired', 'email_sent', 'reminder_sent', 'note_added',
    ];

    public function payment(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(MembershipPayment::class, 'order_id');
    }

    /**
     * Registrar un evento.
     *
     * Nunca lanza excepción: un fallo de bitácora no debe tumbar una acción
     * del usuario que ya se completó. Si el tipo no está en la lista blanca se
     * guarda como 'note_added' conservando el original en payload.
     */
    public static function log(
        int $orderId,
        string $type,
        string $actorType = 'system',
        ?string $actorId = null,
        array $payload = []
    ): void {
        if (! in_array($type, self::TYPES, true)) {
            $payload = array_merge($payload, ['requested_type' => $type]);
            $type    = 'note_added';
        }

        if (! in_array($actorType, ['system', 'customer', 'admin'], true)) {
            $actorType = 'system';
        }

        // CHECK payment_events_admin_actor_check: admin exige actor_id.
        if ($actorType === 'admin' && ($actorId === null || $actorId === '')) {
            $actorId = 'admin';
        }

        try {
            DB::table('payment_events')->insert([
                'order_id'   => $orderId,
                'type'       => $type,
                'actor_type' => $actorType,
                'actor_id'   => $actorId,
                'payload'    => json_encode($payload, JSON_UNESCAPED_UNICODE),
                'ip'         => request()->ip(),
                'user_agent' => substr((string) request()->userAgent(), 0, 500),
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

    /** Línea de tiempo de un pedido, de lo más reciente a lo más antiguo. */
    public static function timeline(int $orderId)
    {
        return static::where('order_id', $orderId)->orderByDesc('created_at')->get();
    }
}
