@extends('layouts.app')
@section('title', 'Eventos — LOBBY69')
@push('sidebar-left')
    @include('layouts.sidebar-left')
@endpush
@push('sidebar-right')
    @include('layouts.sidebar-right')
@endpush

@section('content')
<div style="margin-bottom:1.5rem;">
    <h1 style="font-size:1.6rem;font-weight:800;color:var(--theme-text);margin:0 0 .25rem;">
        🎉 Eventos
    </h1>
    <p style="color:var(--theme-muted);margin:0;">Próximos eventos de la comunidad</p>
</div>

@if($events->isEmpty())
<div style="text-align:center;padding:4rem 2rem;background:var(--theme-surface-2);border-radius:16px;border:1px solid rgba(180,60,120,.15);">
    <i class="fas fa-calendar-times" style="font-size:3rem;color:rgba(180,60,120,.4);margin-bottom:1rem;display:block;"></i>
    <p style="color:rgba(226,217,243,.6);margin:0;">No hay eventos publicados por el momento.</p>
</div>
@else
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:1.25rem;">
    @foreach($events as $event)
    <div style="background:var(--theme-card);border:1px solid var(--theme-border);border-radius:16px;overflow:hidden;">
        @if($event->image_path)
        <div style="height:180px;overflow:hidden;">
            <img src="{{ asset('storage/' . $event->image_path) }}"
                 alt="{{ $event->title }}"
                 style="width:100%;height:100%;object-fit:cover;"
                 onerror="this.parentElement.style.background='rgba(180,60,120,.1)';this.remove()">
        </div>
        @else
        <div style="height:120px;background:linear-gradient(135deg,rgba(180,60,120,.2),rgba(108,63,197,.2));display:flex;align-items:center;justify-content:center;">
            <i class="fas fa-calendar-alt" style="font-size:2.5rem;color:rgba(180,60,120,.5);"></i>
        </div>
        @endif
        <div style="padding:1.25rem;">
            <h2 style="font-size:1.1rem;font-weight:700;color:var(--theme-text);margin:0 0 .5rem;">
                {{ $event->title }}
            </h2>
            @if($event->starts_at)
            <p style="font-size:.82rem;color:var(--theme-muted);margin:0 0 .4rem;">
                <i class="fas fa-calendar" style="color:#e056a0;margin-right:.35rem;"></i>
                {{ \Carbon\Carbon::parse($event->starts_at)->format('d M Y, H:i') }}
            </p>
            @endif
            @if($event->address)
            <p style="font-size:.82rem;color:var(--theme-muted);margin:0 0 .75rem;">
                <i class="fas fa-map-marker-alt" style="color:#e056a0;margin-right:.35rem;"></i>
                {{ $event->address }}
            </p>
            @endif
            @if($event->description)
            <p style="font-size:.85rem;color:var(--theme-text);opacity:.8;margin:0 0 1rem;line-height:1.5;">
                {{ Str::limit($event->description, 120) }}
            </p>
            @endif
            <a href="{{ route('events.public.show', $event->id) }}"
               class="l69-quick-btn"
               style="display:inline-flex;width:auto;padding:.5rem 1.25rem;font-size:.85rem;">
                Ver más <i class="fas fa-arrow-right" style="margin-left:.4rem;"></i>
            </a>
        </div>
    </div>
    @endforeach
</div>
@endif
@endsection
