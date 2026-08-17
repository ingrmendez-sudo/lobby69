@extends('layouts.app')

@section('title', 'Estado de mis membresías')

@push('sidebar-left')
    @include('layouts.sidebar-left')
@endpush

@section('content')

@php
// Mapa de imágenes por tier
$tierImages = [
    'explorer'   => '/img/membership/explorer.png',
    'connectors' => '/img/membership/connectors.png',
    'influencer' => '/img/membership/influencer.png',
    'vip_elite'  => '/img/membership/vip-elite.png',
    'Fundador'  => '/img/membership/Fundador.png',
];

// Mapa de colores por tier
$tierColors = [
    'invitado'   => '#6b7280',
    'explorer'   => '#3b82f6',
    'connectors' => '#8b5cf6',
    'influencer' => '#ec4899',
    'vip_elite'  => '#f59e0b',
    'Fundador'  => '#ef4444',
];

// Membresía activa actual del usuario
$currentTier     = auth()->user()->membership_type ?? 'invitado';
$currentExpiry   = auth()->user()->membership_expires_at;
$currentStarted  = auth()->user()->membership_started_at;
$currentColor    = $tierColors[$currentTier] ?? '#6b7280';
$currentImage    = $tierImages[$currentTier] ?? null;
@endphp

{{-- ══════════════════════════════════════════════════════
     TARJETA VISUAL DE MEMBRESÍA ACTIVA
══════════════════════════════════════════════════════ --}}
<div style="margin-bottom:1.75rem;">

    <div style="position:relative;overflow:hidden;border-radius:16px;
                border:1.5px solid {{ $currentColor }}55;
                background:linear-gradient(135deg, {{ $currentColor }}18 0%, rgba(15,15,26,.95) 60%);
                padding:1.5rem;display:flex;align-items:center;gap:1.25rem;
                box-shadow:0 0 40px {{ $currentColor }}22;">

        {{-- Glow de fondo --}}
        <div style="position:absolute;top:-40px;right:-40px;width:180px;height:180px;
                    border-radius:50%;background:{{ $currentColor }}18;filter:blur(40px);pointer-events:none;"></div>

        {{-- Imagen del tier --}}
        <div style="flex-shrink:0;width:90px;height:90px;position:relative;z-index:1;">
            @if($currentImage)
                <img loading="lazy" src="{{ $currentImage }}"
                     alt="{{ $currentTier }}"
                     style="width:90px;height:90px;object-fit:contain;
                            filter:drop-shadow(0 0 12px {{ $currentColor }}88);">
            @else
                {{-- Fallback invitado --}}
                <div style="width:90px;height:90px;border-radius:50%;
                            background:{{ $currentColor }}22;border:2px solid {{ $currentColor }}44;
                            display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-user-circle" style="font-size:2.8rem;color:{{ $currentColor }};"></i>
                </div>
            @endif
        </div>

        {{-- Info membresía --}}
        <div style="flex:1;position:relative;z-index:1;">
            <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:.08em;
                        color:{{ $currentColor }};font-weight:700;margin-bottom:.2rem;">
                Membresía activa
            </div>
            <div style="font-size:1.4rem;font-weight:900;color:var(--theme-text);
                        letter-spacing:.02em;margin-bottom:.6rem;">
                {{ strtoupper(str_replace('_', ' ', $currentTier)) }}
            </div>

            <div style="display:flex;flex-wrap:wrap;gap:.75rem;">
                {{-- Fecha de aprobación --}}
                @if($currentStarted)
                <div style="background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);
                            border-radius:8px;padding:.35rem .7rem;">
                    <div style="font-size:.65rem;color:var(--theme-muted);text-transform:uppercase;
                                letter-spacing:.06em;margin-bottom:.1rem;">
                        <i class="fas fa-calendar-check" style="color:{{ $currentColor }};"></i>
                        Aprobación
                    </div>
                    <div style="font-size:.85rem;font-weight:700;color:var(--theme-text);">
                        {{ \Carbon\Carbon::parse($currentStarted)->format('d/m/y') }}
                    </div>
                </div>
                @endif

                {{-- Vigencia --}}
                <div style="background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);
                            border-radius:8px;padding:.35rem .7rem;">
                    <div style="font-size:.65rem;color:var(--theme-muted);text-transform:uppercase;
                                letter-spacing:.06em;margin-bottom:.1rem;">
                        <i class="fas fa-hourglass-half" style="color:{{ $currentColor }};"></i>
                        Vigencia hasta
                    </div>
                    <div style="font-size:.85rem;font-weight:700;color:var(--theme-text);">
                        @if($currentExpiry)
                            {{ \Carbon\Carbon::parse($currentExpiry)->format('d/m/y') }}
                            @php $daysLeft = \Carbon\Carbon::now()->diffInDays($currentExpiry, false); @endphp
                            @if($daysLeft <= 7 && $daysLeft >= 0)
                            <span style="font-size:.68rem;color:#f59e0b;margin-left:.3rem;">
                                <i class="fas fa-exclamation-triangle"></i> {{ $daysLeft }}d
                            </span>
                            @elseif($daysLeft < 0)
                            <span style="font-size:.68rem;color:#ef4444;margin-left:.3rem;">
                                <i class="fas fa-times-circle"></i> Vencida
                            </span>
                            @endif
                        @else
                            <span style="color:{{ $currentColor }};">
                                <i class="fas fa-infinity"></i> Fundador
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Días restantes (solo si tiene expiración) --}}
                @if($currentExpiry)
                @php $daysRemaining = max(0, \Carbon\Carbon::now()->diffInDays($currentExpiry, false)); @endphp
                <div style="background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);
                            border-radius:8px;padding:.35rem .7rem;">
                    <div style="font-size:.65rem;color:var(--theme-muted);text-transform:uppercase;
                                letter-spacing:.06em;margin-bottom:.1rem;">
                        <i class="fas fa-clock" style="color:{{ $currentColor }};"></i>
                        Días restantes
                    </div>
                    <div style="font-size:.85rem;font-weight:700;
                                color:{{ $daysRemaining <= 7 ? '#f59e0b' : 'var(--theme-text)' }};">
                        {{ $daysRemaining }} días
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Badge tier (esquina) --}}
        <div style="position:absolute;top:.75rem;right:.85rem;
                    background:{{ $currentColor }}22;color:{{ $currentColor }};
                    border:1px solid {{ $currentColor }}44;border-radius:20px;
                    font-size:.68rem;font-weight:800;padding:.2rem .6rem;
                    letter-spacing:.05em;z-index:1;">
            {{ strtoupper($currentTier) }}
        </div>

    </div>
