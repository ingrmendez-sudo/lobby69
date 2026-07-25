@extends('layouts.app')
@section('title', ($event->title ?? 'Evento') . ' — LOBBY69')
@push('sidebar-left')
    @include('layouts.sidebar-left')
@endpush
@push('sidebar-right')
    @include('layouts.sidebar-right')
@endpush

@section('content')
<div style="margin-bottom:1rem;">
    <a href="{{ route('events.public.index') }}"
       style="font-size:.85rem;color:var(--theme-muted);text-decoration:none;">
        ← Volver a eventos
    </a>
</div>

<div style="background:var(--theme-card);border:1px solid var(--theme-border);border-radius:16px;overflow:hidden;">
    @if($event->image_path)
    <div style="height:280px;overflow:hidden;">
        <img loading="eager" src="{{ asset('storage/' . $event->image_path) }}"
             alt="{{ $event->title }}"
             style="width:100%;height:100%;object-fit:cover;">
    </div>
    @endif
    <div style="padding:2rem;">
        <h1 style="font-size:1.8rem;font-weight:800;color:var(--theme-text);margin:0 0 1rem;">
            {{ $event->title }}
        </h1>
        <div style="display:flex;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
            @if($event->starts_at)
            <span style="font-size:.9rem;color:var(--theme-muted);">
                <i class="fas fa-calendar" style="color:#e056a0;margin-right:.35rem;"></i>
                {{ \Carbon\Carbon::parse($event->starts_at)->format('d \d\e M Y, H:i') }}
            </span>
            @endif
            @if($event->address)
            <span style="font-size:.9rem;color:var(--theme-muted);">
                <i class="fas fa-map-marker-alt" style="color:#e056a0;margin-right:.35rem;"></i>
                {{ $event->address }}
            </span>
            @endif
            @if($event->organized_by)
            <span style="font-size:.9rem;color:var(--theme-muted);">
                <i class="fas fa-user" style="color:#e056a0;margin-right:.35rem;"></i>
                {{ $event->organized_by }}
            </span>
            @endif
        </div>
        @if($event->description)
        <div style="font-size:.95rem;color:var(--theme-text);line-height:1.7;opacity:.9;">
            {!! nl2br(e($event->description)) !!}
        </div>
        @endif
    </div>
</div>
@endsection
