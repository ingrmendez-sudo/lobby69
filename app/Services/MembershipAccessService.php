<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * MembershipAccessService — Wrapper sobre MembershipService
 *
 * Mantiene la interfaz original para no romper MessagesController
 * ni otros consumers existentes. Delega toda la lógica de features
 * y límites a MembershipService (fuente única de verdad desde DB).
 */
class MembershipAccessService
{
    // Jerarquía de niveles (solo para comparaciones de nivel mínimo)
    private const LEVELS = [
        'invitado'   => 0,
        'explorer'   => 1,
        'connectors' => 2,
        'influencer' => 3,
        'vip_elite'  => 4,
        'vitalicio'  => 5,
    ];

    // Mapa: feature antigua -> key en features JSON de membership_plans
    private const FEATURE_MAP = [
        'chat_general'     => null,             // todos tienen acceso
        'chat_private'     => 'can_send_friend_request',
        'video_chat'       => 'can_video_call',
        'view_profiles'    => null,             // todos tienen acceso
        'follow_users'     => 'can_send_friend_request',
        'publish_photos'   => 'max_photos',     // acceso si max_photos > 0
        'publish_videos'   => 'max_videos',     // acceso si max_videos > 0
        'publish_stories'  => null,             // todos tienen acceso
        'publish_announce' => 'can_send_friend_request',
        'view_announces'   => null,             // todos tienen acceso
        'view_reviews'     => null,             // todos tienen acceso
        'write_reviews'    => 'can_send_friend_request',
        'view_visitors'    => 'can_see_visitors',
        'priority_search'  => 'profile_boost',
        'verified_badge'   => 'can_send_friend_request',
        'no_ads'           => 'can_send_friend_request',
    ];

    // Mapa: limitKey antiguo -> key en features JSON
    private const LIMIT_MAP = [
        'daily_messages'  => 'max_messages_day',
        'daily_videos'    => 'max_videos',
        'monthly_photos'  => 'max_photos',
        'monthly_stories' => null,
    ];

    // ─── API Pública (interfaz original preservada) ──────────────────────────

    /** Tier activo del usuario */
    public function tier(User $user): string
    {
        return MembershipService::getSlug((string) $user->id);
    }

    /** ¿Tiene acceso a una feature? */
    public function can(User $user, string $feature): bool
    {
        // Features que todos tienen
        if (self::FEATURE_MAP[$feature] === null) return true;

        $key = self::FEATURE_MAP[$feature] ?? null;
        if (!$key) return false;

        // Features numéricas: acceso si el límite es > 0
        if (str_starts_with($key, 'max_')) {
            return MembershipService::limit((string) $user->id, $key) > 0;
        }

        return MembershipService::can((string) $user->id, $key);
    }

    /** Límite numérico para una feature (null = ilimitado) */
    public function limit(User $user, string $limitKey): ?int
    {
        $key = self::LIMIT_MAP[$limitKey] ?? null;
        if ($key === null) return null; // sin límite definido

        $value = MembershipService::limit((string) $user->id, $key);

        // 9999 = ilimitado en nuestro sistema
        if ($value >= 999) return null;

        return $value > 0 ? $value : 0;
    }

    /** Nivel mínimo requerido para una feature */
    public function requiredTierFor(string $feature): string
    {
        // Determinar tier mínimo leyendo los planes en orden
        $key = self::FEATURE_MAP[$feature] ?? null;
        if ($key === null) return 'invitado';

        $plans = DB::table('membership_plans')
            ->orderBy('sort_order')
            ->get();

        foreach ($plans as $plan) {
            $features = json_decode($plan->features ?? '{}', true) ?? [];
            $val = $features[$key] ?? false;
            $hasAccess = str_starts_with($key, 'max_') ? ($val > 0) : (bool) $val;
            if ($hasAccess) return $plan->slug;
        }

        return 'vitalicio';
    }

    /** ¿Es un nivel mayor o igual al requerido? */
    public function hasMinLevel(User $user, string $minTier): bool
    {
        $userLevel = self::LEVELS[MembershipService::getSlug((string) $user->id)] ?? 0;
        $minLevel  = self::LEVELS[$minTier] ?? 0;
        return $userLevel >= $minLevel;
    }

    /** Limpiar caché de un usuario */
    public function clearCache(string $userId): void
    {
        MembershipService::clearCache($userId);
        Cache::forget("user.membership.{$userId}");
    }

    /** Respuesta estándar de acceso denegado para APIs */
    public function denyResponse(string $feature): \Illuminate\Http\JsonResponse
    {
        $required = $this->requiredTierFor($feature);
        return response()->json([
            'error'         => 'membership_required',
            'message'       => 'Tu membresía actual no permite acceder a esta función.',
            'required_tier' => $required,
            'upgrade_url'   => '/membresias',
        ], 403);
    }

    /** ¿La membresía expira pronto? (dentro de N días) */
    public function expiresSoon(User $user, int $days = 7): bool
    {
        $membership = DB::table('memberships')
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->whereNotNull('expires_at')
            ->first();

        if (!$membership) return false;

        $expires = \Carbon\Carbon::parse($membership->expires_at);
        return now()->diffInDays($expires, false) <= $days && now()->lt($expires);
    }

    /** Obtener todos los niveles disponibles */
    public function allLevels(): array
    {
        return array_keys(self::LEVELS);
    }
}
