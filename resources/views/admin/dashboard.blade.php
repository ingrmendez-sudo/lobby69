@extends('layouts.admin')

@section('title', 'Dashboard Admin')
@section('page-title', '📊 Dashboard')

@push('styles')
<style>
/* ── Stats grid ── */
.adm-stats { display:grid; grid-template-columns:repeat(auto-fill,minmax(180px,1fr)); gap:1rem; margin-bottom:1.5rem; }
.adm-stat  { background:var(--theme-card); border:1px solid var(--theme-border);
             border-radius:12px; padding:1.1rem 1.25rem; display:flex; flex-direction:column; gap:.3rem; }
.adm-stat__icon  { font-size:1.4rem; margin-bottom:.2rem; }
.adm-stat__value { font-size:1.6rem; font-weight:800; color:var(--theme-text); line-height:1; }
.adm-stat__label { font-size:.75rem; color:var(--theme-muted); font-weight:500; }
.adm-stat__sub   { font-size:.72rem; color:var(--theme-muted); margin-top:.2rem; }
.adm-stat.pink   { border-color:rgba(224,86,160,.3); }
.adm-stat.purple { border-color:rgba(108,63,197,.3); }
.adm-stat.green  { border-color:rgba(40,167,69,.3);  }
.adm-stat.yellow { border-color:rgba(240,192,64,.3); }

/* ── Alerts pendientes ── */
.adm-alerts { display:grid; grid-template-columns:repeat(auto-fill,minmax(220px,1fr)); gap:1rem; margin-bottom:1.5rem; }
.adm-alert  { background:var(--theme-card); border:1px solid var(--theme-border);
              border-radius:12px; padding:1rem 1.25rem;
              display:flex; align-items:center; justify-content:space-between;
              text-decoration:none; transition:.2s; }
