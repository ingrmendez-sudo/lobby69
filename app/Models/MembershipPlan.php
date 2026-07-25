<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MembershipPlan extends Model
{
    protected $table = 'membership_plans';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'slug', 'name', 'description',
        'price_promo', 'price_normal',
        'duration_days', 'is_lifetime',
        'is_active', 'promo_active', 'sort_order',
    ];

    protected $casts = [
        'price_promo'   => 'decimal:2',
        'price_normal'  => 'decimal:2',
        'duration_days' => 'integer',
        'is_lifetime'   => 'boolean',
        'is_active'     => 'boolean',
        'promo_active'  => 'boolean',
        'sort_order'    => 'integer',
    ];

    /** Precio efectivo según promoción activa */
    public function effectivePrice(): float
    {
        return (float) ($this->promo_active ? $this->price_promo : $this->price_normal);
    }

    /** Obtener plan por slug con caché de 1 hora */
    public static function findBySlug(string $slug): ?self
    {
        return cache()->remember("plan.{$slug}", 3600, fn() =>
            static::where('slug', $slug)->where('is_active', true)->first()
        );
    }

    /** Todos los planes activos ordenados */
    public static function allActive(): \Illuminate\Support\Collection
    {
        return cache()->remember('plans.all_active', 3600, fn() =>
            static::where('is_active', true)->orderBy('sort_order')->get()
        );
    }
}