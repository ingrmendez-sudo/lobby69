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
    ];

    protected $casts = [
        'amount'      => 'decimal:2',
        'reviewed_at' => 'datetime',
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
            'approved' => '#22c55e',
            'rejected' => '#ef4444',
            default    => '#f59e0b',
        };
    }

    public function statusLabel(): string
    {
        return match($this->status) {
            'approved' => 'Aprobado',
            'rejected' => 'Rechazado',
            default    => 'Pendiente',
        };
    }
}
