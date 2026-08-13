@extends('layouts.app')
@section('title', '¿Cómo funcionan las estrellas? — LOBBY69')

@push('sidebar-left')
    @include('layouts.sidebar-left')
@endpush
@push('sidebar-right')
    @include('layouts.sidebar-right')
@endpush

@section('content')
<div style="max-width:680px;margin:0 auto;padding:1rem 0;">

    {{-- Header --}}
    <div style="text-align:center;margin-bottom:2rem;padding:2rem 1rem;
                background:var(--theme-surface-2);border-radius:16px;
                border:1px solid rgba(180,60,120,.15);">
        <div style="font-size:2.5rem;margin-bottom:.5rem;">⭐</div>
        <h1 style="font-size:1.4rem;font-weight:800;color:var(--theme-text);margin:0 0 .5rem;">
            ¿Cómo funcionan las estrellas?
        </h1>
        <p style="color:var(--theme-muted);font-size:.9rem;max-width:480px;margin:0 auto;line-height:1.6;">
            Tu nivel de recomendación se calcula automáticamente cada 24 horas
            en base a tu actividad real en la plataforma.
        </p>
    </div>


    {{-- ── Breakdown personal ── --}}
    @auth
    @php
        $bUserId = auth()->id();
        $bHistory = \Illuminate\Support\Facades\DB::table('profile_score_history')
            ->whereRaw('user_id::text = ?', [(string)$bUserId])
            ->orderByDesc('calculated_at')
            ->first();
        $bProfile = \Illuminate\Support\Facades\DB::table('profiles')
            ->whereRaw('user_id::text = ?', [(string)$bUserId])
            ->first();
        $bScore   = floatval($bProfile->recommendation_score ?? 0);
        $bFull    = (int) floor($bScore);
        $bHalf    = ($bScore - $bFull) >= 0.4 ? 1 : 0;
        $bEmpty   = max(0, 5 - $bFull - $bHalf);
        $bFactors = $bHistory ? [
            ['icon'=>'📸','label'=>'Fotos aprobadas',      'pts'=> (float)$bHistory->factor_photos,     'max'=>1.50],
            ['icon'=>'👁️','label'=>'Visitas al perfil',    'pts'=> (float)$bHistory->factor_visits,     'max'=>1.25],
            ['icon'=>'⚡','label'=>'Actividad reciente',   'pts'=> (float)$bHistory->factor_activity,   'max'=>1.00],
            ['icon'=>'💬','label'=>'Mensajes enviados',    'pts'=> (float)$bHistory->factor_responses,  'max'=>0.75],
            ['icon'=>'✅','label'=>'Perfil completo',      'pts'=> (float)$bHistory->factor_completeness,'max'=>0.50],
            ['icon'=>'🔗','label'=>'Invitaciones exitosas','pts'=> 0.00,                                 'max'=>0.50],
        ] : [];
    @endphp
    @if($bHistory)
    <div style="background:var(--theme-card);border:1px solid rgba(108,63,197,.3);
                border-radius:14px;padding:1.5rem;margin-bottom:1.25rem;
                border-top:3px solid #6C3FC5;">
        <h2 style="font-size:1rem;font-weight:700;color:var(--theme-text);margin:0 0 .5rem;">
            🎯 Tu desglose personal
        </h2>
        <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1.25rem;">
            <div style="font-size:1.5rem;color:#f59e0b;">
                @for($i=0;$i<$bFull;$i++)<i class="fa fa-star"></i>@endfor
                @if($bHalf)<i class="fa fa-star-half-o"></i>@endif
                @for($i=0;$i<$bEmpty;$i++)<i class="fa fa-star-o" style="opacity:.3;"></i>@endfor
            </div>
            <span style="font-size:1.6rem;font-weight:800;color:#f59e0b;">{{ number_format($bScore,2) }}</span>
            <span style="font-size:.8rem;color:var(--theme-muted);">/ 5.00</span>
        </div>
        <div style="display:flex;flex-direction:column;gap:.65rem;">
            @foreach($bFactors as $bf)
            @php $pct = $bf['max'] > 0 ? min(100, round(($bf['pts']/$bf['max'])*100)) : 0; @endphp
            <div>
                <div style="display:flex;justify-content:space-between;margin-bottom:.25rem;">
                    <span style="font-size:.82rem;color:var(--theme-text);">{{ $bf['icon'] }} {{ $bf['label'] }}</span>
                    <span style="font-size:.78rem;font-weight:700;color:{{ $bf['pts'] >= $bf['max'] ? '#22c55e' : '#f59e0b' }};">
                        {{ number_format($bf['pts'],2) }} / {{ number_format($bf['max'],2) }}
                    </span>
                </div>
                <div style="background:rgba(255,255,255,.06);border-radius:99px;height:6px;overflow:hidden;">
                    <div style="height:100%;width:{{ $pct }}%;border-radius:99px;
                                background:{{ $pct >= 100 ? 'linear-gradient(90deg,#22c55e,#16a34a)' : 'linear-gradient(90deg,#6C3FC5,#e056a0)' }};
                                transition:width .4s ease;"></div>
                </div>
            </div>
            @endforeach
        </div>
        <p style="font-size:.72rem;color:var(--theme-muted);margin-top:1rem;text-align:right;">
            Último cálculo: {{ \Carbon\Carbon::parse($bHistory->calculated_at)->diffForHumans() }}
        </p>
    </div>
    @endif
    @endauth
    {{-- Factores --}}
    <div style="background:var(--theme-card);border:1px solid var(--theme-border);
                border-radius:14px;padding:1.5rem;margin-bottom:1.25rem;">
        <h2 style="font-size:1rem;font-weight:700;color:var(--theme-text);margin:0 0 1.25rem;">
            📊 Los 6 factores que determinan tu score
        </h2>

        @php
        $factors = [
            ['icon'=>'📸', 'label'=>'Fotos aprobadas',        'pts'=>'hasta 1.5 ⭐', 'tip'=>'Sube fotos de calidad. Con 10 fotos aprobadas alcanzas el máximo.'],
            ['icon'=>'👁️', 'label'=>'Visitas a tu perfil',    'pts'=>'hasta 1.25 ⭐', 'tip'=>'Cuantos más usuarios visiten tu perfil en los últimos 30 días, mayor será tu score.'],
            ['icon'=>'⚡', 'label'=>'Actividad reciente',      'pts'=>'hasta 1.0 ⭐', 'tip'=>'Conéctate seguido. Si tu último acceso fue hace menos de 7 días obtienes el máximo.'],
            ['icon'=>'💬', 'label'=>'Mensajes enviados',       'pts'=>'hasta 0.75 ⭐', 'tip'=>'Responde mensajes y conversa. Enviar 10+ mensajes al mes te da el máximo.'],
            ['icon'=>'✅', 'label'=>'Perfil completo',         'pts'=>'hasta 0.5 ⭐', 'tip'=>'Llena tu bio, ciudad, intereses, lo que buscas, foto de perfil y tipo de perfil.'],
            ['icon'=>'🔗', 'label'=>'Invitaciones exitosas',   'pts'=>'hasta 0.5 ⭐', 'tip'=>'Si invitaste a 3 o más personas que se registraron con tu código, obtienes el máximo.'],
        ];
        @endphp

        <div style="display:flex;flex-direction:column;gap:1rem;">
            @foreach($factors as $f)
            <div style="display:flex;align-items:flex-start;gap:1rem;
                        padding:.9rem;background:var(--theme-bg);
                        border-radius:10px;border:1px solid var(--theme-border);">
                <div style="font-size:1.4rem;flex-shrink:0;width:2rem;text-align:center;">
                    {{ $f['icon'] }}
                </div>
                <div style="flex:1;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.25rem;">
                        <span style="font-weight:700;font-size:.9rem;color:var(--theme-text);">{{ $f['label'] }}</span>
                        <span style="font-size:.78rem;font-weight:700;color:#f59e0b;
                                     background:rgba(245,158,11,.12);padding:.15rem .5rem;
                                     border-radius:20px;">{{ $f['pts'] }}</span>
                    </div>
                    <div style="font-size:.82rem;color:var(--theme-muted);line-height:1.5;">
                        {{ $f['tip'] }}
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Niveles --}}
    <div style="background:var(--theme-card);border:1px solid var(--theme-border);
                border-radius:14px;padding:1.5rem;margin-bottom:1.25rem;">
        <h2 style="font-size:1rem;font-weight:700;color:var(--theme-text);margin:0 0 1.25rem;">
            🏆 Niveles de recomendación
        </h2>
        @php
        $levels = [
            ['stars'=>5, 'half'=>false, 'label'=>'Perfil élite',      'range'=>'4.5 – 5.0', 'desc'=>'Eres uno de los perfiles más activos y completos de la plataforma.', 'color'=>'#f59e0b'],
            ['stars'=>4, 'half'=>true,  'label'=>'Perfil muy activo', 'range'=>'4.0 – 4.4', 'desc'=>'Gran actividad. Sigues creciendo en visibilidad.', 'color'=>'#f59e0b'],
            ['stars'=>3, 'half'=>false, 'label'=>'Perfil activo',     'range'=>'3.0 – 3.9', 'desc'=>'Buen nivel. Completa tu perfil y sube más fotos para avanzar.', 'color'=>'#6C3FC5'],
            ['stars'=>2, 'half'=>false, 'label'=>'En crecimiento',    'range'=>'1.5 – 2.9', 'desc'=>'Vas bien. Conéctate más seguido y responde mensajes.', 'color'=>'#3b82f6'],
            ['stars'=>1, 'half'=>false, 'label'=>'Inicio',            'range'=>'0.0 – 1.4', 'desc'=>'Completa tu perfil y sube tu primera foto para empezar a crecer.', 'color'=>'var(--theme-muted)'],
        ];
        @endphp
        <div style="display:flex;flex-direction:column;gap:.75rem;">
            @foreach($levels as $lv)
            <div style="display:flex;align-items:center;gap:1rem;padding:.75rem;
                        background:var(--theme-bg);border-radius:8px;
                        border:1px solid var(--theme-border);">
                <div style="display:flex;gap:.1rem;flex-shrink:0;">
                    @for($i=0;$i<$lv['stars'];$i++)<i class="fas fa-star" style="color:{{ $lv['color'] }};font-size:.85rem;"></i>@endfor
                    @if($lv['half'])<i class="fas fa-star-half-alt" style="color:{{ $lv['color'] }};font-size:.85rem;"></i>@endif
                </div>
                <div style="flex:1;">
                    <div style="font-weight:700;font-size:.85rem;color:var(--theme-text);">
                        {{ $lv['label'] }}
                        <span style="font-weight:400;color:var(--theme-muted);font-size:.78rem;">({{ $lv['range'] }})</span>
                    </div>
                    <div style="font-size:.78rem;color:var(--theme-muted);">{{ $lv['desc'] }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- CTA --}}
    <div style="text-align:center;padding:1.5rem;background:linear-gradient(135deg,rgba(108,63,197,.15),rgba(224,86,160,.15));
                border-radius:14px;border:1px solid rgba(180,60,120,.2);">
        <div style="font-size:1.1rem;font-weight:700;color:var(--theme-text);margin-bottom:.5rem;">
            ¿Listo para subir tu score?
        </div>
        <div style="font-size:.85rem;color:var(--theme-muted);margin-bottom:1rem;">
            Los scores se actualizan cada noche a las 3:00 AM.
        </div>
        <a href="{{ route('profile.edit') }}"
           style="display:inline-block;background:linear-gradient(135deg,#6C3FC5,#e056a0);
                  color:#fff;padding:.6rem 1.5rem;border-radius:10px;
                  text-decoration:none;font-weight:700;font-size:.88rem;">
            ✏️ Editar mi perfil
        </a>
    </div>

</div>
@endsection