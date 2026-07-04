<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Photo;
use App\Models\User;

class DashboardController extends Controller
{
    private function isValidPhotoId(string $value): bool
    {
        return ctype_digit($value) && (int)$value > 0;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        $profile = $user->profile;
        $tab     = $request->input('tab', 'new');
        $page    = max(1, (int) $request->input('page', 1));
        $feed    = $this->getFeed((string) $user->id, $tab, $page);

        // ── Quién me vio ──
        $whoViewedMe      = collect();
        $whoViewedMeCount = 0;
        try {
            $views = DB::table('profile_views')
                ->whereRaw('viewed_id::text = ?', [(string) $user->id])
                ->orderByDesc('viewed_at')
                ->limit(10)
                ->get();

            $viewerIds        = $views->pluck('viewer_id')->map(fn($id) => (string)$id)->toArray();
            $whoViewedMeCount = DB::table('profile_views')
                ->whereRaw('viewed_id::text = ?', [(string) $user->id])
                ->count();

            $whoViewedMe = User::with('profile')
                ->whereIn(DB::raw('id::text'), $viewerIds)
                ->where('role', '!=', 'admin')
                ->get();
        } catch (\Throwable $e) {
            Log::warning('DashboardController@index - whoViewedMe: ' . $e->getMessage());
        }

        // ── A quién vi ──
        $iViewed      = collect();
        $iViewedCount = 0;
        try {
            $myViews = DB::table('profile_views')
                ->whereRaw('viewer_id::text = ?', [(string) $user->id])
                ->orderByDesc('viewed_at')
                ->limit(10)
                ->get();

            $viewedIds    = $myViews->pluck('viewed_id')->map(fn($id) => (string)$id)->toArray();
            $iViewedCount = DB::table('profile_views')
                ->whereRaw('viewer_id::text = ?', [(string) $user->id])
                ->count();

            $iViewed = User::with('profile')
                ->whereIn(DB::raw('id::text'), $viewedIds)
                ->where('role', '!=', 'admin')
                ->get();
        } catch (\Throwable $e) {
            Log::warning('DashboardController@index - iViewed: ' . $e->getMessage());
        }

        // ── Usuarios en línea ──
        $onlineUsers = collect();
        try {
            $onlineUsers = User::with('profile')
                ->where('last_seen_at', '>=', now()->subMinutes(15))
                ->whereRaw('id::text != ?', [(string) $user->id])
                ->where('role', '!=', 'admin')
                ->limit(20)
                ->get();
        } catch (\Throwable $e) {
            Log::warning('DashboardController@index - onlineUsers: ' . $e->getMessage());
        }

        // ── Usuarios nuevos ──
        $newUsers = collect();
        try {
            $newUsers = User::with('profile')
                ->whereRaw('id::text != ?', [(string) $user->id])
                ->where('role', '!=', 'admin')
                ->orderByDesc('created_at')
                ->limit(12)
                ->get();
        } catch (\Throwable $e) {
            Log::warning('DashboardController@index - newUsers: ' . $e->getMessage());
        }

        return view('dashboard.index', compact(
            'user', 'profile', 'feed', 'tab',
            'whoViewedMe', 'whoViewedMeCount',
            'iViewed', 'iViewedCount',
            'onlineUsers', 'newUsers'
        ));
    }

