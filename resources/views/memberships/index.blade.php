@extends('layouts.app')

@section('title', 'Planes de Membresía')

@push('sidebar-left')
    @include('layouts.sidebar-left')
@endpush

@push('sidebar-right')
    @include('layouts.sidebar-right')
@endpush

@section('content')

{{-- Header de sección --}}
<div style="margin-bottom:1.5rem;">
    <h1 style="font-size:1.35rem;font-weight:800;color:var(--theme-text);margin:0 0 .35rem;">
        <i class="fas fa-crown" style="color:#fbbf24;margin-right:.4rem;"></i>
        Membresías
    </h1>
    <p style="font-size:.85rem;color:var(--theme-muted);margin:0;">
        Elige tu nivel de acceso. Todos los pagos son revisados manualmente en menos de 24 horas.
    </p>
</div>

{{-- Alerta de pago pendiente --}}
@if($pendingPayment)
<div style="background:#f59e0b22;border:1px solid #f59e0b55;border-radius:10px;padding:.9rem 1.1rem;margin-bottom:1.25rem;display:flex;align-items:center;gap:.75rem;">
    <i class="fas fa-clock" style="color:#f59e0b;font-size:1.1rem;flex-shrink:0;"></i>
    <div>
        <div style="font-size:.85rem;font-weight:700;color:#f59e0b;">Solicitud en revisión</div>
        <div style="font-size:.78rem;color:var(--theme-muted);">
            Tienes una solicitud para <strong>{{ ucfirst($pendingPayment->requested_membership) }}</strong>
            enviada el {{ \Carbon\Carbon::parse($pendingPayment->created_at)->format('d/m/Y') }}.
            Te notificaremos cuando sea procesada.
        </div>
    </div>
    <a href="{{ route('membership.status') }}"
       style="margin-left:auto;font-size:.78rem;color:var(--theme-accent);text-decoration:none;white-space:nowrap;">
        Ver estado <i class="fas fa-arrow-right"></i>
    </a>
</div>
@endif

{{-- Badge membresía actual --}}
<div style="margin-bottom:1.5rem;padding:.85rem 1rem;background:var(--theme-surface-2);border-radius:10px;border:1px solid rgba(180,60,120,.18);display:flex;align-items:center;gap:.75rem;">
    <i class="fas fa-id-badge" style="color:var(--theme-accent);"></i>
    <span style="font-size:.85rem;color:var(--theme-muted);">Tu membresía actual:</span>
    <span class="l69-membership-badge l69-membership-badge--{{ $currentTier }}" style="font-size:.78rem;">
        {{ strtoupper($currentTier) }}
    </span>
    @if($user->membership_expires_at)
    <span style="font-size:.75rem;color:var(--theme-muted);margin-left:auto;">
        Vence: {{ \Carbon\Carbon::parse($user->membership_expires_at)->format('d/m/Y') }}
    </span>
    @endif
</div>

