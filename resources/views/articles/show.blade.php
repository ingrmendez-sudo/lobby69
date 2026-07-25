@extends('layouts.app')
@section('title', ($article->title ?? 'Artículo') . ' — LOBBY69')
@push('sidebar-left')
    @include('layouts.sidebar-left')
@endpush
@push('sidebar-right')
    @include('layouts.sidebar-right')
@endpush

@push('styles')
<style>
.art-body { font-size:.97rem; color:var(--theme-text); line-height:1.8; opacity:.92; }
.art-body p { margin:0 0 1rem; }
.art-comment-item {
    display:flex; gap:.75rem; padding:.75rem 0;
    border-bottom:1px solid var(--theme-border);
}
.art-comment-item:last-child { border-bottom:none; }
.art-comment-avatar {
    width:36px; height:36px; border-radius:50%; object-fit:cover; flex-shrink:0;
}
.art-comment-body { font-size:.88rem; color:var(--theme-text); margin:.2rem 0 0; line-height:1.5; }
.art-comment-nick { font-size:.82rem; font-weight:700; color:var(--theme-text); }
.art-comment-date { font-size:.72rem; color:var(--theme-muted); margin-left:.4rem; }
.art-like-btn {
    display:inline-flex; align-items:center; gap:.5rem;
    background:transparent; border:1px solid var(--theme-border);
    border-radius:20px; padding:.45rem 1rem; cursor:pointer;
    font-size:.88rem; color:var(--theme-text); transition:all .2s;
}
.art-like-btn:hover, .art-like-btn.is-liked {
    background:rgba(224,86,160,.12); border-color:#e056a0; color:#e056a0;
}
.art-like-btn.is-liked i { color:#e056a0; }
</style>
@endpush

@section('content')

<div style="margin-bottom:1rem;">
    <a href="{{ route('articles.public.index') }}"
       style="font-size:.85rem;color:var(--theme-muted);text-decoration:none;">
        ← Volver a noticias
    </a>
</div>

<div style="background:var(--theme-card);border:1px solid var(--theme-border);border-radius:16px;overflow:hidden;margin-bottom:1.5rem;">
    @if($article->cover_path)
    <div style="height:280px;overflow:hidden;">
        <img loading="eager" src="{{ asset('storage/' . $article->cover_path) }}"
             alt="{{ $article->title }}"
             style="width:100%;height:100%;object-fit:cover;">
    </div>
    @endif
    <div style="padding:2rem;">
        @if($article->category)
        <span style="font-size:.75rem;font-weight:700;color:#e056a0;
                     text-transform:uppercase;letter-spacing:.05em;">
            {{ $article->category }}
        </span>
        @endif
        <h1 style="font-size:1.8rem;font-weight:800;color:var(--theme-text);margin:.5rem 0 .75rem;">
            {{ $article->title }}
        </h1>
        <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.5rem;flex-wrap:wrap;">
            <span style="font-size:.82rem;color:var(--theme-muted);">
                <i class="fas fa-calendar" style="margin-right:.3rem;"></i>
                {{ \Carbon\Carbon::parse($article->created_at)->format('d \d\e M Y') }}
            </span>
            <span style="font-size:.82rem;color:var(--theme-muted);">
                <i class="fas fa-eye" style="margin-right:.3rem;"></i>
                {{ number_format($article->views ?? 0) }} lecturas
            </span>
            <span style="font-size:.82rem;color:var(--theme-muted);">
                <i class="fas fa-heart" style="margin-right:.3rem;color:#e056a0;"></i>
                <span id="likesCount">{{ $likesCount }}</span> likes
            </span>
        </div>

        @if($article->excerpt)
        <p style="font-size:1rem;font-weight:500;color:var(--theme-text);
                  opacity:.75;margin:0 0 1.5rem;line-height:1.6;
                  border-left:3px solid #e056a0;padding-left:1rem;">
            {{ $article->excerpt }}
        </p>
        @endif

        <div class="art-body">
            {!! nl2br(e($article->body)) !!}
        </div>

        {{-- Botón Like --}}
        <div style="margin-top:2rem;padding-top:1.5rem;border-top:1px solid var(--theme-border);">
            <button id="likeBtn"
                    class="art-like-btn {{ $userLiked ? 'is-liked' : '' }}"
                    data-article-id="{{ $article->id }}"
                    data-liked="{{ $userLiked ? '1' : '0' }}">
                <i class="{{ $userLiked ? 'fas' : 'far' }} fa-heart"></i>
                <span id="likeBtnText">{{ $userLiked ? 'Te gustó' : 'Me gusta' }}</span>
            </button>
        </div>
    </div>
</div>

{{-- Comentarios --}}
<div style="background:var(--theme-card);border:1px solid var(--theme-border);border-radius:16px;padding:1.5rem;margin-bottom:1.5rem;">
    <h2 style="font-size:1.1rem;font-weight:700;color:var(--theme-text);margin:0 0 1.25rem;">
        💬 Comentarios
        @if($comments->count() > 0)
        <span style="font-size:.85rem;font-weight:400;color:var(--theme-muted);margin-left:.5rem;">
            ({{ $comments->count() }})
        </span>
        @endif
    </h2>

    @if(session('success'))
    <div style="background:#d1fae5;border:1px solid #6ee7b7;color:#065f46;
                padding:.75rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:.88rem;">
        {{ session('success') }}
    </div>
    @endif

    @if($comments->isEmpty())
    <p style="color:var(--theme-muted);font-size:.88rem;text-align:center;padding:1rem 0;">
        Sé el primero en comentar.
    </p>
    @else
    <div style="margin-bottom:1.5rem;">
        @foreach($comments as $comment)
        <div class="art-comment-item">
            <img loading="lazy" src="{{ $comment->avatar_photo_id ? route('photos.serve', $comment->avatar_photo_id) : asset('img/default-avatar.svg') }}"
                 alt="{{ $comment->nickname }}"
                 class="art-comment-avatar"
                 onerror="this.src='{{ asset('img/default-avatar.svg') }}'">
            <div style="flex:1;min-width:0;">
                <div>
                    <span class="art-comment-nick">{{ $comment->nickname }}</span>
                    <span class="art-comment-date">
                        {{ \Carbon\Carbon::parse($comment->created_at)->diffForHumans() }}
                    </span>
                </div>
                <p class="art-comment-body">{{ $comment->body }}</p>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Formulario comentario --}}
    <form method="POST" action="{{ route('articles.comment', $article->id) }}">
        @csrf
        <div style="display:flex;flex-direction:column;gap:.75rem;">
            <textarea name="body"
                      placeholder="Escribe tu comentario... (será revisado antes de publicarse)"
                      rows="3"
                      required
                      style="width:100%;background:var(--theme-input,rgba(255,255,255,.06));
                             border:1px solid var(--theme-border);border-radius:8px;
                             color:var(--theme-text);font-size:.88rem;
                             padding:.65rem .9rem;resize:vertical;
                             font-family:inherit;box-sizing:border-box;">{{ old('body') }}</textarea>
            @error('body')
            <p style="color:#ef4444;font-size:.82rem;margin:0;">{{ $message }}</p>
            @enderror
            <div style="text-align:right;">
                <button type="submit"
                        style="background:#e056a0;border:none;border-radius:8px;
                               color:#fff;font-size:.88rem;font-weight:600;
                               padding:.5rem 1.5rem;cursor:pointer;">
                    <i class="fas fa-paper-plane"></i> Enviar comentario
                </button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
