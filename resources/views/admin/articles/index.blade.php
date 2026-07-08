@extends('layouts.admin')
@php use Illuminate\Support\Facades\Storage; @endphp

@section('title', 'Artículos')
@section('page-title', 'Gestión de Artículos')

@section('content')

{{-- Header --}}
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
    <div>
        @if($stats)
        <div style="display:flex;gap:1rem;font-size:.8rem;color:var(--theme-muted);">
            <span><strong style="color:var(--theme-text);">{{ $stats->total }}</strong> total</span>
            <span style="color:#22c55e;"><strong>{{ $stats->publicados }}</strong> publicados</span>
            <span style="color:#f59e0b;"><strong>{{ $stats->borradores }}</strong> borradores</span>
            <span style="color:#06b6d4;"><strong>{{ number_format($stats->vistas_total) }}</strong> vistas</span>
        </div>
        @endif
    </div>
    <a href="{{ route('admin.articles.create') }}"
       style="padding:.5rem 1.2rem;background:var(--theme-accent);color:#fff;border-radius:8px;text-decoration:none;font-size:.85rem;display:flex;align-items:center;gap:.5rem;">
        <i class="fas fa-plus"></i> Nuevo artículo
    </a>
</div>

@if(session('success'))
<div style="background:#22c55e22;border:1px solid #22c55e;color:#22c55e;padding:.75rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:.85rem;">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif

@if($articles->isEmpty())
<div class="adm-card" style="padding:3rem;text-align:center;color:var(--theme-muted);">
    <i class="fas fa-newspaper" style="font-size:3rem;opacity:.2;display:block;margin-bottom:1rem;"></i>
    <p style="font-size:.9rem;">No hay artículos registrados aún.</p>
    <a href="{{ route('admin.articles.create') }}"
       style="display:inline-block;margin-top:1rem;padding:.5rem 1.2rem;background:var(--theme-accent);color:#fff;border-radius:8px;text-decoration:none;font-size:.85rem;">
        <i class="fas fa-plus"></i> Crear primer artículo
    </a>
