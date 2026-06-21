<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class Account extends Authenticatable
{
    use Notifiable;

    protected $table = 'accounts';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id', 'auth_user_id', 'email', 'password', 'status',
        'is_active', 'email_verified', 'last_login_at', 'last_seen_at',
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'id' => 'string',
        'is_active' => 'boolean',
        'email_verified' => 'boolean',
        'last_login_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
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

    public function profile()
    {
        return $this->hasOne(Profile::class, 'account_id', 'id');
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'account_id', 'id');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin' || $this->role === 'superadmin';
    }

    public function activeSubscription()
    {
        return $this->subscriptions()
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->first();
    }
}
