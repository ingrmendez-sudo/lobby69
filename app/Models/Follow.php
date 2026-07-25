<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Follow extends Model
{
    protected $table      = 'follows';
    protected $keyType    = 'string';
    public $incrementing  = false;

    // La tabla no tiene updated_at
    const UPDATED_AT = null;

    protected $fillable = [
        'id',
        'follower_id',
        'following_id',
    ];

    protected $casts = [
        'id'           => 'string',
        'follower_id'  => 'string',
        'following_id' => 'string',
        'created_at'   => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (Follow $model): void {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    // ── Relaciones ──────────────────────────────────────────

    public function follower(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'follower_id', 'id');
    }

    public function following(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'following_id', 'id');
    }
}