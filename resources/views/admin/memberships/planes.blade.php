@extends('layouts.admin')

@section('title', 'Planes y Privilegios')

@section('content')
<div style="max-width:1100px;margin:0 auto;padding:1.5rem;">

    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;">
        <div>
            <h1 style="font-size:1.4rem;font-weight:700;margin:0;">Planes y Privilegios</h1>
            <p style="color:var(--theme-muted);font-size:.83rem;margin:.25rem 0 0;">
                Configura precios y permisos por tier. Los cambios aplican en tiempo real.
            </p>
        </div>
        <a href="{{ route('admin.memberships.index') }}" 
           style="padding:.5rem 1rem;border-radius:8px;border:1px solid var(--theme-border);font-size:.83rem;text-decoration:none;color:var(--theme-text);">
            ← Membresías
        </a>
    </div>

    @if(session('success'))
    <div style="background:#22c55e22;border:1px solid #22c55e;color:#22c55e;padding:.75rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:.85rem;">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif

    @foreach($plans as $plan)
    @php
        $f = is_string($plan->features) ? json_decode($plan->features, true) : (array)($plan->features ?? []);
        $isLifetime = $plan->is_lifetime;
        $tierColors = [
            'invitado'   => '#6b7280',
            'explorer'   => '#3b82f6',
            'connectors' => '#8b5cf6',
            'influencer' => '#ec4899',
            'vip_elite'  => '#f59e0b',
            'fundador'   => '#e056a0',
        ];
        $color = $tierColors[$plan->slug] ?? '#6b7280';
    @endphp

    <div class="adm-card" style="margin-bottom:1.5rem;border-left:4px solid {{ $color }};">
        <form method="POST" action="{{ route('admin.memberships.plans.update', $plan->slug) }}">
            @csrf @method('PUT')

            {{-- Header del plan --}}
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.2rem;flex-wrap:wrap;gap:.75rem;">
                <div style="display:flex;align-items:center;gap:.75rem;">
                    <span style="background:{{ $color }}22;color:{{ $color }};border:1px solid {{ $color }}44;
                                 padding:.25rem .75rem;border-radius:20px;font-size:.78rem;font-weight:700;text-transform:uppercase;">
                        {{ $plan->slug }}
                    </span>
                    <span style="font-size:1.1rem;font-weight:700;">{{ $plan->name }}</span>
                    @if(!$plan->is_active)
                        <span style="background:#ef444422;color:#ef4444;border:1px solid #ef444444;
                                     padding:.15rem .5rem;border-radius:10px;font-size:.72rem;">INACTIVO</span>
                    @endif
                </div>
                <div style="display:flex;gap:.5rem;align-items:center;">
                    <form method="POST" action="{{ route('admin.memberships.plans.toggle-promo', $plan->slug) }}" style="display:inline;">
                        @csrf
                        <button type="submit" style="padding:.35rem .85rem;border-radius:6px;border:1px solid {{ $plan->promo_active ? '#f59e0b' : 'var(--theme-border)' }};
                                background:{{ $plan->promo_active ? '#f59e0b22' : 'transparent' }};color:{{ $plan->promo_active ? '#f59e0b' : 'var(--theme-muted)' }};
                                font-size:.76rem;cursor:pointer;">
                            {{ $plan->promo_active ? '🏷️ Promo ON' : 'Promo OFF' }}
                        </button>
                    </form>
                    <button type="submit" style="padding:.35rem 1rem;border-radius:6px;background:#6C3FC5;color:#fff;border:none;font-size:.8rem;cursor:pointer;font-weight:600;">
                        Guardar cambios
                    </button>
                </div>
            </div>

            {{-- Precios --}}
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:1rem;margin-bottom:1.2rem;padding-bottom:1.2rem;border-bottom:1px solid var(--theme-border);">
                <div>
                    <label style="font-size:.72rem;color:var(--theme-muted);display:block;margin-bottom:.3rem;">Precio Normal (MXN)</label>
                    <input type="number" name="price_normal" step="0.01" min="0" value="{{ $plan->price_normal }}"
                           style="width:100%;padding:.4rem .7rem;border-radius:6px;border:1px solid var(--theme-border);background:var(--theme-bg);color:var(--theme-text);font-size:.9rem;">
                </div>
                <div>
                    <label style="font-size:.72rem;color:var(--theme-muted);display:block;margin-bottom:.3rem;">Precio Promo (MXN)</label>
                    <input type="number" name="price_promo" step="0.01" min="0" value="{{ $plan->price_promo }}"
                           style="width:100%;padding:.4rem .7rem;border-radius:6px;border:1px solid var(--theme-border);background:var(--theme-bg);color:var(--theme-text);font-size:.9rem;">
                </div>
                @if(!$isLifetime)
                <div>
                    <label style="font-size:.72rem;color:var(--theme-muted);display:block;margin-bottom:.3rem;">Duración (días)</label>
                    <input type="number" name="duration_days" min="1" value="{{ $plan->duration_days ?? 30 }}"
                           style="width:100%;padding:.4rem .7rem;border-radius:6px;border:1px solid var(--theme-border);background:var(--theme-bg);color:var(--theme-text);font-size:.9rem;">
                </div>
                @else
                <div style="display:flex;align-items:center;padding:.4rem .7rem;background:var(--theme-border);border-radius:6px;font-size:.8rem;color:var(--theme-muted);">
                    ♾️ Membresía fundador
                </div>
                @endif
                <div>
                    <label style="font-size:.72rem;color:var(--theme-muted);display:block;margin-bottom:.3rem;">Estado del plan</label>
                    <label style="display:flex;align-items:center;gap:.4rem;cursor:pointer;padding:.4rem 0;">
                        <input type="checkbox" name="is_active" value="1" {{ $plan->is_active ? 'checked' : '' }}>
                        <span style="font-size:.83rem;">Plan activo</span>
                    </label>
                </div>
            </div>

            {{-- Privilegios / Features --}}
            <div>
                <div style="font-size:.8rem;font-weight:700;color:var(--theme-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.75rem;">
                    Privilegios
                </div>
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:.75rem;">

                    {{-- Numéricos --}}
                    @foreach([
                        ['max_photos',              'Fotos máx.',           'number'],
                        ['max_videos',              'Videos máx.',          'number'],
                        ['max_messages_day',        'Msgs/día (general)',   'number'],
                        ['max_direct_messages_day', 'Msgs/día (directo)',   'number'],
                        ['grace_period_hours',      'Gracia (horas)',       'number'],
                    ] as [$key, $label, $type])
                    <div>
                        <label style="font-size:.72rem;color:var(--theme-muted);display:block;margin-bottom:.25rem;">{{ $label }}</label>
                        <input type="number" name="features[{{ $key }}]" min="0"
                               value="{{ $f[$key] ?? 0 }}"
                               style="width:100%;padding:.35rem .6rem;border-radius:5px;border:1px solid var(--theme-border);background:var(--theme-bg);color:var(--theme-text);font-size:.85rem;">
                    </div>
                    @endforeach

                    {{-- Booleanos --}}
                    @foreach([
                        ['can_view_private_photos',  'Ver fotos privadas'],
                        ['can_video_call',           'Videollamadas'],
                        ['can_see_visitors',         'Ver visitantes'],
                        ['can_send_friend_request',  'Enviar solicitudes'],
                        ['profile_boost',            'Boost en búsqueda'],
                        ['priority_support',         'Soporte prioritario'],
                    ] as [$key, $label])
                    <div style="display:flex;align-items:center;gap:.5rem;padding:.35rem .6rem;
                                border-radius:5px;border:1px solid var(--theme-border);">
                        <input type="hidden" name="features[{{ $key }}]" value="0">
                        <input type="checkbox" name="features[{{ $key }}]" value="1"
                               id="{{ $plan->slug }}_{{ $key }}"
                               {{ !empty($f[$key]) ? 'checked' : '' }}
                               style="width:16px;height:16px;cursor:pointer;">
                        <label for="{{ $plan->slug }}_{{ $key }}" style="font-size:.82rem;cursor:pointer;">
                            {{ $label }}
                        </label>
                    </div>
                    @endforeach

                </div>
            </div>

            {{-- Descripción --}}
            <div style="margin-top:1rem;">
                <label style="font-size:.72rem;color:var(--theme-muted);display:block;margin-bottom:.3rem;">Descripción pública</label>
                <textarea name="description" rows="2"
                          style="width:100%;padding:.4rem .7rem;border-radius:6px;border:1px solid var(--theme-border);background:var(--theme-bg);color:var(--theme-text);font-size:.83rem;resize:vertical;">{{ $plan->description }}</textarea>
            </div>

        </form>
    </div>
    @endforeach

</div>
@endsection
