<?php

namespace App\Http\Controllers;

use App\Models\Availability;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AvailabilityController extends Controller
{
    // Duraciones permitidas en horas
    const DURATIONS = [1, 2, 3, 4, 6, 8];

    /**
     * Activar o actualizar disponibilidad del usuario autenticado.
     */
    public function activate(Request $request)
    {
        $request->validate([
            'duration_hours'    => 'required|integer|in:1,2,3,4,6,8',
            'message'           => 'nullable|string|max:200',
            'notify_followers'  => 'nullable|boolean',
        ]);

        $userId = (string) auth()->id();

        // Eliminar disponibilidad previa si existe
        DB::table('availability')
            ->whereRaw('user_id::text = ?', [$userId])
            ->delete();

        $hours     = (int) $request->input('duration_hours');
        $expiresAt = Carbon::now()->addHours($hours);

        $availability = Availability::create([
            'user_id'          => $userId,
            'duration_hours'   => $hours,
            'expires_at'       => $expiresAt,
            'message'          => $request->input('message'),
            'notify_followers' => $request->boolean('notify_followers', true),
        ]);

        // Notificar a seguidores si el usuario lo permite
        if ($availability->notify_followers) {
            $this->notifyFollowers($userId, $availability);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'active'    => true,
                'expires_at'=> $expiresAt->toISOString(),
                'remaining' => $availability->humanTimeRemaining(),
                'message'   => 'Disponibilidad activada por ' . $hours . 'h',
            ]);
        }

        return back()->with('success', "✅ Disponibilidad activada por {$hours} hora(s).");
    }

    /**
     * Desactivar disponibilidad manualmente.
     */
    public function deactivate(Request $request)
    {
        $userId = (string) auth()->id();

        DB::table('availability')
            ->whereRaw('user_id::text = ?', [$userId])
            ->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'active'  => false,
                'message' => 'Disponibilidad desactivada.',
            ]);
        }

        return back()->with('success', 'Disponibilidad desactivada.');
    }

    /**
     * Estado actual del usuario autenticado.
     */
    public function status(Request $request)
    {
        $userId = (string) auth()->id();

        $availability = Availability::whereRaw('user_id::text = ?', [$userId])
            ->active()
            ->first();

        return response()->json([
            'active'    => (bool) $availability,
            'expires_at'=> $availability?->expires_at?->toISOString(),
            'remaining' => $availability?->humanTimeRemaining(),
            'message'   => $availability?->message,
        ]);
    }

    /**
     * Listado de usuarios disponibles ahora (para el dashboard).
     */
    public static function activeUsers(string $currentUserId, int $limit = 20): \Illuminate\Support\Collection
    {
        return DB::table('availability as av')
            ->join('users as u', DB::raw('u.id::text'), '=', DB::raw('av.user_id::text'))
            ->leftJoin('profiles as p', DB::raw('p.user_id::text'), '=', DB::raw('u.id::text'))
            ->leftJoin(DB::raw("(
                SELECT DISTINCT ON (user_id) user_id::text AS av_uid, file_path AS avatar_path
                FROM photos
                WHERE is_profile_photo = true AND status = 'approved'
                ORDER BY user_id
            ) as ph"), 'ph.av_uid', '=', DB::raw('u.id::text'))
            ->where('av.expires_at', '>', now())
            ->whereRaw('av.user_id::text != ?', [$currentUserId])
            ->where('u.active', true)
            ->orderBy('av.expires_at', 'asc')
            ->limit($limit)
            ->select([
                DB::raw('u.id::text as user_id'),
                DB::raw('COALESCE(p.nickname, u.username) as nickname'),
                DB::raw('COALESCE(p.display_name, u.username) as display_name'),
                DB::raw('p.verified_profile as verified_profile'),
                DB::raw('p.profile_type as profile_type'),
                'av.expires_at',
                'av.message',
                'av.duration_hours',
                'ph.avatar_path',
            ])
            ->get();
    }

    /**
     * Notifica a los seguidores del usuario que se activó disponibilidad.
     */
    private function notifyFollowers(string $userId, Availability $availability): void
    {
        // Obtener seguidores (máx 100 para no saturar)
        $followers = DB::table('follows')
            ->whereRaw('following_id::text = ?', [$userId])
            ->limit(100)
            ->pluck('follower_id');

        $nick = DB::table('profiles')
            ->whereRaw('user_id::text = ?', [$userId])
            ->value('nickname') ?? 'Alguien';

        foreach ($followers as $followerId) {
            NotificationController::create((string) $followerId, 'disponible', [
                'from_nick'  => $nick,
                'from_id'    => $userId,
                'expires_at' => $availability->expires_at->toISOString(),
                'message'    => $availability->message,
            ]);
        }
    }
}