var CSRF = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';

document.getElementById('likeBtn').addEventListener('click', function() {
    var btn       = this;
    var articleId = btn.dataset.articleId;
    var wasLiked  = btn.dataset.liked === '1';
    var countEl   = document.getElementById('likesCount');
    var icon      = btn.querySelector('i');
    var text      = document.getElementById('likeBtnText');

    // Optimistic UI
    if (wasLiked) {
        btn.classList.remove('is-liked');
        btn.dataset.liked = '0';
        if (icon) { icon.className = 'far fa-heart'; }
        if (text) text.textContent = 'Me gusta';
        if (countEl) countEl.textContent = Math.max(0, parseInt(countEl.textContent) - 1);
    } else {
        btn.classList.add('is-liked');
        btn.dataset.liked = '1';
        if (icon) { icon.className = 'fas fa-heart'; }
        if (text) text.textContent = 'Te gustó';
        if (countEl) countEl.textContent = parseInt(countEl.textContent) + 1;
    }

    fetch('/noticias/' + articleId + '/like', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        if (countEl) countEl.textContent = d.count;
        btn.dataset.liked = d.liked ? '1' : '0';
    })
    .catch(function() {
        // Revertir si falla
        btn.dataset.liked = wasLiked ? '1' : '0';
        btn.classList.toggle('is-liked', wasLiked);
    });
});
</script>
@endpush

@endsection
