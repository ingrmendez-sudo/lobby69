<?php

namespace App\Http\Controllers;

use App\Models\Availability;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AvailabilityController extends Controller
{
    // ── Slots válidos ─────────────────────────────────────────────────
    public const VALID_SLOTS = ['hoy', 'entre_semana', 'viernes', 'finde', 'sabado', 'domingo'];

    // ── Activar ───────────────────────────────────────────────────────
    public function activate(Request $request)
    {
        $request->validate([
            'slot'             => 'required|string|in:hoy,entre_semana,viernes,finde,sabado,domingo',
            'message'          => 'nullable|string|max:200',
            'notify_followers' => 'nullable|boolean',
        ]);

        $userId    = (string) auth()->id();
        $slot      = $request->input('slot', 'hoy');
        $expiresAt = $this->calculateExpiry($slot);

        // Eliminar disponibilidad previa si existe
        DB::table('availability')
            ->whereRaw('user_id::text = ?', [$userId])
            ->delete();

        $availability = Availability::create([
            'user_id'          => $userId,
            'slot'             => $slot,
            'expires_at'       => $expiresAt,
            'message'          => $request->input('message'),
            'notify_followers' => $request->boolean('notify_followers', true),
        ]);

        if ($availability->notify_followers) {
            $this->notifyFollowers($userId, $availability);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'active'     => true,
                'slot'       => $slot,
                'slot_label' => $availability->humanSlotLabel(),
                'expires_at' => $expiresAt->toISOString(),
                'remaining'  => $availability->humanTimeRemaining(),
                'message'    => 'Disponibilidad activada: ' . $availability->humanSlotLabel(),
            ]);
        }

        return back()->with('success', '✅ Disponibilidad activada: ' . $availability->humanSlotLabel());
    }

    // ── Desactivar ────────────────────────────────────────────────────
    public function deactivate(Request $request)
    {
        $userId = (string) auth()->id();

        DB::table('availability')
            ->whereRaw('user_id::text = ?', [$userId])
            ->delete();

        if ($request->expectsJson()) {
            return response()->json(['active' => false, 'message' => 'Disponibilidad desactivada.']);
        }

        return back()->with('success', 'Disponibilidad desactivada.');
    }

    // ── Estado actual ─────────────────────────────────────────────────
    public function status(Request $request)
    {
        $userId = (string) auth()->id();

        $availability = Availability::whereRaw('user_id::text = ?', [$userId])
            ->active()
            ->first();

        return response()->json([
            'active'     => (bool) $availability,
            'slot'       => $availability?->slot,
            'slot_label' => $availability?->humanSlotLabel(),
            'expires_at' => $availability?->expires_at?->toISOString(),
            'remaining'  => $availability?->humanTimeRemaining(),
            'message'    => $availability?->message,
        ]);
    }

    // ── Usuarios activos (para dashboard) ─────────────────────────────
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
                'av.slot',
                'av.expires_at',
                'av.message',
                'ph.avatar_path',
            ])
            ->get();
    }

    // ── Calcular expiración por slot ──────────────────────────────────
    private function calculateExpiry(string $slot): Carbon
    {
        $now = Carbon::now();

        $dayMap = [
            'entre_semana' => Carbon::THURSDAY,
            'viernes'      => Carbon::FRIDAY,
            'sabado'       => Carbon::SATURDAY,
            'finde'        => Carbon::SUNDAY,
            'domingo'      => Carbon::SUNDAY,
        ];

        if ($slot === 'hoy') {
            return $now->copy()->endOfDay();
        }

        if (isset($dayMap[$slot])) {
            $targetDay = $dayMap[$slot];
            // Si HOY ya es ese día, expira hoy al final del día (no saltar a siguiente semana)
            return $now->dayOfWeek === $targetDay
                ? $now->copy()->endOfDay()
                : $now->copy()->next($targetDay)->endOfDay();
        }

        return $now->copy()->endOfDay();
    }

    // ── Notificar seguidores ──────────────────────────────────────────
    private function notifyFollowers(string $userId, Availability $availability): void
    {
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
                'slot'       => $availability->slot,
                'slot_label' => $availability->humanSlotLabel(),
                'expires_at' => $availability->expires_at->toISOString(),
                'message'    => $availability->message,
            ]);
        }
    }

    // == Pagina publica: todos los disponibles ==
    public function publicList(\Illuminate\Http\Request \)
    {
        \ = (string) auth()->id();
        \    = \->input('slot');
        \        = \->input('q');

        \ = \Illuminate\Support\Facades\DB::table('availability as av')
            ->join('users as u', \Illuminate\Support\Facades\DB::raw('u.id::text'), '=', \Illuminate\Support\Facades\DB::raw('av.user_id::text'))
            ->leftJoin('profiles as p', \Illuminate\Support\Facades\DB::raw('p.user_id::text'), '=', \Illuminate\Support\Facades\DB::raw('u.id::text'))
            ->leftJoin(\Illuminate\Support\Facades\DB::raw("(SELECT DISTINCT ON (user_id) user_id::text AS av_uid, id AS avatar_id, file_path AS avatar_path FROM photos WHERE is_profile_photo = true AND status = 'approved' ORDER BY user_id) as ph"), 'ph.av_uid', '=', \Illuminate\Support\Facades\DB::raw('u.id::text'))
            ->where('av.expires_at', '>', now())
            ->whereRaw('av.user_id::text != ?', [\])
            ->where('u.active', true)
            ->orderByRaw('CASE WHEN p.verified_profile = true THEN 0 ELSE 1 END ASC, av.expires_at ASC');

        if (\ && in_array(\, self::VALID_SLOTS)) {
            \->where('av.slot', \);
        }

        if (\) {
            \->where(function(\) use (\) {
                \->where('p.nickname',      'ilike', '%' . \ . '%')
                  ->orWhere('p.display_name', 'ilike', '%' . \ . '%')
                  ->orWhere('p.city',         'ilike', '%' . \ . '%');
            });
        }

        \ = \->select([
            \Illuminate\Support\Facades\DB::raw('u.id::text as user_id'),
            \Illuminate\Support\Facades\DB::raw('COALESCE(p.nickname, u.username) as nickname'),
            \Illuminate\Support\Facades\DB::raw('COALESCE(p.display_name, u.username) as display_name'),
            \Illuminate\Support\Facades\DB::raw('p.verified_profile as verified_profile'),
            \Illuminate\Support\Facades\DB::raw('p.profile_type as profile_type'),
            \Illuminate\Support\Facades\DB::raw('p.city as city'),
            'av.slot', 'av.expires_at', 'av.message',
            'ph.avatar_id', 'ph.avatar_path',
        ])->paginate(24)->withQueryString();

        \ = \App\Models\Availability::SLOTS;
        \369      = \Illuminate\Support\Facades\DB::table('availability')
            ->where('expires_at', '>', now())->count();

        return view('availability.index', compact('users', 'slotLabels', 'slotFilter', 'search', 'total'));
    }
}
