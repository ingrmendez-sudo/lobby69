@extends('layouts.app')
@section('title', 'Eventos — LOBBY69')
@push('sidebar-left')
    @include('layouts.sidebar-left')
@endpush
@push('sidebar-right')
    @include('layouts.sidebar-right')
@endpush

@push('styles')
<style>
.ev-header { margin-bottom:1.75rem; }
.ev-header h1 { font-size:1.5rem;font-weight:800;color:var(--theme-text);margin:0 0 .25rem; }
.ev-header p  { color:var(--theme-muted);margin:0;font-size:.9rem; }

.ev-grid {
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(300px,1fr));
    gap:1.25rem;
}
.ev-card {
    background:var(--theme-card);border:1px solid var(--theme-border);
    border-radius:16px;overflow:hidden;
    transition:transform .18s,box-shadow .18s;
    display:flex;flex-direction:column;
}
.ev-card:hover { transform:translateY(-4px);box-shadow:0 10px 28px rgba(0,0,0,.22); }

.ev-card__img {
    height:190px;overflow:hidden;position:relative;flex-shrink:0;
    background:linear-gradient(135deg,rgba(180,60,120,.18),rgba(108,63,197,.18));
}
.ev-card__img img { width:100%;height:100%;object-fit:cover;display:block; }
.ev-card__img-placeholder {
    width:100%;height:100%;display:flex;align-items:center;justify-content:center;
}
.ev-card__badge {
    position:absolute;top:.75rem;left:.75rem;
    padding:.25rem .7rem;border-radius:20px;font-size:.7rem;font-weight:700;
    backdrop-filter:blur(6px);
}
.ev-card__badge--online  { background:rgba(6,182,212,.2);color:#06b6d4;border:1px solid rgba(6,182,212,.3); }
.ev-card__badge--presencial { background:rgba(224,86,160,.15);color:#e056a0;border:1px solid rgba(224,86,160,.25); }

.ev-card__price {
    position:absolute;top:.75rem;right:.75rem;
    padding:.3rem .8rem;border-radius:20px;font-size:.75rem;font-weight:800;
    background:rgba(0,0,0,.55);color:#fff;backdrop-filter:blur(6px);
    border:1px solid rgba(255,255,255,.1);
}
.ev-card__price--free { background:rgba(46,204,113,.2);color:#2ecc71;border-color:rgba(46,204,113,.3); }

.ev-card__body { padding:1.1rem 1.25rem;flex:1;display:flex;flex-direction:column; }
.ev-card__title { font-size:1rem;font-weight:700;color:var(--theme-text);margin:0 0 .6rem;line-height:1.35; }

.ev-card__meta { display:flex;flex-direction:column;gap:.3rem;margin-bottom:.85rem; }
.ev-card__meta-item { display:flex;align-items:center;gap:.4rem;font-size:.8rem;color:var(--theme-muted); }
.ev-card__meta-item i { color:#e056a0;width:14px;text-align:center;flex-shrink:0; }

.ev-card__desc {
    font-size:.82rem;color:var(--theme-text);opacity:.75;line-height:1.5;
    margin-bottom:1rem;flex:1;
    display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;
}
.ev-card__footer {
    border-top:1px solid var(--theme-border);padding-top:.85rem;
    display:flex;align-items:center;justify-content:space-between;
}
.ev-card__organizer { font-size:.75rem;color:var(--theme-muted); }
.ev-card__cta {
    display:inline-flex;align-items:center;gap:.4rem;
    padding:.4rem 1rem;border-radius:8px;
    background:linear-gradient(135deg,#e056a0,#7c3aed);
    color:#fff;font-size:.8rem;font-weight:600;text-decoration:none;
    transition:opacity .2s;
}
.ev-card__cta:hover { opacity:.85;color:#fff; }

.ev-empty {
    text-align:center;padding:4rem 2rem;
    background:var(--theme-surface-2);border-radius:16px;
    border:1px solid rgba(180,60,120,.12);
}
.ev-empty i { font-size:3rem;color:rgba(180,60,120,.35);display:block;margin-bottom:1rem; }
.ev-empty p  { color:var(--theme-muted);margin:0; }

@media(max-width:640px){ .ev-grid{ grid-template-columns:1fr; } }
</style>
@endpush

@section('content')

<div class="ev-header">
    <h1>🎉 Eventos</h1>
    <p>Próximos eventos y fiestas de la comunidad Lobby69</p>
</div>

@if($events->isEmpty())
    <div class="ev-empty">
        <i class="fas fa-calendar-times"></i>
        <p>No hay eventos publicados por el momento.<br>¡Vuelve pronto!</p>
    </div>
@else
    <div class="ev-grid">
        @foreach($events as $event)
        @php
            $isFree = is_null($event->price) || $event->price == 0;
            $currency = $event->currency ?? 'MXN';
            $priceLabel = $isFree ? 'Gratis' : $currency . ' ' . number_format($event->price, 0, '.', ',');
        @endphp
        <div class="ev-card">
            <div class="ev-card__img">
                @if($event->image_path)
                    <img loading="lazy"
                         src="{{ asset('storage/' . $event->image_path) }}"
                         alt="{{ $event->title }}"
                         onerror="this.parentElement.classList.add('ev-card__img-placeholder');this.remove()">
                @else
                    <div class="ev-card__img-placeholder">
                        <i class="fas fa-calendar-alt" style="font-size:3rem;color:rgba(180,60,120,.4);"></i>
                    </div>
                @endif

                <span class="ev-card__badge {{ $event->is_online ? 'ev-card__badge--online' : 'ev-card__badge--presencial' }}">
                    <i class="fas {{ $event->is_online ? 'fa-wifi' : 'fa-map-marker-alt' }}" style="margin-right:.3rem;"></i>
                    {{ $event->is_online ? 'Online' : 'Presencial' }}
                </span>

                <span class="ev-card__price {{ $isFree ? 'ev-card__price--free' : '' }}">
                    {{ $priceLabel }}
                </span>
            </div>

            <div class="ev-card__body">
                <div class="ev-card__title">{{ $event->title }}</div>

                <div class="ev-card__meta">
                    @if($event->starts_at)
                    <div class="ev-card__meta-item">
                        <i class="fas fa-calendar"></i>
                        <span>{{ \Carbon\Carbon::parse($event->starts_at)->translatedFormat('d \d\e F Y') }}</span>
                    </div>
                    <div class="ev-card__meta-item">
                        <i class="fas fa-clock"></i>
                        <span>{{ \Carbon\Carbon::parse($event->starts_at)->format('H:i') }}
                            @if($event->ends_at) — {{ \Carbon\Carbon::parse($event->ends_at)->format('H:i') }} hrs @else hrs @endif
                        </span>
                    </div>
                    @endif
                    @if($event->address)
                    <div class="ev-card__meta-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>{{ $event->address }}</span>
                    </div>
                    @endif
                    @if($event->price_notes)
                    <div class="ev-card__meta-item">
                        <i class="fas fa-info-circle"></i>
                        <span style="color:#d4af37;">{{ $event->price_notes }}</span>
                    </div>
                    @endif
                </div>

                @if($event->description)
                <div class="ev-card__desc">{{ $event->description }}</div>
                @endif

                <div class="ev-card__footer">
                    <span class="ev-card__organizer">
                        @if($event->organized_by)
                            <i class="fas fa-user" style="margin-right:.3rem;"></i>{{ $event->organized_by }}
                        @else
                            <i class="fas fa-heart" style="color:#e056a0;margin-right:.3rem;"></i>Lobby69
                        @endif
                    </span>
                    <a href="{{ route('events.public.show', $event->id) }}" class="ev-card__cta">
                        Ver detalles <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
@endif

@endsection
