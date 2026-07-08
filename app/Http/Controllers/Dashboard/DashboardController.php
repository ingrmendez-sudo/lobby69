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
                DB::raw('COALESCE(p.display_name, u.username) as display_name'),
                DB::raw('(SELECT id FROM photos ap WHERE ap.user_id::text = photo_comments.user_id::text AND ap.is_profile_photo = true AND ap.status = \'approved\' LIMIT 1) as avatar_photo_id'),
            ])
            ->get();

        $avatarPhotoId = $photo->avatar_photo_id ?? null;

        return response()->json([
            'photo' => [
                'id'             => $photo->photo_uuid,
                'file_path'      => $photo->file_path,
                'caption'        => $photo->caption ?? '',
                'likes_count'    => (int) $photo->likes_count,
                'comments_count' => (int) $photo->comments_count,
                'user_liked'     => (bool) $photo->user_liked,
                'comments'       => $comments,
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

        return response()->json([
            'success' => true,
            'comment' => [
                'id'              => $commentId,
                'body'            => $request->input('body'),
                'user_nick'       => $profile?->nickname ?? $profile?->display_name ?? 'Usuario',
                'avatar_photo_id' => $avatarPhotoId,
                'created_at'      => Carbon::now()->toISOString(),
            ],
        ]);
    }

    private function buildFeedQuery(User $user, string $tab)
    {
        $userId = (string) $user->id;

        $query = DB::table('photos')
            ->join('users as u', function ($j) {
                $j->on(DB::raw('u.id::text'), '=', DB::raw('photos.user_id::text'));
            })
            ->leftJoin('profiles as p', function ($j) {
                $j->on(DB::raw('p.user_id::text'), '=', DB::raw('u.id::text'));
            })
            ->where('photos.status', 'approved')
            ->where('u.active', true)
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
                DB::raw('(SELECT id FROM photos ap WHERE ap.user_id::text = u.id::text AND ap.is_profile_photo = true AND ap.status = \'approved\' LIMIT 1) as avatar_photo_id'),
                DB::raw('(SELECT COUNT(*) FROM photo_likes pl WHERE pl.photo_id::text = photos.photo_uuid::text) as likes_count'),
                DB::raw('(SELECT COUNT(*) FROM photo_comments pc WHERE pc.photo_id::text = photos.photo_uuid::text AND pc.status = \'approved\') as comments_count'),
                DB::raw('EXISTS(SELECT 1 FROM photo_likes pl WHERE pl.photo_id::text = photos.photo_uuid::text AND pl.user_id::text = \'' . $userId . '\') as user_liked'),
            ]);

        if ($tab === 'following') {
            $query->whereIn(DB::raw('photos.user_id::text'), function ($sub) use ($userId) {
                $sub->select(DB::raw('following_id::text'))
                    ->from('follows')
                    ->whereRaw('follower_id::text = ?', [$userId]);
            });
        }

        if ($tab === 'popular') {
            $query->orderByDesc(DB::raw('(SELECT COUNT(*) FROM photo_likes pl WHERE pl.photo_id::text = photos.photo_uuid::text)'));
        } else {
            $query->orderByDesc('photos.created_at');
        }

        return $query;
    }
}
