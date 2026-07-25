<?php

namespace App\Http\Controllers\Video;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VideoInteractionController extends Controller
{
    public function likers($videoId)
    {
        $videoId = (int) $videoId;
        $userId  = Auth::id();

        $count = DB::table('video_likes')->where('video_id', $videoId)->count();

        $liked = $userId
            ? DB::table('video_likes')
                ->where('video_id', $videoId)
                ->whereRaw('user_id::text = ?', [(string)$userId])
                ->exists()
            : false;

        $likers = DB::table('video_likes as vl')
            ->join('users as u',         DB::raw('u.id::text'), '=', DB::raw('vl.user_id::text'))
            ->leftJoin('profiles as pr', DB::raw('pr.user_id::text'), '=', DB::raw('u.id::text'))
            ->where('vl.video_id', $videoId)
            ->orderByDesc('vl.created_at')
            ->limit(8)
            ->select([
                DB::raw('COALESCE(pr.nickname, u.username) AS nick'),
                DB::raw("(SELECT ap.id FROM photos ap
                          WHERE ap.user_id::text = u.id::text
                            AND ap.is_profile_photo = true
                            AND ap.status = 'approved'
                          LIMIT 1) AS avatar_id"),
            ])
            ->get();

        return response()->json(['count' => $count, 'liked' => $liked, 'likers' => $likers]);
    }

