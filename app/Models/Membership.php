<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Membership extends Model
{
    protected $table = 'memberships';
    protected $keyType = 'string';
    public $incrementing = false;

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

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function isActive(): bool
    {
        if ($this->status !== 'active') return false;
        if (is_null($this->expires_at))  return true; // vitalicio
        return $this->expires_at->isFuture();
    }

    public function isExpired(): bool
    {
        if (is_null($this->expires_at)) return false;
        return $this->expires_at->isPast();
    }

    /** Crear o reemplazar membresía activa de un usuario */
    public static function activateForUser(
        string $userId,
        string $tier,
        ?int   $durationDays,
        float  $price        = 0,
        string $paymentMethod = 'manual',
        string $transactionId = ''
    ): self {
        // Cancelar membresía anterior si existe
        static::where('user_id', $userId)
              ->where('status', 'active')
              ->update(['status' => 'superseded', 'updated_at' => now()]);

        $expiresAt = $durationDays ? now()->addDays($durationDays) : null;

        $membership = static::create([
            'user_id'        => $userId,
            'tier'           => $tier,
            'price'          => $price,
            'currency'       => 'MXN',
            'payment_method' => $paymentMethod,
            'transaction_id' => $transactionId,
            'started_at'     => now(),
            'expires_at'     => $expiresAt,
            'auto_renew'     => false,
            'status'         => 'active',
        ]);

        // Actualizar columna de conveniencia en users
        \DB::table('users')->where('id', $userId)->update([
            'membership_type'       => $tier,
            'membership_expires_at' => $expiresAt,
            'membership_started_at' => now(),
            'updated_at'            => now(),
        ]);

        // Limpiar caché del usuario
        cache()->forget("user.membership.{$userId}");

        return $membership;
    }
}