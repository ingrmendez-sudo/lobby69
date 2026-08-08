<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Availability extends Model
{
    protected $table = 'availability';

    protected $fillable = [
        'user_id',
        'duration_hours',
        'expires_at',
        'message',
        'notify_followers',
    ];

    protected $casts = [
        'expires_at'        => 'datetime',
        'notify_followers'  => 'boolean',
    ];

    // ── Relaciones ────────────────────────────────────────────────────
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ── Scopes ────────────────────────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('expires_at', '>', now());
    }

    // ── Helpers ───────────────────────────────────────────────────────
    public function isActive(): bool
    {
        return $this->expires_at->isFuture();
    }

    public function minutesRemaining(): int
    {
        return max(0, (int) now()->diffInMinutes($this->expires_at, false));
    }

    public function humanTimeRemaining(): string
    {
        $mins = $this->minutesRemaining();
        if ($mins <= 0)   return 'Expirado';
        if ($mins < 60)   return "{$mins} min restantes";
        $hrs = floor($mins / 60);
        $rem = $mins % 60;
        return $rem > 0 ? "{$hrs}h {$rem}min restantes" : "{$hrs}h restantes";
    }
}