    public function toggleLike(Request $request, $videoId)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'No autenticado'], 401);
        }
        $videoId = (int) $videoId;
        $userId  = Auth::id();

        $exists = DB::table('video_likes')
            ->where('video_id', $videoId)
            ->whereRaw('user_id::text = ?', [(string)$userId])
            ->exists();

        if ($exists) {
            DB::table('video_likes')
                ->where('video_id', $videoId)
                ->whereRaw('user_id::text = ?', [(string)$userId])
                ->delete();
            $liked = false;
        } else {
            DB::table('video_likes')->insert([
                'video_id'   => $videoId,
                'user_id'    => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $liked = true;
        }

        $count = DB::table('video_likes')->where('video_id', $videoId)->count();

        // Retornar likers actualizados para evitar segundo fetch desde el JS
        $likers = DB::table('video_likes as vl')
            ->join('users as u',         DB::raw('u.id::text'), '=', DB::raw('vl.user_id::text'))
            ->leftJoin('profiles as pr', DB::raw('pr.user_id::text'), '=', DB::raw('u.id::text'))
            ->where('vl.video_id', $videoId)
            ->orderByDesc('vl.created_at')
            ->limit(8)
            ->select([
                DB::raw('COALESCE(pr.nickname, u.username) AS nick'),
                DB::raw("(SELECT ap.id FROM photos ap
                          WHERE ap.user_id::text = u.id::text
                            AND ap.is_profile_photo = true
                            AND ap.status = 'approved'
                          LIMIT 1) AS avatar_id"),
            ])
            ->get();

        return response()->json(['count' => $count, 'liked' => $liked, 'likers' => $likers]);
    }

    public function comments($videoId)
    {
        $videoId  = (int) $videoId;
        $comments = DB::table('video_comments')
            ->join('users', 'users.id', '=', 'video_comments.user_id')
            ->leftJoin('profiles', 'profiles.user_id', '=', 'video_comments.user_id')
            ->where('video_comments.video_id', $videoId)
            ->whereNull('video_comments.parent_id')
            ->orderBy('video_comments.created_at', 'asc')
            ->select([
                'video_comments.id',
                'video_comments.body',
                'video_comments.created_at',
                'video_comments.user_id',
                'users.username',
                'users.name',
                'profiles.nickname',
            ])
            ->get();

        foreach ($comments as $comment) {
            $comment->replies = DB::table('video_comments')
                ->join('users', 'users.id', '=', 'video_comments.user_id')
                ->leftJoin('profiles', 'profiles.user_id', '=', 'video_comments.user_id')
                ->where('video_comments.video_id', $videoId)
                ->where('video_comments.parent_id', $comment->id)
                ->orderBy('video_comments.created_at', 'asc')
                ->select([
                    'video_comments.id',
                    'video_comments.body',
                    'video_comments.created_at',
                    'video_comments.user_id',
                    'users.username',
                    'users.name',
                    'profiles.nickname',
                ])
                ->get();
        }

        return response()->json($comments);
    }

    public function storeComment(Request $request, $videoId)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'No autenticado'], 401);
        }
        $request->validate(['body' => 'required|string|max:500']);
        $videoId = (int) $videoId;

        $id = DB::table('video_comments')->insertGetId([
            'video_id'   => $videoId,
            'user_id'    => Auth::id(),
            'parent_id'  => null,
            'body'       => $request->body,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $comment = DB::table('video_comments')
            ->join('users', 'users.id', '=', 'video_comments.user_id')
            ->leftJoin('profiles', 'profiles.user_id', '=', 'video_comments.user_id')
            ->where('video_comments.id', $id)
            ->select([
                'video_comments.id',
                'video_comments.body',
                'video_comments.created_at',
                'video_comments.user_id',
                'users.username',
                'users.name',
                'profiles.nickname',
            ])
            ->first();

        $comment->replies = [];
        return response()->json($comment, 201);
    }

    public function storeReply(Request $request, $videoId, $commentId)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'No autenticado'], 401);
        }
        $request->validate(['body' => 'required|string|max:500']);
        $videoId   = (int) $videoId;
        $commentId = (int) $commentId;

        // Solo el dueno del video puede responder
        $video = DB::table('videos')->where('id', $videoId)->first();
        if (!$video || (string)$video->user_id !== (string)Auth::id()) {
            return response()->json(['error' => 'Solo el dueno puede responder'], 403);
        }

        $id = DB::table('video_comments')->insertGetId([
            'video_id'   => $videoId,
            'user_id'    => Auth::id(),
            'parent_id'  => $commentId,
            'body'       => $request->body,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $reply = DB::table('video_comments')
            ->join('users', 'users.id', '=', 'video_comments.user_id')
            ->leftJoin('profiles', 'profiles.user_id', '=', 'video_comments.user_id')
            ->where('video_comments.id', $id)
            ->select([
                'video_comments.id',
                'video_comments.body',
                'video_comments.created_at',
                'video_comments.user_id',
                'users.username',
                'users.name',
                'profiles.nickname',
            ])
            ->first();

        return response()->json($reply, 201);
    }

    public function deleteComment(Request $request, $videoId, $commentId)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'No autenticado'], 401);
        }
        $commentId = (int) $commentId;
        $comment   = DB::table('video_comments')->where('id', $commentId)->first();

        if (!$comment) {
            return response()->json(['error' => 'No encontrado'], 404);
        }

        $isOwner = (string)$comment->user_id === (string)Auth::id();
        $isAdmin = Auth::user()->role === 'admin';

        if (!$isOwner && !$isAdmin) {
            return response()->json(['error' => 'Sin permisos'], 403);
        }

        DB::table('video_comments')->where('parent_id', $commentId)->delete();
        DB::table('video_comments')->where('id', $commentId)->delete();

        return response()->json(['deleted' => true]);
    }

    /**
     * GET /videos/{id}/likes
     * Retorna count + si el usuario autenticado ya dio like.
     */
    public function likesStatus($videoId)
    {
        $videoId = (int) $videoId;
        $video   = DB::table('videos')->where('id', $videoId)->first();
        if (!$video) return response()->json(['count'=>0,'liked'=>false,'views'=>0,'likers'=>[]]);
        $userId  = Auth::id();

        $count = DB::table('video_likes')
            ->where('video_id', $videoId)
            ->count();

        $liked = $userId
            ? DB::table('video_likes')
                ->where('video_id', $videoId)
                ->whereRaw('user_id::text = ?', [(string)$userId])
                ->exists()
            : false;

        // Top likers (nick + avatar_id) — máx 8
        $likers = DB::table('video_likes as vl')
            ->join('users as u',    DB::raw('u.id::text'), '=', DB::raw('vl.user_id::text'))
            ->leftJoin('profiles as pr', DB::raw('pr.user_id::text'), '=', DB::raw('u.id::text'))
            ->where('vl.video_id', $videoId)
            ->orderByDesc('vl.created_at')
            ->limit(8)
            ->select([
                DB::raw('COALESCE(pr.nickname, u.username) AS nick'),
                DB::raw("(SELECT ap.id FROM photos ap
                          WHERE ap.user_id::text = u.id::text
                            AND ap.is_profile_photo = true
                            AND ap.status = 'approved'
                          LIMIT 1) AS avatar_id"),
            ])
            ->get();

        return response()->json([
            'count'  => $count,
            'liked'  => $liked,
            'views'  => (int)($video->views_count ?? 0),
            'likers' => $likers,
        ]);
    }

    /**
     * POST /videos/{id}/view
     * Registra una vista autenticada (idempotente por sesión).
     */
    public function recordView(Request $request, $videoId)
    {
        $videoId = (int) $videoId;
        $userId  = Auth::id();

        $video = DB::table('videos')->where('id', $videoId)->first();
        if (!$video) {
            return response()->json(['ok' => false], 404);
        }

        // Solo incrementar si el video es aprobado y el viewer no es el dueño
        if ($video->status === 'approved' &&
            (!$userId || (string)$video->user_id !== (string)$userId)) {
            DB::table('videos')
                ->where('id', $videoId)
                ->increment('views_count');
        }

        // Releer views_count tras increment() — el objeto en memoria no se refresca
        $updatedViews = DB::table('videos')->where('id', $videoId)->value('views_count');
        return response()->json(['ok' => true, 'views' => (int)($updatedViews ?? 0)]);
    }
}