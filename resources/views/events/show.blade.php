@extends('layouts.app')
@section('title', ($event->title ?? 'Evento') . ' — LOBBY69')
@push('sidebar-left')
    @include('layouts.sidebar-left')
@endpush
@push('sidebar-right')
    @include('layouts.sidebar-right')
@endpush

@push('styles')
<style>
.evd-back { font-size:.85rem;color:var(--theme-muted);text-decoration:none;display:inline-flex;align-items:center;gap:.4rem;margin-bottom:1.25rem;transition:color .2s; }
.evd-back:hover { color:var(--theme-text); }

.evd-hero {
    position:relative;border-radius:18px;overflow:hidden;
    height:320px;margin-bottom:1.5rem;
    background:linear-gradient(135deg,rgba(180,60,120,.2),rgba(108,63,197,.2));
}
.evd-hero img { width:100%;height:100%;object-fit:cover;display:block; }
.evd-hero-placeholder { width:100%;height:100%;display:flex;align-items:center;justify-content:center; }
.evd-hero__overlay {
    position:absolute;inset:0;
    background:linear-gradient(to top,rgba(0,0,0,.7) 0%,transparent 55%);
}
.evd-hero__title {
    position:absolute;bottom:1.5rem;left:1.75rem;right:1.75rem;
    font-size:1.75rem;font-weight:800;color:#fff;line-height:1.25;
    text-shadow:0 2px 8px rgba(0,0,0,.5);
}
.evd-hero__badges { position:absolute;top:1rem;left:1rem;display:flex;gap:.5rem; }
.evd-badge {
    padding:.3rem .8rem;border-radius:20px;font-size:.72rem;font-weight:700;
    backdrop-filter:blur(6px);
}
.evd-badge--online   { background:rgba(6,182,212,.2);color:#06b6d4;border:1px solid rgba(6,182,212,.3); }
.evd-badge--presencial{ background:rgba(224,86,160,.15);color:#e056a0;border:1px solid rgba(224,86,160,.25); }

.evd-layout { display:grid;grid-template-columns:1fr 300px;gap:1.5rem;align-items:start; }
@media(max-width:780px){ .evd-layout{ grid-template-columns:1fr; } }

.evd-card {
    background:var(--theme-card);border:1px solid var(--theme-border);
    border-radius:16px;padding:1.75rem;
}

.evd-section-title {
    font-size:.75rem;font-weight:700;letter-spacing:.07em;
    color:var(--theme-muted);text-transform:uppercase;
    margin:0 0 1rem;padding-bottom:.5rem;
    border-bottom:1px solid var(--theme-border);
}

.evd-description {
    font-size:.93rem;color:var(--theme-text);line-height:1.75;
    opacity:.9;white-space:pre-line;
}

.evd-info-list { list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:.85rem; }
.evd-info-item { display:flex;align-items:flex-start;gap:.75rem; }
.evd-info-item__icon {
    width:34px;height:34px;border-radius:9px;flex-shrink:0;
    background:rgba(224,86,160,.1);border:1px solid rgba(224,86,160,.2);
    display:flex;align-items:center;justify-content:center;
    font-size:.85rem;color:#e056a0;
}
.evd-info-item__content { min-width:0; }
.evd-info-item__label { font-size:.7rem;font-weight:600;color:var(--theme-muted);margin-bottom:.15rem;text-transform:uppercase;letter-spacing:.04em; }
.evd-info-item__value { font-size:.875rem;color:var(--theme-text);font-weight:500;line-height:1.4; }

.evd-price-box {
    background:linear-gradient(135deg,rgba(224,86,160,.08),rgba(124,58,237,.08));
    border:1px solid rgba(224,86,160,.2);
    border-radius:12px;padding:1.25rem;text-align:center;
    margin-bottom:1.25rem;
}
.evd-price-box__label { font-size:.72rem;color:var(--theme-muted);font-weight:600;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.3rem; }
.evd-price-box__amount { font-size:1.8rem;font-weight:800;color:var(--theme-text);line-height:1; }
.evd-price-box__amount--free { color:#2ecc71; }
.evd-price-box__notes { font-size:.78rem;color:#d4af37;margin-top:.5rem; }

.evd-contact-btn {
    display:flex;align-items:center;justify-content:center;gap:.5rem;
    width:100%;padding:.75rem;border-radius:10px;
    background:linear-gradient(135deg,#e056a0,#7c3aed);
    color:#fff;font-weight:700;font-size:.9rem;text-decoration:none;
    transition:opacity .2s;border:none;cursor:pointer;
}
.evd-contact-btn:hover { opacity:.88;color:#fff; }
</style>
@endpush

@section('content')

@php
    $isFree     = is_null($event->price) || $event->price == 0;
    $currency   = $event->currency ?? 'MXN';
    $priceLabel = $isFree ? 'Gratis' : $currency . ' ' . number_format($event->price, 0, '.', ',');
@endphp

<a href="{{ route('events.public.index') }}" class="evd-back">
    <i class="fas fa-arrow-left"></i> Volver a eventos
</a>

{{-- Hero con imagen --}}
<div class="evd-hero">
    @if($event->image_path)
        <img loading="eager"
             src="{{ asset('storage/' . $event->image_path) }}"
             alt="{{ $event->title }}">
        <div class="evd-hero__overlay"></div>
    @else
        <div class="evd-hero-placeholder">
            <i class="fas fa-calendar-alt" style="font-size:4rem;color:rgba(180,60,120,.35);"></i>
        </div>
    @endif

    <div class="evd-hero__badges">
        <span class="evd-badge {{ $event->is_online ? 'evd-badge--online' : 'evd-badge--presencial' }}">
            <i class="fas {{ $event->is_online ? 'fa-wifi' : 'fa-map-marker-alt' }}" style="margin-right:.3rem;"></i>
            {{ $event->is_online ? 'Evento Online' : 'Presencial' }}
        </span>
    </div>

    @if($event->image_path)
    <div class="evd-hero__title">{{ $event->title }}</div>
    @endif
</div>

@if(!$event->image_path)
<h1 style="font-size:1.6rem;font-weight:800;color:var(--theme-text);margin:0 0 1.5rem;">{{ $event->title }}</h1>
@endif

<div class="evd-layout">

    {{-- Columna principal: descripción --}}
    <div>
        @if($event->description)
        <div class="evd-card">
            <p class="evd-section-title"><i class="fas fa-align-left" style="margin-right:.4rem;"></i>Acerca del evento</p>
            <div class="evd-description">{{ $event->description }}</div>
        </div>
        @endif
    </div>

    {{-- Columna lateral: info + precio --}}
    <div style="display:flex;flex-direction:column;gap:1rem;">

        {{-- Precio --}}
        <div class="evd-card" style="padding:1.25rem;">
            <div class="evd-price-box">
                <div class="evd-price-box__label">Precio de entrada</div>
                <div class="evd-price-box__amount {{ $isFree ? 'evd-price-box__amount--free' : '' }}">
                    {{ $priceLabel }}
                </div>
                @if($event->price_notes)
                <div class="evd-price-box__notes">
                    <i class="fas fa-info-circle" style="margin-right:.3rem;"></i>{{ $event->price_notes }}
                </div>
                @endif
            </div>
            <a href="{{ route('messages.index') }}" class="evd-contact-btn">
                <i class="fas fa-envelope"></i> Consultar o reservar
            </a>
        </div>

        {{-- Detalles del evento --}}
        <div class="evd-card" style="padding:1.25rem;">
            <p class="evd-section-title"><i class="fas fa-info-circle" style="margin-right:.4rem;"></i>Detalles</p>
            <ul class="evd-info-list">
                @if($event->starts_at)
                <li class="evd-info-item">
                    <div class="evd-info-item__icon"><i class="fas fa-calendar"></i></div>
                    <div class="evd-info-item__content">
                        <div class="evd-info-item__label">Fecha</div>
                        <div class="evd-info-item__value">
                            {{ \Carbon\Carbon::parse($event->starts_at)->translatedFormat('l, d \d\e F \d\e Y') }}
                        </div>
                    </div>
                </li>
                <li class="evd-info-item">
                    <div class="evd-info-item__icon"><i class="fas fa-clock"></i></div>
                    <div class="evd-info-item__content">
                        <div class="evd-info-item__label">Horario</div>
                        <div class="evd-info-item__value">
                            {{ \Carbon\Carbon::parse($event->starts_at)->format('H:i') }} hrs
                            @if($event->ends_at)
                                — {{ \Carbon\Carbon::parse($event->ends_at)->format('H:i') }} hrs
                            @endif
                        </div>
                    </div>
                </li>
                @endif

                @if($event->address)
                <li class="evd-info-item">
                    <div class="evd-info-item__icon"><i class="fas fa-map-marker-alt"></i></div>
                    <div class="evd-info-item__content">
                        <div class="evd-info-item__label">Lugar</div>
                        <div class="evd-info-item__value">{{ $event->address }}</div>
                    </div>
                </li>
                @endif

                @if($event->organized_by)
                <li class="evd-info-item">
                    <div class="evd-info-item__icon"><i class="fas fa-user"></i></div>
                    <div class="evd-info-item__content">
                        <div class="evd-info-item__label">Organiza</div>
                        <div class="evd-info-item__value">{{ $event->organized_by }}</div>
                    </div>
                </li>
                @endif
            </ul>
        </div>

    </div>
</div>

@endsection
