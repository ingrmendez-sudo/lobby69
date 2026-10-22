<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class MembershipService
{
    /**
     * Niveles jerárquicos — alineados EXACTAMENTE al CHECK de la tabla:
     *   memberships_tier_check → invitado, explorer, connectors, influencer,
     *                            vip_elite, vitalicio
     *
     * CORREGIDO: se eliminaron 'trial' y 'fundador' (no existen en el CHECK y
     * un INSERT con ellos falla) y se agregó 'vitalicio' (sí existe, pero antes
     * se evaluaba como nivel 0, igual que invitado).
     *
     * El nombre comercial "Fundador" corresponde al slug 'vitalicio'.
     */
    public const LEVELS = [
        'invitado'   => 0,
        'explorer'   => 1,
        'connectors' => 2,
        'influencer' => 3,
        'vip_elite'  => 4,
        'vitalicio'  => 5,
    ];

    /** Clave de caché única. Es la que lee getPlan() y la que invalida activateForUser(). */
    public static function cacheKey($userId): string
    {
        return "membership_plan_{$userId}";
    }

    /**
     * Obtener el plan activo del usuario con sus features.
     * Cache de 5 minutos por usuario.
     */
    public static function getPlan($userId): object
    {
        return Cache::remember(self::cacheKey($userId), 300, function () use ($userId) {

            // Prioridad 1: tabla memberships (suscripción activa)
            $membership = DB::table('memberships')
                ->where('user_id', $userId)
                ->where('status', 'active')
                ->whereRaw("(expires_at IS NULL OR expires_at > NOW())")
                ->orderByDesc('created_at')
                ->first();

            // Prioridad 2: users.membership_type como fallback
            $userMembership = DB::table('users')->where('id', $userId)->value('membership_type');

            // Usar el tier más alto entre memberships y users
            $tierA = $membership->tier ?? 'invitado';
            $tierB = $userMembership ?? 'invitado';
            $slug  = (self::LEVELS[$tierA] ?? 0) >= (self::LEVELS[$tierB] ?? 0) ? $tierA : $tierB;

            $plan = DB::table('membership_plans')->where('slug', $slug)->first();
            if (! $plan) {
                $plan = DB::table('membership_plans')->where('slug', 'invitado')->first();
                $slug = 'invitado';
            }

            $features = json_decode($plan->features ?? '{}', true) ?? [];

            return (object) [
                'slug'     => $slug,
                'features' => $features,
                'plan'     => $plan,
            ];
        });
    }

    /**
     * Verificar un permiso booleano.
     * Uso: MembershipService::can($user->id, 'can_video_call')
     */
    public static function can($userId, string $permission): bool
    {
        $plan     = self::getPlan($userId);
        $features = is_string($plan->features)
            ? json_decode($plan->features, true)
            : (array) $plan->features;
        return (bool) ($features[$permission] ?? false);
    }

    /**
     * Obtener un límite numérico.
     * Uso: MembershipService::limit($user->id, 'max_photos')
     */
    public static function limit($userId, string $key): int
    {
        $plan = self::getPlan($userId);
        return (int) ($plan->features[$key] ?? 0);
    }

    /**
     * Verificar si el usuario excedió un límite diario.
     * Uso: MembershipService::exceededDaily($user->id, 'max_messages_day')
     */
    public static function exceededDaily($userId, string $limitKey): bool
    {
        $limit = self::limit($userId, $limitKey);
        if ($limit >= 999) return false; // ilimitado

        $count = DB::table('messages')
            ->where('sender_id', $userId)
            ->whereDate('created_at', today())
            ->count();

        return $count >= $limit;
    }

    /**
     * Obtener el slug/tier del plan activo.
     *
     * CORREGIDO: antes leía ->active_slug, propiedad que getPlan() nunca
     * devolvía, así que retornaba 'invitado' SIEMPRE. Eso hacía que
     * hasMinLevel() negativa el acceso a todos, incluidos los VIP.
     */
    public static function getSlug($userId): string
    {
        return self::getPlan($userId)->slug ?? 'invitado';
    }

    /**
     * Verificar si el usuario está en período de gracia.
     */
    public static function inGracePeriod($userId): bool
    {
        $user = DB::table('users')->where('id', $userId)->first();
        if (! $user) return false;

        $gracePeriod = self::limit($userId, 'grace_period_hours');
        if ($gracePeriod <= 0) return false;

        $createdAt = \Carbon\Carbon::parse($user->created_at);
        return $createdAt->diffInHours(now()) < $gracePeriod;
    }

    /**
     * Invalidar cache del plan de un usuario.
     * Llamar siempre que se cambie la membresía de un usuario.
     */
    public static function clearCache($userId): void
    {
        Cache::forget(self::cacheKey($userId));
    }

    /**
     * Resumen completo del plan para vistas/API.
     *
     * CORREGIDO: leía $plan->name y $plan->active_slug, que no existen en el
     * objeto devuelto por getPlan(). Ahora usa el objeto anidado.
     */
    public static function summary($userId): array
    {
        $plan = self::getPlan($userId);

        return [
            'plan'                    => $plan->slug,
            'plan_name'               => $plan->plan->name ?? null,
            'max_photos'              => $plan->features['max_photos']              ?? 0,
            'max_videos'              => $plan->features['max_videos']              ?? 0,
            'max_messages_day'        => $plan->features['max_messages_day']        ?? 0,
            'max_direct_messages_day' => $plan->features['max_direct_messages_day'] ?? 0,
            'can_view_private_photos' => $plan->features['can_view_private_photos'] ?? false,
            'can_video_call'          => $plan->features['can_video_call']          ?? false,
            'can_see_visitors'        => $plan->features['can_see_visitors']        ?? false,
            'can_send_friend_request' => $plan->features['can_send_friend_request'] ?? false,
            'in_grace_period'         => self::inGracePeriod($userId),
        ];
    }

    /**
     * Verificar si el usuario tiene al menos el nivel indicado.
     * Uso: MembershipService::hasMinLevel($userId, 'vip_elite')
     */
    public static function hasMinLevel($userId, string $minTier): bool
    {
        $userSlug  = self::getSlug($userId);
        $userLevel = self::LEVELS[$userSlug] ?? 0;
        $minLevel  = self::LEVELS[$minTier]  ?? 0;
        return $userLevel >= $minLevel;
    }
}
