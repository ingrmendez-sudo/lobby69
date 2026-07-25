<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Verification extends Model
{
    protected $table = 'verifications';

    // PK es bigint autoincrement — comportamiento por defecto de Eloquent
    public $incrementing = true;
    protected $keyType   = 'int';

    protected $fillable = [
        'user_id',
        'selfie_path',
        'status',
        'admin_note',
        'reviewed_by',
        'reviewed_at',
        'attempt_number',
    ];

    protected $casts = [
        'reviewed_at'    => 'datetime',
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
        'attempt_number' => 'integer',
    ];

    // ── Relaciones ──────────────────────────────────────────

    /**
     * El usuario dueño de esta verificación.
     * user_id es varchar, users.id es uuid — usamos casteo explícito vía whereRaw
     * pero la relación Eloquent funciona si ambos son string en PHP.
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    // ── Scopes ───────────────────────────────────────────────

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', 'rejected');
    }

    // ── Accessors ────────────────────────────────────────────

    public function getIsApprovedAttribute(): bool
    {
        return $this->status === 'approved';
    }

    public function getIsPendingAttribute(): bool
    {
        return $this->status === 'pending';
    }

    public function getIsRejectedAttribute(): bool
    {
        return $this->status === 'rejected';
    }
}