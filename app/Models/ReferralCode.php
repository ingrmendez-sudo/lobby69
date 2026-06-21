<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ReferralCode extends Model
{
    protected $table = 'referral_codes';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id', 'code', 'owner_user_id', 'max_uses', 'uses_count',
        'is_active', 'expires_at',
    ];

    protected $casts = [
        'id' => 'string',
        'max_uses' => 'integer',
        'uses_count' => 'integer',
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function isValid(): bool
    {
        if (!$this->is_active) return false;
        if ($this->uses_count >= $this->max_uses) return false;
        if ($this->expires_at && now()->gt($this->expires_at)) return false;
        return true;
    }
}
