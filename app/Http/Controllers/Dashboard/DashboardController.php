<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        /** @var User $user */
        $user = auth()->user();
        $tab  = $request->get('tab', 'new');
        $feed = $this->buildFeedQuery($user, $tab)->paginate(24);

        return view('dashboard.index', [
            'user'    => $user,
            'profile' => $user->profile,
            'tab'     => $tab,
            'feed'    => $feed,
        ]);
    }

    public function feedAjax(Request $request)
    {
        $user = auth()->user();
        $tab  = $request->get('tab', 'new');
        $page = max(1, (int) $request->get('page', 1));
        $feed = $this->buildFeedQuery($user, $tab)->paginate(24, ['*'], 'page', $page);

        $html = view('dashboard._feed_items', [
            'feed' => $feed,
            'user' => $user,
        ])->render();

        return response()->json([
            'html'        => $html,
            'hasMore'     => $feed->hasMorePages(),
            'currentPage' => $feed->currentPage(),
            'nextPage'    => $feed->currentPage() + 1,
        ]);
    }

    public function photoModal(Request $request, $id)
    {
        $user   = auth()->user();
        $userId = (string) $user->id;

        $photo = DB::table('photos')
            ->join('users as u', function ($j) {
                $j->on(DB::raw('u.id::text'), '=', DB::raw('photos.user_id::text'));
            })
            ->leftJoin('profiles as p', function ($j) {
                $j->on(DB::raw('p.user_id::text'), '=', DB::raw('u.id::text'));
            })
            ->where(function($q) use ($id) {
                $q->whereRaw('photos.photo_uuid::text = ?', [$id])
                  ->orWhereRaw('photos.id::text = ?', [$id]);
            })
            ->select([
                'photos.id',
                'photos.photo_uuid',
                'photos.user_id',
                'photos.file_path',
                'photos.caption',
                'photos.created_at',
                DB::raw('COALESCE(p.nickname, u.username) as nickname'),
                DB::raw('COALESCE(p.display_name, u.username) as display_name'),
                DB::raw('p.avatar_url as avatar_url'),
                DB::raw('p.verified_profile as verified_profile'),
                DB::raw('p.profile_type as profile_type'),
                DB::raw('(SELECT id FROM photos ap WHERE ap.user_id::text = u.id::text AND ap.is_profile_photo = true AND ap.status = \'approved\' LIMIT 1) as avatar_photo_id'),
                DB::raw('(SELECT COUNT(*) FROM photo_likes pl WHERE pl.photo_id::text = photos.photo_uuid::text) as likes_count'),
                DB::raw('(SELECT COUNT(*) FROM photo_comments pc WHERE pc.photo_id::text = photos.photo_uuid::text AND pc.status = \'approved\') as comments_count'),
                DB::raw('EXISTS(SELECT 1 FROM photo_likes pl WHERE pl.photo_id::text = photos.photo_uuid::text AND pl.user_id::text = \'' . $userId . '\') as user_liked'),
            ])
            ->first();

        if (!$photo) {
            return response()->json(['error' => 'Foto no encontrada'], 404);
        }

        $comments = DB::table('photo_comments')
            ->join('users as u', function ($j) {
                $j->on(DB::raw('u.id::text'), '=', DB::raw('photo_comments.user_id::text'));
            })
            ->leftJoin('profiles as p', function ($j) {
                $j->on(DB::raw('p.user_id::text'), '=', DB::raw('u.id::text'));
            })
            ->whereRaw('photo_comments.photo_id::text = ?', [(string)$photo->photo_uuid])
            ->where('photo_comments.status', 'approved')
            ->orderBy('photo_comments.created_at', 'asc')
            ->select([
                'photo_comments.id',
                'photo_comments.body',
                'photo_comments.created_at',
                DB::raw('COALESCE(p.nickname, u.username) as user_nick'),
                DB::raw('p.nickname as commenter_nick'),
                DB::raw('COALESCE(p.display_name, u.username) as display_name'),
                DB::raw('(SELECT id FROM photos ap WHERE ap.user_id::text = photo_comments.user_id::text AND ap.is_profile_photo = true AND ap.status = \'approved\' LIMIT 1) as avatar_photo_id'),
            ])
            ->get();

        $avatarPhotoId = $photo->avatar_photo_id ?? null;

        // Quienes dieron like (máx. 20, más recientes primero)
        $likers = DB::table('photo_likes')
            ->leftJoin('profiles as lp', function ($j) {
                $j->on(DB::raw('lp.user_id::text'), '=', DB::raw('photo_likes.user_id::text'));
            })
            ->leftJoin('users as lu', function ($j) {
                $j->on(DB::raw('lu.id::text'), '=', DB::raw('photo_likes.user_id::text'));
            })
            ->whereRaw('photo_likes.photo_id::text = ?', [(string) $photo->photo_uuid])
            ->orderByDesc('photo_likes.created_at')
            ->limit(20)
            ->select([
                DB::raw('COALESCE(lp.nickname, lu.username) as nick'),
                DB::raw('(SELECT id FROM photos ap WHERE ap.user_id::text = photo_likes.user_id::text AND ap.is_profile_photo = true AND ap.status = \'approved\' LIMIT 1) as avatar_id'),
            ])
            ->get()
            ->map(fn($l) => ['nick' => $l->nick ?? 'Usuario', 'avatar_id' => $l->avatar_id])
            ->toArray();

        return response()->json([
            'photo' => [
                'id'             => $photo->photo_uuid,
                'file_path'      => $photo->file_path,
                'caption'        => $photo->caption ?? '',
                'likes_count'    => (int) $photo->likes_count,
                'comments_count' => (int) $photo->comments_count,
                'user_liked'     => (bool) $photo->user_liked,
                'comments'       => $comments,
                'likers'         => $likers,
            ],
            'owner' => [
                'name'            => $photo->display_name ?? 'Usuario',
                'nickname'        => $photo->nickname ?? null,
                'avatar_photo_id' => $avatarPhotoId,
                'url'             => $photo->nickname ? '/u/' . $photo->nickname : null,
            ],
        ]);
    }

    public function toggleLike(Request $request, $id)
    {
        $user   = auth()->user();
        $userId = (string) $user->id;

        $photo = DB::table('photos')
            ->where(function($q) use ($id) {
                $q->whereRaw('photo_uuid::text = ?', [$id])
                  ->orWhereRaw('id::text = ?', [$id]);
            })
            ->select(['photo_uuid'])
            ->first();

        if (!$photo) {
            return response()->json(['error' => 'Foto no encontrada'], 404);
        }

        $photoUuid = (string) $photo->photo_uuid;

        $existing = DB::table('photo_likes')
            ->whereRaw('photo_id::text = ?', [$photoUuid])
            ->whereRaw('user_id::text = ?', [$userId])
            ->first();

        if ($existing) {
            DB::table('photo_likes')
                ->whereRaw('photo_id::text = ?', [$photoUuid])
                ->whereRaw('user_id::text = ?', [$userId])
                ->delete();
            $liked = false;
        } else {
            DB::table('photo_likes')->insert([
                'id'         => (string) Str::uuid(),
                'photo_id'   => $photoUuid,
                'user_id'    => $userId,
                'created_at' => Carbon::now(),
            ]);
            $liked = true;
        }

        $count = DB::table('photo_likes')
            ->whereRaw('photo_id::text = ?', [$photoUuid])
            ->count();


        // Notificar al dueño si se dio like (no al quitar)
        if ($liked) {
            $likeOwner = DB::table('photos')
                ->whereRaw('photo_uuid::text = ?', [$photoUuid])
                ->value('user_id');
            $likerNick = DB::table('profiles')
                ->whereRaw('user_id::text = ?', [(string) auth()->id()])
                ->value('nickname');
            if ($likeOwner && (string)$likeOwner !== (string)auth()->id()) {
                \App\Http\Controllers\NotificationController::create((string)$likeOwner, 'like', [
                    'from_nick' => $likerNick ?? 'Alguien',
                    'photo_id'  => $photoUuid,
                ]);
            }
        }
        return response()->json([
            'liked'       => $liked,
            'likes_count' => $count,
        ]);
    }

    public function storeComment(Request $request, $id)
    {
        $request->validate([
            'body' => 'required|string|min:1|max:500',
        ]);

        $user   = auth()->user();
        $userId = (string) $user->id;

        $photo = DB::table('photos')
            ->where(function($q) use ($id) {
                $q->whereRaw('photo_uuid::text = ?', [$id])
                  ->orWhereRaw('id::text = ?', [$id]);
            })
            ->select(['photo_uuid'])
            ->first();

        if (!$photo) {
            return response()->json(['error' => 'Foto no encontrada'], 404);
        }

        $commentId = (string) Str::uuid();

        DB::table('photo_comments')->insert([
            'id'         => $commentId,
            'photo_id'   => (string) $photo->photo_uuid,
            'user_id'    => $userId,
            'body'       => $request->input('body'),
            'status'     => 'approved',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $profile = DB::table('profiles')
            ->whereRaw('user_id::text = ?', [$userId])
            ->select(['display_name', 'nickname'])
            ->first();

        $avatarPhotoId = DB::table('photos')
            ->whereRaw('user_id::text = ?', [$userId])
            ->where('is_profile_photo', true)
            ->where('status', 'approved')
            ->value('id');


        // Notificar al dueño de la foto cuando alguien comenta
        $photoOwner = DB::table('photos')
            ->whereRaw('photo_uuid::text = ?', [(string) $photo->photo_uuid])
            ->value('user_id');
        if ($photoOwner && (string)$photoOwner !== $userId) {
            \App\Http\Controllers\NotificationController::create((string)$photoOwner, 'comment', [
                'from_nick' => $profile?->nickname ?? 'Alguien',
                'photo_id'  => (string) $photo->photo_uuid,
            ]);
        }

        return response()->json([
            'success' => true,
            'comment' => [
                'id'              => $commentId,
                'body'            => $request->input('body'),
                'user_nick'       => $profile?->nickname ?? $profile?->display_name ?? 'Usuario',
                'commenter_nick'  => $profile?->nickname ?? null,
                'avatar_photo_id' => $avatarPhotoId,
                'created_at'      => Carbon::now()->toISOString(),
            ],
        ]);
    }

    private function buildFeedQuery(User $user, string $tab)
    {
        $userId = (string) $user->id;

        $userCity = DB::table('profiles')
            ->whereRaw('user_id::text = ?', [$userId])
            ->value('city');

        $followingIds = DB::table('follows')
            ->whereRaw('follower_id::text = ?', [$userId])
            ->pluck('following_id')
            ->map(fn($id) => (string) $id)
            ->toArray();

        $cityClause = $userCity
            ? "CASE WHEN p.city ILIKE '%" . addslashes($userCity) . "%' THEN 2 ELSE 0 END"
            : '0';

        $followClause = !empty($followingIds)
            ? "CASE WHEN photos.user_id::text IN ('" . implode("','", $followingIds) . "') THEN 3 ELSE 0 END"
            : '0';

        $scoreSQL = "({$followClause} + {$cityClause} + "
                  . "CASE WHEN p.verified_profile = true THEN 1 ELSE 0 END "
                  . "+ EXTRACT(EPOCH FROM (NOW() - photos.created_at)) / -86400.0 * 0.5)";

        $query = DB::table('photos')
            ->join('users as u', DB::raw('u.id::text'), '=', DB::raw('photos.user_id::text'))
            ->leftJoin('profiles as p', DB::raw('p.user_id::text'), '=', DB::raw('u.id::text'))
            ->leftJoin(DB::raw('(
                SELECT photo_id::text AS pl_photo_id, COUNT(*) AS likes_count
                FROM photo_likes
                GROUP BY photo_id::text
            ) as pl_agg'), 'pl_agg.pl_photo_id', '=', DB::raw('photos.photo_uuid::text'))
            ->leftJoin(DB::raw("(
                SELECT photo_id::text AS pc_photo_id, COUNT(*) AS comments_count
                FROM photo_comments
                WHERE status = 'approved'
                GROUP BY photo_id::text
            ) as pc_agg"), 'pc_agg.pc_photo_id', '=', DB::raw('photos.photo_uuid::text'))
            ->leftJoin(DB::raw("(
                SELECT photo_id::text AS ul_photo_id, true AS user_liked
                FROM photo_likes
                WHERE user_id::text = '{$userId}'
            ) as ul_agg"), 'ul_agg.ul_photo_id', '=', DB::raw('photos.photo_uuid::text'))
            ->leftJoin(DB::raw("(
                SELECT DISTINCT ON (user_id) user_id::text AS av_user_id, id AS avatar_photo_id, file_path AS avatar_file_path
                FROM photos
                WHERE is_profile_photo = true AND status = 'approved'
                ORDER BY user_id
            ) as av_agg"), 'av_agg.av_user_id', '=', DB::raw('u.id::text'))
            ->where('photos.status', 'approved')
            ->where('u.active', true)
            ->whereRaw('photos.user_id::text != ?', [$userId])
            ->select([
                'photos.id',
                'photos.photo_uuid',
                'photos.user_id',
                'photos.file_path',
                'photos.thumbnail_path',
                'photos.caption',
                'photos.is_profile_photo',
                'photos.created_at',
                DB::raw('COALESCE(p.nickname, u.username) as nickname'),
                DB::raw('COALESCE(p.display_name, u.username) as display_name'),
                DB::raw('p.verified_profile as verified_profile'),
                DB::raw('p.profile_type as profile_type'),
                DB::raw('p.city as user_city'),
                DB::raw('av_agg.avatar_photo_id as avatar_photo_id'),
                DB::raw('av_agg.avatar_file_path as avatar_file_path'),
                DB::raw('COALESCE(pl_agg.likes_count, 0) as likes_count'),
                DB::raw('COALESCE(pc_agg.comments_count, 0) as comments_count'),
                DB::raw('COALESCE(ul_agg.user_liked, false) as user_liked'),
                DB::raw($scoreSQL . ' as feed_score'),
            ]);

        if ($tab === 'following') {
            $query->whereIn(DB::raw('photos.user_id::text'), function ($sub) use ($userId) {
                $sub->select(DB::raw('following_id::text'))
                    ->from('follows')
                    ->whereRaw('follower_id::text = ?', [$userId]);
            });
        }

        if ($tab === 'popular') {
            $query->orderByDesc(DB::raw('COALESCE(pl_agg.likes_count, 0)'));
        } else {
            $query->orderByDesc(DB::raw($scoreSQL . ' + RANDOM() * 0.3'));
        }

        return $query;
    }

    /**
     * El dueño de la foto responde un comentario.
     */
    public function replyComment(Request $request, $photoId, $commentId)
    {
        $request->validate(['body' => 'required|string|min:1|max:500']);

        $user   = auth()->user();
        $userId = (string) $user->id;

        $photo = DB::table('photos')
            ->where(function($q) use ($photoId) {
                $q->whereRaw('photo_uuid::text = ?', [$photoId])
                  ->orWhereRaw('id::text = ?', [$photoId]);
            })
            ->select(['photo_uuid', 'user_id'])
            ->first();

        if (!$photo) {
            return response()->json(['error' => 'Foto no encontrada.'], 404);
        }

        if ($userId !== (string) $photo->user_id) {
            return response()->json(['error' => 'Solo el dueño de la foto puede responder.'], 403);
        }

        // Verificar que el comentario padre existe
        $parent = DB::table('photo_comments')
            ->whereRaw('id::text = ?', [$commentId])
            ->whereRaw('photo_id::text = ?', [(string) $photo->photo_uuid])
            ->first();

        if (!$parent) {
            return response()->json(['error' => 'Comentario no encontrado.'], 404);
        }

        $replyId = (string) Str::uuid();

        DB::table('photo_comments')->insert([
            'id'         => $replyId,
            'photo_id'   => (string) $photo->photo_uuid,
            'user_id'    => $userId,
            'parent_id'  => $commentId,
            'body'       => strip_tags($request->input('body')),
            'status'     => 'approved',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $profile = DB::table('profiles')
            ->whereRaw('user_id::text = ?', [$userId])
            ->select(['display_name', 'nickname'])
            ->first();

        return response()->json([
            'success' => true,
            'reply'   => [
                'id'       => $replyId,
                'body'     => strip_tags($request->input('body')),
                'user_nick'=> $profile?->nickname ?? $profile?->display_name ?? 'Usuario',
                'commenter_nick'  => $profile?->nickname ?? null,
            ]
        ]);
    }
}




