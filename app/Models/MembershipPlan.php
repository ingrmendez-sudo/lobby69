<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class MembershipPlan extends Model
{
    protected $table = 'membership_plans';

    protected $fillable = [
        'slug', 'name', 'description',
        'price_promo', 'price_normal',
        'duration_days', 'is_lifetime',
        'is_active', 'promo_active',
        'sort_order', 'features',
    ];

    protected $casts = [
        'price_promo'   => 'decimal:2',
        'price_normal'  => 'decimal:2',
        'duration_days' => 'integer',
        'is_lifetime'   => 'boolean',
        'is_active'     => 'boolean',
        'promo_active'  => 'boolean',
        'features'      => 'array',
    ];

    // ── Precio activo según promo ────────────────────────────────────────
    public function getActivePriceAttribute(): float
    {
        return $this->promo_active
            ? (float) $this->price_promo
            : (float) $this->price_normal;
    }

    // ── Porcentaje de descuento ──────────────────────────────────────────
    public function getDiscountPercentAttribute(): int
    {
        if (! $this->promo_active || $this->price_normal <= 0) return 0;
        return (int) round((1 - $this->price_promo / $this->price_normal) * 100);
    }

    // ── Es gratis? ───────────────────────────────────────────────────────
    public function getIsFreeAttribute(): bool
    {
        return $this->active_price == 0;
    }

    // ── Etiqueta de duración ─────────────────────────────────────────────
    public function getDurationLabelAttribute(): string
    {
        if ($this->is_lifetime)          return 'único pago';
        if ($this->duration_days >= 365) return 'por año';
        if ($this->duration_days >= 180) return '6 meses';
        if ($this->duration_days >= 90)  return '3 meses';
        return 'por mes';
    }

    // ── Finders con caché ────────────────────────────────────────────────
    public static function findBySlug(string $slug): ?self
    {
        return Cache::remember("plan.{$slug}", 3600, fn() =>
            static::where('slug', $slug)->where('is_active', true)->first()
        );
    }

    public static function allActive()
    {
        return Cache::remember('plans.active', 3600, fn() =>
            static::where('is_active', true)->orderBy('sort_order')->get()
        );
    }

    // ── Precio para activateForUser ──────────────────────────────────────
    public function getPriceAttribute(): float
    {
        return $this->active_price;
    }
}
