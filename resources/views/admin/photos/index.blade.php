@extends('layouts.admin')

@push('styles')
<style>
/* ── Panel Admin Fotos ── */
.adm-wrap        { max-width: 1400px; margin: 0 auto; padding: 1.5rem 1rem; }
.adm-header      { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1rem; margin-bottom:1.5rem; }
.adm-title       { font-size:1.4rem; font-weight:700; color:var(--theme-text); margin:0; }

/* Tabs de estado */
.adm-tabs        { display:flex; gap:.5rem; flex-wrap:wrap; }
.adm-tab         { padding:.45rem 1.1rem; border-radius:20px; font-size:.82rem; font-weight:600;
                   text-decoration:none; border:2px solid transparent;
                   color:var(--theme-muted); background:var(--theme-surface-2); transition:.2s; }
.adm-tab:hover   { background:var(--theme-border); color:var(--theme-text); }
.adm-tab.active  { background:#6C3FC5; color:#fff; border-color:#6C3FC5; }
.adm-tab .badge  { background:rgba(255,255,255,.25); color:inherit;
                   font-size:.72rem; padding:.1rem .45rem; border-radius:10px; margin-left:.3rem; }

/* Grid de tarjetas */
.adm-grid        { display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:1.2rem; }
.adm-card        { background:var(--theme-card); border:1px solid var(--theme-border);
                   border-radius:14px; overflow:hidden; display:flex; flex-direction:column; }
.adm-card__img   { width:100%; aspect-ratio:4/3; object-fit:cover; display:block; background:#111; }
.adm-card__body  { padding:1rem; flex:1; display:flex; flex-direction:column; gap:.6rem; }
.adm-card__meta  { font-size:.78rem; color:var(--theme-muted); }
.adm-card__meta strong { color:var(--theme-text); }
.adm-card__album { display:inline-block; font-size:.72rem; font-weight:700; padding:.2rem .6rem;
                   border-radius:8px; background:var(--theme-surface-2); color:var(--theme-muted); }
.adm-card__album.public  { background:#d4edda; color:#155724; }
.adm-card__album.private { background:#fff3cd; color:#856404; }
.adm-card__album.vip     { background:#f3e0ff; color:#6C3FC5; }
.adm-card__note  { font-size:.8rem; color:#c0392b; background:#fdf0f0;
                   border-left:3px solid #c0392b; padding:.5rem .7rem; border-radius:4px; }
.adm-card__actions { display:flex; gap:.5rem; margin-top:auto; }
.adm-btn         { flex:1; padding:.5rem; border:none; border-radius:8px; font-size:.82rem;
                   font-weight:600; cursor:pointer; transition:.2s; }
.adm-btn--approve { background:#28a745; color:#fff; }
.adm-btn--approve:hover { background:#218838; }
.adm-btn--reject  { background:#dc3545; color:#fff; }
.adm-btn--reject:hover  { background:#c82333; }
.adm-btn--approved { background:#e9f7ef; color:#28a745; cursor:default; }
.adm-btn--rejected { background:#fdf0f0; color:#dc3545; cursor:default; }

/* Modal rechazo */
.adm-modal-overlay { position:fixed; inset:0; background:rgba(0,0,0,.6);
                     display:none; align-items:center; justify-content:center; z-index:9999; }
.adm-modal-overlay.open { display:flex; }
.adm-modal        { background:var(--theme-card); border-radius:14px; padding:1.5rem;
                    width:100%; max-width:440px; box-shadow:0 8px 32px rgba(0,0,0,.3); }
.adm-modal h3     { margin:0 0 1rem; color:var(--theme-text); font-size:1.1rem; }
.adm-modal textarea{ width:100%; border:1px solid var(--theme-border); border-radius:8px;
                     background:var(--theme-input-bg); color:var(--theme-text);
                     padding:.7rem; font-size:.9rem; resize:vertical; min-height:100px; }
.adm-modal__btns  { display:flex; gap:.6rem; margin-top:1rem; justify-content:flex-end; }
.adm-modal__btns button { padding:.5rem 1.2rem; border:none; border-radius:8px;
                          font-size:.85rem; font-weight:600; cursor:pointer; }
.btn-cancel       { background:var(--theme-surface-2); color:var(--theme-muted); }
.btn-send         { background:#dc3545; color:#fff; }

/* Vacío */
.adm-empty        { text-align:center; padding:3rem 1rem; color:var(--theme-muted); grid-column:1/-1; }
.adm-empty svg    { width:60px; height:60px; opacity:.3; margin-bottom:1rem; }

/* Dark mode fixes */
[data-theme="dark"] .adm-card__album.public  { background:#1a3a22; color:#5cb85c; }
[data-theme="dark"] .adm-card__album.private { background:#3a2e00; color:#f0c040; }
[data-theme="dark"] .adm-card__album.vip     { background:#2e1a4a; color:#b08df0; }
[data-theme="dark"] .adm-card__note          { background:#2a1010; color:#e07070; }
[data-theme="dark"] .adm-btn--approved       { background:#0d2e18; color:#5cb85c; }
[data-theme="dark"] .adm-btn--rejected       { background:#2a1010; color:#e07070; }
</style>
@endpush

@section('content')
<div class="adm-wrap">

  {{-- Encabezado --}}
  <div class="adm-header">
    <h1 class="adm-title">📸 Moderación de Fotos</h1>
    <div class="adm-tabs">
      <a href="{{ route('admin.photos.index', ['status'=>'pending']) }}"
         class="adm-tab {{ $status==='pending' ? 'active' : '' }}">
        Pendientes <span class="badge">{{ $counts['pending'] }}</span>
      </a>
      <a href="{{ route('admin.photos.index', ['status'=>'approved']) }}"
         class="adm-tab {{ $status==='approved' ? 'active' : '' }}">
        Aprobadas <span class="badge">{{ $counts['approved'] }}</span>
      </a>
      <a href="{{ route('admin.photos.index', ['status'=>'rejected']) }}"
         class="adm-tab {{ $status==='rejected' ? 'active' : '' }}">
        Rechazadas <span class="badge">{{ $counts['rejected'] }}</span>
      </a>
      <a href="{{ route('admin.videos.index') }}" class="adm-tab">
        🎬 Ir a Videos
      </a>
    </div>
  </div>

  @if(session('success'))
    <div style="background:#d4edda;color:#155724;padding:.75rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:.9rem;">
      ✅ {{ session('success') }}
    </div>
  @endif

  {{-- Grid de fotos --}}
  <div class="adm-grid">
    @forelse($photos as $photo)
      <div class="adm-card">
        <img
          src="{{ route('admin.photos.serve', $photo->id) }}"
          alt="Foto #{{ $photo->id }}"
          class="adm-card__img"
          loading="lazy"
        >
        <div class="adm-card__body">
          <div class="adm-card__meta">
            <strong>{{ $photo->display_name ?? $photo->username }}</strong>
            @if($photo->nickname)
              <br>@{{ $photo->nickname }}
            @endif
            <br>
            <span>{{ ucfirst($photo->membership_type ?? 'trial') }}</span>
            · <span>{{ \Carbon\Carbon::parse($photo->created_at)->diffForHumans() }}</span>
          </div>

          <span class="adm-card__album {{ $photo->album_type }}">
            {{ match($photo->album_type) {
               'public'  => '🌐 Pública',
               'private' => '🔒 Privada',
               'vip'     => '⭐ VIP',
               default   => $photo->album_type
            } }}
          </span>

          @if($photo->caption)
            <p style="font-size:.8rem;color:var(--theme-muted);margin:0;">"{{ $photo->caption }}"</p>
          @endif

          @if($photo->admin_note)
            <div class="adm-card__note">⚠️ {{ $photo->admin_note }}</div>
          @endif

          <div class="adm-card__actions">
            @if($status === 'pending')
              <form method="POST" action="{{ route('admin.photos.approve', $photo->id) }}" style="flex:1">
                @csrf
                <button type="submit" class="adm-btn adm-btn--approve" style="width:100%">✓ Aprobar</button>
              </form>
              <button
                class="adm-btn adm-btn--reject"
                onclick="openRejectModal({{ $photo->id }}, 'photo')"
              >✗ Rechazar</button>
            @elseif($status === 'approved')
              <span class="adm-btn adm-btn--approved">✓ Aprobada</span>
              <button
                class="adm-btn adm-btn--reject"
                onclick="openRejectModal({{ $photo->id }}, 'photo')"
              >✗ Rechazar</button>
            @else
              <span class="adm-btn adm-btn--rejected">✗ Rechazada</span>
              <form method="POST" action="{{ route('admin.photos.approve', $photo->id) }}" style="flex:1">
                @csrf
                <button type="submit" class="adm-btn adm-btn--approve" style="width:100%">✓ Re-aprobar</button>
              </form>
            @endif
          </div>
        </div>
      </div>
    @empty
      <div class="adm-empty">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
          <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
          <polyline points="9 22 9 12 15 12 15 22"/>
        </svg>
        <p>No hay fotos con estado <strong>{{ $status }}</strong>.</p>
      </div>
    @endforelse
  </div>

  {{-- Paginación --}}
  @if($photos->hasPages())
    <div style="margin-top:2rem; display:flex; justify-content:center;">
      {{ $photos->appends(['status' => $status])->links() }}
    </div>
  @endif

</div>

{{-- Modal de rechazo --}}
<div class="adm-modal-overlay" id="rejectModal">
  <div class="adm-modal">
    <h3>✗ Motivo del rechazo</h3>
    <form id="rejectForm" method="POST">
      @csrf
      <textarea name="reason" id="rejectReason"
        placeholder="Explica brevemente el motivo (ej: contenido inapropiado, imagen borrosa, rostro no visible...)"
        required></textarea>
      <div class="adm-modal__btns">
        <button type="button" class="btn-cancel" onclick="closeRejectModal()">Cancelar</button>
        <button type="submit" class="btn-send">Rechazar</button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
function openRejectModal(id, type) {
  const base = type === 'photo'
    ? '{{ url("admin/fotos") }}/' + id + '/rechazar'
    : '{{ url("admin/videos") }}/' + id + '/rechazar';
  document.getElementById('rejectForm').action = base;
  document.getElementById('rejectReason').value = '';
  document.getElementById('rejectModal').classList.add('open');
}
function closeRejectModal() {
  document.getElementById('rejectModal').classList.remove('open');
}
document.getElementById('rejectModal').addEventListener('click', function(e) {
  if (e.target === this) closeRejectModal();
});
</script>
@endpush
@endsection
