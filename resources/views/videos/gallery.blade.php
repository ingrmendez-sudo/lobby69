@extends('layouts.app')

@section('title', 'Galería de Videos — LOBBY69')

@push('styles')
<style>
/* ── Video Gallery ──────────────────────────── */
.vg-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.4rem;
    padding: 1rem 0;
}
.vg-vcard {
    background: var(--card-bg, #fff);
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,.08);
    transition: transform .2s, box-shadow .2s;
    cursor: pointer;
    position: relative;
}
.vg-vcard:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(0,0,0,.14); }
.vg-thumb { position:relative; width:100%; aspect-ratio:16/9; background:#111; overflow:hidden; }
.vg-thumb video { width:100%; height:100%; object-fit:cover; display:block; }
.vg-play-icon {
    position:absolute; top:50%; left:50%; transform:translate(-50%,-50%);
    width:46px; height:46px; background:rgba(255,255,255,.18); border-radius:50%;
    display:flex; align-items:center; justify-content:center;
    transition:background .2s; pointer-events:none;
}
.vg-play-icon svg { width:22px; height:22px; fill:#fff; }
.vg-vcard:hover .vg-play-icon { background:var(--bs-pink,#e91e8c); }
.vg-duration {
    font-size:.72rem; color:#fff; background:rgba(0,0,0,.55);
    padding:1px 6px; border-radius:4px;
    position:absolute; bottom:.5rem; right:.6rem;
}
.vg-views {
    font-size:.72rem; color:#fff;
    display:flex; align-items:center; gap:3px;
    position:absolute; bottom:.5rem; left:.6rem;
}
.vg-info { padding:.65rem .8rem .4rem; display:flex; flex-direction:column; gap:.25rem; background:var(--card-bg,#fff); }
.vg-card { position:relative; border-radius:10px; overflow:hidden; background:#111; cursor:pointer; aspect-ratio:16/9; }
.vg-card video { width:100%; height:100%; object-fit:cover; display:block; }
.vg-card-overlay { position:absolute; inset:0; background:linear-gradient(to top, rgba(0,0,0,.75) 0%, transparent 50%); pointer-events:none; }
.vg-card-info { position:absolute; bottom:0; left:0; right:0; padding:.5rem .7rem; }
.vg-caption {
    font-size:.9rem; font-weight:600; color:var(--text-main,#111);
    white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
}
.vg-meta { font-size:.78rem; color:var(--text-main,#333); display:flex; align-items:center; gap:.4rem; }
.vg-meta img { width:22px; height:22px; border-radius:50%; object-fit:cover; }
.vg-act-views { display:flex; align-items:center; gap:.25rem; font-size:.75rem; color:var(--text-main,#555); padding:0 .2rem; }
.vg-actions { background:var(--card-bg,#fff);
    display:flex; align-items:center; gap:.7rem;
    padding:.4rem .8rem .6rem;
    border-top:1px solid var(--border-light,rgba(0,0,0,.08));
}
.vg-btn-action {
    background:none; border:none; cursor:pointer;
    display:flex; align-items:center; gap:4px;
    font-size:.82rem; color:var(--text-main,#444);
    padding:4px 8px; border-radius:8px; transition:background .15s,color .15s;
}
.vg-btn-action:hover { background:var(--hover-bg,#f5f5f5); color:var(--bs-pink,#e91e8c); }
.vg-btn-action.liked { color:var(--bs-pink,#e91e8c); }
.vg-btn-action svg { width:16px; height:16px; }
.vg-pag { display:flex; justify-content:center; gap:.5rem; margin-top:1.2rem; flex-wrap:wrap; }
.vg-pag a, .vg-pag span {
    padding:6px 13px; border-radius:8px; font-size:.85rem;
    border:1px solid var(--border-light,#e0e0e0);
    color:var(--text-main,#444); text-decoration:none; transition:background .15s;
}
.vg-pag .active, .vg-pag a:hover { background:var(--bs-pink,#e91e8c); color:#fff; border-color:transparent; }
#vgm { display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,.75); align-items:center; justify-content:center; }
#vgm.open { display:flex; }
.vgm-box {
    background:var(--card-bg,#fff); border-radius:16px; overflow:hidden;
    width:min(760px,94vw); max-height:88vh;
    display:flex; flex-direction:column; box-shadow:0 20px 60px rgba(0,0,0,.4);
}
.vgm-head { display:flex; align-items:center; justify-content:space-between; padding:.8rem 1.2rem; border-bottom:1px solid var(--border-light,#eee); }
.vgm-head h6 { margin:0; font-size:1rem; font-weight:600; }
.vgm-close { background:none; border:none; cursor:pointer; font-size:1.5rem; color:var(--text-muted,#888); line-height:1; }
#vgm-player { width:100%; height:50vh; max-height:50vh; background:#000; flex-shrink:0; display:flex; align-items:center; justify-content:center; }
#vgm-player video { width:100%; height:100%; max-height:50vh; object-fit:contain; display:block; }
.vgm-foot { padding:.8rem 1.1rem; overflow-y:auto; flex:1; min-height:160px; display:flex; flex-direction:column; gap:.6rem; }
.vgm-like-btn {
    background:none; border:1px solid var(--border-light,#ddd); border-radius:20px;
    cursor:pointer; padding:6px 16px; display:flex; align-items:center; gap:6px;
    font-size:.85rem; transition:background .15s,color .15s,border-color .15s;
}
.vgm-like-btn:hover, .vgm-like-btn.liked { background:var(--bs-pink,#e91e8c); color:#fff; border-color:transparent; }
.vgm-views-label { font-size:.8rem; color:var(--text-muted,#888); }
.vgm-comment-item { display:flex; gap:.6rem; font-size:.83rem; padding:.5rem 0; border-bottom:1px solid var(--border-light,#f0f0f0); }
.vgm-comment-item img { width:30px; height:30px; border-radius:50%; object-fit:cover; flex-shrink:0; }
.vgm-comment-body b { display:block; font-size:.82rem; margin-bottom:2px; }
.vgm-comment-del { margin-left:auto; background:none; border:none; cursor:pointer; color:#ccc; font-size:.9rem; }
.vgm-comment-del:hover { color:#e91e8c; }
.vgm-reply-btn { background:none; border:none; cursor:pointer; font-size:.72rem; color:var(--text-muted,#888); padding:2px 6px; border-radius:6px; transition:color .15s; }
.vgm-reply-btn:hover { color:var(--bs-pink,#e91e8c); }
.vgm-replies { margin-left:36px; border-left:2px solid var(--border-light,#f0f0f0); padding-left:.6rem; margin-top:.3rem; }
.vgm-reply-form { display:flex; gap:.4rem; margin-top:.4rem; margin-left:36px; }
.vgm-reply-form input { flex:1; border:1px solid var(--border-light,#ddd); border-radius:16px; padding:4px 12px; font-size:.8rem; background:var(--input-bg,#f8f8f8); }
.vgm-reply-form button { background:var(--bs-pink,#e91e8c); color:#fff; border:none; border-radius:16px; padding:4px 12px; font-size:.8rem; cursor:pointer; }
.vgm-comment-form { display:flex; gap:.5rem; margin-top:.5rem; }
.vgm-comment-form input {
    flex:1; border:1px solid var(--border-light,#ddd); border-radius:20px;
    padding:6px 14px; font-size:.85rem;
    background:var(--input-bg,#fff); color:var(--text-main,#222);
}
.vgm-comment-form button {
    background:var(--bs-pink,#e91e8c); color:#fff; border:none;
    border-radius:20px; padding:6px 16px; cursor:pointer; font-size:.85rem;
    min-width:72px; display:flex; align-items:center; justify-content:center; gap:4px;
}
.vgm-feedback {
    font-size:.78rem; padding:.35rem .7rem; border-radius:8px; margin-top:.3rem;
    display:flex; align-items:center; gap:.4rem;
}
.vgm-feedback.ok  { background:#e8f5e9; color:#2e7d32; }
.vgm-feedback.err { background:#fce4ec; color:#c62828; }

/* ── Hover preview: spinner de carga sobre la tarjeta ── */
.vg-thumb--hoverable .vg-hover-spinner {
    display: none;
    position: absolute; top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    width: 28px; height: 28px;
    border: 3px solid rgba(255,255,255,.3);
    border-top-color: #fff;
    border-radius: 50%;
    animation: vgSpin .6s linear infinite;
    pointer-events: none; z-index: 2;
}
@keyframes vgSpin { to { transform: translate(-50%,-50%) rotate(360deg); } }
.vg-thumb--hoverable.vg-loading .vg-hover-spinner { display: block; }
.vg-thumb--hoverable.vg-loading .vg-play-icon { opacity: 0; }
</style>

{{-- ═══════════════════════════════════════════════════════════
     SCRIPT STUB — cargado en @push('styles') para estar
     disponible antes que cualquier @push('sidebar-*')
     ═══════════════════════════════════════════════════════════ --}}
<script>
/* Stubs tempranos: esperan a que window._vgReady sea true */
function vgOpen(card, fc) {
    if (window._vgReady) return window.vgOpen(card, fc);
    setTimeout(function(){ vgOpen(card, fc); }, 80);
}
function vgOpenById(vid, cap, src) {
    if (window._vgReady) return window.vgOpenById(vid, cap, src);
    setTimeout(function(){ vgOpenById(vid, cap, src); }, 80);
}
function vgClose() {
    if (window._vgReady) return window.vgClose();
}
function vgLike(btn) {
    if (window._vgReady) return window.vgLike(btn);
}
function vgLikeModal() {
    if (window._vgReady) return window.vgLikeModal();
}
function vgComment() {
    if (window._vgReady) return window.vgComment();
}
</script>
@endpush

@push('sidebar-left')
@php
    $sideNick  = $userProfile->nickname    ?? ($user->name ?? 'Usuario');
    $sidePtype = ucfirst($userProfile->profile_type ?? '');
    $sideAv    = !empty($userProfile->avatar_photo_id)
        ? url('/fotos/'.$userProfile->avatar_photo_id.'/ver')
        : null;
    $sideInit  = strtoupper(substr($sideNick, 0, 1));
    $sideClrs  = ['#e91e8c','#9c27b0','#3f51b5','#00bcd4','#ff5722'];
    $sideColor = $sideClrs[abs(crc32($sideNick)) % count($sideClrs)];
@endphp

{{-- Tarjeta usuario --}}
<div style="background:var(--card-bg,#fff);border-radius:14px;padding:1.1rem;box-shadow:0 2px 10px rgba(0,0,0,.07);margin-bottom:1rem">
    <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:.9rem">
        @if($sideAv)
        <img loading="eager" src="{{ $sideAv }}"
             onerror="this.outerHTML='<div style=&quot;width:52px;height:52px;border-radius:50%;background:{{ $sideColor }};display:flex;align-items:center;justify-content:center;font-weight:700;font-size:1.3rem;color:#fff;flex-shrink:0;border:2px solid var(--bs-pink,#e91e8c)&quot;>{{ $sideInit }}</div>'"
             style="width:52px;height:52px;border-radius:50%;object-fit:cover;flex-shrink:0;border:2px solid var(--bs-pink,#e91e8c)">
        @else
        <div style="width:52px;height:52px;border-radius:50%;background:{{ $sideColor }};display:flex;align-items:center;justify-content:center;font-weight:700;font-size:1.3rem;color:#fff;flex-shrink:0;border:2px solid var(--bs-pink,#e91e8c)">{{ $sideInit }}</div>
        @endif
        <div>
            <div style="font-weight:700;font-size:.95rem;color:var(--text-main,#222)">{{ $sideNick }}</div>
            <div style="font-size:.78rem;color:var(--text-muted,#888)">{{ $sidePtype }}</div>
        </div>
    </div>
    <div style="height:1px;background:var(--border-light,#eee);margin:.5rem 0 .9rem"></div>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);text-align:center">
        <div>
            <div style="font-size:1.25rem;font-weight:700;color:var(--text-main,#222)">{{ $myVideoCount }}</div>
            <div style="font-size:.7rem;color:var(--text-muted,#888)">Videos</div>
        </div>
        <div>
            <div style="font-size:1.25rem;font-weight:700;color:var(--bs-pink,#e91e8c)">{{ $myLikesReceived }}</div>
            <div style="font-size:.7rem;color:var(--text-muted,#888)">Likes</div>
        </div>
        <div>
            <div style="font-size:1.25rem;font-weight:700;color:var(--text-main,#222)">{{ $myCommentsReceived }}</div>
            <div style="font-size:.7rem;color:var(--text-muted,#888)">Comentarios</div>
        </div>
    </div>
</div>

{{-- Últimos videos vistos --}}
@if($lastWatched->count())
<div style="background:var(--card-bg,#fff);border-radius:14px;padding:1rem;box-shadow:0 2px 10px rgba(0,0,0,.07);margin-bottom:1rem">
    <div style="font-weight:700;font-size:.85rem;margin-bottom:.7rem;color:var(--text-main,#222)">
        👁️ Últimos vistos
    </div>
    @foreach($lastWatched as $lw)
    @php
        $lwThumb = !empty($lw->thumbnail_path)
            ? (str_starts_with($lw->thumbnail_path,'http') ? $lw->thumbnail_path : asset('storage/'.ltrim($lw->thumbnail_path,'/')))
            : null;
        $lwAv = !empty($lw->avatar_url)
            ? (str_starts_with($lw->avatar_url,'http') ? $lw->avatar_url : asset('storage/'.ltrim($lw->avatar_url,'/')))
            : null;
    @endphp
    <div style="display:flex;gap:.5rem;align-items:center;margin-bottom:.6rem;cursor:pointer"
         onclick="vgOpenById({{ $lw->id }}, this.dataset.cap, this.dataset.src)"
         data-cap="{{ e($lw->caption ?? chr(45)) }}"
         data-src="{{ route('videos.stream', $lw->id) }}">
        <div style="width:52px;height:30px;border-radius:5px;overflow:hidden;flex-shrink:0;background:#111">
            @if($lwThumb)
            <img loading="lazy" src="{{ $lwThumb }}" style="width:100%;height:100%;object-fit:cover">
            @else
            <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center">
                <svg width="14" height="14" fill="none" stroke="#666" stroke-width="2" viewBox="0 0 24 24"><polygon points="5 3 19 12 5 21 5 3"/></svg>
            </div>
            @endif
        </div>
        <div style="flex:1;min-width:0">
            <div style="font-size:.78rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:var(--text-main,#222)">
                {{ $lw->caption ?? 'Sin título' }}
            </div>
            <div style="font-size:.7rem;color:var(--text-muted,#888)">{{ $lw->nickname ?? '' }}</div>
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- Visitantes al perfil --}}
@if($profileVisitors->count())
<div style="background:var(--card-bg,#fff);border-radius:14px;padding:1rem;box-shadow:0 2px 10px rgba(0,0,0,.07);margin-bottom:1rem">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.7rem">
        <div style="font-weight:700;font-size:.85rem;color:var(--text-main,#222)">👤 Visitaron mi perfil</div>
        @if($totalVisitors > 5)
        <a href="{{ route('profile.visitors') }}" style="font-size:.72rem;color:var(--bs-pink,#e91e8c);text-decoration:none;font-weight:600">
            Ver todos ({{ $totalVisitors }}) →
        </a>
        @endif
    </div>
    <div style="display:flex;flex-direction:column;gap:.2rem">
        @foreach($profileVisitors as $pv)
        @php
            $pvNick    = $pv->nickname    ?? "Anonimo";
            $pvAvatar  = $pv->avatar_url  ?? "";
            $pvType    = $pv->profile_type ?? "";
            $pvDate    = \Carbon\Carbon::parse($pv->viewed_at)->diffForHumans();
            $pvInitial = strtoupper(substr($pvNick, 0, 1));
            $pvColors  = ["#e91e8c","#9c27b0","#3f51b5","#00bcd4","#ff5722"];
            $pvColor   = $pvColors[abs(crc32($pvNick)) % count($pvColors)];
            $pvAv      = !empty($pv->avatar_photo_id)
                ? url('/fotos/'.$pv->avatar_photo_id.'/ver')
                : null;
            $pvBadge = match($pvType) {
                "pareja"    => "&#x1F46B;",
                "hombre"    => "&#x1F468;",
                "mujer"     => "&#x1F469;",
                "trans"     => "&#x26A7;",
                "unicornio" => "&#x1F984;",
                default     => "&#x1F464;",
            };
        @endphp
        <a href="{{ route('profile.show', $pvNick) }}" style="display:flex;align-items:center;gap:.55rem;padding:.4rem .5rem;border-radius:8px;transition:background .15s;text-decoration:none;color:inherit"
             onmouseover="this.style.background=&apos;rgba(233,30,140,.08)&apos;"
             onmouseout="this.style.background=&apos;transparent&apos;">
            @if($pvAv)
                <img loading="lazy" src="{{ $pvAv }}"
                     onerror="this.outerHTML='<div style=&quot;width:34px;height:34px;border-radius:50%;background:{{ $pvColor }};display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.85rem;color:#fff;flex-shrink:0&quot;>{{ $pvInitial }}</div>'"
                     style="width:34px;height:34px;border-radius:50%;object-fit:cover;flex-shrink:0">
            @else
                <div style="width:34px;height:34px;border-radius:50%;background:{{ $pvColor }};display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.85rem;color:#fff;flex-shrink:0">
                    {{ $pvInitial }}
                </div>
            @endif
            <div style="min-width:0;flex:1">
                <div style="font-size:.78rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                    {!! $pvBadge !!} {{ $pvNick }}
                </div>
                <div style="font-size:.65rem;color:var(--text-muted,#888);white-space:nowrap">
                    {{ $pvDate }}
                </div>
            </div>
        </a>
        @endforeach
    </div>
</div>
@endif
@endpush

@push('sidebar-right')
{{-- ── Top 5 Videos Populares ── --}}
<div style="background:var(--card-bg,#fff);border-radius:14px;padding:1rem;box-shadow:0 2px 10px rgba(0,0,0,.07);margin-bottom:1rem">
    <div style="font-weight:700;font-size:.85rem;margin-bottom:.7rem;color:var(--text-main,#222)">🔥 Videos populares</div>
    @forelse($topVideos as $tv)
    @php
        $tvThumb = !empty($tv->thumbnail_path)
        ? (str_starts_with($tv->thumbnail_path, 'http')
            ? $tv->thumbnail_path
            : asset('storage/' . ltrim($tv->thumbnail_path, '/')))
        : null;
    @endphp
    <div style="display:flex;align-items:center;gap:.6rem;margin-bottom:.6rem;cursor:pointer;border-radius:8px;padding:.2rem .3rem;transition:background .15s"
         onmouseover="this.style.background='rgba(233,30,140,.08)'"
         onmouseout="this.style.background='transparent'"
         onclick="vgOpenById({{ $tv->id }}, this.dataset.cap, this.dataset.src)"
         data-cap="{{ addslashes($tv->caption ?? 'Sin título') }}"
         data-src="{{ route('videos.stream', $tv->id) }}">
        @if($tvThumb)
        <img loading="lazy" src="{{ $tvThumb }}"
            onerror="this.style.display='none';this.nextElementSibling.style.display='flex'"
            style="width:48px;height:36px;object-fit:cover;border-radius:6px;flex-shrink:0" alt="">
        <div style="width:48px;height:36px;background:var(--input-bg,#eee);border-radius:6px;flex-shrink:0;display:none;align-items:center;justify-content:center;font-size:.9rem">🎬</div>
        @else
        <div style="width:48px;height:36px;background:var(--input-bg,#eee);border-radius:6px;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:.9rem">🎬</div>
        @endif
        <div style="flex:1;min-width:0">
            <div style="font-size:.75rem;font-weight:600;color:var(--text-main,#222);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ Str::limit($tv->caption ?? 'Sin título', 22) }}</div>
            <div style="font-size:.7rem;color:var(--text-muted,#888)">👁 {{ number_format($tv->views_count ?? 0) }} · {{ $tv->nickname }}</div>
        </div>
    </div>
    @empty
    <p style="font-size:.8rem;color:var(--text-muted,#888);margin:0">Sin videos aún.</p>
    @endforelse
</div>

{{-- ── Verificación pendiente ── --}}
@if(!$isVerified)
<div style="background:linear-gradient(135deg,#fff8e1,#fff3cd);border:1px solid #ffc107;border-radius:14px;padding:1rem;box-shadow:0 2px 10px rgba(255,193,7,.15);margin-bottom:1rem">
    <div style="font-weight:700;font-size:.85rem;margin-bottom:.4rem;color:#856404">
        @if($pendingVerification)⏳ Verificación en proceso
        @else✅ Verifica tu perfil
        @endif
    </div>
    <p style="font-size:.78rem;color:#856404;margin:0 0 .6rem">
        @if($pendingVerification)Tu solicitud está siendo revisada. Te notificaremos cuando esté lista.
        @else Los perfiles verificados generan más confianza y visibilidad en la comunidad.
        @endif
    </p>
    @if(!$pendingVerification)
    <a href="/verificacion" style="display:inline-block;background:#ffc107;color:#212529;border-radius:8px;padding:4px 12px;font-size:.78rem;font-weight:600;text-decoration:none">Verificar ahora →</a>
    @endif
</div>
@endif

{{-- ── Anuncios (solo verificados) ── --}}
@if($isVerified && $announcements->count())
<div style="background:var(--card-bg,#fff);border-radius:14px;padding:1rem;box-shadow:0 2px 10px rgba(0,0,0,.07);margin-bottom:1rem">
    <div style="font-weight:700;font-size:.85rem;margin-bottom:.7rem;color:var(--text-main,#222)">📢 Anuncios recientes</div>
    @foreach($announcements as $ann)
    @php
        $annAv    = $ann->avatar_photo_id ? url('/fotos/'.$ann->avatar_photo_id.'/ver') : null;
        $annInit  = strtoupper(substr($ann->nickname ?? '?', 0, 1));
        $annClrs  = ['#e74c3c','#8e44ad','#2980b9','#27ae60','#e67e22','#16a085'];
        $annColor = $annClrs[abs(crc32($ann->nickname ?? '')) % count($annClrs)];
    @endphp
    <div style="background:var(--input-bg,#f7f7f7);border-radius:10px;padding:.6rem;margin-bottom:.6rem">
        <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.3rem">
            @if($annAv)
            <img loading="lazy" src="{{ $annAv }}" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'" style="width:24px;height:24px;border-radius:50%;object-fit:cover;flex-shrink:0" alt="">
            <div style="width:24px;height:24px;border-radius:50%;background:{{ $annColor }};display:none;align-items:center;justify-content:center;font-weight:700;font-size:.7rem;color:#fff;flex-shrink:0">{{ $annInit }}</div>
            @else
            <div style="width:24px;height:24px;border-radius:50%;background:{{ $annColor }};display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.7rem;color:#fff;flex-shrink:0">{{ $annInit }}</div>
            @endif
            <span style="font-size:.78rem;font-weight:600;color:var(--text-main,#222)">{{ $ann->nickname }}</span>
            <span style="font-size:.7rem;color:var(--text-muted,#888);margin-left:auto">{{ \Carbon\Carbon::parse($ann->event_date)->format('d M') }}</span>
        </div>
        <a href="{{ route('profile.show', $ann->nickname) }}" style="font-size:.8rem;font-weight:600;color:var(--text-main,#222);text-decoration:none;display:block">{{ $ann->title }}</a>
        <div style="font-size:.75rem;color:var(--text-muted,#888);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ Str::limit($ann->proposal ?? '', 60) }}</div>
    </div>
    @endforeach
</div>
@endif
@endpush

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
    <h5 class="mb-0 fw-bold">🎬 Galería de Videos</h5>
    <span class="text-muted" style="font-size:.85rem">Videos</span>
</div>

<div class="vg-grid">
@forelse($videos as $video)
@php
    $thumbSrc = !empty($video->thumbnail_path)
        ? (str_starts_with($video->thumbnail_path,'http') ? $video->thumbnail_path : asset('storage/'.ltrim($video->thumbnail_path,'/')))
        : null;
    $dur = $video->duration_seconds ?? 0;
    $durFmt = $dur > 0 ? sprintf('%d:%02d', intdiv($dur,60), $dur%60) : '0:00';
    $cap = $video->caption ?? 'Sin título';
    $nick = $video->nickname ?? 'Usuario';
    $avSrc   = !empty($video->avatar_photo_id)
        ? url('/fotos/'.$video->avatar_photo_id.'/ver')
        : null;
    $avInit  = strtoupper(substr($nick, 0, 1));
    $avClrs  = ['#e91e8c','#9c27b0','#3f51b5','#00bcd4','#ff5722'];
    $avColor = $avClrs[abs(crc32($nick)) % count($avClrs)];
    $isLiked = in_array($video->id, $likedIds ?? []);
    $likes = $video->likes_count ?? 0;
    $comms = $video->comments_count ?? 0;
    $views = $video->views_count ?? 0;
@endphp
<div class="vg-vcard"
     data-vid="{{ $video->id }}"
     data-owner="{{ $video->user_id ?? 0 }}"
     data-vsrc="{{ route('videos.stream', $video->id) }}"
     data-cap="{{ e($cap) }}"
     data-nick="{{ e($nick) }}"
     data-av="{{ $avSrc }}">
    {{-- ── Thumbnail + hover preview ── --}}
    <div class="vg-thumb vg-thumb--hoverable">
        {{-- Spinner visible solo mientras carga el hover --}}
        <div class="vg-hover-spinner"></div>
        <video preload="none" muted playsinline
               @if($thumbSrc) poster="{{ $thumbSrc }}" @endif>
        </video>
        <div class="vg-play-icon">
            <svg viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
        </div>
        <span class="vg-duration">{{ $durFmt }}</span>
        @if($views >= 1000)
            <span class="vg-badge vg-badge--viral">💎 Viral</span>
        @elseif($views >= 500)
            <span class="vg-badge vg-badge--popular">⭐ Popular</span>
        @elseif($views >= 100)
            <span class="vg-badge vg-badge--hot">🔥 Hot</span>
        @endif
        <span class="vg-views">
            <svg width="13" height="13" fill="currentColor" viewBox="0 0 24 24"><path d="M12 4.5C7 4.5 2.7 7.6 1 12c1.7 4.4 6 7.5 11 7.5s9.3-3.1 11-7.5C21.3 7.6 17 4.5 12 4.5zm0 12.5a5 5 0 110-10 5 5 0 010 10zm0-8a3 3 0 100 6 3 3 0 000-6z"/></svg>
            {{ number_format($views) }}
        </span>
    </div>
    <div class="vg-info">
        <div class="vg-caption">{{ $cap }}</div>
        <div class="vg-meta">
            @if($avSrc)
            <img loading="lazy" src="{{ $avSrc }}"
                 onerror="this.outerHTML='<div style=&quot;width:28px;height:28px;border-radius:50%;background:{{ $avColor }};display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.75rem;color:#fff&quot;>{{ $avInit }}</div>'"
                 alt="{{ e($nick) }}" style="width:28px;height:28px;border-radius:50%;object-fit:cover">
            @else
            <div style="width:28px;height:28px;border-radius:50%;background:{{ $avColor }};display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.75rem;color:#fff">{{ $avInit }}</div>
            @endif
            <span>{{ $nick }}</span>
        </div>
    </div>
    <div class="vg-actions">
        <span class="vg-act-views">
            <svg width="12" height="12" fill="currentColor" viewBox="0 0 24 24"><path d="M12 4.5C7 4.5 2.7 7.6 1 12c1.7 4.4 6 7.5 11 7.5s9.3-3.1 11-7.5C21.3 7.6 17 4.5 12 4.5zm0 12.5a5 5 0 110-10 5 5 0 010 10zm0-8a3 3 0 100 6 3 3 0 000-6z"/></svg>
            {{ number_format($views) }}
        </span>
        <button class="vg-btn-action{{ $isLiked ? ' liked' : '' }}"
                data-vid="{{ $video->id }}"
                data-owner="{{ $video->user_id ?? 0 }}"
                onclick="event.stopPropagation();vgLike(this)">
            <svg viewBox="0 0 24 24" fill="{{ $isLiked ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2">
                <path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/>
            </svg>
            <span class="vg-like-count">{{ $likes }}</span>
        </button>
        <button class="vg-btn-action"
                onclick="event.stopPropagation();vgOpen(this.closest('.vg-vcard'),true)">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
            </svg>
            <span>{{ $comms }}</span>
        </button>
    </div>
</div>
@empty
<div style="grid-column:1/-1;text-align:center;padding:3rem;color:var(--text-muted,#888)">
    <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="opacity:.4;margin-bottom:.8rem;display:block;margin-inline:auto"><rect x="2" y="2" width="20" height="20" rx="4"/><path d="M10 8l6 4-6 4V8z"/></svg>
    <p>Aún no hay videos publicados.</p>
</div>
@endforelse
</div>

<div class="vg-pag">
    {{ $videos->links('pagination::simple-bootstrap-5') }}
</div>

<div id="vgm">
    <div class="vgm-box">
        <div class="vgm-head">
            <h6 id="vgm-title">Video</h6>
            <button class="vgm-close" onclick="vgClose()">&#x2715;</button>
        </div>
        <div id="vgm-player"><video id="vgm-video" controls playsinline></video></div>
        <div class="vgm-foot">
            <div style="display:flex;align-items:center;gap:1rem">
                <button class="vgm-like-btn" id="vgm-like-btn" onclick="vgLikeModal()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/>
                    </svg>
                    <span id="vgm-like-count">0</span>
                </button>
                <span class="vgm-views-label" id="vgm-views-label"></span>
            </div>
            <div class="vgm-comments">
                <h6>Comentarios</h6>
                <div id="vgm-comments-list"></div>
                <div class="vgm-comment-form">
                    <input type="text" id="vgm-cinput" placeholder="Escribe un comentario..." maxlength="500">
                    <button onclick="vgComment()">Enviar</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
/* ── Estado global ── */
var VG = {
    vid     : null,
    ownerId : '',
    authId  : '',
    csrf    : (document.querySelector('meta[name="csrf-token"]') || {}).content || ''
};

/* ══════════════════════════════════════════════════════════════
   vgLoadVideo — carga limpia garantizada en todos los browsers
   ══════════════════════════════════════════════════════════════ */
window.vgLoadVideo = function(v, src) {
    if (!v || !src) {
        console.warn('[vgLoadVideo] elemento o src inválido', v, src);
        return;
    }
    console.log('[vgLoadVideo] iniciando carga src=', src);

    /* Cancelar listeners de carga anterior si los hubiera */
    if (v._vgMetaHandler) {
        v.removeEventListener('loadedmetadata', v._vgMetaHandler);
        v._vgMetaHandler = null;
    }
    if (v._vgErrHandler) {
        v.removeEventListener('error', v._vgErrHandler);
        v._vgErrHandler = null;
    }

    /* Detener y limpiar completamente */
    v.pause();
    v.removeAttribute('src');
    v.load();

    setTimeout(function() {
        /* Definir handlers y guardar referencia para poder cancelarlos */
        v._vgMetaHandler = function() {
            v._vgMetaHandler = null;
            console.log('[vgLoadVideo] loadedmetadata OK → play()');
            v.play().catch(function(e) {
                console.warn('[vgLoadVideo] autoplay bloqueado:', e.message);
            });
        };
        v._vgErrHandler = function() {
            v._vgErrHandler = null;
            var err = v.error;
            console.error('[vgLoadVideo] error code=',
                err ? err.code : '?', 'msg=', err ? err.message : 'desconocido');
        };

        v.addEventListener('loadedmetadata', v._vgMetaHandler, { once: true });
        v.addEventListener('error',          v._vgErrHandler,  { once: true });

        v.src = src;
        v.load();
        console.log('[vgLoadVideo] src asignado=', v.src);
    }, 0);
};


/* ══════════════════════════════════════════════════════════════
   Hover preview — preview real de video en tarjetas del grid
   Estrategia: cargar stream con delay de 400ms tras mouseenter.
   faststart garantiza que el browser renderiza frames de inmediato
   sin descargar el archivo completo. Se detiene en mouseleave.
   ══════════════════════════════════════════════════════════════ */
(function () {
    document.addEventListener('DOMContentLoaded', function () {
        var grid = document.querySelector('.vg-grid');
        if (!grid) return;

        /* Timer de hover por tarjeta (evita carga en paso rápido del cursor) */
        var hoverTimers = new WeakMap();
        /* Flag para saber si ya se inició carga en esta tarjeta */
        var hoverLoaded = new WeakMap();

        /* ── Helpers ── */
        function _startHover(card) {
        var thumb = card.querySelector('.vg-thumb--hoverable');
        var video = thumb ? thumb.querySelector('video') : null;
        if (!video || !card.dataset.vsrc) return;

        if (hoverLoaded.get(card)) {
            video.currentTime = 0;
            video.play().catch(function () {});
            return;
        }

        thumb.classList.add('vg-loading');

        if (video._hoverCanPlay) {
            video.removeEventListener('canplay', video._hoverCanPlay);
            video._hoverCanPlay = null;
        }

        // Obtener URL firmada directamente para evitar el 302 cross-origin
        var resolveUrl = card.dataset.vsrc + '?direct=1';

        fetch(resolveUrl, {
            method: 'GET',
            redirect: 'manual',         // capturar el 302 sin seguirlo
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(resp) {
            // resp.type === 'opaqueredirect' cuando hay 302
            // En ese caso usamos la URL directamente (el <video> sí sigue redirects)
            return card.dataset.vsrc;
        })
        .catch(function() {
            return card.dataset.vsrc;
        })
        .then(function(src) {
            video._hoverCanPlay = function () {
                thumb.classList.remove('vg-loading');
                video.play().catch(function () {});
                hoverLoaded.set(card, true);
            };
            video.addEventListener('canplay', video._hoverCanPlay, { once: true });
            video.addEventListener('error', function () {
                thumb.classList.remove('vg-loading');
            }, { once: true });

            video.muted   = true;
            video.loop    = true;
            video.preload = 'auto';
            video.src     = src;
            video.load();
        });
    }


        function _stopHover(card) {
            var thumb = card.querySelector('.vg-thumb--hoverable');
            var video = thumb ? thumb.querySelector('video') : null;
            if (!video) return;

            thumb.classList.remove('vg-loading');
            video.pause();
            video.currentTime = 0;

            /* Limpiar src para liberar conexión de red */
            video.removeAttribute('src');
            video.load();
            hoverLoaded.set(card, false);

            /* Cancelar listener pendiente */
            if (video._hoverCanPlay) {
                video.removeEventListener('canplay', video._hoverCanPlay);
                video._hoverCanPlay = null;
            }
        }

        /* ── Mouseenter con delay de 400ms ── */
        grid.addEventListener('mouseover', function (e) {
            var card = e.target.closest('.vg-vcard');
            if (!card) return;
            /* Si ya hay un timer corriendo para esta tarjeta, no hacer nada */
            if (hoverTimers.get(card)) return;

            var t = setTimeout(function () {
                hoverTimers.set(card, null);
                _startHover(card);
            }, 400);
            hoverTimers.set(card, t);
        });

        /* ── Mouseleave: cancelar timer y detener preview ── */
        grid.addEventListener('mouseout', function (e) {
            var card = e.target.closest('.vg-vcard');
            if (!card) return;
            /* Solo procesar si el cursor salió completamente de la tarjeta */
            if (card.contains(e.relatedTarget)) return;

            var t = hoverTimers.get(card);
            if (t) {
                clearTimeout(t);
                hoverTimers.set(card, null);
            }
            _stopHover(card);
        });

        /* ── Click en la tarjeta: delegar en grid ── */
        grid.addEventListener('click', function (e) {
            if (e.target.closest('.vg-btn-action')) return;

            var card = e.target.closest('.vg-vcard');
            if (!card) return;

            /* Debounce: prevenir doble-disparo */
            if (grid._vgOpening) return;
            grid._vgOpening = true;
            setTimeout(function () { grid._vgOpening = false; }, 400);

            /* Detener hover preview y limpiar antes de abrir modal */
            _stopHover(card);

            var focusComment = !!e.target.closest('[data-focus-comment]');
            vgOpen(card, focusComment);
        });
    });
})();


/* ══════════════════════════════════════════════════════════════
   vgOpen — abrir modal desde tarjeta del grid
   ══════════════════════════════════════════════════════════════ */
window.vgOpen = function(card, focusComment) {
    var vid = card.dataset.vid;
    var src = card.dataset.vsrc;
    var cap = card.dataset.cap;
    if (!vid || !src) return;

    VG.vid     = vid;
    VG.ownerId = card.dataset.owner || '';
    VG.authId  = (document.querySelector('meta[name="auth-id"]') || {}).content || '';

    document.getElementById('vgm-title').textContent = cap;
    vgLoadVideo(document.getElementById('vgm-video'), src);
    document.getElementById('vgm').classList.add('open');
    vgLoadLikes();
    vgLoadComments();

    if (focusComment) {
        setTimeout(function() {
            var inp = document.getElementById('vgm-cinput');
            if (inp) inp.focus();
        }, 300);
    }

    /* Registrar vista (sin bloquear) */
    fetch('/videos/' + vid + '/view', {
        method  : 'POST',
        headers : { 'X-CSRF-TOKEN': VG.csrf, 'Accept': 'application/json' }
    }).catch(function() {});
};

/* ══════════════════════════════════════════════════════════════
   vgClose — cerrar modal y liberar recursos del video
   ══════════════════════════════════════════════════════════════ */
window.vgClose = function() {
    var v = document.getElementById('vgm-video');
    if (v) { v.pause(); v.removeAttribute('src'); v.load(); }
    document.getElementById('vgm').classList.remove('open');
    VG.vid = null;
};

/* ══════════════════════════════════════════════════════════════
   vgOpenById — abrir modal desde sidebar / últimos vistos
   ══════════════════════════════════════════════════════════════ */
window.vgOpenById = function(vid, cap, src) {
    if (!vid || !src) return;

    VG.vid     = String(vid);
    VG.ownerId = '';
    VG.authId  = (document.querySelector('meta[name="auth-id"]') || {}).content || '';

    document.getElementById('vgm-title').textContent = cap;
    vgLoadVideo(document.getElementById('vgm-video'), src);
    document.getElementById('vgm').classList.add('open');
    vgLoadLikes();
    vgLoadComments();

    fetch('/videos/' + vid + '/view', {
        method  : 'POST',
        headers : { 'X-CSRF-TOKEN': VG.csrf, 'Accept': 'application/json' }
    }).catch(function() {});
};

/* ── Cerrar modal al hacer click en el backdrop ── */
document.addEventListener('DOMContentLoaded', function() {
    var vgm = document.getElementById('vgm');
    if (vgm) {
        vgm.addEventListener('click', function(e) {
            if (e.target === this) vgClose();
        });
    }
});

/* ══════════════════════════════════════════════════════════════
   Likes
   ══════════════════════════════════════════════════════════════ */
window.vgLoadLikes = function() {
    if (!VG.vid) return;
    fetch('/videos/' + VG.vid + '/likes', { headers: { 'Accept': 'application/json' }})
    .then(function(r) { return r.json(); })
    .then(function(d) {
        document.getElementById('vgm-like-count').textContent = d.count || 0;
        document.getElementById('vgm-views-label').textContent =
            (d.views || 0) + ' reproducciones';
        var btn = document.getElementById('vgm-like-btn');
        btn.classList.toggle('liked', !!d.liked);
        var svg = btn.querySelector('svg');
        if (svg) svg.setAttribute('fill', d.liked ? 'currentColor' : 'none');
    }).catch(function() {});
};

window.vgLike = function(btn) {
    var vid = btn.dataset.vid;
    if (!vid) return;
    fetch('/videos/' + vid + '/like', {
        method  : 'POST',
        headers : {
            'X-CSRF-TOKEN'  : VG.csrf,
            'Accept'        : 'application/json',
            'Content-Type'  : 'application/json'
        },
        body: JSON.stringify({})
    }).then(function(r) { return r.json(); })
    .then(function(d) {
        var countEl = btn.querySelector('.vg-like-count');
        if (countEl) countEl.textContent = d.count || 0;
        btn.classList.toggle('liked', !!d.liked);
        var svg = btn.querySelector('svg');
        if (svg) svg.setAttribute('fill', d.liked ? 'currentColor' : 'none');
    }).catch(function() {});
};

window.vgLikeModal = function() {
    if (!VG.vid) return;
    fetch('/videos/' + VG.vid + '/like', {
        method  : 'POST',
        headers : {
            'X-CSRF-TOKEN'  : VG.csrf,
            'Accept'        : 'application/json',
            'Content-Type'  : 'application/json'
        },
        body: JSON.stringify({})
    }).then(function(r) { return r.json(); })
    .then(function(d) {
        document.getElementById('vgm-like-count').textContent = d.count || 0;
        var btn = document.getElementById('vgm-like-btn');
        btn.classList.toggle('liked', !!d.liked);
        var svg = btn.querySelector('svg');
        if (svg) svg.setAttribute('fill', d.liked ? 'currentColor' : 'none');
    }).catch(function() {});
};

/* ══════════════════════════════════════════════════════════════
   Comentarios
   ══════════════════════════════════════════════════════════════ */
window.vgLoadComments = function() {
    if (!VG.vid) return;
    fetch('/videos/' + VG.vid + '/comments', { headers: { 'Accept': 'application/json' }})
    .then(function(r) { return r.json(); })
    .then(function(d) {
        var list     = document.getElementById('vgm-comments-list');
        var comments = Array.isArray(d) ? d : (d.data || []);
        if (comments.length === 0) {
            list.innerHTML =
                '<p style="font-size:.8rem;color:var(--text-muted,#888)">Sin comentarios aún.</p>';
            return;
        }
        list.innerHTML = '';
        comments.forEach(function(c) { list.appendChild(vgMkComment(c)); });
    }).catch(function() {});
};

function vgMkAvatar(nick, avatarUrl) {
    var clrs = ['#e91e8c','#9c27b0','#3f51b5','#00bcd4','#ff5722'];
    var hsh  = nick.split('').reduce(function(a, b) { return a + b.charCodeAt(0); }, 0);
    var clr  = clrs[Math.abs(hsh) % clrs.length];
    var ini  = nick.charAt(0).toUpperCase();
    return avatarUrl
        ? '<img src="' + vgE(avatarUrl) +
          '" style="width:30px;height:30px;border-radius:50%;object-fit:cover;flex-shrink:0">'
        : '<div style="width:30px;height:30px;border-radius:50%;background:' + clr +
          ';display:flex;align-items:center;justify-content:center;font-weight:700;' +
          'font-size:.75rem;color:#fff;flex-shrink:0">' + ini + '</div>';
}

function vgMkComment(c) {
    var w      = document.createElement('div');
    w.className    = 'vgm-comment-item';
    w.dataset.cid  = c.id;
    var nick   = c.nickname || c.name || 'Usuario';
    var canDel = c.can_delete
        ? '<button class="vgm-comment-del" onclick="vgDelComment(' + c.id + ')">&#x2715;</button>'
        : '';
    var showReply = (VG.ownerId && VG.ownerId === VG.authId)
        ? '<button class="vgm-reply-btn" onclick="vgToggleReplyForm(' + c.id + ', this)">&#x21A9; Responder</button>'
        : '';

    w.innerHTML = vgMkAvatar(nick, c.avatar_url)
        + '<div class="vgm-comment-body" style="flex:1">'
        + '<b>' + vgE(nick) + '</b>'
        + '<p style="margin:0;line-height:1.5;font-size:.77rem">' + vgE(c.body) + '</p>'
        + '<div style="display:flex;align-items:center;gap:.5rem;margin-top:2px">'
        + '<span style="opacity:.4;font-size:.65rem">' + vgT(c.created_at) + '</span>'
        + showReply
        + '</div></div>' + canDel;

    var repliesDiv = document.createElement('div');
    repliesDiv.className = 'vgm-replies';
    repliesDiv.id        = 'replies-' + c.id;

    if (c.replies && c.replies.length > 0) {
        c.replies.forEach(function(r) {
            var rw     = document.createElement('div');
            rw.className   = 'vgm-comment-item';
            rw.style.paddingTop = '.3rem';
            var rnick  = r.nickname || r.name || 'Usuario';
            var rDel   = r.can_delete
                ? '<button class="vgm-comment-del" onclick="vgDelComment(' + r.id + ')">&#x2715;</button>'
                : '';
            rw.innerHTML = vgMkAvatar(rnick, r.avatar_url)
                + '<div class="vgm-comment-body" style="flex:1">'
                + '<b style="font-size:.78rem">' + vgE(rnick) + '</b>'
                + '<p style="margin:0;line-height:1.4;font-size:.75rem">' + vgE(r.body) + '</p>'
                + '<span style="opacity:.4;font-size:.63rem">' + vgT(r.created_at) + '</span>'
                + '</div>' + rDel;
            repliesDiv.appendChild(rw);
        });
    } else {
        repliesDiv.style.display = 'none';
    }
    w.appendChild(repliesDiv);
    return w;
}

function vgToggleReplyForm(cid, btn) {
    var existing = document.getElementById('reply-form-' + cid);
    if (existing) { existing.remove(); return; }

    var repliesDiv = document.getElementById('replies-' + cid);
    if (repliesDiv) repliesDiv.style.display = '';

    var form  = document.createElement('div');
    form.className = 'vgm-reply-form';
    form.id        = 'reply-form-' + cid;

    var inp = document.createElement('input');
    inp.type        = 'text';
    inp.placeholder = 'Escribe una respuesta...';
    inp.maxLength   = 500;
    inp.id          = 'reply-inp-' + cid;

    var rbtn = document.createElement('button');
    rbtn.innerHTML = '&#x27A4;';
    rbtn.onclick   = function() { vgSendReply(cid); };

    form.appendChild(inp);
    form.appendChild(rbtn);
    btn.closest('.vgm-comment-item').after(form);
    inp.focus();
}

function vgSendReply(cid) {
    var inp  = document.getElementById('reply-inp-' + cid);
    var body = inp ? inp.value.trim() : '';
    if (!body || !VG.vid) return;

    var rbtn = inp.nextElementSibling;
    rbtn.disabled = true;
    inp.disabled  = true;

    fetch('/videos/' + VG.vid + '/comments/' + cid + '/reply', {
        method  : 'POST',
        headers : {
            'X-CSRF-TOKEN'  : VG.csrf,
            'Content-Type'  : 'application/json',
            'Accept'        : 'application/json'
        },
        body: JSON.stringify({ body: body })
    }).then(function(r) { return r.json(); })
    .then(function(reply) {
        var repliesDiv = document.getElementById('replies-' + cid);
        if (repliesDiv) {
            repliesDiv.style.display = '';
            var rw     = document.createElement('div');
            rw.className   = 'vgm-comment-item';
            rw.style.paddingTop = '.3rem';
            var rnick  = reply.nickname || reply.name || 'Usuario';
            rw.innerHTML = vgMkAvatar(rnick, reply.avatar_url)
                + '<div class="vgm-comment-body" style="flex:1">'
                + '<b style="font-size:.78rem">' + vgE(rnick) + '</b>'
                + '<p style="margin:0;line-height:1.4;font-size:.75rem">'
                + vgE(reply.body) + '</p>'
                + '<span style="opacity:.4;font-size:.63rem">Ahora mismo</span>'
                + '</div>';
            repliesDiv.appendChild(rw);
        }
        var form = document.getElementById('reply-form-' + cid);
        if (form) form.remove();
    }).catch(function() {
        rbtn.disabled = false;
        inp.disabled  = false;
        inp.style.borderColor = '#ef4444';
        setTimeout(function() { inp.style.borderColor = ''; }, 2000);
    });
}

window.vgComment = function() {
    var inp  = document.getElementById('vgm-cinput');
    var btn  = inp ? inp.nextElementSibling : null;
    var body = inp ? inp.value.trim() : '';
    if (!body || !VG.vid || !btn) return;

    var orig      = btn.textContent;
    btn.textContent = 'Enviando...';
    btn.disabled  = true;
    inp.disabled  = true;

    fetch('/videos/' + VG.vid + '/comments', {
        method  : 'POST',
        headers : {
            'X-CSRF-TOKEN'  : VG.csrf,
            'Content-Type'  : 'application/json',
            'Accept'        : 'application/json'
        },
        body: JSON.stringify({ body: body })
    }).then(function(r) { return r.json(); })
    .then(function() {
        inp.value       = '';
        btn.textContent = orig;
        btn.disabled    = false;
        inp.disabled    = false;

        _vgFeedback('ok', '✔ Comentario enviado', inp);
        vgLoadComments();
        setTimeout(function() {
            var list = document.getElementById('vgm-comments-list');
            if (list) list.scrollTop = list.scrollHeight;
        }, 400);
    }).catch(function() {
        btn.textContent = orig;
        btn.disabled    = false;
        inp.disabled    = false;
        _vgFeedback('err', '✖ Error al enviar. Intenta de nuevo.', inp);
    });
};

function _vgFeedback(type, msg, refEl) {
    var fb = document.getElementById('vgm-comment-feedback');
    if (fb) fb.remove();
    fb            = document.createElement('div');
    fb.id         = 'vgm-comment-feedback';
    fb.className  = 'vgm-feedback ' + type;
    fb.textContent = msg;
    refEl.closest('.vgm-comment-form').insertAdjacentElement('afterend', fb);
    setTimeout(function() { if (fb.parentNode) fb.remove(); }, type === 'ok' ? 3000 : 4000);
}

function vgDelComment(cid) {
    if (!confirm('¿Eliminar comentario?')) return;
    fetch('/videos/' + VG.vid + '/comments/' + cid, {
        method  : 'DELETE',
        headers : { 'X-CSRF-TOKEN': VG.csrf, 'Accept': 'application/json' }
    }).then(function() { vgLoadComments(); }).catch(function() {});
}

/* ── Utilidades ── */
function vgE(s) {
    return String(s || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}
function vgT(ts) {
    if (!ts) return '';
    var d = new Date(String(ts).replace(' ', 'T'));
    return isNaN(d) ? ts : d.toLocaleDateString('es-MX', { day: '2-digit', month: 'short' });
}

/* ── Activar stubs ── */
window._vgReady = true;
</script>
@endpush