{{-- Grid de planes --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:1rem;margin-bottom:2rem;">
@foreach($plans as $plan)

@php
    $isCurrent  = ($currentTier === $plan->slug);
    $isVitalicio = ($plan->slug === 'vitalicio');
    $borderColor = $isCurrent  ? 'rgba(34,197,94,.4)'
                 : ($isVitalicio ? 'rgba(192,57,43,.5)'
                 : 'rgba(180,60,120,.18)');
    $glowColor   = $isVitalicio ? '0 0 24px rgba(192,57,43,.15)' : 'none';
@endphp

<div style="background:var(--theme-surface-2);border:1.5px solid {{ $borderColor }};
            border-radius:14px;padding:1.25rem;position:relative;
            box-shadow:{{ $glowColor }};
            {{ $isVitalicio ? 'background:linear-gradient(145deg,rgba(192,57,43,.08),rgba(142,68,173,.08));' : '' }}">

    {{-- Badge "Actual" --}}
    @if($isCurrent)
    <div style="position:absolute;top:.75rem;right:.75rem;
                background:#22c55e22;color:#22c55e;border:1px solid #22c55e44;
                border-radius:20px;font-size:.68rem;font-weight:700;padding:.2rem .55rem;">
        <i class="fas fa-check"></i> Actual
    </div>
    @elseif($isVitalicio)
    <div style="position:absolute;top:.75rem;right:.75rem;
                background:linear-gradient(90deg,rgba(192,57,43,.25),rgba(142,68,173,.25));
                color:#e056a0;border:1px solid rgba(192,57,43,.3);
                border-radius:20px;font-size:.68rem;font-weight:700;padding:.2rem .55rem;">
        <i class="fas fa-infinity"></i> Exclusivo
    </div>
    @endif

    {{-- Nombre del plan --}}
    <div style="font-size:1.05rem;font-weight:800;color:var(--theme-text);margin-bottom:.3rem;">
        {{ $plan->name }}
    </div>

    {{-- Precio --}}
    <div style="margin-bottom:.85rem;">
        @if($plan->active_price > 0)

            {{-- Precio tachado + badge de ahorro en verde --}}
            @if($plan->promo_active && $plan->price_normal > $plan->price_promo)
            <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.3rem;">
                <span style="font-size:.78rem;color:var(--theme-muted);text-decoration:line-through;opacity:.7;">
                    ${{ number_format($plan->price_normal, 0) }} MXN
                </span>
                <span style="background:rgba(34,197,94,.15);color:#22c55e;
                            border:1px solid rgba(34,197,94,.3);
                            border-radius:20px;font-size:.68rem;font-weight:700;
                            padding:.15rem .5rem;letter-spacing:.02em;">
                    Ahorras {{ $plan->discount_percent }}%
                </span>
            </div>
            @endif

            {{-- Precio activo --}}
            <div style="display:flex;align-items:baseline;gap:.35rem;flex-wrap:wrap;">
                <span style="font-size:1.65rem;font-weight:900;color:var(--theme-text);">
                    ${{ number_format($plan->active_price, 0) }}
                </span>
                <span style="font-size:.78rem;color:var(--theme-muted);">
                    MXN / {{ $plan->duration_label }}
                </span>
            </div>

            {{-- Duración en tono suave --}}
            @if(! $plan->is_lifetime && $plan->duration_days)
            <div style="font-size:.72rem;color:var(--theme-muted);margin-top:.25rem;
                        display:flex;align-items:center;gap:.3rem;">
                <i class="fas fa-calendar-alt" style="color:var(--theme-accent);opacity:.7;font-size:.65rem;"></i>
                {{ $plan->duration_days }} días de acceso
            </div>
            @endif

        @else
            <span style="font-size:1.4rem;font-weight:800;color:#22c55e;">Gratis</span>
        @endif
    </div>



    {{-- Descripción --}}
    @if($plan->description)
    <p style="font-size:.8rem;color:var(--theme-muted);margin:0 0 .85rem;line-height:1.5;">
        {{ $plan->description }}
    </p>
    @endif

    {{-- Features --}}
    @if($plan->features && is_array($plan->features))
    <ul style="list-style:none;margin:0 0 1rem;padding:0;display:flex;flex-direction:column;gap:.3rem;">
        @foreach($plan->features as $feat)
        <li style="font-size:.78rem;color:var(--theme-muted);display:flex;align-items:center;gap:.4rem;">
            <i class="fas fa-check" style="color:#22c55e;font-size:.65rem;flex-shrink:0;"></i>
            {{ $feat }}
        </li>
        @endforeach
    </ul>
    @endif

    {{-- CTA --}}
    @if($isCurrent)
    <button disabled
            style="width:100%;padding:.6rem;border-radius:8px;
                border:1px solid rgba(34,197,94,.3);
                background:rgba(34,197,94,.08);
                color:#22c55e;font-size:.82rem;
                cursor:not-allowed;font-weight:600;">
        <i class="fas fa-check-circle"></i> Plan activo
    </button>

    @elseif($plan->active_price == 0)
    <span style="display:block;text-align:center;font-size:.78rem;
                color:var(--theme-muted);padding:.55rem;">
        Plan base automático
    </span>

    @else
    @php
        // Degradado cálido por tier — invita sin agredir
        $ctaGradients = [
            'explorer'   => 'linear-gradient(135deg,#3b82f6,#6366f1)',
            'connectors' => 'linear-gradient(135deg,#8b5cf6,#a855f7)',
            'influencer' => 'linear-gradient(135deg,#ec4899,#f43f5e)',
            'vip_elite'  => 'linear-gradient(135deg,#f59e0b,#f97316)',
            'vitalicio'  => 'linear-gradient(135deg,#b43c78,#7c3aed)',
        ];
        $ctaGrad = $ctaGradients[$plan->slug] ?? 'linear-gradient(135deg,var(--theme-accent),#7c3aed)';
    @endphp
    <a href="{{ route('membership.request', ['plan' => $plan->slug]) }}"
    style="display:block;text-align:center;padding:.6rem 1rem;
            border-radius:8px;background:{{ $ctaGrad }};
            color:#fff;text-decoration:none;font-size:.84rem;
            font-weight:700;letter-spacing:.02em;
            box-shadow:0 4px 14px rgba(0,0,0,.25);
            transition:opacity .15s,transform .15s;"
    onmouseover="this.style.opacity='.9';this.style.transform='translateY(-1px)'"
    onmouseout="this.style.opacity='1';this.style.transform='translateY(0)'">
        Obtener {{ $plan->name }}
        <i class="fas fa-arrow-right" style="margin-left:.3rem;font-size:.75rem;"></i>
    </a>
    @endif


</div>
@endforeach
</div>

{{-- Info de proceso de pago --}}
<div class="l69-sidebar-card" style="max-width:580px;margin:0 auto;">
    <div class="l69-sidebar-card__title">
        <i class="fas fa-info-circle"></i> ¿Cómo funciona?
    </div>
    <ol style="margin:0;padding-left:1.25rem;display:flex;flex-direction:column;gap:.5rem;">
        <li style="font-size:.82rem;color:var(--theme-muted);">Elige tu plan y haz clic en <strong>Obtener</strong>.</li>
        <li style="font-size:.82rem;color:var(--theme-muted);">Realiza el pago por transferencia, OXXO, tarjeta, PayPal o cripto.</li>
        <li style="font-size:.82rem;color:var(--theme-muted);">Adjunta tu comprobante o indica tu referencia de pago.</li>
        <li style="font-size:.82rem;color:var(--theme-muted);">Nuestro equipo verificará y activará tu membresía en <strong>menos de 24 horas</strong>.</li>
    </ol>
    <div style="margin-top:.85rem;padding-top:.85rem;border-top:1px solid rgba(180,60,120,.12);">
        <a href="{{ route('membership.status') }}"
           style="font-size:.8rem;color:var(--theme-accent);text-decoration:none;">
            <i class="fas fa-list-alt"></i> Ver historial de mis solicitudes →
        </a>
    </div>
</div>

@endsection
