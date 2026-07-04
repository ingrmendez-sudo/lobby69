<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminArticleController extends Controller
{
    public function index()
    {
        $articles = DB::table('articles')->orderByDesc('created_at')->paginate(20);
        return view('admin.articles.index', compact('articles'));
    }

    public function create()
    {
        return view('admin.articles.form', ['article' => null]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'        => 'required|string|max:200',
            'excerpt'      => 'nullable|string|max:500',
            'body'         => 'required|string',
            'category'     => 'nullable|string|max:100',
            'cover_url'    => 'nullable|url|max:500',
            'published'    => 'boolean',
        ]);

        DB::table('articles')->insert([
            ...$data,
            'slug'       => Str::slug($data['title']) . '-' . Str::random(4),
            'published'  => $request->boolean('published'),
            'author_id'  => (string) auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.articles.index')->with('success', 'Artículo creado.');
    }

    public function edit($id)
    {
        $article = DB::table('articles')->where('id', $id)->first();
        abort_if(!$article, 404);
        return view('admin.articles.form', compact('article'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'title'     => 'required|string|max:200',
            'excerpt'   => 'nullable|string|max:500',
            'body'      => 'required|string',
            'category'  => 'nullable|string|max:100',
            'cover_url' => 'nullable|url|max:500',
            'published' => 'boolean',
        ]);

        DB::table('articles')->where('id', $id)->update([
            ...$data,
            'published'  => $request->boolean('published'),
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.articles.index')->with('success', 'Artículo actualizado.');
    }

    public function destroy($id)
    {
        DB::table('articles')->where('id', $id)->delete();
        return back()->with('success', 'Artículo eliminado.');
    }
}
