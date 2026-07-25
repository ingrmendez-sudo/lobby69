<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class Notification extends Model
{
    protected $table     = 'notifications';
    protected $keyType   = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'user_id',
        'type',
        'data',
        'read_at',
    ];

    protected $casts = [
        'id'         => 'string',
        'user_id'    => 'string',
        'data'       => 'array',   // cast automático JSON <-> array
        'read_at'    => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (Notification $model): void {
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

    // ── Scopes ───────────────────────────────────────────────

    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    public function scopeForUser(Builder $query, string $userId): Builder
    {
        return $query->whereRaw('user_id::text = ?', [$userId]);
    }

    // ── Métodos de utilidad ──────────────────────────────────

    public function getIsReadAttribute(): bool
    {
        return $this->read_at !== null;
    }

    /**
     * Factory estático — reemplaza el DB::table directo en NotificationController.
     * Mantiene compatibilidad total con el código existente.
     */
    public static function dispatch(string $userId, string $type, array $data): void
    {
        try {
            static::create([
                'id'      => (string) Str::uuid(),
                'user_id' => $userId,
                'type'    => $type,
                'data'    => $data, // el cast 'array' lo serializa automáticamente
            ]);
        } catch (\Exception $e) {
            // Silencioso — las notificaciones no deben romper el flujo principal
            \Illuminate\Support\Facades\Log::warning('Notification dispatch failed: ' . $e->getMessage());
        }
    }
}