</div>
@else
<div class="adm-card" style="overflow-x:auto;">
    <table style="width:100%;border-collapse:collapse;font-size:.83rem;">
        <thead>
            <tr style="border-bottom:2px solid var(--theme-border);">
                <th style="padding:.6rem .8rem;color:var(--theme-muted);font-weight:600;text-align:left;">Artículo</th>
                <th style="padding:.6rem .8rem;color:var(--theme-muted);font-weight:600;text-align:left;">Categoría</th>
                <th style="padding:.6rem .8rem;color:var(--theme-muted);font-weight:600;text-align:center;">Estado</th>
                <th style="padding:.6rem .8rem;color:var(--theme-muted);font-weight:600;text-align:center;">Vistas</th>
                <th style="padding:.6rem .8rem;color:var(--theme-muted);font-weight:600;text-align:left;">Fecha</th>
                <th style="padding:.6rem .8rem;color:var(--theme-muted);font-weight:600;text-align:center;">Acciones</th>
            </tr>
        </thead>
        <tbody>
        @foreach($articles as $article)
        <tr style="border-bottom:1px solid var(--theme-border);">

            {{-- Artículo --}}
            <td style="padding:.6rem .8rem;">
                <div style="display:flex;align-items:center;gap:.75rem;">
                    @if($article->cover_path)
                    <img src="{{ Storage::url($article->cover_path) }}"
                         style="width:52px;height:40px;object-fit:cover;border-radius:6px;flex-shrink:0;"
                         onerror="this.style.display='none'">
                    @else
                    <div style="width:52px;height:40px;border-radius:6px;background:linear-gradient(135deg,#6C3FC522,#ec489922);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fas fa-newspaper" style="color:var(--theme-accent);opacity:.5;font-size:.85rem;"></i>
                    </div>
                    @endif
                    <div>
                        <div style="font-weight:600;color:var(--theme-text);line-height:1.3;">
                            {{ Str::limit($article->title, 55) }}
                        </div>
                        @if($article->excerpt)
                        <div style="font-size:.72rem;color:var(--theme-muted);margin-top:.15rem;">
                            {{ Str::limit($article->excerpt, 70) }}
                        </div>
                        @endif
                    </div>
                </div>
            </td>

            {{-- Categoría --}}
            <td style="padding:.6rem .8rem;">
                @if($article->category)
                <span style="background:var(--theme-accent)22;color:var(--theme-accent);padding:.15rem .55rem;border-radius:20px;font-size:.72rem;font-weight:600;">
                    {{ $article->category }}
                </span>
                @else
                <span style="color:var(--theme-muted);font-size:.78rem;">—</span>
                @endif
            </td>

            {{-- Estado --}}
            <td style="padding:.6rem .8rem;text-align:center;">
                @if($article->published)
                <span style="background:#22c55e22;color:#22c55e;padding:.2rem .55rem;border-radius:20px;font-size:.72rem;font-weight:600;">
                    <i class="fas fa-circle" style="font-size:.45rem;"></i> Publicado
                </span>
                @else
                <span style="background:#f59e0b22;color:#f59e0b;padding:.2rem .55rem;border-radius:20px;font-size:.72rem;font-weight:600;">
                    <i class="fas fa-circle" style="font-size:.45rem;"></i> Borrador
                </span>
                @endif
            </td>

            {{-- Vistas --}}
            <td style="padding:.6rem .8rem;text-align:center;font-weight:600;color:var(--theme-text);">
                {{ number_format($article->views ?? 0) }}
            </td>

            {{-- Fecha --}}
            <td style="padding:.6rem .8rem;color:var(--theme-muted);font-size:.75rem;">
                @if($article->published_at)
                    <div>Pub: {{ \Carbon\Carbon::parse($article->published_at)->format('d/m/Y') }}</div>
                @endif
                <div style="font-size:.7rem;">Creado: {{ \Carbon\Carbon::parse($article->created_at)->format('d/m/Y') }}</div>
            </td>

            {{-- Acciones --}}
            <td style="padding:.6rem .8rem;text-align:center;">
                <div style="display:flex;gap:.35rem;justify-content:center;">
                    <a href="{{ route('admin.articles.show', $article->id) }}"
                       style="padding:.25rem .6rem;background:#7c3aed22;color:#a78bfa;border:1px solid #7c3aed;border-radius:5px;font-size:.75rem;text-decoration:none;position:relative;"
                       title="Ver comentarios">
                        <i class="fas fa-comments"></i>
                    </a>
                    <a href="{{ route('admin.articles.edit', $article->id) }}"
                       style="padding:.25rem .6rem;background:var(--theme-accent);color:#fff;border-radius:5px;font-size:.75rem;text-decoration:none;"
                       title="Editar">
                        <i class="fas fa-edit"></i>
                    </a>
                    <form method="POST" action="{{ route('admin.articles.update', $article->id) }}">
                        @csrf @method('PUT')
                        <input type="hidden" name="title"     value="{{ $article->title }}">
                        <input type="hidden" name="body"      value="{{ $article->body }}">
                        <input type="hidden" name="excerpt"   value="{{ $article->excerpt }}">
                        <input type="hidden" name="category"  value="{{ $article->category }}">
                        <input type="hidden" name="published" value="{{ $article->published ? '0' : '1' }}">
                        <button type="submit"
                                style="padding:.25rem .6rem;background:{{ $article->published ? '#f59e0b22' : '#22c55e22' }};color:{{ $article->published ? '#f59e0b' : '#22c55e' }};border:1px solid {{ $article->published ? '#f59e0b' : '#22c55e' }};border-radius:5px;font-size:.75rem;cursor:pointer;"
                                title="{{ $article->published ? 'Despublicar' : 'Publicar' }}">
                            <i class="fas fa-{{ $article->published ? 'eye-slash' : 'eye' }}"></i>
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.articles.destroy', $article->id) }}">
                        @csrf @method('DELETE')
                        <button type="submit"
                                onclick="return confirm('¿Eliminar «{{ addslashes(Str::limit($article->title, 40)) }}»?')"
                                style="padding:.25rem .6rem;background:#ef444422;color:#ef4444;border:1px solid #ef4444;border-radius:5px;font-size:.75rem;cursor:pointer;"
                                title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </td>

        </tr>
        @endforeach
        </tbody>
    </table>
</div>

@if($articles->hasPages())
<div style="margin-top:1rem;display:flex;justify-content:center;">
    {{ $articles->links() }}
</div>
@endif
@endif

@endsection

