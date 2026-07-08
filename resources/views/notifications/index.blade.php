@extends('layouts.app')
@section('title', 'Notificaciones — LOBBY69')
@push('sidebar-left')
    @include('layouts.sidebar-left')
@endpush
@push('sidebar-right')
    @include('layouts.sidebar-right')
@endpush

@section('content')
<div style="margin-bottom:1.5rem;">
    <h1 style="font-size:1.6rem;font-weight:800;color:var(--theme-text);margin:0 0 .25rem;">
        🔔 Notificaciones
    </h1>
    <p style="color:var(--theme-muted);margin:0;">Tus últimas actividades</p>
</div>

<div style="background:var(--theme-card);border:1px solid var(--theme-border);border-radius:16px;overflow:hidden;">
    @if($notifs->isEmpty())
    <div style="text-align:center;padding:4rem 2rem;">
        <i class="fas fa-bell-slash" style="font-size:3rem;color:rgba(180,60,120,.3);margin-bottom:1rem;display:block;"></i>
        <p style="color:var(--theme-muted);margin:0;">No tienes notificaciones aún.</p>
    </div>
    @else
    @foreach($notifs as $notif)
    @php
        $icon    = 'fa-bell';
        $color   = '#e056a0';
        $message = '';
        $url     = '#';
        $d       = $notif->data;

        switch($notif->type) {
            case 'like':
                $icon    = 'fa-heart';
                $color   = '#e056a0';
                $message = ($d['from_nick'] ?? 'Alguien') . ' le dio like a tu foto';
                $url     = $d['photo_id'] ? '/dashboard' : '#';
                break;
            case 'comment':
                $icon    = 'fa-comment';
                $color   = '#8b5cf6';
                $message = ($d['from_nick'] ?? 'Alguien') . ' comentó tu foto';
                $url     = '/dashboard';
                break;
            case 'follow':
                $icon    = 'fa-user-plus';
                $color   = '#22c55e';
                $message = ($d['from_nick'] ?? 'Alguien') . ' empezó a seguirte';
                $url     = isset($d['from_nick']) ? '/u/' . $d['from_nick'] : '#';
                break;
            case 'article_like':
                $icon    = 'fa-newspaper';
                $color   = '#f59e0b';
                $message = ($d['from_nick'] ?? 'Alguien') . ' le dio like a un artículo';
                break;
        }
    @endphp
    <div style="display:flex;align-items:flex-start;gap:1rem;padding:1rem 1.25rem;
                border-bottom:1px solid var(--theme-border);
                {{ $notif->read_at ? '' : 'background:rgba(180,60,120,.05);' }}">
        <div style="width:38px;height:38px;border-radius:50%;flex-shrink:0;
                    background:rgba(180,60,120,.1);display:flex;align-items:center;justify-content:center;">
            <i class="fas {{ $icon }}" style="color:{{ $color }};font-size:.9rem;"></i>
        </div>
        <div style="flex:1;min-width:0;">
            <a href="{{ $url }}"
               style="font-size:.88rem;color:var(--theme-text);text-decoration:none;font-weight:{{ $notif->read_at ? '400' : '600' }};">
                {{ $message }}
            </a>
            <div style="font-size:.75rem;color:var(--theme-muted);margin-top:.2rem;">
                {{ \Carbon\Carbon::parse($notif->created_at)->diffForHumans() }}
            </div>
        </div>
        @if(!$notif->read_at)
        <div style="width:8px;height:8px;border-radius:50%;background:#e056a0;flex-shrink:0;margin-top:.35rem;"></div>
        @endif
    </div>
    @endforeach
    @endif
</div>
@endsection
