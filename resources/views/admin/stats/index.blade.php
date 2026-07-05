@extends('layouts.admin')

@section('title', 'Estadísticas')
@section('page-title', 'Estadísticas del sitio')

@section('content')

{{-- Tarjetas totales --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:1rem;margin-bottom:1.5rem;">

    <div class="adm-card" style="padding:1.1rem;text-align:center;">
        <div style="font-size:2rem;margin-bottom:.3rem;">👥</div>
        <div style="font-size:1.6rem;font-weight:800;color:var(--theme-accent);">{{ number_format($totals['users']) }}</div>
        <div style="font-size:.72rem;color:var(--theme-muted);text-transform:uppercase;letter-spacing:.05em;">Usuarios</div>
    </div>

    <div class="adm-card" style="padding:1.1rem;text-align:center;">
        <div style="font-size:2rem;margin-bottom:.3rem;">📸</div>
        <div style="font-size:1.6rem;font-weight:800;color:#ec4899;">{{ number_format($totals['photos']) }}</div>
        <div style="font-size:.72rem;color:var(--theme-muted);text-transform:uppercase;letter-spacing:.05em;">Fotos aprobadas</div>
    </div>

    <div class="adm-card" style="padding:1.1rem;text-align:center;">
        <div style="font-size:2rem;margin-bottom:.3rem;">🎬</div>
        <div style="font-size:1.6rem;font-weight:800;color:#a855f7;">{{ number_format($totals['videos']) }}</div>
        <div style="font-size:.72rem;color:var(--theme-muted);text-transform:uppercase;letter-spacing:.05em;">Videos aprobados</div>
    </div>

    <div class="adm-card" style="padding:1.1rem;text-align:center;">
        <div style="font-size:2rem;margin-bottom:.3rem;">❤️</div>
        <div style="font-size:1.6rem;font-weight:800;color:#ef4444;">{{ number_format($totals['likes']) }}</div>
        <div style="font-size:.72rem;color:var(--theme-muted);text-transform:uppercase;letter-spacing:.05em;">Likes totales</div>
    </div>

    <div class="adm-card" style="padding:1.1rem;text-align:center;">
        <div style="font-size:2rem;margin-bottom:.3rem;">💬</div>
        <div style="font-size:1.6rem;font-weight:800;color:#06b6d4;">{{ number_format($totals['comments']) }}</div>
        <div style="font-size:.72rem;color:var(--theme-muted);text-transform:uppercase;letter-spacing:.05em;">Comentarios</div>
    </div>

    <div class="adm-card" style="padding:1.1rem;text-align:center;">
        <div style="font-size:2rem;margin-bottom:.3rem;">🔗</div>
        <div style="font-size:1.6rem;font-weight:800;color:#22c55e;">{{ number_format($totals['follows']) }}</div>
        <div style="font-size:.72rem;color:var(--theme-muted);text-transform:uppercase;letter-spacing:.05em;">Follows</div>
    </div>

</div>

{{-- Fila gráficas --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.5rem;">

    {{-- Usuarios por día --}}
    <div class="adm-card" style="padding:1.25rem;">
        <h3 style="font-size:.85rem;font-weight:700;color:var(--theme-text);margin-bottom:1rem;">
            <i class="fas fa-user-plus" style="color:var(--theme-accent);"></i>
            Registros últimos 30 días
        </h3>
        <canvas id="chartUsers" height="120"></canvas>
    </div>

    {{-- Fotos por día --}}
    <div class="adm-card" style="padding:1.25rem;">
        <h3 style="font-size:.85rem;font-weight:700;color:var(--theme-text);margin-bottom:1rem;">
            <i class="fas fa-camera" style="color:#ec4899;"></i>
            Fotos subidas últimos 30 días
        </h3>
        <canvas id="chartPhotos" height="120"></canvas>
    </div>

</div>

{{-- Fila inferior --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.5rem;">

    {{-- Membresías dona --}}
    <div class="adm-card" style="padding:1.25rem;">
        <h3 style="font-size:.85rem;font-weight:700;color:var(--theme-text);margin-bottom:1rem;">
            <i class="fas fa-crown" style="color:#f59e0b;"></i>
            Distribución de membresías
        </h3>
        <div style="display:flex;align-items:center;gap:1.5rem;">
            <div style="width:160px;height:160px;flex-shrink:0;">
                <canvas id="chartMemberships"></canvas>
            </div>
            <div style="flex:1;">
                @foreach($membershipStats as $m)
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.5rem;font-size:.82rem;">
                    <span style="color:var(--theme-muted);text-transform:capitalize;">
                        {{ $m->membership_type ?? 'Sin tipo' }}
                    </span>
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

    {{-- Top uploaders --}}
    <div class="adm-card" style="padding:1.25rem;">
        <h3 style="font-size:.85rem;font-weight:700;color:var(--theme-text);margin-bottom:1rem;">
            <i class="fas fa-trophy" style="color:#f59e0b;"></i>
            Top 10 usuarios más activos
        </h3>
        <div style="overflow-y:auto;max-height:220px;">
            @forelse($topUploaders as $i => $u)
            <div style="display:flex;align-items:center;gap:.75rem;padding:.4rem 0;border-bottom:1px solid var(--theme-border);">
                <span style="font-size:.75rem;font-weight:700;color:var(--theme-muted);width:18px;text-align:right;">
                    {{ $i + 1 }}
                </span>
                <div style="flex:1;">
                    <div style="font-size:.82rem;font-weight:600;color:var(--theme-text);">{{ $u->name }}</div>
                </div>
                <div style="font-size:.82rem;font-weight:700;color:var(--theme-accent);">
                    {{ $u->total }} <span style="font-size:.7rem;font-weight:400;color:var(--theme-muted);">fotos</span>
                </div>
                @if($i === 0)
                    <span style="font-size:.9rem;">🥇</span>
                @elseif($i === 1)
                    <span style="font-size:.9rem;">🥈</span>
                @elseif($i === 2)
                    <span style="font-size:.9rem;">🥉</span>
                @endif
            </div>
            @empty
            <div style="text-align:center;color:var(--theme-muted);padding:1rem;font-size:.82rem;">
                Sin datos disponibles
            </div>
            @endforelse
        </div>
    </div>

</div>

{{-- Barra conversión trial vs pagado --}}
<div class="adm-card" style="padding:1.25rem;margin-bottom:1.5rem;">
    <h3 style="font-size:.85rem;font-weight:700;color:var(--theme-text);margin-bottom:1rem;">
        <i class="fas fa-funnel-dollar" style="color:#22c55e;"></i>
        Conversión Trial → Membresía paga
    </h3>
    @php
        $total     = $totals['users'] ?: 1;
        $paidPct   = round(($paidCount  / $total) * 100, 1);
        $trialPct  = round(($trialCount / $total) * 100, 1);
    @endphp
    <div style="display:flex;gap:1rem;align-items:center;margin-bottom:.75rem;font-size:.83rem;">
        <div style="display:flex;align-items:center;gap:.4rem;">
            <div style="width:12px;height:12px;border-radius:3px;background:#22c55e;"></div>
            <span style="color:var(--theme-muted);">Pagados:</span>
            <strong style="color:var(--theme-text);">{{ $paidCount }} ({{ $paidPct }}%)</strong>
        </div>
        <div style="display:flex;align-items:center;gap:.4rem;">
            <div style="width:12px;height:12px;border-radius:3px;background:#f59e0b;"></div>
            <span style="color:var(--theme-muted);">Trial:</span>
            <strong style="color:var(--theme-text);">{{ $trialCount }} ({{ $trialPct }}%)</strong>
        </div>
    </div>
    <div style="height:22px;border-radius:20px;overflow:hidden;background:var(--theme-border);display:flex;">
        <div style="width:{{ $paidPct }}%;background:linear-gradient(90deg,#22c55e,#16a34a);transition:.5s;"></div>
        <div style="width:{{ $trialPct }}%;background:linear-gradient(90deg,#f59e0b,#d97706);transition:.5s;"></div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const isDark = () => document.getElementById('adminRoot')?.getAttribute('data-theme') !== 'light';
const gridColor  = () => isDark() ? 'rgba(255,255,255,.07)' : 'rgba(0,0,0,.07)';
const labelColor = () => isDark() ? '#a89bc2' : '#6b7280';

// Datos desde PHP
const userDays   = @json($usersByDay->pluck('day'));
const userTots   = @json($usersByDay->pluck('total')->map(fn($v) => (int)$v));
const photoDays  = @json($photosByDay->pluck('day'));
const photoTots  = @json($photosByDay->pluck('total')->map(fn($v) => (int)$v));
const memLabels  = @json($membershipStats->pluck('membership_type'));
const memTots    = @json($membershipStats->pluck('total')->map(fn($v) => (int)$v));

const chartDefaults = {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
        x: { grid: { color: gridColor() }, ticks: { color: labelColor(), font: { size: 10 } } },
        y: { grid: { color: gridColor() }, ticks: { color: labelColor(), font: { size: 10 } }, beginAtZero: true }
    }
};

// Gráfica usuarios
new Chart(document.getElementById('chartUsers'), {
    type: 'bar',
    data: {
        labels: userDays,
        datasets: [{
            data: userTots,
            backgroundColor: 'rgba(108,63,197,.6)',
            borderColor: '#6C3FC5',
            borderWidth: 1,
            borderRadius: 4,
        }]
    },
    options: chartDefaults
});

// Gráfica fotos
new Chart(document.getElementById('chartPhotos'), {
    type: 'line',
    data: {
        labels: photoDays,
        datasets: [{
            data: photoTots,
            borderColor: '#ec4899',
            backgroundColor: 'rgba(236,72,153,.15)',
            borderWidth: 2,
            pointRadius: 3,
            fill: true,
            tension: .35,
        }]
    },
    options: chartDefaults
});

// Gráfica dona membresías
new Chart(document.getElementById('chartMemberships'), {
    type: 'doughnut',
    data: {
        labels: memLabels,
        datasets: [{
            data: memTots,
            backgroundColor: ['#6C3FC5','#ec4899','#f59e0b','#22c55e','#06b6d4','#ef4444'],
            borderWidth: 2,
            borderColor: isDark() ? '#1a1028' : '#ffffff',
        }]
    },
    options: {
        responsive: true,
        cutout: '65%',
        plugins: {
            legend: { display: false }
        }
    }
});

// Actualizar colores al cambiar tema
const origToggle = window.toggleAdminTheme;
window.toggleAdminTheme = function() {
    origToggle();
    // Pequeño delay para que el DOM actualice data-theme
    setTimeout(() => Chart.instances && Object.values(Chart.instances).forEach(c => c.update()), 50);
};
</script>
@endpush
