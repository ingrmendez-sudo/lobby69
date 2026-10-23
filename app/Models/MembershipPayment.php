<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MembershipPayment extends Model
{
    // ❌ ELIMINAR esta línea del trait:
    // use HasUuids;

    protected $table = 'membership_payments';

    protected $fillable = [
        'user_id',
        'requested_membership',
        'current_membership',
        'amount',
        'currency',
        'payment_method',
        'payment_reference',
        'receipt_path',
        'status',
        'admin_note',
        'reviewed_by',
        'reviewed_at',
        // ── Cobro por SPEI (columnas de 2026_10_21_spei_final.sql) ──────
        // Sin estas claves en $fillable, create()/update() las descarta en
        // silencio y la orden se guarda sin referencia ni vencimiento.
        'public_code',
        'email',
        'expires_at',
        'bank_tracking_key',
        'payer_name',
        'payer_bank',
        'plan_snapshot',
        'referral_code',
        'metadata',
    ];

    protected $casts = [
        'amount'        => 'decimal:2',
        'reviewed_at'   => 'datetime',
        'expires_at'    => 'datetime',
        'plan_snapshot' => 'array',
        'metadata'      => 'array',
    ];

    // ── Relaciones ──────────────────────────────────────────────────────
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function plan(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(MembershipPlan::class, 'requested_membership', 'slug');
    }

    // ── Scopes ───────────────────────────────────────────────────────────
    public function scopePending($q)  { return $q->where('status', 'pending');  }
    public function scopeApproved($q) { return $q->where('status', 'approved'); }
    public function scopeRejected($q) { return $q->where('status', 'rejected'); }

    // ── Helpers ──────────────────────────────────────────────────────────
    public function isPending():  bool { return $this->status === 'pending';  }
    public function isApproved(): bool { return $this->status === 'approved'; }
    public function isRejected(): bool { return $this->status === 'rejected'; }

    public function statusColor(): string
    {
        return match($this->status) {
            'approved'     => '#22c55e',
            'rejected'     => '#ef4444',
            'under_review' => '#3b82f6',
            'expired'      => '#6b7280',
            default        => '#f59e0b',
        };
    }

    public function statusLabel(): string
    {
        return match($this->status) {
            'approved'     => 'Aprobado',
            'rejected'     => 'Rechazado',
            'under_review' => 'En revisión',
            'expired'      => 'Vencido',
            default        => 'Pendiente',
        };
    }

    public function isUnderReview(): bool { return $this->status === 'under_review'; }
    public function isExpired(): bool     { return $this->status === 'expired'; }

    /** ¿La ventana de pago SPEI sigue abierta? */
    public function isWindowOpen(): bool
    {
        return $this->status === 'pending'
            && $this->expires_at !== null
            && $this->expires_at->isFuture();
    }
}
