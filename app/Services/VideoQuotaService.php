<?php

namespace App\Services;

use App\Models\User;
use App\Models\VideoSession;

class VideoQuotaService
{
    /**
     * Duración máxima por sesión según membership_type.
     * Básico / Premium / Premium+ = 30 min
     * Vitalicio (lifetime)        = 50 min
     * Free / null                 = sin acceso
     */
    const SESSION_LIMITS = [
        'lifetime'     => 50,
        'premium_plus' => 30,
        'premium'      => 30,
        'basic'        => 30,
    ];

    const COOLDOWN_SECONDS = 120;

    public function canInitiateCall(User $user): array
    {
        $maxMinutes = self::SESSION_LIMITS[$user->membership_type] ?? null;

        if ($maxMinutes === null) {
            return [
                'allowed' => false,
                'reason'  => 'no_membership',
                'message' => 'Necesitas una membresía activa para usar videollamadas.',
            ];
        }

        $activeSession = VideoSession::where('initiator_id', $user->id)
            ->whereNull('ended_at')
            ->first();

        if ($activeSession) {
            return [
                'allowed' => false,
                'reason'  => 'already_in_call',
                'message' => 'Ya tienes una videollamada activa.',
            ];
        }

        $lastSession = VideoSession::where(function($q) use ($user) {
                $q->where('initiator_id', $user->id)
                  ->orWhere('receiver_id', $user->id);
            })
            ->whereNotNull('ended_at')
            ->orderByDesc('ended_at')
            ->first();

        if ($lastSession) {
            $secondsSinceLast = (int) $lastSession->ended_at->diffInSeconds(now());
            if ($secondsSinceLast < self::COOLDOWN_SECONDS) {
                $wait = self::COOLDOWN_SECONDS - $secondsSinceLast;
                return [
                    'allowed'      => false,
                    'reason'       => 'cooldown',
                    'message'      => "Espera {$wait} segundos antes de iniciar otra llamada.",
                    'wait_seconds' => $wait,
                ];
            }
        }

        return [
            'allowed'              => true,
            'max_duration_minutes' => $maxMinutes,
            'max_duration_seconds' => $maxMinutes * 60,
        ];
    }

    public function closeSession(string $token, string $endedBy = 'initiator'): bool
    {
        $session = VideoSession::where('session_token', $token)
            ->whereNull('ended_at')
            ->first();

        if (!$session) return false;

        $actualMinutes = (int) ceil(
            $session->started_at->diffInSeconds(now()) / 60
        );

        $session->update([
            'ended_at'       => now(),
            'actual_minutes' => $actualMinutes,
            'ended_by'       => $endedBy,
        ]);

        return true;
    }

    public function getSessionLimitMinutes(User $user): int
    {
        return self::SESSION_LIMITS[$user->membership_type] ?? 0;
    }
}
