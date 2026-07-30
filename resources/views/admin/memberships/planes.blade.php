@extends('layouts.admin')
@section('title', 'Planes y Precios')
@section('page-title', 'Gestión de Planes y Precios')

@section('content')

@if(session('success'))
<div style="background:#22c55e22;border:1px solid #22c55e;color:#22c55e;padding:.75rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:.85rem;">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(340px,1fr));gap:1.25rem;">
@foreach($plans as $plan)
<div class="adm-card" style="padding:1.5rem;position:relative;">

    {{-- Badge estado --}}
    <div style="position:absolute;top:1rem;right:1rem;display:flex;gap:.4rem;">
        <span style="padding:.2rem .6rem;border-radius:20px;font-size:.7rem;font-weight:700;
            background:{{ $plan->is_active ? '#22c55e22' : '#ef444422' }};
            color:{{ $plan->is_active ? '#22c55e' : '#ef4444' }};
            border:1px solid {{ $plan->is_active ? '#22c55e' : '#ef4444' }};">
            {{ $plan->is_active ? 'Activo' : 'Inactivo' }}
        </span>
        @if($plan->promo_active)
        <span style="padding:.2rem .6rem;border-radius:20px;font-size:.7rem;font-weight:700;
            background:#f59e0b22;color:#f59e0b;border:1px solid #f59e0b;">
            PROMO ON
        </span>
        @endif
    </div>

    <h3 style="margin:0 0 .25rem;font-size:1rem;color:var(--theme-text);">
        {{ $plan->name }}
    </h3>
    <p style="font-size:.75rem;color:var(--theme-muted);margin:0 0 1.25rem;">
        {{ $plan->is_lifetime ? 'Acceso permanente' : ($plan->duration_days . ' días') }}
        · slug: <code style="background:var(--theme-bg);padding:.1rem .3rem;border-radius:4px;">{{ $plan->slug }}</code>
    </p>

    <form method="POST" action="{{ route('admin.memberships.plans.update', $plan->slug) }}">
        @csrf @method('PUT')

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:.75rem;">
            <div>
                <label style="display:block;font-size:.72rem;color:var(--theme-muted);margin-bottom:.3rem;">
                    💰 Precio Promoción (MXN)
                </label>
                <input type="number" name="price_promo" step="0.01" min="0"
                       value="{{ $plan->price_promo }}"
                       style="width:100%;padding:.45rem .75rem;border-radius:7px;border:1px solid var(--theme-border);background:var(--theme-bg);color:var(--theme-text);font-size:.9rem;font-weight:700;">
            </div>
            <div>
                <label style="display:block;font-size:.72rem;color:var(--theme-muted);margin-bottom:.3rem;">
                    💳 Precio Normal (MXN)
                </label>
                <input type="number" name="price_normal" step="0.01" min="0"
                       value="{{ $plan->price_normal }}"
                       style="width:100%;padding:.45rem .75rem;border-radius:7px;border:1px solid var(--theme-border);background:var(--theme-bg);color:var(--theme-text);font-size:.9rem;">
            </div>
        </div>

        @if(!$plan->is_lifetime)
        <div style="margin-bottom:.75rem;">
            <label style="display:block;font-size:.72rem;color:var(--theme-muted);margin-bottom:.3rem;">
                📅 Duración (días)
            </label>
            <input type="number" name="duration_days" min="1"
                   value="{{ $plan->duration_days }}"
                   style="width:100%;padding:.45rem .75rem;border-radius:7px;border:1px solid var(--theme-border);background:var(--theme-bg);color:var(--theme-text);font-size:.85rem;">
        </div>
        @else
            <input type="hidden" name="duration_days" value="">
        @endif

        <div style="margin-bottom:.75rem;">
            <label style="display:block;font-size:.72rem;color:var(--theme-muted);margin-bottom:.3rem;">
                📝 Descripción
            </label>
            <textarea name="description" rows="2"
                      style="width:100%;padding:.45rem .75rem;border-radius:7px;border:1px solid var(--theme-border);background:var(--theme-bg);color:var(--theme-text);font-size:.82rem;resize:none;">{{ $plan->description }}</textarea>
        </div>

        <div style="display:flex;gap:1rem;margin-bottom:1rem;">
            <label style="display:flex;align-items:center;gap:.4rem;font-size:.8rem;color:var(--theme-text);cursor:pointer;">
                <input type="checkbox" name="is_active" value="1"
                       {{ $plan->is_active ? 'checked' : '' }}
                       style="width:15px;height:15px;">
                Plan activo
            </label>
            <label style="display:flex;align-items:center;gap:.4rem;font-size:.8rem;color:var(--theme-text);cursor:pointer;">
                <input type="checkbox" name="promo_active" value="1"
                       {{ $plan->promo_active ? 'checked' : '' }}
                       style="width:15px;height:15px;">
                Promoción activa
            </label>
        </div>

        <div style="display:flex;align-items:center;justify-content:space-between;padding-top:.75rem;border-top:1px solid var(--theme-border);">
            <div style="font-size:.75rem;color:var(--theme-muted);">
                @if($plan->promo_active)
                    Precio actual:
                    <strong style="color:#f59e0b;">${{ number_format($plan->price_promo, 2) }}</strong>
                    <span style="text-decoration:line-through;margin-left:.3rem;">${{ number_format($plan->price_normal, 2) }}</span>
                @else
                    Precio actual:
                    <strong style="color:#22c55e;">${{ number_format($plan->price_normal, 2) }}</strong>
                @endif
            </div>
            <button type="submit"
                    style="padding:.4rem 1rem;background:var(--theme-accent);color:#fff;border:none;border-radius:8px;cursor:pointer;font-size:.82rem;font-weight:600;">
                <i class="fas fa-save"></i> Guardar
            </button>
        </div>
    </form>
</div>
@endforeach
</div>

@endsection
