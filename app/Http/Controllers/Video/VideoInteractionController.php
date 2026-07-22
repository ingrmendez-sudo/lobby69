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
        $count = DB::table('video_likes')->where('video_id', $videoId)->count();
        $liked = false;
        if (Auth::check()) {
            $liked = DB::table('video_likes')
                ->where('video_id', $videoId)
                ->where('user_id', Auth::id())
                ->exists();
        }
        $likers = DB::table('video_likes')
            ->join('users', 'users.id', '=', 'video_likes.user_id')
            ->leftJoin('profiles', 'profiles.user_id', '=', 'video_likes.user_id')
            ->where('video_likes.video_id', $videoId)
            ->orderBy('video_likes.created_at', 'desc')
            ->select(['users.username', 'users.name', 'profiles.nickname'])
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
            ->where('user_id', $userId)
            ->exists();

        if ($exists) {
            DB::table('video_likes')
                ->where('video_id', $videoId)
                ->where('user_id', $userId)
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
        return response()->json(['count' => $count, 'liked' => $liked]);
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
}