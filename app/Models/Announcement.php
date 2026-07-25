<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class Announcement extends Model
{
    protected $table     = 'announcements';
    protected $keyType   = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'user_id',
        'title',
        'looking_for',
        'event_date',
        'proposal',
        'status',
        'directed_to',
        'what_looking',
        'expires_at',
    ];

    protected $casts = [
        'id'           => 'string',
        'user_id'      => 'string',
        'directed_to'  => 'array',
        'what_looking' => 'array',
        'event_date'   => 'date',
        'expires_at'   => 'datetime',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (Announcement $model): void {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    // ── Relaciones ──────────────────────────────────────────

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function profile(): \Illuminate\Database\Eloquent\Relations\HasOneThrough
    {
        return $this->hasOneThrough(
            Profile::class,
            User::class,
            'id',       // users.id
            'user_id',  // profiles.user_id
            'user_id',  // announcements.user_id
            'id'        // users.id
        );
    }

    // ── Scopes ───────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active')
                     ->where(function (Builder $q): void {
                         $q->whereNull('expires_at')
                           ->orWhere('expires_at', '>', now());
                     });
    }

    public function scopeByUser(Builder $query, string $userId): Builder
    {
        return $query->where('user_id', $userId);
    }
}