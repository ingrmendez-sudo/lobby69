@extends('layouts.app')
@section('title', 'Noticias — LOBBY69')
@push('sidebar-left')
    @include('layouts.sidebar-left')
@endpush
@push('sidebar-right')
    @include('layouts.sidebar-right')
@endpush

@section('content')
<div style="margin-bottom:1.5rem;">
    <h1 style="font-size:1.6rem;font-weight:800;color:var(--theme-text);margin:0 0 .25rem;">
        📰 Noticias
    </h1>
    <p style="color:var(--theme-muted);margin:0;">Artículos y novedades de la comunidad</p>
</div>

@if($articles->isEmpty())
<div style="text-align:center;padding:4rem 2rem;background:var(--theme-surface-2);border-radius:16px;border:1px solid rgba(180,60,120,.15);">
    <i class="fas fa-newspaper" style="font-size:3rem;color:rgba(180,60,120,.4);margin-bottom:1rem;display:block;"></i>
    <p style="color:rgba(226,217,243,.6);margin:0;">No hay artículos publicados por el momento.</p>
</div>
@else
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:1.25rem;">
    @foreach($articles as $article)
    <div style="background:var(--theme-card);border:1px solid var(--theme-border);border-radius:16px;overflow:hidden;">
        @if($article->cover_path)
        <div style="height:180px;overflow:hidden;">
            <img loading="lazy" src="{{ asset('storage/' . $article->cover_path) }}"
                 alt="{{ $article->title }}"
                 style="width:100%;height:100%;object-fit:cover;"
                 onerror="this.parentElement.style.background='rgba(180,60,120,.1)';this.remove()">
        </div>
        @else
        <div style="height:120px;background:linear-gradient(135deg,rgba(180,60,120,.2),rgba(108,63,197,.2));display:flex;align-items:center;justify-content:center;">
            <i class="fas fa-newspaper" style="font-size:2.5rem;color:rgba(180,60,120,.5);"></i>
        </div>
        @endif
        <div style="padding:1.25rem;">
            @if($article->category)
            <span style="font-size:.72rem;font-weight:700;color:#e056a0;text-transform:uppercase;letter-spacing:.05em;">
                {{ $article->category }}
            </span>
            @endif
            <h2 style="font-size:1.05rem;font-weight:700;color:var(--theme-text);margin:.4rem 0 .5rem;">
                {{ $article->title }}
            </h2>
            @if($article->excerpt)
            <p style="font-size:.85rem;color:var(--theme-text);opacity:.75;margin:0 0 1rem;line-height:1.5;">
                {{ Str::limit($article->excerpt, 120) }}
            </p>
            @endif
            <div style="display:flex;align-items:center;justify-content:space-between;">
                <span style="font-size:.75rem;color:var(--theme-muted);">
                    {{ \Carbon\Carbon::parse($article->created_at)->format('d M Y') }}
                </span>
                <a href="{{ route('articles.public.show', $article->id) }}"
                   class="l69-quick-btn"
                   style="display:inline-flex;width:auto;padding:.4rem 1rem;font-size:.82rem;">
                    Leer <i class="fas fa-arrow-right" style="margin-left:.4rem;"></i>
                </a>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif
@endsection
