<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Services\MembershipAccessService;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'users';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id', 'email', 'username', 'password', 'name', 'phone', 'role',
        'age_verified', 'terms_accepted', 'email_verified_at', 'active',
        'last_login_at', 'membership_type', 'membership_expires_at',
        'membership_started_at', 'trial_started_at',
        'verification_status', 'referral_code', 'referred_by',
        'referral_count', 'referral_paid_count', 'last_seen_at',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'id'                    => 'string',
        'age_verified'          => 'boolean',
        'terms_accepted'        => 'boolean',
        'active'                => 'boolean',
        'email_verified_at'     => 'datetime',
        'last_login_at'         => 'datetime',
        'membership_expires_at' => 'datetime',
        'membership_started_at' => 'datetime',
        'trial_started_at'      => 'datetime',
        'last_seen_at'          => 'datetime',
        'password_changed'      => 'boolean',
        'password_changed_at' => 'datetime',
        'welcomed_at'          => 'datetime',
        'created_at'            => 'datetime',
        'updated_at'            => 'datetime',
        'referral_count'        => 'integer',
        'referral_paid_count'   => 'integer',
    ];

    // ─── Relaciones ──────────────────────────────────────────────────────────

    public function profile()
    {
        return $this->hasOne(Profile::class, 'user_id', 'id');
    }

    public function verification(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Verification::class, 'user_id', 'id');
    }

    public function followers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Follow::class, 'following_id', 'id');
    }

    public function following(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Follow::class, 'follower_id', 'id');
    }

    public function announcements(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Announcement::class, 'user_id', 'id');
    }

    public function notifications(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Notification::class, 'user_id', 'id');
    }

    public function memberships(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Membership::class, 'user_id', 'id');
    }

    public function activeMembership(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Membership::class, 'user_id', 'id')
                    ->where('status', 'active')
                    ->latest('started_at');
    }

    public function referralCode(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ReferralCode::class, 'owner_user_id', 'id');
    }

    // ─── Helpers de membresía ────────────────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function membershipTier(): string
    {
        return $this->membership_type ?? 'invitado';
    }

    public function isMember(): bool
    {
        return !in_array($this->membershipTier(), ['invitado']);
    }

    public function isFundador(): bool
    {
        return $this->membershipTier() === 'Fundador';
    }

    public function membershipExpiresSoon(int $days = 7): bool
    {
        return app(MembershipAccessService::class)->expiresSoon($this, $days);
    }

    /** Verificar acceso a feature desde Blade o controladores */
    public function canAccess(string $feature): bool
    {
        return app(MembershipAccessService::class)->can($this, $feature);
    }

    /** Límite numérico para una feature */
    public function membershipLimit(string $key): ?int
    {
        return app(MembershipAccessService::class)->limit($this, $key);
    }
}


