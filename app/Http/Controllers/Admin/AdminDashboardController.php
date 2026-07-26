<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // ── Conteos pendientes ──
        $pendingPhotos        = DB::table('photos')->where('status', 'pending')->count();
        $pendingVideos        = DB::table('videos')->where('status', 'pending')->count();
        $pendingVerifications = DB::table('verifications')->where('status', 'pending')->count();
        $pendingInvitations   = DB::table('invitation_requests')->where('status', 'pending')->count();


        // ── Usuarios ──
        $totalUsers    = DB::table('users')->where('role', '!=', 'admin')->count();
        $newUsersToday = DB::table('users')
            ->where('role', '!=', 'admin')
            ->whereDate('created_at', today())
            ->count();
        $newUsersWeek  = DB::table('users')
            ->where('role', '!=', 'admin')
            ->where('created_at', '>=', now()->subDays(7))
            ->count();
        $onlineNow     = DB::table('users')
            ->where('role', '!=', 'admin')
            ->where('last_seen_at', '>=', now()->subMinutes(15))
            ->count();

        // ── Membresías ──
        $memberships = DB::table('users')
            ->where('role', '!=', 'admin')
            ->selectRaw('membership_type, count(*) as total')
            ->groupBy('membership_type')
            ->orderByDesc('total')
            ->get();

        // ── Contenido ──
        $totalPhotos    = DB::table('photos')->where('status', 'approved')->count();
        $totalVideos    = DB::table('videos')->where('status', 'approved')->count();
        $totalComments  = DB::table('photo_comments')->where('status', 'approved')->count();
        $totalLikes     = DB::table('photo_likes')->count();

        // ── Actividad últimos 7 días ──
        $dailyUsers = DB::table('users')
            ->where('role', '!=', 'admin')
            ->where('created_at', '>=', now()->subDays(6))
            ->selectRaw("DATE(created_at) as day, count(*) as total")
            ->groupByRaw("DATE(created_at)")
            ->orderBy('day')
            ->get();

        $dailyPhotos = DB::table('photos')
            ->where('created_at', '>=', now()->subDays(6))
            ->selectRaw("DATE(created_at) as day, count(*) as total")
            ->groupByRaw("DATE(created_at)")
            ->orderBy('day')
            ->get();

        // ── Fotos más populares ──
        $topPhotos = DB::table('photos')
            ->joinSub(
                DB::table('users')->selectRaw('"id"::text as uid, "username"'),
                'u',
                DB::raw('photos.user_id::text'), '=', 'u.uid'       // ← ::text aquí
            )
            ->leftJoinSub(
                DB::table('profiles')->selectRaw('"user_id"::text as pid, "nickname"'),
                'p',
                DB::raw('photos.user_id::text'), '=', 'p.pid'       // ← ::text aquí
            )
            ->leftJoin(
                DB::raw('(SELECT photo_id::text as lpid, COUNT(*) as lc FROM photo_likes GROUP BY photo_id) as lk'),
                DB::raw('photos.photo_uuid::text'), '=', 'lk.lpid'
            )
            ->where('photos.status', 'approved')
            ->select(
                'photos.id',
                'photos.file_path',
                'u.username',
                'p.nickname',
                DB::raw('COALESCE(lk.lc, 0) as likes_count')
            )
            ->orderByDesc('likes_count')
            ->limit(5)
            ->get();


        // ── Usuarios más activos ──
        // ── Usuarios más activos ──
        $topUsers = DB::table('profiles')
        ->join(
            DB::raw('(SELECT id::text as uid, username, membership_type FROM users WHERE role != \'admin\') as u'),
            DB::raw('profiles.user_id::text'), '=', 'u.uid'   // ← ::text aquí
        )
        ->leftJoin(
            DB::raw('(SELECT user_id::text as fpid, COUNT(*) as fc FROM photos WHERE status = \'approved\' GROUP BY user_id) as ph'),
            DB::raw('profiles.user_id::text'), '=', 'ph.fpid'  // ← ::text aquí
        )
        ->selectRaw('profiles.nickname, profiles.display_name, u.username, u.membership_type,
                    COALESCE(ph.fc, 0) as photos_count')
        ->orderByDesc('photos_count')
        ->limit(5)
        ->get();

        // ── Últimas acciones pendientes ──
        $recentPending = collect([
            ...$this->recentItems('photos',        'pending', 'Foto'),
            ...$this->recentItems('videos',        'pending', 'Video'),
            ...$this->recentItems('verifications', 'pending', 'Verificación'),
            ...$this->recentItems('invitation_requests', 'pending', 'Invitación'),
        ])->sortByDesc('created_at')->take(8)->values();

        return view('admin.dashboard', compact(
            'pendingPhotos', 'pendingVideos', 'pendingVerifications', 'pendingInvitations',
            'totalUsers', 'newUsersToday', 'newUsersWeek', 'onlineNow',
            'memberships', 'totalPhotos', 'totalVideos', 'totalComments', 'totalLikes',
            'dailyUsers', 'dailyPhotos', 'topPhotos', 'topUsers', 'recentPending'
        ));
    }

    private function recentItems(string $table, string $status, string $label): array
    {
        try {
            return DB::table($table)
                ->where('status', $status)
                ->orderByDesc('created_at')
                ->limit(3)
                ->get(['id', 'created_at'])
                ->map(fn($r) => (object)[
                    'type'       => $label,
                    'id'         => $r->id,
                    'created_at' => $r->created_at,
                ])
                ->toArray();
        } catch (\Throwable $e) {
            return [];
        }
    }
}
