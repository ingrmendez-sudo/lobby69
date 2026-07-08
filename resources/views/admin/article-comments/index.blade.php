@extends('layouts.admin')

@section('title', 'Comentarios de Artículos')

@section('content')
<div class="adm-topbar">
    <h1 style="font-size:1.25rem;font-weight:700;">
        <i class="fas fa-comments"></i> Moderación de Comentarios
    </h1>
</div>

<div style="padding:1.5rem;">

    {{-- Tabs de estado --}}
    <div style="display:flex;gap:.5rem;margin-bottom:1.5rem;">
        @foreach(['pending'=>'Pendientes','approved'=>'Aprobados','rejected'=>'Rechazados'] as $key=>$label)
        <a href="{{ route('admin.article-comments.index', ['status'=>$key]) }}"
           style="padding:.4rem 1rem;border-radius:6px;font-size:.85rem;font-weight:600;text-decoration:none;
                  background:{{ $status===$key ? '#6C3FC5' : 'rgba(255,255,255,.07)' }};
                  color:{{ $status===$key ? '#fff' : 'var(--theme-text)' }};">
            {{ $label }}
            @if(isset($counts[$key]) && $counts[$key] > 0)
                <span style="background:{{ $key==='pending'?'#ef4444':'rgba(255,255,255,.2)' }};
                             color:#fff;border-radius:10px;padding:0 .45rem;font-size:.75rem;margin-left:.3rem;">
                    {{ $counts[$key] }}
                </span>
            @endif
        </a>
        @endforeach
    </div>

    @if(session('success'))
    <div style="background:rgba(34,197,94,.15);border:1px solid #22c55e;border-radius:8px;
                padding:.75rem 1rem;margin-bottom:1rem;color:#22c55e;font-size:.88rem;">
        {{ session('success') }}
    </div>
    @endif

    @if($comments->isEmpty())
    <div style="text-align:center;padding:3rem;opacity:.5;">
        <i class="fas fa-comment-slash" style="font-size:2rem;margin-bottom:.5rem;display:block;"></i>
        No hay comentarios {{ $status === 'pending' ? 'pendientes' : ($status === 'approved' ? 'aprobados' : 'rechazados') }}.
    </div>
    @else
    <div style="display:flex;flex-direction:column;gap:1rem;">
        @foreach($comments as $comment)
        <div class="adm-card" style="padding:1.25rem;border-radius:10px;">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;flex-wrap:wrap;">
                <div style="flex:1;min-width:0;">
                    <div style="font-size:.78rem;opacity:.6;margin-bottom:.4rem;">
                        <i class="fas fa-user"></i> <strong>{{ $comment->author }}</strong>
                        &nbsp;·&nbsp;
                        <i class="fas fa-newspaper"></i>
                        <a href="{{ route('articles.public.show', $comment->article_id) }}"
                           target="_blank"
                           style="color:#a78bfa;text-decoration:none;">
                            {{ Str::limit($comment->article_title, 50) }}
                        </a>
                        &nbsp;·&nbsp;
                        <i class="fas fa-clock"></i> {{ \Carbon\Carbon::parse($comment->created_at)->diffForHumans() }}
                    </div>
                    <p style="margin:0;font-size:.92rem;line-height:1.5;word-break:break-word;">
                        {{ $comment->body }}
                    </p>
                </div>
                <div style="display:flex;gap:.5rem;flex-shrink:0;">
                    @if($comment->status !== 'approved')
                    <form method="POST" action="{{ route('admin.article-comments.approve', $comment->id) }}">
                        @csrf
                        <button type="submit"
                                style="background:#22c55e;border:none;border-radius:6px;color:#fff;
                                       font-size:.8rem;font-weight:600;padding:.4rem .9rem;cursor:pointer;">
                            <i class="fas fa-check"></i> Aprobar
                        </button>
                    </form>
                    @endif
                    @if($comment->status !== 'rejected')
                    <form method="POST" action="{{ route('admin.article-comments.reject', $comment->id) }}">
                        @csrf
                        <button type="submit"
                                style="background:#f59e0b;border:none;border-radius:6px;color:#fff;
                                       font-size:.8rem;font-weight:600;padding:.4rem .9rem;cursor:pointer;">
                            <i class="fas fa-times"></i> Rechazar
                        </button>
                    </form>
                    @endif
                    <form method="POST" action="{{ route('admin.article-comments.destroy', $comment->id) }}"
                          onsubmit="return confirm('¿Eliminar este comentario permanentemente?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                style="background:#ef4444;border:none;border-radius:6px;color:#fff;
                                       font-size:.8rem;font-weight:600;padding:.4rem .9rem;cursor:pointer;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div style="margin-top:1.5rem;">
        {{ $comments->appends(request()->query())->links() }}
    </div>
    @endif
</div>
@endsection

