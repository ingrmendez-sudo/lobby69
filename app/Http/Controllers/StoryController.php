<?php

namespace App\Http\Controllers;

use App\Models\Story;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class StoryController extends Controller
{
    // ─── Listado público de historias ──────────────────────────────
    public function index(Request $request)
    {
        $category = $request->get('category');

        $stories = Story::published()
            ->with('author')
            ->when($category, fn($q) => $q->where('category', $category))
            ->latest()
            ->paginate(12);

        $latestStories = Story::published()
            ->with('author')
            ->latest()
            ->take(5)
            ->get();

        $mostViewed = Story::published()
            ->with('author')
            ->orderByDesc('views')
            ->take(5)
            ->get();

        $recommended = Story::published()
            ->with('author')
            ->inRandomOrder()
            ->take(4)
            ->get();

        $categories = [
            'general'  => 'General',
            'romance'  => 'Romance',
            'misterio' => 'Misterio',
            'fantasia' => 'Fantasía',
            'terror'   => 'Terror',
            'aventura' => 'Aventura',
            'erotico'  => 'Erótico',
        ];

        return view('stories.index', compact(
            'stories', 'latestStories', 'mostViewed',
            'recommended', 'categories', 'category'
        ));
    }

    // ─── Ver historia completa ──────────────────────────────────────
    public function show(string $slug)
    {
        $story = Story::published()->where('slug', $slug)->with('author')->firstOrFail();
        $story->incrementViews();

        $related = Story::published()
            ->where('category', $story->category)
            ->where('id', '!=', $story->id)
            ->with('author')
            ->take(4)
            ->get();

        return view('stories.show', compact('story', 'related'));
    }

    // ─── Formulario de nueva historia ──────────────────────────────
    public function create()
    {
        $categories = [
            'general'  => 'General',
            'romance'  => 'Romance',
            'misterio' => 'Misterio',
            'fantasia' => 'Fantasía',
            'terror'   => 'Terror',
            'aventura' => 'Aventura',
            'erotico'  => 'Erótico',
        ];

        return view('stories.create', compact('categories'));
    }

    // ─── Guardar nueva historia ─────────────────────────────────────
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'content'     => 'required|string|min:50',
            'category'    => 'required|string',
            'status'      => 'required|in:draft,published',
            'cover_image' => 'nullable|image|max:4096',
        ]);

        $coverPath = null;
        if ($request->hasFile('cover_image')) {
            $coverPath = $request->file('cover_image')->store('stories/covers', 'public');
        }

        $story = Story::create([
            'user_id'      => Auth::id(),
            'title'        => $validated['title'],
            'slug'         => Story::generateSlug($validated['title']),
            'content'      => $validated['content'],
            'cover_image'  => $coverPath,
            'category'     => $validated['category'],
            'status'       => $validated['status'],
            'reading_time' => Story::calculateReadingTime($validated['content']),
        ]);

        $msg = $story->status === 'published'
            ? '¡Historia publicada exitosamente!'
            : 'Historia guardada como borrador.';

        return redirect()->route('stories.show', $story->slug)->with('success', $msg);
    }

    // ─── Formulario de edición ──────────────────────────────────────
    public function edit(Story $story)
    {
        abort_if($story->user_id !== Auth::id(), 403);

        $categories = [
            'general'  => 'General',
            'romance'  => 'Romance',
            'misterio' => 'Misterio',
            'fantasia' => 'Fantasía',
            'terror'   => 'Terror',
            'aventura' => 'Aventura',
            'erotico'  => 'Erótico',
        ];

        return view('stories.edit', compact('story', 'categories'));
    }

    // ─── Actualizar historia ────────────────────────────────────────
    public function update(Request $request, Story $story)
    {
        abort_if($story->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'content'     => 'required|string|min:50',
            'category'    => 'required|string',
            'status'      => 'required|in:draft,published',
            'cover_image' => 'nullable|image|max:4096',
        ]);

        if ($request->hasFile('cover_image')) {
            if ($story->cover_image) {
                Storage::disk('public')->delete($story->cover_image);
            }
            $validated['cover_image'] = $request->file('cover_image')
                ->store('stories/covers', 'public');
        }

        $story->update([
            'title'        => $validated['title'],
            'content'      => $validated['content'],
            'category'     => $validated['category'],
            'status'       => $validated['status'],
            'cover_image'  => $validated['cover_image'] ?? $story->cover_image,
            'reading_time' => Story::calculateReadingTime($validated['content']),
        ]);

        return redirect()->route('stories.show', $story->slug)
            ->with('success', 'Historia actualizada.');
    }

    // ─── Eliminar historia ──────────────────────────────────────────
    public function destroy(Story $story)
    {
        abort_if($story->user_id !== Auth::id(), 403);

        if ($story->cover_image) {
            Storage::disk('public')->delete($story->cover_image);
        }

        $story->delete();

        return redirect()->route('stories.index')->with('success', 'Historia eliminada.');
    }

    // ─── Mis historias ──────────────────────────────────────────────
    public function myStories()
    {
        $stories = Story::byUser(Auth::id())
            ->latest()
            ->paginate(10);

        return view('stories.my-stories', compact('stories'));
    }
}