    private function getFeed(string $userId, string $tab, int $page): LengthAwarePaginator
    {
        $perPage = 12;

        // ── Gustos del usuario actual ──
        $myProfile = DB::table('profiles')
            ->whereRaw('user_id::text = ?', [$userId])
            ->select('gender', 'orientation', 'looking_for', 'profile_type', 'city', 'age')
            ->first();

        $query = DB::table('photos')
            ->where('photos.status', 'approved')
            ->where('photos.album_type', 'public')
            ->whereNotExists(function($q) {
                $q->select(DB::raw(1))
                ->from('users')
                ->whereRaw('users.id::text = photos.user_id::text')
                ->where('users.role', 'admin');
            })
            ->whereRaw('photos.user_id::text != ?', [$userId]);

        switch ($tab) {
            case 'popular':
                $query
                    ->select('photos.*')
                    ->leftJoin(
                        DB::raw('(SELECT photo_id::text as lpid, COUNT(*) as lc FROM photo_likes GROUP BY photo_id) as lk'),
                        DB::raw('photos.photo_uuid::text'), '=', 'lk.lpid'
                    )
                    ->orderByRaw('COALESCE(lk.lc, 0) DESC');
                break;

            case 'following':
                $following = DB::table('follows')
                    ->whereRaw('follower_id::text = ?', [$userId])
                    ->pluck('following_id')
                    ->map(fn($id) => (string)$id)
                    ->toArray();
                if (!empty($following)) {
                    $query->whereIn(DB::raw('photos.user_id::text'), $following);
                } else {
                    $query->whereRaw('1 = 0');
                }
                $query->select('photos.*')->orderByDesc('photos.created_at');
                break;

            default:
                if ($myProfile) {
                    $city = addslashes($myProfile->city ?? '');
                    $age  = (int)($myProfile->age ?? 0);

                    $query->leftJoinSub(
                        DB::table('profiles')->selectRaw(
                            '"user_id"::text as pid, "verified_profile", "city", "age"'
                        ),
                        'p',
                        'photos.user_id', '=', 'p.pid'
                    )
                    ->selectRaw('
                        photos.*,
                        (
                            CASE WHEN p.verified_profile = true THEN 3 ELSE 0 END +
                            CASE WHEN p.city = \'' . $city . '\' THEN 2 ELSE 0 END +
                            CASE WHEN ABS(COALESCE(p.age, 0) - ' . $age . ') <= 10 THEN 1 ELSE 0 END
                        ) as affinity_score
                    ')
                    ->orderByRaw('affinity_score DESC, photos.created_at DESC');
                } else {
                    $query->select('photos.*')->orderByDesc('photos.created_at');
                }
                break;
        }


        $total = (clone $query)->count();

        $photos = (clone $query)
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        if ($photos->isEmpty()) {
            return new LengthAwarePaginator(collect(), $total, $perPage, $page, [
                'path'  => request()->url(),
                'query' => request()->query(),
            ]);
        }

        $photoIds = $photos->pluck('id')->toArray();
        $uuids    = $photos->pluck('photo_uuid')->filter()->toArray();
        $userIds  = $photos->pluck('user_id')->unique()->toArray();

        // ── Likes y comentarios ──
        $likesMap    = collect();
        $commentsMap = collect();
        $likedUuids  = [];

        if (!empty($uuids)) {
            $placeholders = implode(',', array_fill(0, count($uuids), '?'));

            $likesMap = DB::table('photo_likes')
                ->whereRaw('photo_id::text IN (' . $placeholders . ')', $uuids)
                ->selectRaw('photo_id::text as pid, count(*) as total')
                ->groupBy('photo_id')
                ->pluck('total', 'pid');

            $commentsMap = DB::table('photo_comments')
                ->whereRaw('photo_id::text IN (' . $placeholders . ')', $uuids)
                ->where('status', 'approved')
                ->selectRaw('photo_id::text as pid, count(*) as total')
                ->groupBy('photo_id')
                ->pluck('total', 'pid');

            $likedUuids = DB::table('photo_likes')
                ->whereRaw('user_id::text = ?', [$userId])
                ->whereRaw('photo_id::text IN (' . $placeholders . ')', $uuids)
                ->pluck('photo_id')
                ->map(fn($id) => (string)$id)
                ->toArray();
        }

        // ── Avatars ──
        $avatarMap = DB::table('photos')
            ->whereIn(DB::raw('user_id::text'), $userIds)
            ->where('is_profile_photo', true)
            ->where('status', 'approved')
            ->select('user_id', 'file_path')
            ->get()->keyBy('user_id');

        $missingIds = collect($userIds)
            ->filter(fn($id) => !isset($avatarMap[$id]))
            ->values()->toArray();

        if (!empty($missingIds)) {
            $fallbacks = DB::table('photos')
                ->whereIn(DB::raw('user_id::text'), $missingIds)
                ->where('album_type', 'public')
                ->where('status', 'approved')
                ->orderBy('created_at')
                ->select('user_id', 'file_path')
                ->get()->unique('user_id')->keyBy('user_id');
            $avatarMap = $avatarMap->merge($fallbacks);
        }

        // ── Perfiles ──
        $profileMap = DB::table('profiles')
            ->whereIn(DB::raw('user_id::text'), $userIds)
            ->select('user_id', 'display_name', 'nickname', 'city', 'age', 'verified_profile')
            ->get()->keyBy('user_id');

        // ── Enriquecer ──
        $enriched = $photos->map(function($photo) use (
            $likesMap, $commentsMap, $likedUuids, $avatarMap, $profileMap
        ) {
            $uuid    = (string)($photo->photo_uuid ?? '');
            $profile = $profileMap[$photo->user_id] ?? null;

            $photo->likes_count      = $likesMap[$uuid]    ?? 0;
            $photo->comments_count   = $commentsMap[$uuid] ?? 0;
            $photo->userLiked        = in_array($uuid, $likedUuids);
            $photo->display_name     = $profile->display_name    ?? 'Usuario';
            $photo->nickname         = $profile->nickname        ?? null;
            $photo->city             = $profile->city            ?? null;
            $photo->age              = $profile->age             ?? null;
            $photo->verified_profile = $profile->verified_profile ?? false;
            $photo->avatar_path      = $avatarMap[$photo->user_id]->file_path ?? null;

            return $photo;
        });

        return new LengthAwarePaginator(
            $enriched, $total, $perPage, $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    public function feedAjax(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) return response()->json(['error' => 'No autenticado'], 401);

        try {
            $tab  = $request->input('tab', 'new');
            $page = max(1, (int) $request->input('page', 1));
            $feed = $this->getFeed((string) $user->id, $tab, $page);

            $html = '';
            foreach ($feed as $photo) {
                $html .= view('dashboard.partials.photo-card', [
                    'photo'   => $photo,
                    'isLiked' => $photo->userLiked,
                    'user'    => $user,
                ])->render();
            }

            return response()->json([
                'html'        => $html,
                'hasMore'     => $feed->hasMorePages(),
                'currentPage' => $feed->currentPage(),
                'lastPage'    => $feed->lastPage(),
                'total'       => $feed->total(),
            ]);

        } catch (\Throwable $e) {
            Log::error('feedAjax error: ' . $e->getMessage() . ' | ' . $e->getFile() . ':' . $e->getLine());
            return response()->json([
                'error'   => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'html'    => '',
                'hasMore' => false,
            ], 500);
        }
    }


    public function toggleLike(Request $request, string $photoId): JsonResponse
    {
        $user = Auth::user();
        if (!$user) return response()->json(['error' => 'No autenticado'], 401);

        if (!$this->isValidPhotoId($photoId)) {
            return response()->json(['error' => 'ID de foto inválido'], 422);
        }

        try {
            $photo = DB::table('photos')->where('id', (int)$photoId)->first();
            if (!$photo) return response()->json(['error' => 'Foto no encontrada'], 404);

            $uuid = (string)$photo->photo_uuid;

            $exists = DB::table('photo_likes')
                ->whereRaw('photo_id::text = ?', [$uuid])
                ->whereRaw('user_id::text = ?', [(string)$user->id])
                ->exists();

            if ($exists) {
                DB::table('photo_likes')
                    ->whereRaw('photo_id::text = ?', [$uuid])
                    ->whereRaw('user_id::text = ?', [(string)$user->id])
                    ->delete();
                $liked = false;
            } else {
                DB::table('photo_likes')->insertOrIgnore([
                    'id'         => \Illuminate\Support\Str::uuid(),
                    'photo_id'   => $photo->photo_uuid,
                    'user_id'    => (string)$user->id,
                    'created_at' => now(),
                ]);
                $liked = true;
            }

            $likesCount = DB::table('photo_likes')
                ->whereRaw('photo_id::text = ?', [$uuid])
                ->count();

            return response()->json([
                'success'     => true,
                'liked'       => $liked,
                'likes_count' => $likesCount,
            ]);

        } catch (\Throwable $e) {
            Log::error('DashboardController@toggleLike: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno'], 500);
        }
    }


    public function photoModal(Request $request, string $photoId): JsonResponse
    {
        $user = Auth::user();
        if (!$user) return response()->json(['error' => 'No autenticado'], 401);

        if (!$this->isValidPhotoId($photoId)) {
            return response()->json(['error' => 'ID de foto inválido'], 422);
        }

        try {
            // ── Foto básica sin Eloquent para evitar cast issues ──
            $photo = DB::table('photos')->where('id', (int)$photoId)->first();
            abort_if(!$photo, 404);

            $likesCount = DB::table('photo_likes')
                ->whereRaw('photo_id::text = ?', [(string)$photo->photo_uuid])
                ->count();

            $userLiked = DB::table('photo_likes')
                ->whereRaw('photo_id::text = ?', [(string)$photo->photo_uuid])
                ->whereRaw('user_id::text = ?', [(string)$user->id])
                ->exists();

            // ── Comentarios con cast correcto ──
            $comments = DB::table('photo_comments')
                ->joinSub(
                    DB::table('users')->selectRaw('"id"::text as uid, "username"'),
                    'u',
                    DB::raw('photo_comments.user_id::text'), '=', 'u.uid'
                )
                ->leftJoinSub(
                    DB::table('profiles')->selectRaw('"user_id"::text as pid, "nickname", "display_name"'),
                    'p',
                    DB::raw('photo_comments.user_id::text'), '=', 'p.pid'
                )
                ->whereRaw('photo_comments.photo_id::text = ?', [(string)$photo->photo_uuid])
                ->where('photo_comments.status', 'approved')
                ->orderBy('photo_comments.created_at', 'asc')
                ->selectRaw("
                    photo_comments.id,
                    photo_comments.body as comment,
                    photo_comments.created_at,
                    u.username,
                    COALESCE(p.nickname, p.display_name, u.username, 'Usuario') as user_nick
                ")
                ->limit(50)
                ->get();


            // ── Avatar del dueño de la foto ──
            $ownerAvatar = DB::table('photos')
                ->whereRaw('user_id::text = ?', [(string)$photo->user_id])
                ->where('is_profile_photo', true)
                ->where('status', 'approved')
                ->value('file_path');

            if (!$ownerAvatar) {
                $ownerAvatar = DB::table('photos')
                    ->whereRaw('user_id::text = ?', [(string)$photo->user_id])
                    ->where('album_type', 'public')
                    ->where('status', 'approved')
                    ->orderBy('created_at')
                    ->value('file_path');
            }

            // ── Perfil del dueño ──
            $ownerProfile = DB::table('profiles')
                ->whereRaw('user_id::text = ?', [(string)$photo->user_id])
                ->select('nickname', 'display_name', 'city', 'verified_profile')
                ->first();

            $ownerUser = DB::table('users')
                ->whereRaw('id::text = ?', [(string)$photo->user_id])
                ->value('username');

            $ownerNickname = $ownerProfile->nickname     ?? null;
            $ownerName     = $ownerProfile->display_name ?? $ownerUser ?? 'Usuario';
            $ownerUrl      = $ownerNickname
                ? route('profile.show', $ownerNickname)
                : null;

            return response()->json([
                'success' => true,
                'photo'   => [
                    'id'             => $photo->id,
                    'file_path'      => $photo->file_path,
                    'caption'        => $photo->caption ?? '',
                    'created_at'     => $photo->created_at,
                    'likes_count'    => $likesCount,
                    'user_liked'     => $userLiked,
                    'comments_count' => $comments->count(),
                ],
                'owner' => [
                    'name'     => $ownerName,
                    'nickname' => $ownerNickname,
                    'avatar'   => $ownerAvatar,
                    'url'      => $ownerUrl,
                ],
                'comments' => $comments,
            ]);

        } catch (\Throwable $e) {
            Log::error('DashboardController@photoModal: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno'], 500);
        }
    }


    public function storeComment(Request $request, string $photoId): JsonResponse
    {
        $user = Auth::user();
        if (!$user) return response()->json(['error' => 'No autenticado'], 401);

        if (!$this->isValidPhotoId($photoId)) {
            return response()->json(['error' => 'ID de foto inválido'], 422);
        }

        $validated = $request->validate([
            'body' => ['required', 'string', 'min:1', 'max:500'],
        ]);

        $body = $validated['body'];
        $forbiddenPatterns = [
            '/(\+?[\d\s\-\.\(\)]{7,20}\d)/',
            '/(https?:\/\/|www\.)/i',
            '/\b[\w\-]+\.(com|net|org|mx|io|co|me|ly|app|club|online|site)\b/i',
            '/@[a-zA-Z0-9_\.]{2,}/i',
            '/\b(instagram|insta|ig|facebook|fb|whatsapp|wsp|wa|twitter|tiktok|telegram|snap|snapchat|onlyfans|of)\b/i',
            '/[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}/i',
        ];

        foreach ($forbiddenPatterns as $pattern) {
            if (preg_match($pattern, $body)) {
                return response()->json([
                    'error' => 'El comentario no puede contener teléfonos, links, emails ni referencias a redes sociales.',
                ], 422);
            }
        }

        try {
            $photo       = Photo::findOrFail((int)$photoId);
            $commentUuid = \Illuminate\Support\Str::uuid();

            DB::table('photo_comments')->insert([
                'id'         => $commentUuid,
                'photo_id'   => $photo->photo_uuid,
                'user_id'    => (string) $user->id,
                'body'       => $validated['body'],
                'status'     => 'approved',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $profile = $user->profile;

            return response()->json([
                'success' => true,
                'comment' => [
                    'id'          => $commentUuid,
                    'comment'     => $validated['body'],
                    'created_at'  => now()->toISOString(),
                    'user_id'     => (string) $user->id,
                    'user_nick'   => $profile->nickname ?? 'Usuario',
                    'user_avatar' => $profile->avatar_url ?? null,
                ],
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => $e->errors()], 422);
        } catch (\Throwable $e) {
            Log::error('DashboardController@storeComment: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno'], 500);
        }
    }
}
