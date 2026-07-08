<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminArticleCommentController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'pending');

        $comments = DB::table('article_comments as ac')
            ->join('articles as a', 'a.id', '=', 'ac.article_id')
            ->join('users as u', DB::raw('u.id::text'), '=', DB::raw('ac.user_id::text'))
            ->leftJoin('profiles as p', DB::raw('p.user_id::text'), '=', DB::raw('u.id::text'))
            ->where('ac.status', $status)
            ->orderByDesc('ac.created_at')
            ->select([
                'ac.id',
                'ac.body',
                'ac.status',
                'ac.created_at',
                'a.title as article_title',
                'a.id as article_id',
                DB::raw('COALESCE(p.nickname, u.username) as author'),
            ])
            ->paginate(20);

        $counts = [
            'pending'  => DB::table('article_comments')->where('status', 'pending')->count(),
            'approved' => DB::table('article_comments')->where('status', 'approved')->count(),
            'rejected' => DB::table('article_comments')->where('status', 'rejected')->count(),
        ];

        return view('admin.article-comments.index', compact('comments', 'status', 'counts'));
    }

    public function approve($id)
    {
        DB::table('article_comments')
            ->where('id', $id)
            ->update(['status' => 'approved', 'updated_at' => now()]);

        $redirect = request()->input('_redirect');
        return $redirect ? redirect($redirect)->with('success', 'Comentario aprobado.') : back()->with('success', 'Comentario aprobado.');
    }

    public function reject($id)
    {
        DB::table('article_comments')
            ->where('id', $id)
            ->update(['status' => 'rejected', 'updated_at' => now()]);

        $redirect = request()->input('_redirect');
        return $redirect ? redirect($redirect)->with('success', 'Comentario rechazado.') : back()->with('success', 'Comentario rechazado.');
    }

    public function destroy($id)
    {
        DB::table('article_comments')->where('id', $id)->delete();
        $redirect = request()->input('_redirect');
        return $redirect ? redirect($redirect)->with('success', 'Comentario eliminado.') : back()->with('success', 'Comentario eliminado.');
    }
}