.adm-alert:hover { transform:translateY(-2px); box-shadow:0 4px 16px rgba(0,0,0,.2); }
.adm-alert__info { display:flex; flex-direction:column; gap:.2rem; }
.adm-alert__label { font-size:.8rem; color:var(--theme-muted); font-weight:500; }
.adm-alert__count { font-size:1.4rem; font-weight:800; color:var(--theme-text); }
.adm-alert__count.urgent { color:#e056a0; }
.adm-alert__icon { font-size:1.6rem; opacity:.4; }

/* ── Dos columnas ── */
.adm-row  { display:grid; grid-template-columns:1fr 1fr; gap:1.2rem; margin-bottom:1.5rem; }
@media(max-width:900px) { .adm-row { grid-template-columns:1fr; } }

/* ── Panel ── */
.adm-panel { background:var(--theme-card); border:1px solid var(--theme-border); border-radius:12px; overflow:hidden; }
.adm-panel__head { padding:.9rem 1.2rem; border-bottom:1px solid var(--theme-border);
                   font-size:.9rem; font-weight:700; color:var(--theme-text);
                   display:flex; align-items:center; gap:.5rem; }
.adm-panel__body { padding:1rem 1.2rem; }

/* ── Tabla simple ── */
.adm-table { width:100%; border-collapse:collapse; font-size:.82rem; }
.adm-table th { text-align:left; padding:.5rem .6rem; color:var(--theme-muted);
                font-weight:600; font-size:.72rem; text-transform:uppercase; letter-spacing:.05em;
                border-bottom:1px solid var(--theme-border); }
.adm-table td { padding:.55rem .6rem; color:var(--theme-text); border-bottom:1px solid rgba(128,128,128,.08); vertical-align:middle; }
.adm-table tr:last-child td { border-bottom:none; }
.adm-table img { width:36px; height:36px; object-fit:cover; border-radius:6px; }

/* ── Membresías bar ── */
.mem-bar { display:flex; flex-direction:column; gap:.6rem; }
.mem-item { display:flex; align-items:center; gap:.75rem; font-size:.82rem; }
.mem-item__name  { width:90px; color:var(--theme-text); font-weight:600; }
.mem-item__track { flex:1; background:var(--theme-surface-2); border-radius:20px; height:8px; overflow:hidden; }
.mem-item__fill  { height:100%; border-radius:20px; background:linear-gradient(90deg,#6C3FC5,#e056a0); transition:.6s; }
.mem-item__count { width:35px; text-align:right; color:var(--theme-muted); font-size:.78rem; }

/* ── Pending list ── */
.pend-list { display:flex; flex-direction:column; gap:.5rem; }
.pend-item { display:flex; align-items:center; justify-content:space-between;
             padding:.5rem .75rem; background:var(--theme-surface-2);
             border-radius:8px; font-size:.82rem; }
.pend-item__type  { font-weight:600; color:var(--theme-text); }
.pend-item__time  { color:var(--theme-muted); font-size:.75rem; }
.pend-badge { padding:.2rem .6rem; border-radius:8px; font-size:.72rem; font-weight:700; }
.pend-badge.foto  { background:#2e1a4a; color:#b08df0; }
.pend-badge.video { background:#1a2e4a; color:#60b0f0; }
.pend-badge.verif { background:#1a3a22; color:#5cb85c; }
.pend-badge.invit { background:#3a2e00; color:#f0c040; }
</style>
@endpush

@section('content')

{{-- ── Alertas pendientes ── --}}
<div class="adm-alerts">
    <a href="{{ route('admin.photos.index', ['status'=>'pending']) }}" class="adm-alert">
        <div class="adm-alert__info">
            <span class="adm-alert__label">Fotos pendientes</span>
            <span class="adm-alert__count {{ $pendingPhotos > 0 ? 'urgent' : '' }}">{{ $pendingPhotos }}</span>
        </div>
        <span class="adm-alert__icon">📸</span>
    </a>
    <a href="{{ route('admin.videos.index', ['status'=>'pending']) }}" class="adm-alert">
        <div class="adm-alert__info">
            <span class="adm-alert__label">Videos pendientes</span>
            <span class="adm-alert__count {{ $pendingVideos > 0 ? 'urgent' : '' }}">{{ $pendingVideos }}</span>
        </div>
        <span class="adm-alert__icon">🎬</span>
    </a>
    <a href="{{ route('admin.verifications.index') }}" class="adm-alert">
        <div class="adm-alert__info">
            <span class="adm-alert__label">Verificaciones</span>
            <span class="adm-alert__count {{ $pendingVerifications > 0 ? 'urgent' : '' }}">{{ $pendingVerifications }}</span>
        </div>
        <span class="adm-alert__icon">🪪</span>
    </a>
    <a href="{{ route('admin.invitations.index') }}" class="adm-alert">
        <div class="adm-alert__info">
            <span class="adm-alert__label">Invitaciones</span>
            <span class="adm-alert__count {{ $pendingInvitations > 0 ? 'urgent' : '' }}">{{ $pendingInvitations }}</span>
        </div>
        <span class="adm-alert__icon">✉️</span>
    </a>
</div>

{{-- ── Stats generales ── --}}
<div class="adm-stats">
    <div class="adm-stat purple">
        <span class="adm-stat__icon">👥</span>
        <span class="adm-stat__value">{{ number_format($totalUsers) }}</span>
        <span class="adm-stat__label">Usuarios totales</span>
        <span class="adm-stat__sub">+{{ $newUsersWeek }} esta semana</span>
    </div>
    <div class="adm-stat green">
        <span class="adm-stat__icon">🟢</span>
        <span class="adm-stat__value">{{ $onlineNow }}</span>
        <span class="adm-stat__label">En línea ahora</span>
        <span class="adm-stat__sub">últimos 15 min</span>
    </div>
    <div class="adm-stat pink">
        <span class="adm-stat__icon">📸</span>
        <span class="adm-stat__value">{{ number_format($totalPhotos) }}</span>
        <span class="adm-stat__label">Fotos aprobadas</span>
    </div>
    <div class="adm-stat yellow">
        <span class="adm-stat__icon">🎬</span>
        <span class="adm-stat__value">{{ number_format($totalVideos) }}</span>
        <span class="adm-stat__label">Videos aprobados</span>
    </div>
    <div class="adm-stat purple">
        <span class="adm-stat__icon">💬</span>
        <span class="adm-stat__value">{{ number_format($totalComments) }}</span>
        <span class="adm-stat__label">Comentarios</span>
    </div>
    <div class="adm-stat pink">
        <span class="adm-stat__icon">❤️</span>
        <span class="adm-stat__value">{{ number_format($totalLikes) }}</span>
        <span class="adm-stat__label">Likes totales</span>
    </div>
    <div class="adm-stat green">
        <span class="adm-stat__icon">🆕</span>
        <span class="adm-stat__value">{{ $newUsersToday }}</span>
        <span class="adm-stat__label">Nuevos hoy</span>
    </div>
</div>

{{-- ── Fila 1: Membresías + Pendientes recientes ── --}}
<div class="adm-row">

    <div class="adm-panel">
        <div class="adm-panel__head">📊 Distribución de membresías</div>
        <div class="adm-panel__body">
            @php $maxMem = $memberships->max('total') ?: 1; @endphp
            <div class="mem-bar">
                @forelse($memberships as $m)
                    <div class="mem-item">
                        <span class="mem-item__name">{{ ucfirst($m->membership_type ?? 'sin tipo') }}</span>
                        <div class="mem-item__track">
                            <div class="mem-item__fill" style="width:{{ round($m->total / $maxMem * 100) }}%"></div>
                        </div>
                        <span class="mem-item__count">{{ $m->total }}</span>
                    </div>
                @empty
                    <p style="color:var(--theme-muted);font-size:.85rem;">Sin datos</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="adm-panel">
        <div class="adm-panel__head">⏳ Pendientes recientes</div>
        <div class="adm-panel__body">
            <div class="pend-list">
                @forelse($recentPending as $item)
                    <div class="pend-item">
                        <div style="display:flex;align-items:center;gap:.6rem;">
                            <span class="pend-badge {{ strtolower(substr($item->type,0,4)) }}">
                                {{ $item->type }}
                            </span>
                            <span class="pend-item__type">#{{ $item->id }}</span>
                        </div>
                        <span class="pend-item__time">
                            {{ \Carbon\Carbon::parse($item->created_at)->diffForHumans() }}
                        </span>
                    </div>
                @empty
                    <p style="color:var(--theme-muted);font-size:.85rem;text-align:center;padding:1rem 0;">
                        ✅ Todo al día
                    </p>
                @endforelse
            </div>
        </div>
    </div>

</div>

{{-- ── Fila 2: Top fotos + Top usuarios ── --}}
<div class="adm-row">

    <div class="adm-panel">
        <div class="adm-panel__head">🔥 Fotos más populares</div>
        <div class="adm-panel__body">
            <table class="adm-table">
                <thead>
                    <tr>
                        <th>Foto</th>
                        <th>Usuario</th>
                        <th>❤️ Likes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topPhotos as $p)
                        <tr>
                            <td><img loading="lazy" src="{{ route('admin.photos.serve', $p->id) }}" alt="foto"></td>
                            <td>{{ $p->nickname ?? $p->username }}</td>
                            <td><strong>{{ $p->likes_count }}</strong></td>
                        </tr>
                    @empty
                        <tr><td colspan="3" style="color:var(--theme-muted);text-align:center;">Sin datos</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="adm-panel">
        <div class="adm-panel__head">⭐ Usuarios más activos</div>
        <div class="adm-panel__body">
            <table class="adm-table">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Membresía</th>
                        <th>📸 Fotos</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topUsers as $u)
                        <tr>
                            <td>{{ $u->nickname ?? $u->display_name ?? $u->username }}</td>
                            <td>{{ ucfirst($u->membership_type ?? 'trial') }}</td>
                            <td><strong>{{ $u->photos_count }}</strong></td>
                        </tr>
                    @empty
                        <tr><td colspan="3" style="color:var(--theme-muted);text-align:center;">Sin datos</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- ── Fila 3: Actividad 7 días ── --}}
<div class="adm-row" style="margin-bottom:1.5rem;">

    <div class="adm-panel">
        <div class="adm-panel__head">
            <i class="fas fa-user-plus" style="color:var(--theme-accent);"></i> Registros últimos 7 días
        </div>
        <div class="adm-panel__body">
            <canvas id="chartDailyUsers" height="120"></canvas>
        </div>
    </div>

    <div class="adm-panel">
        <div class="adm-panel__head">
            <i class="fas fa-camera" style="color:#ec4899;"></i> Fotos subidas últimos 7 días
        </div>
        <div class="adm-panel__body">
            <canvas id="chartDailyPhotos" height="120"></canvas>
        </div>
    </div>

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const isDark     = () => document.getElementById('adminRoot')?.getAttribute('data-theme') !== 'light';
const gridColor  = () => isDark() ? 'rgba(255,255,255,.07)' : 'rgba(0,0,0,.07)';
const labelColor = () => isDark() ? '#a89bc2' : '#6b7280';

const userDays  = @json($dailyUsers->pluck('day'));
const userTots  = @json($dailyUsers->pluck('total')->map(fn($v) => (int)$v));
const photoDays = @json($dailyPhotos->pluck('day'));
const photoTots = @json($dailyPhotos->pluck('total')->map(fn($v) => (int)$v));

const baseOpts = {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
        x: { grid: { color: gridColor() }, ticks: { color: labelColor(), font: { size: 10 } } },
        y: { grid: { color: gridColor() }, ticks: { color: labelColor(), font: { size: 10 } }, beginAtZero: true }
    }
};

new Chart(document.getElementById('chartDailyUsers'), {
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
    options: baseOpts
});

new Chart(document.getElementById('chartDailyPhotos'), {
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
    options: baseOpts
});
</script>
@endpush

{{-- REFERIDOS --}}
<div style="margin-bottom:1.5rem;">
</div>

@endsection