@extends('layouts.app')
@section('title', 'Quién visitó mi perfil')
@section('content')
@php
    $myNick = $userProfile->nickname ?? Auth::user()->name ?? 'Mi perfil';
@endphp
<div class="d-flex align-items-center justify-content-between mb-3">
    <h5 class="mb-0 fw-bold">👤 Visitantes de {{ $myNick }}</h5>
    <span class="text-muted" style="font-size:.85rem">{{ $totalVisitors }} visita(s) únicas</span>
</div>
<div style="background:var(--card-bg,#fff);border-radius:14px;box-shadow:0 2px 10px rgba(0,0,0,.07);overflow:hidden">
    @forelse($visitors as $v)
    @php
        $vDate  = \Carbon\Carbon::parse($v->viewed_at)->format('d M Y H:i');
        $vDiff  = \Carbon\Carbon::parse($v->viewed_at)->diffForHumans();
        $vInit  = strtoupper(substr($v->nickname ?? 'U', 0, 1));
        $vColor = '#' . substr(md5($v->nickname ?? 'U'), 0, 6);
    @endphp
    <div style="display:flex;align-items:center;gap:.9rem;padding:.9rem 1.1rem;border-bottom:1px solid var(--border-light,#f0f0f0)">
        @if(!empty($v->avatar_photo_id))
            <img src="{{ route('photos.serve', $v->avatar_photo_id) }}"
                 onerror="this.style.display='none';this.nextElementSibling.style.display='flex';"
                 style="width:46px;height:46px;border-radius:50%;object-fit:cover;flex-shrink:0">
            <div style="display:none;width:46px;height:46px;border-radius:50%;background:{{ $vColor }};
                        align-items:center;justify-content:center;flex-shrink:0;
                        font-weight:700;font-size:1.1rem;color:#fff;">{{ $vInit }}</div>
        @else
            <div style="display:flex;width:46px;height:46px;border-radius:50%;background:{{ $vColor }};
                        align-items:center;justify-content:center;flex-shrink:0;
                        font-weight:700;font-size:1.1rem;color:#fff;">{{ $vInit }}</div>
        @endif
        <div style="flex:1">
            <div style="font-weight:600;font-size:.92rem;color:var(--text-main,#222)">{{ $v->nickname ?? 'Usuario' }}</div>
            <div style="font-size:.76rem;color:var(--text-muted,#888);text-transform:capitalize">{{ $v->profile_type ?? '' }}</div>
        </div>
        <div style="text-align:right">
            <div style="font-size:.78rem;font-weight:600;color:var(--bs-pink,#e91e8c)">{{ $vDiff }}</div>
            <div style="font-size:.7rem;color:var(--text-muted,#888)">{{ $vDate }}</div>
        </div>
    </div>
    @empty
    <div style="padding:3rem;text-align:center;color:var(--text-muted,#888)">
        <div style="font-size:2.5rem;margin-bottom:.5rem">👤</div>
        <p>Nadie ha visitado tu perfil aún.</p>
    </div>
    @endforelse
</div>
@if($visitors->hasPages())
<div class="vg-pag mt-3">{{ $visitors->links('pagination::simple-bootstrap-5') }}</div>
@endif
@endsection