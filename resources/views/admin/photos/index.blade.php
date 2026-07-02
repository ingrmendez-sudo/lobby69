@extends('layouts.app')
@section('title', 'Moderación de Fotos — Admin')

@push('sidebar-left')
    @include('layouts.sidebar-left')
@endpush
@push('sidebar-right')
    @include('layouts.sidebar-right')
@endpush

@section('content')

@push('styles')
<style>
.adm-header {
    display: flex; justify-content: space-between;
    align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: .75rem;
}
.adm-title {
    font-size: 1.4rem; font-weight: 800;
    color: var(--theme-text); margin: 0;
}
.adm-subtitle {
    font-size: .85rem; color: var(--theme-text-secondary, #9ca3af); margin: .2rem 0 0;
}
.adm-counters {
    display: grid; grid-template-columns: repeat(3,1fr);
    gap: 1rem; margin-bottom: 1.25rem;
}
.adm-counter {
    background: var(--theme-surface-2);
    border: 1px solid rgba(180,60,120,.15);
    border-radius: 12px; padding: 1rem;
    text-align: center; text-decoration: none;
    transition: border-color .2s;
    border-top: 3px solid transparent;
}
.adm-counter:hover { border-color: rgba(180,60,120,.4); }
.adm-counter__num {
    font-size: 1.8rem; font-weight: 800; display: block;
}
.adm-counter__label {
    font-size: .82rem; color: var(--theme-text-secondary, #9ca3af);
}
.adm-counter--pending  .adm-counter__num { color: #f59e0b; }
.adm-counter--approved .adm-counter__num { color: #34d399; }
.adm-counter--rejected .adm-counter__num { color: #ef4444; }
.adm-counter--active { border-top-color: #e056a0 !important; }

.adm-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 1.25rem;
    margin-bottom: 1.5rem;
}
.adm-photo-card {
    background: var(--theme-surface-2);
    border: 1px solid rgba(180,60,120,.15);
    border-radius: 12px; overflow: hidden;
}
.adm-photo-card__img-wrap {
    aspect-ratio: 1; background: #0f0a1a; position: relative; overflow: hidden;
}
.adm-photo-card__img {
    width: 100%; height: 100%; object-fit: cover; display: block;
}
.adm-photo-card__type {
    position: absolute; top: .4rem; left: .4rem;
    background: rgba(0,0,0,.65); color: #fff;
    padding: .2rem .5rem; border-radius: 20px; font-size: .7rem; font-weight: 700;
}
.adm-photo-card__body { padding: .85rem; }
.adm-photo-card__nick {
    font-weight: 700; font-size: .9rem;
    color: var(--theme-text); margin: 0 0 .2rem;
}
.adm-photo-card__meta {
    font-size: .75rem; color: var(--theme-text-secondary, #9ca3af);
    margin: 0 0 .75rem;
}
.adm-photo-card__caption {
    font-size: .8rem; color: var(--theme-text-secondary);
    font-style: italic; margin: 0 0 .75rem;
}
.adm-btn {
    width: 100%; padding: .55rem;
    border: none; border-radius: 8px;
    font-size: .82rem; font-weight: 700;
    cursor: pointer; transition: opacity .2s;
    margin-bottom: .4rem; display: block; text-align: center;
}
.adm-btn:hover { opacity: .85; }
.adm-btn--approve { background: #34d399; color: #fff; }
.adm-btn--reject  { background: transparent; color: #ef4444; border: 2px solid #ef4444; }
.adm-btn--confirm { background: #ef4444; color: #fff; }
.adm-reject-form { margin-top: .5rem; }
.adm-reject-form textarea {
    width: 100%; padding: .5rem;
    background: var(--bg-input, rgba(255,255,255,.06));
    border: 1px solid rgba(180,60,120,.2);
    border-radius: 6px; color: var(--theme-text);
    font-size: .8rem; resize: none;
    margin-bottom: .4rem; box-sizing: border-box;
    font-family: inherit;
}
.adm-reject-form textarea:focus { outline: none; border-color: #ef4444; }
.adm-status-badge {
    padding: .45rem .75rem; border-radius: 8px;
    font-size: .82rem; font-weight: 600; text-align: center;
}
.adm-status-badge--approved { background: rgba(52,211,153,.15); color: #34d399; }
.adm-status-badge--rejected { background: rgba(239,68,68,.15);  color: #ef4444; }
.adm-empty {
    grid-column: 1/-1; text-align: center;
    padding: 3rem; color: var(--theme-text-secondary, #9ca3af);
}
.adm-toast {
    background: rgba(52,211,153,.15);
    border: 1px solid rgba(52,211,153,.3);
    color: #34d399; padding: .75rem 1rem;
    border-radius: 10px; margin-bottom: 1.25rem;
    font-size: .88rem;
}
</style>
@endpush

{{-- Header --}}
<div class="adm-header">
    <div>
        <h1 class="adm-title">🖼️ Moderación de Fotos</h1>
        <p class="adm-subtitle">Aprueba o rechaza las fotos subidas por los miembros</p>
    </div>
    <a href="{{ route('admin.invitations.index') }}" class="l69-quick-btn" style="width:auto;">
        ← Panel Admin
    </a>
</div>

@if(session('success'))
<div class="adm-toast">✅ {{ session('success') }}</div>
@endif

{{-- Contadores --}}
<div class="adm-counters">
    @foreach([
        'pending'  => ['⏳ Pendientes', 'pending'],
        'approved' => ['✅ Aprobadas',  'approved'],
        'rejected' => ['❌ Rechazadas', 'rejected'],
    ] as $s => [$label, $mod])
    <a href="{{ route('admin.photos.index', ['status' => $s]) }}"
       class="adm-counter adm-counter--{{ $mod }} {{ $status === $s ? 'adm-counter--active' : '' }}">
        <span class="adm-counter__num">{{ $counts[$s] }}</span>
        <span class="adm-counter__label">{{ $label }}</span>
    </a>
    @endforeach
</div>

{{-- Grid --}}
<div class="adm-grid">
    @forelse($photos as $photo)
    <div class="adm-photo-card">

        <div class="adm-photo-card__img-wrap">
            <img class="adm-photo-card__img"
                 src="{{ route('admin.photos.serve', $photo->id) }}"
                 alt="Foto"
                 loading="lazy"
                 onerror="this.style.display='none'">
            <span class="adm-photo-card__type">{{ strtoupper($photo->album_type) }}</span>
        </div>

        <div class="adm-photo-card__body">
            <p class="adm-photo-card__nick">{{ $photo->nickname ?? $photo->display_name ?? '—' }}</p>
            <p class="adm-photo-card__meta">
                {{ $photo->email }} · {{ ucfirst($photo->profile_type ?? '') }}
            </p>
            @if($photo->caption)
            <p class="adm-photo-card__caption">"{{ $photo->caption }}"</p>
            @endif

            @if($status === 'pending')
            {{-- Aprobar --}}
            <form method="POST" action="{{ route('admin.photos.approve', $photo->id) }}">
                @csrf
                <button type="submit" class="adm-btn adm-btn--approve">✅ Aprobar</button>
            </form>

            {{-- Rechazar con toggle JS --}}
            <button class="adm-btn adm-btn--reject"
                    onclick="this.nextElementSibling.style.display =
                        this.nextElementSibling.style.display === 'none' ? 'block' : 'none'">
                ❌ Rechazar
            </button>
            <div class="adm-reject-form" style="display:none;">
                <form method="POST" action="{{ route('admin.photos.reject', $photo->id) }}">
                    @csrf
                    <textarea name="note" rows="2" required
                              placeholder="Motivo del rechazo..."></textarea>
                    <button type="submit" class="adm-btn adm-btn--confirm">Confirmar rechazo</button>
                </form>
            </div>

            @else
            <div class="adm-status-badge adm-status-badge--{{ $status }}">
                @if($status === 'approved')
                    ✅ Aprobada
                @else
                    ❌ {{ $photo->admin_note ?? 'Rechazada' }}
                @endif
            </div>
            @endif
        </div>
    </div>
    @empty
    <div class="adm-empty">
        <i class="fas fa-images" style="font-size:2rem;display:block;margin-bottom:.75rem;opacity:.4;"></i>
        No hay fotos {{ $status === 'pending' ? 'pendientes' : ($status === 'approved' ? 'aprobadas' : 'rechazadas') }}.
    </div>
    @endforelse
</div>

@if($photos->hasPages())
<div style="margin-top:1rem;">{{ $photos->links() }}</div>
@endif

@endsection
