@extends('layouts.admin')

@section('title', 'Estadísticas')
@section('page-title', 'Analíticas y Marketing')

@section('content')

{{-- ── Totales ── --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:.75rem;margin-bottom:1.5rem;">
    <div class="adm-card" style="padding:1rem;text-align:center;">
        <div style="font-size:1.5rem;font-weight:800;color:var(--theme-accent);">{{ number_format($totals['users']) }}</div>
        <div style="font-size:.72rem;color:var(--theme-muted);text-transform:uppercase;">Usuarios</div>
    </div>
    <div class="adm-card" style="padding:1rem;text-align:center;">
        <div style="font-size:1.5rem;font-weight:800;color:#06b6d4;">{{ number_format($totals['profile_views']) }}</div>
        <div style="font-size:.72rem;color:var(--theme-muted);text-transform:uppercase;">Visitas totales</div>
    </div>
    <div class="adm-card" style="padding:1rem;text-align:center;">
        <div style="font-size:1.5rem;font-weight:800;color:#22c55e;">{{ $activeUsers7d }}</div>
        <div style="font-size:.72rem;color:var(--theme-muted);text-transform:uppercase;">Activos 7d</div>
    </div>
    <div class="adm-card" style="padding:1rem;text-align:center;">
        <div style="font-size:1.5rem;font-weight:800;color:#a855f7;">{{ $activeUsers30d }}</div>
        <div style="font-size:.72rem;color:var(--theme-muted);text-transform:uppercase;">Activos 30d</div>
    </div>
    <div class="adm-card" style="padding:1rem;text-align:center;">
        <div style="font-size:1.5rem;font-weight:800;color:#ec4899;">{{ number_format($totals['photos']) }}</div>
        <div style="font-size:.72rem;color:var(--theme-muted);text-transform:uppercase;">Fotos</div>
    </div>
    <div class="adm-card" style="padding:1rem;text-align:center;">
        <div style="font-size:1.5rem;font-weight:800;color:#ef4444;">{{ number_format($totals['likes']) }}</div>
        <div style="font-size:.72rem;color:var(--theme-muted);text-transform:uppercase;">Likes</div>
    </div>
    <div class="adm-card" style="padding:1rem;text-align:center;">
        <div style="font-size:1.5rem;font-weight:800;color:#f59e0b;">{{ number_format($totals['follows']) }}</div>
        <div style="font-size:.72rem;color:var(--theme-muted);text-transform:uppercase;">Follows</div>
    </div>
</div>

{{-- ── Visitas comparativas ── --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.5rem;">
    <div class="adm-card" style="padding:1.25rem;display:flex;align-items:center;gap:1rem;">
        <div style="font-size:2rem;opacity:.6;">📅</div>
        <div>
            <div style="font-size:.75rem;color:var(--theme-muted);margin-bottom:.2rem;">Visitas esta semana</div>
            <div style="font-size:1.6rem;font-weight:800;color:var(--theme-text);">{{ number_format($viewsThisWeek) }}</div>
            @php $weekDiff = $viewsLastWeek > 0 ? round((($viewsThisWeek - $viewsLastWeek) / $viewsLastWeek) * 100, 1) : 0; @endphp
            <div style="font-size:.78rem;color:{{ $weekDiff >= 0 ? '#22c55e' : '#ef4444' }};">
                <i class="fas fa-{{ $weekDiff >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                {{ abs($weekDiff) }}% vs semana anterior ({{ number_format($viewsLastWeek) }})
            </div>
        </div>
    </div>
    <div class="adm-card" style="padding:1.25rem;display:flex;align-items:center;gap:1rem;">
        <div style="font-size:2rem;opacity:.6;">🗓️</div>
        <div>
            <div style="font-size:.75rem;color:var(--theme-muted);margin-bottom:.2rem;">Visitas este mes</div>
            <div style="font-size:1.6rem;font-weight:800;color:var(--theme-text);">{{ number_format($viewsThisMonth) }}</div>
            @php $monthDiff = $viewsLastMonth > 0 ? round((($viewsThisMonth - $viewsLastMonth) / $viewsLastMonth) * 100, 1) : 0; @endphp
            <div style="font-size:.78rem;color:{{ $monthDiff >= 0 ? '#22c55e' : '#ef4444' }};">
                <i class="fas fa-{{ $monthDiff >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                {{ abs($monthDiff) }}% vs mes anterior ({{ number_format($viewsLastMonth) }})
            </div>
        </div>
    </div>
</div>

{{-- ── Gráficas visitas ── --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.5rem;">
    <div class="adm-card" style="padding:1.25rem;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
            <h3 style="font-size:.85rem;font-weight:700;color:var(--theme-text);margin:0;">
                <i class="fas fa-eye" style="color:#06b6d4;"></i> Visitas por día (30d)
            </h3>
        </div>
        <canvas id="chartViewsDay" height="120"></canvas>
    </div>
    <div class="adm-card" style="padding:1.25rem;">
        <h3 style="font-size:.85rem;font-weight:700;color:var(--theme-text);margin:0 0 1rem;">
            <i class="fas fa-calendar-week" style="color:#a855f7;"></i> Visitas por semana (12s)
        </h3>
        <canvas id="chartViewsWeek" height="120"></canvas>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.5rem;">
    <div class="adm-card" style="padding:1.25rem;">
        <h3 style="font-size:.85rem;font-weight:700;color:var(--theme-text);margin:0 0 1rem;">
            <i class="fas fa-calendar-alt" style="color:#f59e0b;"></i> Visitas por mes (12m)
        </h3>
        <canvas id="chartViewsMonth" height="120"></canvas>
    </div>
    <div class="adm-card" style="padding:1.25rem;">
        <h3 style="font-size:.85rem;font-weight:700;color:var(--theme-text);margin:0 0 1rem;">
            <i class="fas fa-clock" style="color:#ec4899;"></i> Actividad por hora del día
        </h3>
        <canvas id="chartHour" height="120"></canvas>
    </div>
</div>

{{-- ── Registros y fotos ── --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.5rem;">
    <div class="adm-card" style="padding:1.25rem;">
        <h3 style="font-size:.85rem;font-weight:700;color:var(--theme-text);margin:0 0 1rem;">
            <i class="fas fa-user-plus" style="color:var(--theme-accent);"></i> Registros por día (30d)
        </h3>
        <canvas id="chartUsers" height="120"></canvas>
    </div>
    <div class="adm-card" style="padding:1.25rem;">
        <h3 style="font-size:.85rem;font-weight:700;color:var(--theme-text);margin:0 0 1rem;">
            <i class="fas fa-camera" style="color:#ec4899;"></i> Fotos subidas (30d)
        </h3>
        <canvas id="chartPhotos" height="120"></canvas>
    </div>
</div>

{{-- ── Funnel de conversión ── --}}
<div class="adm-card" style="padding:1.25rem;margin-bottom:1.5rem;">
    <h3 style="font-size:.85rem;font-weight:700;color:var(--theme-text);margin:0 0 1.25rem;">
        <i class="fas fa-filter" style="color:#22c55e;"></i> Funnel de conversión
    </h3>
    @php $maxFunnel = collect($funnel)->max('value') ?: 1; @endphp
    <div style="display:flex;flex-direction:column;gap:.75rem;">
        @foreach($funnel as $step)
        @php
            $pct     = round(($step['value'] / $maxFunnel) * 100);
            $convPct = $totalRegistered > 0 ? round(($step['value'] / $totalRegistered) * 100, 1) : 0;
        @endphp
        <div style="display:flex;align-items:center;gap:1rem;">
            <div style="width:130px;font-size:.8rem;color:var(--theme-muted);text-align:right;flex-shrink:0;">
                {{ $step['label'] }}
            </div>
            <div style="flex:1;height:28px;background:var(--theme-border);border-radius:6px;overflow:hidden;">
                <div style="width:{{ $pct }}%;height:100%;background:{{ $step['color'] }};border-radius:6px;display:flex;align-items:center;padding-left:.5rem;transition:.5s;">
                    <span style="font-size:.72rem;color:#fff;font-weight:700;white-space:nowrap;">
                        {{ number_format($step['value']) }}
                    </span>
                </div>
            </div>
            <div style="width:45px;text-align:right;font-size:.78rem;font-weight:700;color:{{ $step['color'] }};">
                {{ $convPct }}%
            </div>
        </div>
        @endforeach
    </div>
    <div style="margin-top:1rem;padding-top:1rem;border-top:1px solid var(--theme-border);font-size:.78rem;color:var(--theme-muted);">
        💡 <strong style="color:var(--theme-text);">Tasa de conversión a pago:</strong>
        {{ $totalRegistered > 0 ? round(($paidCount / $totalRegistered) * 100, 1) : 0 }}%
        de los registros se convierten a membresía paga.
    </div>
</div>

{{-- ── Visitas vs Membresías ── --}}
<div class="adm-card" style="padding:1.25rem;margin-bottom:1.5rem;">
    <h3 style="font-size:.85rem;font-weight:700;color:var(--theme-text);margin:0 0 1rem;">
        <i class="fas fa-chart-line" style="color:#22c55e;"></i>
        Conversiones a membresía paga por mes (6m)
    </h3>
    <canvas id="chartMembershipMonth" height="80"></canvas>
    <div style="margin-top:.75rem;font-size:.75rem;color:var(--theme-muted);">
        💡 Correlaciona este gráfico con tus publicaciones en X para medir impacto de campañas.
    </div>
</div>

{{-- ── Usuarios por estado + Distribución membresías ── --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.5rem;">

    {{-- Por estado --}}
    <div class="adm-card" style="padding:1.25rem;">
        <h3 style="font-size:.85rem;font-weight:700;color:var(--theme-text);margin:0 0 1rem;">
            <i class="fas fa-map-marker-alt" style="color:#ef4444;"></i> Usuarios por estado
        </h3>
        @php $maxState = $usersByState->max('total') ?: 1; @endphp
        <div style="display:flex;flex-direction:column;gap:.5rem;max-height:280px;overflow-y:auto;">
            @foreach($usersByState as $s)
            @php $statePct = round(($s->total / $maxState) * 100); @endphp
            <div style="display:flex;align-items:center;gap:.6rem;font-size:.8rem;">
                <div style="width:110px;color:var(--theme-muted);text-align:right;flex-shrink:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"
                     title="{{ $s->estado }}">
                    {{ $s->estado }}
                </div>
                <div style="flex:1;height:18px;background:var(--theme-border);border-radius:4px;overflow:hidden;">
                    <div style="width:{{ $statePct }}%;height:100%;background:linear-gradient(90deg,#6C3FC5,#ec4899);border-radius:4px;"></div>
                </div>
                <div style="width:28px;text-align:right;font-weight:700;color:var(--theme-text);">
                    {{ $s->total }}
                </div>
            </div>
            @endforeach
        </div>
        <div style="margin-top:.75rem;font-size:.72rem;color:var(--theme-muted);padding-top:.75rem;border-top:1px solid var(--theme-border);">
            💡 Los estados con más usuarios son los mercados prioritarios para campañas en X.
        </div>
    </div>

    {{-- Membresías dona --}}
    <div class="adm-card" style="padding:1.25rem;">
        <h3 style="font-size:.85rem;font-weight:700;color:var(--theme-text);margin:0 0 1rem;">
            <i class="fas fa-crown" style="color:#f59e0b;"></i> Distribución de membresías
        </h3>
        <div style="display:flex;align-items:center;gap:1.5rem;">
            <div style="width:150px;height:150px;flex-shrink:0;">
                <canvas id="chartMemberships"></canvas>
            </div>
            <div style="flex:1;">
                @foreach($membershipStats as $m)
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.45rem;font-size:.82rem;">
                    <span style="color:var(--theme-muted);text-transform:capitalize;">{{ $m->membership_type ?? 'sin tipo' }}</span>
                    <span style="font-weight:700;color:var(--theme-text);">{{ $m->total }}</span>
                </div>
                @endforeach
                <div style="border-top:1px solid var(--theme-border);padding-top:.5rem;margin-top:.5rem;font-size:.82rem;display:flex;justify-content:space-between;">
                    <span style="color:var(--theme-muted);">Conversión a pago</span>
                    <span style="font-weight:700;color:#22c55e;">
                        {{ $totals['users'] > 0 ? round(($paidCount / $totals['users']) * 100, 1) : 0 }}%
                    </span>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ── Retención ── --}}
<div class="adm-card" style="padding:1.25rem;margin-bottom:1.5rem;">
    <h3 style="font-size:.85rem;font-weight:700;color:var(--theme-text);margin:0 0 1rem;">
        <i class="fas fa-redo" style="color:#06b6d4;"></i> Retención de usuarios
    </h3>
    @php
        $ret7  = $totals['users'] > 0 ? round(($activeUsers7d  / $totals['users']) * 100, 1) : 0;
        $ret30 = $totals['users'] > 0 ? round(($activeUsers30d / $totals['users']) * 100, 1) : 0;
    @endphp
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
        <div>
            <div style="font-size:.75rem;color:var(--theme-muted);margin-bottom:.4rem;">Retención 7 días</div>
            <div style="height:24px;background:var(--theme-border);border-radius:20px;overflow:hidden;">
                <div style="width:{{ $ret7 }}%;height:100%;background:linear-gradient(90deg,#06b6d4,#22c55e);border-radius:20px;display:flex;align-items:center;padding-left:.6rem;">
                    <span style="font-size:.72rem;color:#fff;font-weight:700;">{{ $ret7 }}%</span>
                </div>
            </div>
            <div style="font-size:.72rem;color:var(--theme-muted);margin-top:.3rem;">
                {{ $activeUsers7d }} de {{ $totals['users'] }} usuarios activos
            </div>
        </div>
        <div>
            <div style="font-size:.75rem;color:var(--theme-muted);margin-bottom:.4rem;">Retención 30 días</div>
            <div style="height:24px;background:var(--theme-border);border-radius:20px;overflow:hidden;">
                <div style="width:{{ $ret30 }}%;height:100%;background:linear-gradient(90deg,#a855f7,#ec4899);border-radius:20px;display:flex;align-items:center;padding-left:.6rem;">
                    <span style="font-size:.72rem;color:#fff;font-weight:700;">{{ $ret30 }}%</span>
                </div>
            </div>
            <div style="font-size:.72rem;color:var(--theme-muted);margin-top:.3rem;">
                {{ $activeUsers30d }} de {{ $totals['users'] }} usuarios activos
            </div>
        </div>
    </div>
    <div style="margin-top:.75rem;font-size:.72rem;color:var(--theme-muted);padding-top:.75rem;border-top:1px solid var(--theme-border);">
        💡 Una retención a 30 días superior al 40% es señal de comunidad saludable.
        Bajo ese umbral, prioriza contenido de reengagement en X.
    </div>
</div>

{{-- ── Top uploaders ── --}}
<div class="adm-card" style="padding:1.25rem;margin-bottom:1.5rem;">
    <h3 style="font-size:.85rem;font-weight:700;color:var(--theme-text);margin:0 0 1rem;">
        <i class="fas fa-trophy" style="color:#f59e0b;"></i> Top 10 usuarios más activos
    </h3>
    <div style="display:flex;flex-direction:column;gap:.4rem;">
        @forelse($topUploaders as $i => $u)
        <div style="display:flex;align-items:center;gap:.75rem;padding:.35rem 0;border-bottom:1px solid var(--theme-border);font-size:.82rem;">
            <span style="width:20px;text-align:center;font-weight:700;color:var(--theme-muted);">
                @if($i===0) 🥇 @elseif($i===1) 🥈 @elseif($i===2) 🥉 @else {{ $i+1 }} @endif
            </span>
            <span style="flex:1;font-weight:600;color:var(--theme-text);">{{ $u->name }}</span>
            <span style="font-weight:700;color:var(--theme-accent);">
                {{ $u->total }} <span style="font-size:.7rem;font-weight:400;color:var(--theme-muted);">fotos</span>
            </span>
        </div>
        @empty
        <div style="text-align:center;color:var(--theme-muted);padding:1rem;font-size:.82rem;">Sin datos</div>
        @endforelse
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const isDark     = () => document.getElementById('adminRoot')?.getAttribute('data-theme') !== 'light';
const gridColor  = () => isDark() ? 'rgba(255,255,255,.07)' : 'rgba(0,0,0,.07)';
const labelColor = () => isDark() ? '#a89bc2' : '#6b7280';

const lineOpts = (color, fill = false) => ({
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
        x: { grid: { color: gridColor() }, ticks: { color: labelColor(), font: { size: 9 } } },
        y: { grid: { color: gridColor() }, ticks: { color: labelColor(), font: { size: 9 } }, beginAtZero: true }
    }
});

// Visitas por día
new Chart(document.getElementById('chartViewsDay'), {
    type: 'line',
    data: {
        labels: @json($viewsByDay->pluck('day')),
        datasets: [{ data: @json($viewsByDay->pluck('total')->map(fn($v)=>(int)$v)),
            borderColor:'#06b6d4', backgroundColor:'rgba(6,182,212,.12)',
            borderWidth:2, pointRadius:2, fill:true, tension:.35 }]
    }, options: lineOpts('#06b6d4', true)
});

// Visitas por semana
new Chart(document.getElementById('chartViewsWeek'), {
    type: 'bar',
    data: {
        labels: @json($viewsByWeek->pluck('week')),
        datasets: [{ data: @json($viewsByWeek->pluck('total')->map(fn($v)=>(int)$v)),
            backgroundColor:'rgba(168,85,247,.6)', borderColor:'#a855f7',
            borderWidth:1, borderRadius:4 }]
    }, options: lineOpts('#a855f7')
});

// Visitas por mes
new Chart(document.getElementById('chartViewsMonth'), {
    type: 'bar',
    data: {
        labels: @json($viewsByMonth->pluck('month')),
        datasets: [{ data: @json($viewsByMonth->pluck('total')->map(fn($v)=>(int)$v)),
            backgroundColor:'rgba(245,158,11,.6)', borderColor:'#f59e0b',
            borderWidth:1, borderRadius:4 }]
    }, options: lineOpts('#f59e0b')
});

// Actividad por hora
const hours = Array.from({length:24}, (_,i) => i + 'h');
const hourData = Array(24).fill(0);
@foreach($activityByHour as $h)
hourData[{{ (int)$h->hour }}] = {{ (int)$h->total }};
@endforeach
new Chart(document.getElementById('chartHour'), {
    type: 'bar',
    data: {
        labels: hours,
        datasets: [{ data: hourData,
            backgroundColor: hourData.map(v => {
                const max = Math.max(...hourData);
                const pct = max > 0 ? v / max : 0;
                return `rgba(224,86,160,${0.2 + pct * 0.8})`;
            }),
            borderWidth: 0, borderRadius: 3 }]
    }, options: lineOpts('#ec4899')
});

// Registros por día
new Chart(document.getElementById('chartUsers'), {
    type: 'bar',
    data: {
        labels: @json($usersByDay->pluck('day')),
        datasets: [{ data: @json($usersByDay->pluck('total')->map(fn($v)=>(int)$v)),
            backgroundColor:'rgba(108,63,197,.6)', borderColor:'#6C3FC5',
            borderWidth:1, borderRadius:4 }]
    }, options: lineOpts('#6C3FC5')
});

// Fotos por día
new Chart(document.getElementById('chartPhotos'), {
    type: 'line',
    data: {
        labels: @json($photosByDay->pluck('day')),
        datasets: [{ data: @json($photosByDay->pluck('total')->map(fn($v)=>(int)$v)),
            borderColor:'#ec4899', backgroundColor:'rgba(236,72,153,.15)',
            borderWidth:2, pointRadius:3, fill:true, tension:.35 }]
    }, options: lineOpts('#ec4899')
});

// Membresías por mes
new Chart(document.getElementById('chartMembershipMonth'), {
    type: 'line',
    data: {
        labels: @json($membershipsByMonth->pluck('month')),
        datasets: [{ data: @json($membershipsByMonth->pluck('total')->map(fn($v)=>(int)$v)),
            borderColor:'#22c55e', backgroundColor:'rgba(34,197,94,.15)',
            borderWidth:2, pointRadius:4, fill:true, tension:.3 }]
    }, options: lineOpts('#22c55e')
});

// Dona membresías
new Chart(document.getElementById('chartMemberships'), {
    type: 'doughnut',
    data: {
        labels: @json($membershipStats->pluck('membership_type')),
        datasets: [{ data: @json($membershipStats->pluck('total')->map(fn($v)=>(int)$v)),
            backgroundColor:['#6C3FC5','#ec4899','#f59e0b','#22c55e','#06b6d4','#ef4444'],
            borderWidth:2, borderColor: isDark() ? '#1a1028' : '#ffffff' }]
    },
    options: { responsive:true, cutout:'65%', plugins:{ legend:{ display:false } } }
});
</script>
@endpush
