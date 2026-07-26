<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class Membership extends Model
{
    protected $table      = 'memberships';
    protected $keyType    = 'string';
    public    $incrementing = false;

    protected $fillable = [
        'id', 'user_id', 'tier', 'price', 'currency',
        'payment_method', 'transaction_id',
        'started_at', 'expires_at',
        'auto_renew', 'status', 'features',
    ];

    protected $casts = [
        'id'         => 'string',
        'user_id'    => 'string',
        'price'      => 'decimal:2',
        'auto_renew' => 'boolean',
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($m) {
            if (empty($m->id)) {
                $m->id = (string) Str::uuid();
            }
        });
    }

    // ── Relaciones ───────────────────────────────────────────────────────
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    // ── Helpers de estado ────────────────────────────────────────────────
    public function isActive(): bool
    {
        if ($this->status !== 'active') return false;
        if (is_null($this->expires_at))  return true;
        return $this->expires_at->isFuture();
    }

    public function isExpired(): bool
    {
        if (is_null($this->expires_at)) return false;
        return $this->expires_at->isPast();
    }

    // ── Activación centralizada ──────────────────────────────────────────
    /**
     * Crear o reemplazar membresía activa de un usuario.
     *
     * @param  string       $userId        UUID del usuario (varchar o uuid)
     * @param  string       $tier          Slug del plan
     * @param  int|null     $durationDays  null = vitalicio
     * @param  float        $price
     * @param  string       $paymentMethod
     * @param  string       $transactionId
     */
    public static function activateForUser(
        string $userId,
        string $tier,
        ?int   $durationDays,
        float  $price         = 0,
        string $paymentMethod = 'manual',
        string $transactionId = ''
    ): self {
        // Normalizar UUID — quitar espacios y forzar formato limpio
        $userId = trim($userId);

        // Validar que sea un UUID válido antes de continuar
        if (! preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $userId)) {
            throw new \InvalidArgumentException("user_id no es un UUID válido: {$userId}");
        }

        $expiresAt = $durationDays ? now()->addDays($durationDays) : null;

        // 1. Marcar membresía anterior como superseded usando SQL directo con cast
        DB::statement("
            UPDATE memberships
            SET status = 'superseded', updated_at = NOW()
            WHERE user_id = ?::uuid
            AND status = 'active'
        ", [$userId]);

        // 2. Insertar nueva membresía usando SQL directo para controlar el cast uuid
        $newId = (string) Str::uuid();

        DB::statement("
            INSERT INTO memberships
                (id, user_id, tier, price, currency, payment_method, transaction_id,
                 started_at, expires_at, auto_renew, status, created_at, updated_at)
            VALUES
                (?::uuid, ?::uuid, ?, ?, 'MXN', ?, ?, NOW(), ?, false, 'active', NOW(), NOW())
        ", [
            $newId,
            $userId,
            $tier,
            $price,
            $paymentMethod,
            $transactionId ?: null,
            $expiresAt?->toDateTimeString(),
        ]);

        // 3. Actualizar columna de conveniencia en users con cast explícito
        DB::statement("
            UPDATE users
            SET membership_type        = ?,
                membership_expires_at  = ?,
                membership_started_at  = NOW(),
                updated_at             = NOW()
            WHERE id = ?::uuid
        ", [
            $tier,
            $expiresAt?->toDateTimeString(),
            $userId,
        ]);

        // 4. Limpiar caché del usuario
        cache()->forget("user.membership.{$userId}");

        // 5. Retornar el modelo recién creado
        return static::find($newId);
    }
}
