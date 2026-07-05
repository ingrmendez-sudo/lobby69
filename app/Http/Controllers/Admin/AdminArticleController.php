<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminArticleController extends Controller
{
    public function index()
    {
        $articles = DB::table('articles')->orderByDesc('created_at')->paginate(20);

        $stats = DB::table('articles')->selectRaw("
            count(*) as total,
            sum(case when published = true then 1 else 0 end) as publicados,
            sum(case when published = false then 1 else 0 end) as borradores,
            coalesce(sum(views), 0) as vistas_total
        ")->first();

        return view('admin.articles.index', compact('articles', 'stats'));
    }

    public function create()
    {
        $categories = $this->getCategories();
        return view('admin.articles.form', ['article' => null, 'categories' => $categories]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'     => 'required|string|max:200',
            'excerpt'   => 'nullable|string|max:500',
            'body'      => 'required|string',
            'category'  => 'nullable|string|max:100',
            'published' => 'nullable',
            'cover'     => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $coverPath = null;
        if ($request->hasFile('cover') && $request->file('cover')->isValid()) {
            $coverPath = $request->file('cover')->store('articles', 'public');
        }

        $isPublished = $request->boolean('published');

        DB::table('articles')->insert([
            'title'        => $request->title,
            'slug'         => $this->uniqueSlug($request->title),
            'excerpt'      => $request->excerpt,
            'body'         => $request->body,
            'category'     => $request->category,
            'cover_path'   => $coverPath,
            'published'    => $isPublished,
            'published_at' => $isPublished ? now() : null,
            'author_id'    => (string) auth()->id(),
            'views'        => 0,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        return redirect()->route('admin.articles.index')
                         ->with('success', 'Artículo creado correctamente.');
    }

    public function edit($id)
    {
        $article    = DB::table('articles')->where('id', $id)->first();
        $categories = $this->getCategories();
        abort_if(!$article, 404);
        return view('admin.articles.form', compact('article', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title'     => 'required|string|max:200',
            'excerpt'   => 'nullable|string|max:500',
            'body'      => 'required|string',
            'category'  => 'nullable|string|max:100',
            'published' => 'nullable',
            'cover'     => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $article   = DB::table('articles')->where('id', $id)->first();
        abort_if(!$article, 404);

        $coverPath   = $article->cover_path;
        $isPublished = $request->boolean('published');

        if ($request->hasFile('cover') && $request->file('cover')->isValid()) {
            if ($coverPath && Storage::disk('public')->exists($coverPath)) {
                Storage::disk('public')->delete($coverPath);
            }
            $coverPath = $request->file('cover')->store('articles', 'public');
        }

        if ($request->has('remove_cover') && $coverPath) {
            if (Storage::disk('public')->exists($coverPath)) {
                Storage::disk('public')->delete($coverPath);
            }
            $coverPath = null;
        }

        // Asignar published_at solo la primera vez que se publica
        $publishedAt = $article->published_at;
        if ($isPublished && !$article->published) {
            $publishedAt = now();
        }

        DB::table('articles')->where('id', $id)->update([
            'title'        => $request->title,
            'excerpt'      => $request->excerpt,
            'body'         => $request->body,
            'category'     => $request->category,
            'cover_path'   => $coverPath,
            'published'    => $isPublished,
            'published_at' => $publishedAt,
            'updated_at'   => now(),
        ]);

        return redirect()->route('admin.articles.index')
                         ->with('success', 'Artículo actualizado correctamente.');
    }

    public function destroy($id)
    {
        $article = DB::table('articles')->where('id', $id)->first();
        if ($article && $article->cover_path) {
            Storage::disk('public')->delete($article->cover_path);
        }
        DB::table('articles')->where('id', $id)->delete();
        return back()->with('success', 'Artículo eliminado.');
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $i    = 1;
        while (DB::table('articles')->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }

    private function getCategories(): array
    {
        return [
            'Noticias',
            'Eventos',
            'Consejos',
            'Comunidad',
            'Lifestyle',
            'Seguridad',
            'Novedades',
        ];
    }
}
