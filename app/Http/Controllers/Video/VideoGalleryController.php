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
            ->join('users',    'videos.user_id', '=', 'users.id')
            ->join('profiles', 'users.id',       '=', 'profiles.user_id')
            ->leftJoin('photos', function($j) {
                $j->on('photos.user_id', '=', 'videos.user_id')
                   ->where('photos.is_profile_photo', true);
            })
            ->where('videos.status', 'approved')
            ->selectRaw("
                videos.id,
                videos.caption,
                videos.thumbnail_path,
                videos.file_path,
                videos.views_count,
                videos.duration_seconds,
                videos.user_id,
                videos.created_at,
                profiles.nickname,
                profiles.avatar_url,
                photos.id AS avatar_photo_id,
                (SELECT COUNT(*) FROM video_likes  WHERE video_likes.video_id  = videos.id) AS likes_count,
                (SELECT COUNT(*) FROM video_comments WHERE video_comments.video_id = videos.id AND video_comments.parent_id IS NULL) AS comments_count
            ")
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
        $topVideos           = collect();

        if ($user) {
            $uid = (string) $user->id;

            // Perfil del usuario sesión
            $userProfile = DB::table('profiles')
                ->leftJoin('photos as ph_side', function($j) {
                    $j->on('ph_side.user_id', '=', 'profiles.user_id')
                       ->where('ph_side.is_profile_photo', true);
                })
                ->where('profiles.user_id', $uid)
                ->select('profiles.*', 'ph_side.id as avatar_photo_id')
                ->first();

            // Verificación
            $verification = DB::table('verifications')
                ->where('user_id', $uid)
                ->orderByDesc('created_at')
                ->first();
            $isVerified          = $verification && $verification->status === 'approved';
            $pendingVerification = $verification && $verification->status === 'pending';

            // Últimos videos vistos (usa video_likes como proxy si no hay video_views)
            // Usamos videos con más vistas como "últimos vistos" fallback
            $lastWatched = DB::table('videos')
                ->join('profiles', 'videos.user_id', '=', 'profiles.user_id')
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
            // Visitantes unicos: DISTINCT ON viewer_id, la visita mas reciente, max 5
            $profileVisitors = collect(DB::select("
                SELECT DISTINCT ON (pv.viewer_id)
                    pv.viewer_id,
                    pv.viewed_at,
                    COALESCE(p.nickname, u.name, u.email, 'Anonimo') AS nickname,
                    COALESCE(p.avatar_url, '')                        AS avatar_url,
                    COALESCE(p.profile_type, '')                      AS profile_type,
                    ph.id                                              AS avatar_photo_id
                FROM profile_views pv
                LEFT JOIN photos   ph ON ph.user_id = pv.viewer_id AND ph.is_profile_photo = true
                LEFT JOIN users    u ON u.id = pv.viewer_id
                LEFT JOIN profiles p ON p.user_id = pv.viewer_id
                WHERE pv.viewed_id = ?
                  AND pv.viewer_id != ?
                ORDER BY pv.viewer_id, pv.viewed_at DESC
            ", [$uid, $uid]))
                ->sortByDesc('viewed_at')
                ->take(5)
                ->values();

            $totalVisitors = DB::table('profile_views')
                ->where('viewed_id', $uid)
                ->where('viewer_id', '!=', $uid)
                ->distinct('viewer_id')
                ->count('viewer_id');

            // Mis últimos 5 videos
            $myLatestVideos = DB::table('videos')
                ->where('user_id', $uid)
                ->where('status', 'approved')
                ->select('id','caption','thumbnail_path','views_count','created_at')
                ->orderByDesc('created_at')
                ->limit(5)
                ->get();

            // Top 5 videos más populares
            $topVideos = DB::table('videos')
                ->join('profiles', 'videos.user_id', '=', 'profiles.user_id')
                ->where('videos.status', 'approved')
                ->select('videos.id','videos.caption','videos.thumbnail_path','videos.views_count','profiles.nickname')
                ->orderByDesc('videos.views_count')
                ->limit(5)
                ->get();

            // Mi actividad (likes y comentarios recibidos)
            $myVideoIds = DB::table('videos')
                ->where('user_id', $uid)
                ->pluck('id')->toArray();

            if (!empty($myVideoIds)) {
                $recentLikes = DB::table('video_likes')
                    ->join('videos',   'video_likes.video_id', '=', 'videos.id')
                    ->join('profiles', 'video_likes.user_id', '=', 'profiles.user_id')
                    ->leftJoin('photos as ph_lk', function($j) {
                        $j->on('ph_lk.user_id', '=', 'video_likes.user_id')
                           ->where('ph_lk.is_profile_photo', true);
                    })
                    ->whereIn('video_likes.video_id', $myVideoIds)
                    ->select(
                        DB::raw("'like' as type"),
                        'profiles.nickname',
                        'profiles.avatar_url',
                        'ph_lk.id as avatar_photo_id',
                        'videos.caption',
                        'video_likes.created_at'
                    )
                    ->orderByDesc('video_likes.created_at')
                    ->limit(5)
                    ->get();

                $recentComments = DB::table('video_comments')
                    ->join('videos',   'video_comments.video_id', '=', 'videos.id')
                    ->join('profiles', 'video_comments.user_id', '=', 'profiles.user_id')
                    ->leftJoin('photos as ph_cm', function($j) {
                        $j->on('ph_cm.user_id', '=', 'video_comments.user_id')
                           ->where('ph_cm.is_profile_photo', true);
                    })
                    ->whereIn('video_comments.video_id', $myVideoIds)
                    ->select(
                        DB::raw("'comment' as type"),
                        'profiles.nickname',
                        'profiles.avatar_url',
                        'ph_cm.id as avatar_photo_id',
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
                ->where('user_id', $uid)
                ->where('status', 'approved')
                ->count();

            $likedIds = DB::table('video_likes')
                ->where('user_id', $uid)
                ->pluck('video_id')->toArray();

            // Anuncios activos dirigidos al tipo de perfil del usuario
            $profileType = $userProfile->profile_type ?? null;
            $announcements = DB::table('announcements')
                ->join('profiles', 'announcements.user_id', '=', 'profiles.user_id')
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
                    DB::raw('(SELECT id FROM photos WHERE photos.user_id = announcements.user_id AND photos.is_profile_photo = true ORDER BY id DESC LIMIT 1) as avatar_photo_id')
                )
                ->orderByDesc('announcements.created_at')
                ->limit(3)
                ->get();
        }

        return view('videos.gallery', compact(
            'user', 'userProfile', 'videos',
            'lastWatched', 'profileVisitors',
            'myLatestVideos', 'myActivity', 'topVideos',
            'isVerified', 'pendingVerification', 'announcements',
            'myVideoCount', 'myLikesReceived', 'myCommentsReceived', 'likedIds', 'totalVisitors'
        ));
    }
}
