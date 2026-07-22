<?php
namespace App\Http\Controllers\Video;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VideoGalleryController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // ── Galería principal ────────────────────────────────────────
        $videos = DB::table('videos')
            ->join('users',    DB::raw('videos.user_id::text'), '=', DB::raw('users.id::text'))
            ->join('profiles', DB::raw('users.id::text'),       '=', DB::raw('profiles.user_id::text'))
            ->where('videos.status', 'approved')
            ->select(
                'videos.id',
                'videos.caption',
                'videos.thumbnail_path',
                'videos.file_path',
                'videos.views_count',
                'videos.duration_seconds',
                'videos.user_id',
                'videos.created_at',
                'profiles.nickname',
                'profiles.avatar_url'
            )
            ->orderByDesc('videos.created_at')
            ->paginate(24);

        // ── Defaults ─────────────────────────────────────────────────
        $userProfile         = null;
        $isVerified          = false;
        $pendingVerification = false;
        $lastWatched         = collect();
        $profileVisitors     = collect();
        $myLatestVideos      = collect();
        $myActivity          = collect();
        $announcements       = collect();
        $myVideoCount        = 0;
        $myLikesReceived     = 0;
        $myCommentsReceived  = 0;
        $likedIds            = [];
        $totalVisitors       = 0;

        if ($user) {
            $uid = (string) $user->id;

            // Perfil del usuario sesión
            $userProfile = DB::table('profiles')
                ->where(DB::raw('user_id::text'), $uid)
                ->first();

            // Verificación
            $verification = DB::table('verifications')
                ->where(DB::raw('user_id::text'), $uid)
                ->orderByDesc('created_at')
                ->first();
            $isVerified          = $verification && $verification->status === 'approved';
            $pendingVerification = $verification && $verification->status === 'pending';

            // Últimos videos vistos (usa video_likes como proxy si no hay video_views)
            // Usamos videos con más vistas como "últimos vistos" fallback
            $lastWatched = DB::table('videos')
                ->join('profiles', DB::raw('videos.user_id::text'), '=', DB::raw('profiles.user_id::text'))
                ->where('videos.status', 'approved')
                ->select(
                    'videos.id',
                    'videos.caption',
                    'videos.thumbnail_path',
                    'videos.duration_seconds',
                    'videos.views_count',
                    'profiles.nickname',
                    'profiles.avatar_url'
                )
                ->orderByDesc('videos.views_count')
                ->limit(5)
                ->get();

            // Visitantes al perfil del usuario
            // Visitantes únicos: 1 entrada por viewer, la más reciente, máx 5
            $profileVisitors = DB::table('profile_views as pv')
                ->join('profiles', DB::raw('pv.viewer_id::text'), '=', DB::raw('profiles.user_id::text'))
                ->where(DB::raw('pv.viewed_id::text'), $uid)
                ->where(DB::raw('pv.viewer_id::text'), '!=', $uid)
                ->whereRaw('pv.viewed_at = (SELECT MAX(pv2.viewed_at) FROM profile_views pv2 WHERE pv2.viewer_id = pv.viewer_id AND pv2.viewed_id = pv.viewed_id)')
                ->select(
                    'profiles.nickname',
                    'profiles.avatar_url',
                    'profiles.profile_type',
                    'pv.viewed_at'
                )
                ->orderByDesc('pv.viewed_at')
                ->limit(5)
                ->get();
            $totalVisitors = DB::table('profile_views')
                ->where(DB::raw('viewed_id::text'), $uid)
                ->where(DB::raw('viewer_id::text'), '!=', $uid)
                ->distinct()
                ->count('viewer_id');

            // Mis últimos 5 videos
            $myLatestVideos = DB::table('videos')
                ->where(DB::raw('user_id::text'), $uid)
                ->where('status', 'approved')
                ->select('id','caption','thumbnail_path','views_count','created_at')
                ->orderByDesc('created_at')
                ->limit(5)
                ->get();

            // Mi actividad (likes y comentarios recibidos)
            $myVideoIds = DB::table('videos')
                ->where(DB::raw('user_id::text'), $uid)
                ->pluck('id')->toArray();

            if (!empty($myVideoIds)) {
                $recentLikes = DB::table('video_likes')
                    ->join('videos',   'video_likes.video_id', '=', 'videos.id')
                    ->join('profiles', DB::raw('video_likes.user_id::text'), '=', DB::raw('profiles.user_id::text'))
                    ->whereIn('video_likes.video_id', $myVideoIds)
                    ->select(
                        DB::raw("'like' as type"),
                        'profiles.nickname',
                        'profiles.avatar_url',
                        'videos.caption',
                        'video_likes.created_at'
                    )
                    ->orderByDesc('video_likes.created_at')
                    ->limit(5)
                    ->get();

                $recentComments = DB::table('video_comments')
                    ->join('videos',   'video_comments.video_id', '=', 'videos.id')
                    ->join('profiles', DB::raw('video_comments.user_id::text'), '=', DB::raw('profiles.user_id::text'))
                    ->whereIn('video_comments.video_id', $myVideoIds)
                    ->select(
                        DB::raw("'comment' as type"),
                        'profiles.nickname',
                        'profiles.avatar_url',
                        'videos.caption',
                        'video_comments.created_at'
                    )
                    ->orderByDesc('video_comments.created_at')
                    ->limit(5)
                    ->get();

                $myActivity = $recentLikes->concat($recentComments)
                    ->sortByDesc('created_at')
                    ->take(8)
                    ->values();

                $myLikesReceived    = DB::table('video_likes')->whereIn('video_id', $myVideoIds)->count();
                $myCommentsReceived = DB::table('video_comments')->whereIn('video_id', $myVideoIds)->count();
            }

            $myVideoCount = DB::table('videos')
                ->where(DB::raw('user_id::text'), $uid)
                ->where('status', 'approved')
                ->count();

            $likedIds = DB::table('video_likes')
                ->where(DB::raw('user_id::text'), $uid)
                ->pluck('video_id')->toArray();

            // Anuncios activos dirigidos al tipo de perfil del usuario
            $profileType = $userProfile->profile_type ?? null;
            $announcements = DB::table('announcements')
                ->join('profiles', DB::raw('announcements.user_id::text'), '=', DB::raw('profiles.user_id::text'))
                ->where('announcements.status', 'active')
                ->where(function($q) {
                    $q->whereNull('announcements.expires_at')
                      ->orWhere('announcements.expires_at', '>', now());
                })
                ->select(
                    'announcements.id',
                    'announcements.title',
                    'announcements.proposal',
                    'announcements.event_date',
                    'announcements.directed_to',
                    'announcements.created_at',
                    'profiles.nickname',
                    'profiles.avatar_url',
                    'profiles.profile_type'
                )
                ->orderByDesc('announcements.created_at')
                ->limit(3)
                ->get();
        }

        return view('videos.gallery', compact(
            'user', 'userProfile', 'videos',
            'lastWatched', 'profileVisitors',
            'myLatestVideos', 'myActivity',
            'isVerified', 'pendingVerification', 'announcements',
            'myVideoCount', 'myLikesReceived', 'myCommentsReceived', 'likedIds', 'totalVisitors'
        ));
    }
}