</div>

{{-- ══════════════════════════════════════════════════════
     HEADER + BOTÓN
══════════════════════════════════════════════════════ --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;flex-wrap:wrap;gap:.75rem;">
    <h2 style="font-size:1rem;font-weight:800;color:var(--theme-text);margin:0;">
        <i class="fas fa-list-alt" style="color:var(--theme-accent);margin-right:.4rem;"></i>
        Historial de solicitudes
    </h2>
    <a href="{{ route('membership.index') }}"
       style="padding:.4rem 1rem;background:rgba(180,60,120,.12);color:var(--theme-accent);
              border:1px solid rgba(180,60,120,.25);border-radius:20px;text-decoration:none;
              font-size:.8rem;font-weight:600;">
        <i class="fas fa-arrow-left"></i> Ver planes
    </a>
</div>

@if(session('success'))
<div style="background:#22c55e22;border:1px solid #22c55e;color:#22c55e;padding:.75rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:.85rem;">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif

{{-- ══════════════════════════════════════════════════════
     LISTA DE SOLICITUDES
══════════════════════════════════════════════════════ --}}
@if($payments->isEmpty())
<div style="background:var(--theme-surface-2);border:1px solid rgba(180,60,120,.15);
            border-radius:12px;padding:3rem;text-align:center;color:var(--theme-muted);">
    <i class="fas fa-inbox" style="font-size:2.5rem;opacity:.2;display:block;margin-bottom:1rem;"></i>
    <p style="font-size:.9rem;">Aún no tienes solicitudes de membresía.</p>
    <a href="{{ route('membership.index') }}"
       style="display:inline-block;margin-top:.75rem;padding:.5rem 1.25rem;
              background:var(--theme-accent);color:#fff;border-radius:8px;
              text-decoration:none;font-size:.85rem;font-weight:600;">
        Ver planes disponibles
    </a>
</div>

@else
<div style="display:flex;flex-direction:column;gap:.75rem;">
@foreach($payments as $payment)

@php
    $pTier  = $payment->requested_membership;
    $pColor = $tierColors[$pTier] ?? '#6b7280';
    $pImage = $tierImages[$pTier] ?? null;
@endphp

<div style="background:var(--theme-surface-2);
            border:1px solid {{ $payment->isApproved() ? $pColor.'44' : 'rgba(180,60,120,.15)' }};
            border-radius:12px;overflow:hidden;
            border-left:3px solid {{ $payment->statusColor() }};">

    <div style="padding:1rem 1.15rem;display:flex;align-items:flex-start;gap:1rem;flex-wrap:wrap;">

        {{-- Imagen del tier solicitado --}}
        @if($pImage && $payment->isApproved())
        <div style="flex-shrink:0;width:52px;height:52px;">
            <img loading="lazy" src="{{ $pImage }}"
                 alt="{{ $pTier }}"
                 style="width:52px;height:52px;object-fit:contain;
                        filter:drop-shadow(0 0 6px {{ $pColor }}66);">
        </div>
        @endif

        {{-- Info principal --}}
        <div style="flex:1;min-width:0;">
            <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.3rem;flex-wrap:wrap;">
                <span style="font-size:.85rem;font-weight:700;color:var(--theme-muted);">
                    {{ ucfirst(str_replace('_',' ', $payment->current_membership ?? 'invitado')) }}
                </span>
                <i class="fas fa-arrow-right" style="font-size:.6rem;color:var(--theme-muted);"></i>
                <span style="font-size:.9rem;font-weight:800;color:{{ $pColor }};">
                    {{ strtoupper(str_replace('_',' ', $pTier)) }}
                </span>
            </div>

            {{-- Fechas cuando está aprobado --}}
            @if($payment->isApproved())
            <div style="display:flex;flex-wrap:wrap;gap:.5rem;margin-top:.4rem;">
                <span style="font-size:.72rem;color:var(--theme-muted);
                             background:rgba(255,255,255,.05);padding:.2rem .5rem;border-radius:6px;">
                    <i class="fas fa-calendar-check" style="color:#22c55e;"></i>
                    Aprobado: {{ \Carbon\Carbon::parse($payment->reviewed_at ?? $payment->updated_at)->format('d/m/y') }}
                </span>
                @if(auth()->user()->membership_expires_at && $pTier === auth()->user()->membership_type)
                <span style="font-size:.72rem;color:var(--theme-muted);
                             background:rgba(255,255,255,.05);padding:.2rem .5rem;border-radius:6px;">
                    <i class="fas fa-hourglass-half" style="color:{{ $pColor }};"></i>
                    Vigente hasta: {{ \Carbon\Carbon::parse(auth()->user()->membership_expires_at)->format('d/m/y') }}
                </span>
                @elseif(! auth()->user()->membership_expires_at && $pTier === auth()->user()->membership_type)
                <span style="font-size:.72rem;color:{{ $pColor }};
                             background:{{ $pColor }}15;padding:.2rem .5rem;border-radius:6px;">
                    <i class="fas fa-infinity"></i> Fundador
                </span>
                @endif
            </div>
            @else
            <div style="font-size:.75rem;color:var(--theme-muted);margin-top:.2rem;">
                {{ $payment->payment_method ? ucfirst($payment->payment_method) : '—' }}
                @if($payment->payment_reference)
                    · Ref: <code style="font-size:.72rem;">{{ $payment->payment_reference }}</code>
                @endif
            </div>
            @endif
        </div>

        {{-- Badge de status --}}
        <div style="text-align:right;flex-shrink:0;">
            <div style="display:inline-flex;align-items:center;gap:.35rem;
                        padding:.25rem .65rem;border-radius:20px;
                        background:{{ $payment->statusColor() }}22;
                        color:{{ $payment->statusColor() }};
                        border:1px solid {{ $payment->statusColor() }}44;
                        font-size:.72rem;font-weight:700;">
                @if($payment->isPending())
                    <i class="fas fa-clock"></i>
                @elseif($payment->isApproved())
                    <i class="fas fa-check"></i>
                @else
                    <i class="fas fa-times"></i>
                @endif
                {{ $payment->statusLabel() }}
            </div>
            <div style="font-size:.7rem;color:var(--theme-muted);margin-top:.3rem;">
                {{ \Carbon\Carbon::parse($payment->created_at)->format('d/m/Y H:i') }}
            </div>
        </div>

    </div>

    {{-- Motivo de rechazo --}}
    @if($payment->isRejected() && $payment->admin_note)
    <div style="margin:.0 1rem .75rem;padding:.5rem .75rem;
                background:#ef444414;border-radius:6px;font-size:.78rem;color:#ef4444;">
        <i class="fas fa-exclamation-circle"></i> {{ $payment->admin_note }}
    </div>
    @endif

</div>
@endforeach
</div>

@if($payments->hasPages())
<div style="margin-top:1rem;display:flex;justify-content:center;">
    {{ $payments->links() }}
</div>
@endif
@endif

@endsection

