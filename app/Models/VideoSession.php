<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoSession extends Model
{
    protected $fillable = [
        'initiator_id',
        'receiver_id',
        'session_token',
        'type',
        'max_duration_minutes',
        'started_at',
        'ended_at',
        'actual_minutes',
        'ended_by',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at'   => 'datetime',
    ];

    // initiator_id y receiver_id son UUID strings
    public function initiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiator_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function isActive(): bool
    {
        return is_null($this->ended_at);
    }

    public function durationSeconds(): int
    {
        $end = $this->ended_at ?? now();
        return (int) $this->started_at->diffInSeconds($end);
    }
}
