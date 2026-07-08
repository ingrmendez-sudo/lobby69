@extends('layouts.admin')

@section('title', 'Artículo: ' . $article->title)

@section('content')
<div class="adm-topbar" style="display:flex;align-items:center;gap:1rem;">
    <a href="{{ route('admin.articles.index') }}"
       style="color:var(--theme-text);opacity:.6;text-decoration:none;font-size:.9rem;">
        <i class="fas fa-arrow-left"></i> Artículos
    </a>
    <h1 style="font-size:1.15rem;font-weight:700;margin:0;flex:1;">
        {{ Str::limit($article->title, 60) }}
    </h1>
    <a href="{{ route('admin.articles.edit', $article->id) }}"
       style="background:#6C3FC5;color:#fff;border-radius:7px;padding:.4rem 1rem;
              text-decoration:none;font-size:.85rem;font-weight:600;">
        <i class="fas fa-edit"></i> Editar
    </a>
    @if($article->published)
    <a href="{{ route('articles.public.show', $article->id) }}" target="_blank"
       style="background:rgba(255,255,255,.08);color:var(--theme-text);border-radius:7px;
              padding:.4rem 1rem;text-decoration:none;font-size:.85rem;">
        <i class="fas fa-external-link-alt"></i> Ver público
    </a>
    @endif
</div>

<div style="padding:1.5rem;max-width:900px;">

    {{-- Info del artículo --}}
    <div class="adm-card" style="padding:1.25rem;border-radius:10px;margin-bottom:1.5rem;">
        <div style="display:flex;gap:1rem;flex-wrap:wrap;font-size:.82rem;opacity:.65;margin-bottom:.75rem;">
            <span><i class="fas fa-tag"></i> {{ $article->category ?? 'Sin categoría' }}</span>
            <span><i class="fas fa-eye"></i> {{ number_format($article->views) }} vistas</span>
            <span><i class="fas fa-calendar"></i> {{ \Carbon\Carbon::parse($article->created_at)->format('d/m/Y') }}</span>
            <span style="color:{{ $article->published ? '#22c55e' : '#f59e0b' }}">
                <i class="fas fa-circle" style="font-size:.5rem;vertical-align:middle;"></i>
                {{ $article->published ? 'Publicado' : 'Borrador' }}
            </span>
        </div>
        @if($article->excerpt)
        <p style="margin:0;font-size:.9rem;opacity:.8;font-style:italic;">{{ $article->excerpt }}</p>
        @endif
    </div>

    {{-- Sección comentarios --}}
    <div class="adm-card" style="padding:1.25rem;border-radius:10px;">
        <h2 style="font-size:1rem;font-weight:700;margin:0 0 1rem 0;">
            <i class="fas fa-comments"></i> Comentarios
        </h2>

        {{-- Contadores --}}
        <div style="display:flex;gap:.75rem;margin-bottom:1.25rem;flex-wrap:wrap;">
            @foreach(['pending'=>['Pendientes','#ef4444'],'approved'=>['Aprobados','#22c55e'],'rejected'=>['Rechazados','#f59e0b']] as $key=>[$label,$color])
            <div style="background:rgba(255,255,255,.05);border-radius:8px;padding:.5rem .9rem;
                        border-left:3px solid {{ $color }};font-size:.82rem;">
                <span style="color:{{ $color }};font-weight:700;">{{ $counts[$key] }}</span>
                <span style="opacity:.7;margin-left:.3rem;">{{ $label }}</span>
            </div>
            @endforeach
        </div>

        @if(session('success'))
        <div style="background:rgba(34,197,94,.15);border:1px solid #22c55e;border-radius:8px;
                    padding:.65rem 1rem;margin-bottom:1rem;color:#22c55e;font-size:.85rem;">
            {{ session('success') }}
        </div>
        @endif

        @if($comments->isEmpty())
        <div style="text-align:center;padding:2rem;opacity:.4;font-size:.9rem;">
            <i class="fas fa-comment-slash" style="font-size:1.5rem;display:block;margin-bottom:.5rem;"></i>
            No hay comentarios aún.
        </div>
        @else
        <div style="display:flex;flex-direction:column;gap:.85rem;">
            @foreach($comments as $comment)
            <div style="background:rgba(255,255,255,.04);border-radius:8px;padding:1rem;
                        border-left:3px solid {{ $comment->status === 'approved' ? '#22c55e' : ($comment->status === 'rejected' ? '#ef4444' : '#f59e0b') }};">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;flex-wrap:wrap;">
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:.76rem;opacity:.55;margin-bottom:.4rem;">
                            <i class="fas fa-user"></i> <strong>{{ $comment->author }}</strong>
                            &nbsp;·&nbsp;
                            {{ \Carbon\Carbon::parse($comment->created_at)->diffForHumans() }}
                            &nbsp;·&nbsp;
                            <span style="color:{{ $comment->status === 'approved' ? '#22c55e' : ($comment->status === 'rejected' ? '#ef4444' : '#f59e0b') }}">
                                {{ ucfirst($comment->status) }}
                            </span>
                        </div>
                        <p style="margin:0;font-size:.9rem;line-height:1.5;word-break:break-word;">
                            {{ $comment->body }}
                        </p>
                    </div>
                    <div style="display:flex;gap:.4rem;flex-shrink:0;">
                        @if($comment->status !== 'approved')
                        <form method="POST" action="{{ route('admin.article-comments.approve', $comment->id) }}">
                            @csrf
                            <input type="hidden" name="_redirect" value="{{ route('admin.articles.show', $article->id) }}">
                            <button type="submit"
                                    style="background:#22c55e;border:none;border-radius:6px;color:#fff;
                                           font-size:.78rem;font-weight:600;padding:.35rem .8rem;cursor:pointer;">
                                <i class="fas fa-check"></i> Aprobar
                            </button>
                        </form>
                        @endif
                        @if($comment->status !== 'rejected')
                        <form method="POST" action="{{ route('admin.article-comments.reject', $comment->id) }}">
                            @csrf
                            <input type="hidden" name="_redirect" value="{{ route('admin.articles.show', $article->id) }}">
                            <button type="submit"
                                    style="background:#f59e0b;border:none;border-radius:6px;color:#fff;
                                           font-size:.78rem;font-weight:600;padding:.35rem .8rem;cursor:pointer;">
                                <i class="fas fa-times"></i> Rechazar
                            </button>
                        </form>
                        @endif
                        <form method="POST" action="{{ route('admin.article-comments.destroy', $comment->id) }}"
                              onsubmit="return confirm('¿Eliminar este comentario?')">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="_redirect" value="{{ route('admin.articles.show', $article->id) }}">
                            <button type="submit"
                                    style="background:#ef4444;border:none;border-radius:6px;color:#fff;
                                           font-size:.78rem;padding:.35rem .7rem;cursor:pointer;">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection
