<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

/**
 * MembershipAccessService
 *
 * Punto único de verdad para control de acceso por nivel de membresía.
 * Todos los controllers y middleware deben usar este servicio.
 *
 * Niveles (de menor a mayor):
 *   invitado < explorer < connectors < influencer < vip_elite < vitalicio
 */
class MembershipAccessService
{
    // Jerarquía de niveles: mayor número = mayor acceso
    private const LEVELS = [
        'invitado'   => 0,
        'explorer'   => 1,
        'connectors' => 2,
        'influencer' => 3,
        'vip_elite'  => 4,
        'vitalicio'  => 5,
    ];

    // Límites por feature y nivel
    private const LIMITS = [
        // Cuántos videos puede ver por día (null = ilimitado)
        'daily_videos' => [
            'invitado'   => 3,
            'explorer'   => 10,
            'connectors' => null,
            'influencer' => null,
            'vip_elite'  => null,
            'vitalicio'  => null,
        ],
        // Cuántas fotos puede subir por mes (null = ilimitado)
        'monthly_photos' => [
            'invitado'   => 5,
            'explorer'   => 20,
            'connectors' => 50,
            'influencer' => null,
            'vip_elite'  => null,
            'vitalicio'  => null,
        ],
        // Cuántas historias puede publicar por mes
        'monthly_stories' => [
            'invitado'   => 1,
            'explorer'   => 1,
            'connectors' => null,
            'influencer' => null,
            'vip_elite'  => null,
            'vitalicio'  => null,
        ],
        // Cuántos mensajes directos puede enviar por día
        'daily_messages' => [
            'invitado'   => 0,
            'explorer'   => 5,
            'connectors' => 30,
            'influencer' => null,
            'vip_elite'  => null,
            'vitalicio'  => null,
        ],
    ];

    // Acceso a features (boolean por nivel mínimo requerido)
    private const FEATURES = [
        'chat_general'     => 'invitado',   // sala general: todos
        'chat_private'     => 'explorer',   // chat privado: desde explorer
        'video_chat'       => 'connectors', // videochat: desde connectors
        'view_profiles'    => 'invitado',   // ver perfiles: todos
        'follow_users'     => 'explorer',   // seguir usuarios: desde explorer
        'publish_photos'   => 'explorer',   // subir fotos: desde explorer
        'publish_videos'   => 'connectors', // subir videos: desde connectors
        'publish_stories'  => 'invitado',   // historias: todos (con límite)
        'publish_announce' => 'explorer',   // anuncios: desde explorer
        'view_announces'   => 'invitado',   // ver anuncios: todos
        'view_reviews'     => 'invitado',   // ver reseñas: todos
        'write_reviews'    => 'connectors', // escribir reseñas: desde connectors
        'view_visitors'    => 'explorer',   // ver quién visitó mi perfil
        'priority_search'  => 'influencer', // aparecer primero en búsquedas
        'verified_badge'   => 'connectors', // badge verificado visible
        'no_ads'           => 'explorer',   // sin publicidad
    ];

    // ─── Helpers privados ────────────────────────────────────────────────────

    private function getLevel(string $tier): int
    {
        return self::LEVELS[$tier] ?? 0;
    }

    private function getUserTier(User $user): string
    {
        return Cache::remember(
            "user.membership.{$user->id}",
            300, // 5 minutos de caché
            fn() => $this->resolveActiveTier($user)
        );
    }

    private function resolveActiveTier(User $user): string
    {
        $tier = $user->membership_type ?? 'invitado';

        // Verificar si la membresía expiró
        if (!in_array($tier, ['invitado', 'vitalicio'])) {
            $expires = $user->membership_expires_at;
            if ($expires && now()->gt($expires)) {
                // Expiró → degradar a invitado y actualizar BD
                \DB::table('users')->where('id', $user->id)->update([
                    'membership_type' => 'invitado',
                    'updated_at'      => now(),
                ]);
                return 'invitado';
            }
        }

        return in_array($tier, array_keys(self::LEVELS)) ? $tier : 'invitado';
    }

    // ─── API Pública ─────────────────────────────────────────────────────────

    /** Tier activo del usuario (con caché) */
    public function tier(User $user): string
    {
        return $this->getUserTier($user);
    }

    /** ¿Tiene acceso a una feature? */
    public function can(User $user, string $feature): bool
    {
        $required = self::FEATURES[$feature] ?? 'vitalicio';
        $userLevel = $this->getLevel($this->getUserTier($user));
        $requiredLevel = $this->getLevel($required);
        return $userLevel >= $requiredLevel;
    }

    /** Nivel mínimo requerido para una feature (para mostrar en UI) */
    public function requiredTierFor(string $feature): string
    {
        return self::FEATURES[$feature] ?? 'vitalicio';
    }

    /** Límite numérico para una feature (null = ilimitado) */
    public function limit(User $user, string $limitKey): ?int
    {
        $tier = $this->getUserTier($user);
        return self::LIMITS[$limitKey][$tier] ?? 0;
    }

    /** ¿Es un nivel mayor o igual al requerido? */
    public function hasMinLevel(User $user, string $minTier): bool
    {
        return $this->getLevel($this->getUserTier($user)) >= $this->getLevel($minTier);
    }

    /** Limpiar caché de un usuario (llamar después de cambiar membresía) */
    public function clearCache(string $userId): void
    {
        Cache::forget("user.membership.{$userId}");
    }

    /** Respuesta estándar de acceso denegado para APIs */
    public function denyResponse(string $feature): \Illuminate\Http\JsonResponse
    {
        $required = $this->requiredTierFor($feature);
        return response()->json([
            'error'          => 'membership_required',
            'message'        => 'Tu membresía actual no permite acceder a esta función.',
            'required_tier'  => $required,
            'upgrade_url'    => '/membresias',
        ], 403);
    }

    /** Obtener todos los niveles disponibles con sus datos */
    public function allLevels(): array
    {
        return array_keys(self::LEVELS);
    }

    /** ¿La membresía expira pronto? (dentro de N días) */
    public function expiresSoon(User $user, int $days = 7): bool
    {
        $expires = $user->membership_expires_at;
        if (!$expires) return false;
        return now()->diffInDays($expires, false) <= $days && now()->lt($expires);
    }
}