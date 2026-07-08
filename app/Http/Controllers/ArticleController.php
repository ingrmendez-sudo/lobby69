<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $articles = DB::table('articles')
            ->where('published', true)
            ->orderByDesc('created_at')
            ->get();

        return view('articles.index', compact('articles'));
    }

    public function show(Request $request, $id)
    {
        $user = auth()->user();

        $article = DB::table('articles')
            ->where('id', $id)
            ->where('published', true)
            ->first();

        if (!$article) abort(404);

        // Incrementar contador de vistas
        DB::table('articles')->where('id', $id)->increment('views');

        // ¿El usuario ya dio like?
        $userLiked = DB::table('article_likes')
            ->where('article_id', $id)
            ->whereRaw('user_id::text = ?', [(string) $user->id])
            ->exists();

        // Contadores
        $likesCount = DB::table('article_likes')
            ->where('article_id', $id)
            ->count();

        // Comentarios aprobados
        $comments = DB::table('article_comments')
            ->join('users as u', function($j) {
                $j->on(DB::raw('u.id::text'), '=', DB::raw('article_comments.user_id::text'));
            })
            ->leftJoin('profiles as p', function($j) {
                $j->on(DB::raw('p.user_id::text'), '=', DB::raw('u.id::text'));
            })
            ->where('article_comments.article_id', $id)
            ->where('article_comments.status', 'approved')
            ->orderBy('article_comments.created_at', 'asc')
            ->select([
                'article_comments.id',
                'article_comments.body',
                'article_comments.created_at',
                DB::raw('COALESCE(p.nickname, u.username) as nickname'),
                DB::raw('COALESCE(p.display_name, u.username) as display_name'),
                DB::raw('(SELECT ph.id FROM photos ph WHERE ph.user_id::text = u.id::text AND ph.is_profile_photo = true AND ph.status = \'approved\' LIMIT 1) as avatar_photo_id'),
            ])
            ->get();

        return view('articles.show', compact(
            'article',
            'userLiked',
            'likesCount',
            'comments'
        ));
    }

    public function toggleLike(Request $request, $id)
    {
        $user   = auth()->user();
        $userId = (string) $user->id;

        $article = DB::table('articles')->where('id', $id)->where('published', true)->first();
        if (!$article) return response()->json(['error' => 'No encontrado'], 404);

        $existing = DB::table('article_likes')
            ->where('article_id', $id)
            ->whereRaw('user_id::text = ?', [$userId])
            ->first();

        if ($existing) {
            DB::table('article_likes')
                ->where('article_id', $id)
                ->whereRaw('user_id::text = ?', [$userId])
                ->delete();
            $liked = false;
        } else {
            DB::table('article_likes')->insert([
                'id'         => (string) Str::uuid(),
                'article_id' => $id,
                'user_id'    => $userId,
                'created_at' => Carbon::now(),
            ]);
            $liked = true;
        }

        $count = DB::table('article_likes')->where('article_id', $id)->count();

        return response()->json(['liked' => $liked, 'count' => $count]);
    }

    public function storeComment(Request $request, $id)
    {
        $request->validate(['body' => 'required|string|min:1|max:1000']);

        $user   = auth()->user();
        $userId = (string) $user->id;

        $article = DB::table('articles')->where('id', $id)->where('published', true)->first();
        if (!$article) return response()->json(['error' => 'No encontrado'], 404);

        DB::table('article_comments')->insert([
            'id'         => (string) Str::uuid(),
            'article_id' => $id,
            'user_id'    => $userId,
            'body'       => $request->input('body'),
            'status'     => 'pending',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        return back()->with('success', '✅ Comentario enviado. Será revisado antes de publicarse.');
    }
}
