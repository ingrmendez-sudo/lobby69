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
.vg-info { padding:.65rem .8rem .4rem; display:flex; flex-direction:column; gap:.25rem; }
.vg-card { position:relative; border-radius:10px; overflow:hidden; background:#111; cursor:pointer; aspect-ratio:16/9; }
.vg-card video { width:100%; height:100%; object-fit:cover; display:block; }
.vg-card-overlay { position:absolute; inset:0; background:linear-gradient(to top, rgba(0,0,0,.75) 0%, transparent 50%); pointer-events:none; }
.vg-card-info { position:absolute; bottom:0; left:0; right:0; padding:.5rem .7rem; }
.vg-caption {
    font-size:.9rem; font-weight:600; color:var(--text-main,#222);
    white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
}
.vg-meta { font-size:.78rem; color:var(--text-muted,#888); display:flex; align-items:center; gap:.4rem; }
.vg-meta img { width:22px; height:22px; border-radius:50%; object-fit:cover; }
.vg-actions {
    display:flex; align-items:center; gap:.7rem;
    padding:.4rem .8rem .6rem;
    border-top:1px solid var(--border-light,#f0f0f0);
}
.vg-btn-action {
    background:none; border:none; cursor:pointer;
    display:flex; align-items:center; gap:4px;
    font-size:.82rem; color:var(--text-muted,#888);
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
/* ── Modal ── */
#vgm { display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,.75); align-items:center; justify-content:center; }
#vgm.open { display:flex; }
.vgm-box {
    background:#1a1a2e; color:#eee; border-radius:12px;
    width:min(92vw,780px); max-height:92vh;
    display:flex; flex-direction:column; overflow:hidden; box-shadow:0 8px 40px rgba(0,0,0,.7);
    background:var(--card-bg,#fff); border-radius:16px; overflow:hidden;
    width:min(760px,94vw); max-height:88vh;
    display:flex; flex-direction:column; box-shadow:0 20px 60px rgba(0,0,0,.4);
}
.vgm-head { display:flex; align-items:center; justify-content:space-between; padding:.8rem 1.2rem; border-bottom:1px solid var(--border-light,#eee); }
.vgm-head h6 { margin:0; font-size:1rem; font-weight:600; }
.vgm-close { background:none; border:none; cursor:pointer; font-size:1.5rem; color:var(--text-muted,#888); line-height:1; }
#vgm-player { width:100%; max-height:50vh; background:#000; flex-shrink:0; }
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
@keyframes vg-spin { to { transform: rotate(360deg); } }
.vgm-feedback {
    font-size:.78rem; padding:.35rem .7rem; border-radius:8px; margin-top:.3rem;
    display:flex; align-items:center; gap:.4rem;
}
.vgm-feedback.ok  { background:#e8f5e9; color:#2e7d32; }
.vgm-feedback.err { background:#fce4ec; color:#c62828; }
</style>
@endpush

@push('sidebar-left')
@php
    $sideNick  = $userProfile->nickname    ?? ($user->name ?? 'Usuario');
    $sidePtype = ucfirst($userProfile->profile_type ?? '');
    $sideAv    = !empty($userProfile->avatar_url)
        ? (str_starts_with($userProfile->avatar_url,'http')
            ? $userProfile->avatar_url
            : asset('storage/'.ltrim($userProfile->avatar_url,'/')))
        : 'https://ui-avatars.com/api/?name='.urlencode($sideNick).'&background=e91e8c&color=fff&size=80&bold=true';
@endphp

{{-- Tarjeta usuario --}}
<div style="background:var(--card-bg,#fff);border-radius:14px;padding:1.1rem;box-shadow:0 2px 10px rgba(0,0,0,.07);margin-bottom:1rem">
    <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:.9rem">
        <img src="{{ $sideAv }}"
             onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($sideNick) }}&background=e91e8c&color=fff&size=80'"
             style="width:52px;height:52px;border-radius:50%;object-fit:cover;flex-shrink:0;border:2px solid var(--bs-pink,#e91e8c)">
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
    <div style="display:flex;gap:.5rem;align-items:center;margin-bottom:.6rem">
        <div style="width:52px;height:30px;border-radius:5px;overflow:hidden;flex-shrink:0;background:#111">
            @if($lwThumb)
            <img src="{{ $lwThumb }}" style="width:100%;height:100%;object-fit:cover">
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
            $pvAv      = !empty($pvAvatar)
                ? (str_starts_with($pvAvatar,"http") ? $pvAvatar : asset("storage/".ltrim($pvAvatar,"/")))
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
        <div style="display:flex;align-items:center;gap:.55rem;padding:.4rem .5rem;border-radius:8px;transition:background .15s"
             onmouseover="this.style.background=&apos;rgba(233,30,140,.08)&apos;"
             onmouseout="this.style.background=&apos;transparent&apos;">
            @if($pvAv)
                <img src="{{ $pvAv }}"
                     onerror="this.style.display=&apos;none&apos;"
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
        </div>
        @endforeach
    </div>
</div>
@endif
@endpush

@push('sidebar-right')

{{-- Mi actividad --}}
@if($myActivity->count())
<div style="background:var(--card-bg,#fff);border-radius:14px;padding:1rem;box-shadow:0 2px 10px rgba(0,0,0,.07);margin-bottom:1rem">
    <div style="font-weight:700;font-size:.85rem;margin-bottom:.7rem;color:var(--text-main,#222)">
        ⚡ Mi actividad
    </div>
    @foreach($myActivity as $act)
    @php
        $actAv   = !empty($act->avatar_url)
            ? (str_starts_with($act->avatar_url,'http') ? $act->avatar_url : asset('storage/'.ltrim($act->avatar_url,'/')))
            : null;
        $actInit  = strtoupper(substr($act->nickname ?? 'U', 0, 1));
        $actClrs  = ['#e91e8c','#9c27b0','#3f51b5','#00bcd4','#ff5722'];
        $actColor = $actClrs[abs(crc32($act->nickname ?? 'x')) % count($actClrs)];
        $actIcon = $act->type === 'like' ? '❤️' : '💬';
        $actText = $act->type === 'like' ? 'le dio like a' : 'comentó en';
    @endphp
    <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.55rem">
        <img src="{{ $actAv }}"
             onerror="this.src='https://ui-avatars.com/api/?name=U&background=888&color=fff&size=32'"
             style="width:28px;height:28px;border-radius:50%;object-fit:cover;flex-shrink:0">
        <div style="flex:1;min-width:0;font-size:.75rem;color:var(--text-muted,#888)">
            <span style="font-weight:600;color:var(--text-main,#222)">{{ $act->nickname }}</span>
            {{ $actIcon }} {{ $actText }}
            <span style="color:var(--bs-pink,#e91e8c)">"{{ Str::limit($act->caption ?? 'un video', 20) }}"</span>
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- Mis últimos 5 videos --}}
<div style="background:var(--card-bg,#fff);border-radius:14px;padding:1rem;box-shadow:0 2px 10px rgba(0,0,0,.07);margin-bottom:1rem">
    <div style="font-weight:700;font-size:.85rem;margin-bottom:.7rem;color:var(--text-main,#222)">
        📹 Mis últimos videos
    </div>
    @forelse($myLatestVideos as $mv)
    @php
        $mvThumb = !empty($mv->thumbnail_path)
            ? (str_starts_with($mv->thumbnail_path,'http') ? $mv->thumbnail_path : asset('storage/'.ltrim($mv->thumbnail_path,'/')))
            : null;
    @endphp
    <div style="display:flex;gap:.5rem;align-items:center;margin-bottom:.6rem">
        <div style="width:56px;height:34px;border-radius:6px;overflow:hidden;flex-shrink:0;background:#111">
            @if($mvThumb)
            <img src="{{ $mvThumb }}" style="width:100%;height:100%;object-fit:cover">
            @else
            <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center">
                <svg width="14" height="14" fill="none" stroke="#666" stroke-width="2" viewBox="0 0 24 24"><polygon points="5 3 19 12 5 21 5 3"/></svg>
            </div>
            @endif
        </div>
        <div style="flex:1;min-width:0">
            <div style="font-size:.78rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:var(--text-main,#222)">
                {{ $mv->caption ?? 'Sin título' }}
            </div>
            <div style="font-size:.7rem;color:var(--text-muted,#888)">
                <svg width="11" height="11" fill="currentColor" viewBox="0 0 24 24" style="vertical-align:middle"><path d="M12 4.5C7 4.5 2.7 7.6 1 12c1.7 4.4 6 7.5 11 7.5s9.3-3.1 11-7.5C21.3 7.6 17 4.5 12 4.5zm0 12.5a5 5 0 110-10 5 5 0 010 10zm0-8a3 3 0 100 6 3 3 0 000-6z"/></svg>
                {{ number_format($mv->views_count ?? 0) }} vistas
            </div>
        </div>
    </div>
    @empty
    <p style="font-size:.8rem;color:var(--text-muted,#888);margin:0">Sin videos publicados aún.</p>
    @endforelse
</div>

{{-- Globo verificación pendiente --}}
@if(!$isVerified)
<div style="background:linear-gradient(135deg,#fff8e1,#fff3cd);border:1px solid #ffc107;border-radius:14px;padding:1rem;box-shadow:0 2px 10px rgba(255,193,7,.15);margin-bottom:1rem">
    <div style="font-weight:700;font-size:.85rem;margin-bottom:.4rem;color:#856404">
        @if($pendingVerification)
        ⏳ Verificación en proceso
        @else
        ✅ Verifica tu perfil
        @endif
    </div>
    <p style="font-size:.78rem;color:#856404;margin:0 0 .6rem">
        @if($pendingVerification)
        Tu solicitud está siendo revisada. Te notificaremos cuando esté lista.
        @else
        Los perfiles verificados generan más confianza y visibilidad en la comunidad.
        @endif
    </p>
    @if(!$pendingVerification)
    <a href="/verificacion" style="display:inline-block;background:#ffc107;color:#212529;border-radius:8px;padding:4px 12px;font-size:.78rem;font-weight:600;text-decoration:none">
        Verificar ahora →
    </a>
    @endif
</div>
@endif

{{-- Globo anuncios --}}
@if($announcements->count())
<div style="background:var(--card-bg,#fff);border-radius:14px;padding:1rem;box-shadow:0 2px 10px rgba(0,0,0,.07);margin-bottom:1rem">
    <div style="font-weight:700;font-size:.85rem;margin-bottom:.7rem;color:var(--text-main,#222)">
        📢 Anuncios recientes
    </div>
    @foreach($announcements as $ann)
    @php
        $annAv    = !empty($ann->avatar_url)
            ? (str_starts_with($ann->avatar_url,'http') ? $ann->avatar_url : asset('storage/'.ltrim($ann->avatar_url,'/')))
            : null;
        $annInit  = strtoupper(substr($ann->nickname ?? 'U', 0, 1));
        $annClrs  = ['#e91e8c','#9c27b0','#3f51b5','#00bcd4','#ff5722'];
        $annColor = $annClrs[abs(crc32($ann->nickname ?? 'x')) % count($annClrs)];
    @endphp
    <div style="border-bottom:1px solid var(--border-light,#f0f0f0);padding-bottom:.6rem;margin-bottom:.6rem">
        <div style="display:flex;align-items:center;gap:.4rem;margin-bottom:.3rem">
            <img src="{{ $annAv }}"
                 onerror="this.src='https://ui-avatars.com/api/?name=U&background=e91e8c&color=fff&size=32'"
                 style="width:24px;height:24px;border-radius:50%;object-fit:cover">
            <span style="font-size:.78rem;font-weight:600;color:var(--text-main,#222)">{{ $ann->nickname }}</span>
            <span style="font-size:.7rem;color:var(--text-muted,#888);margin-left:auto">{{ \Carbon\Carbon::parse($ann->event_date)->format('d M') }}</span>
        </div>
        <div style="font-size:.8rem;font-weight:600;color:var(--text-main,#222)">{{ $ann->title }}</div>
        <div style="font-size:.75rem;color:var(--text-muted,#888);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
            {{ Str::limit($ann->proposal, 60) }}
        </div>
    </div>
    @endforeach
</div>
@endif

@endpush

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
    <h5 class="mb-0 fw-bold">🎬 Galería de Videos</h5>
    <span class="text-muted" style="font-size:.85rem">{{ $videos->total() }} videos</span>
</div>

<div class="vg-grid">
@forelse($videos as $video)
@php
    $thumbSrc = !empty($video->thumbnail_path)
        ? (str_starts_with($video->thumbnail_path,'http') ? $video->thumbnail_path : asset('storage/'.ltrim($video->thumbnail_path,'/')))
        : null;
    $videoSrc = !empty($video->file_path)
        ? (str_starts_with($video->file_path,'http') ? $video->file_path : asset('storage/'.ltrim($video->file_path,'/')))
        : null;
    $dur = $video->duration_seconds ?? 0;
    $durFmt = $dur > 0 ? sprintf('%d:%02d', intdiv($dur,60), $dur%60) : '0:00';
    $cap = $video->caption ?? 'Sin título';
    $nick = $video->nickname ?? 'Usuario';
    $avSrc = !empty($video->avatar_url)
        ? (str_starts_with($video->avatar_url,'http') ? $video->avatar_url : asset('storage/'.ltrim($video->avatar_url,'/')))
        : 'https://ui-avatars.com/api/?name='.urlencode($nick).'&size=40&background=e91e8c&color=fff';
    $isLiked = in_array($video->id, $likedIds ?? []);
    $likes = $video->likes_count ?? 0;
    $comms = $video->comments_count ?? 0;
    $views = $video->views_count ?? 0;
@endphp
<div class="vg-vcard"
     data-vid="{{ $video->id }}"
     data-vsrc="{{ route('videos.stream', $video->id) }}"
     data-cap="{{ e($cap) }}"
     data-nick="{{ e($nick) }}"
     data-av="{{ $avSrc }}">
    <div class="vg-thumb"
         onmouseenter="vgHover(this,true)"
         onmouseleave="vgHover(this,false)"
         onclick="vgOpen(this.closest('.vg-vcard'))">
        <video preload="none" muted playsinline
               @if($thumbSrc) poster="{{ $thumbSrc }}" @endif
               src="{{ route('videos.stream', $video->id) }}">
        </video>
        <div class="vg-play-icon">
            <svg viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
        </div>
        <span class="vg-duration">{{ $durFmt }}</span>
        <span class="vg-views">
            <svg width="13" height="13" fill="currentColor" viewBox="0 0 24 24"><path d="M12 4.5C7 4.5 2.7 7.6 1 12c1.7 4.4 6 7.5 11 7.5s9.3-3.1 11-7.5C21.3 7.6 17 4.5 12 4.5zm0 12.5a5 5 0 110-10 5 5 0 010 10zm0-8a3 3 0 100 6 3 3 0 000-6z"/></svg>
            {{ number_format($views) }}
        </span>
    </div>
    <div class="vg-info" onclick="vgOpen(this.closest('.vg-vcard'))">
        <div class="vg-caption">{{ $cap }}</div>
        <div class="vg-meta">
            <img src="{{ $avSrc }}"
                 onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($nick) }}&size=40&background=e91e8c&color=fff'"
                 alt="{{ e($nick) }}">
            <span>{{ $nick }}</span>
        </div>
    </div>
    <div class="vg-actions">
        <button class="vg-btn-action{{ $isLiked ? ' liked' : '' }}"
                data-vid="{{ $video->id }}"
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

{{-- ── Modal reproductor ── --}}

@push('scripts')
<script>
var VG = {
    vid: null,
    csrf: document.querySelector('meta[name="csrf-token"]')?.content || '',
    hoverTimers: new WeakMap()
};

function vgHover(thumb, entering) {
    var video = thumb.querySelector('video');
    if (!video) return;
    if (entering) {
        video.currentTime = 0;
        var p = video.play();
        if (p) p.catch(function(){});
        var t = setTimeout(function() {
            video.pause();
            video.currentTime = 0;
        }, 3000);
        VG.hoverTimers.set(thumb, t);
    } else {
        var t = VG.hoverTimers.get(thumb);
        if (t) clearTimeout(t);
        video.pause();
        video.currentTime = 0;
    }
}

function vgOpen(card, focusComment) {
    var vid = card.dataset.vid;
    var src = card.dataset.vsrc;
    var cap = card.dataset.cap;
    VG.vid = vid;
    document.getElementById('vgm-title').textContent = cap;
    var v = document.getElementById('vgm-video');
    v.src = src;
    v.load();
    v.play().catch(function(){});
    document.getElementById('vgm').classList.add('open');
    vgLoadLikes();
    vgLoadComments();
    if (focusComment) setTimeout(function(){ document.getElementById('vgm-cinput').focus(); }, 300);
    fetch('/videos/' + vid + '/view', { method: 'POST', headers: { 'X-CSRF-TOKEN': VG.csrf, 'Accept': 'application/json' }}).catch(function(){});
}

function vgClose() {
    var v = document.getElementById('vgm-video');
    v.pause(); v.src = '';
    document.getElementById('vgm').classList.remove('open');
    VG.vid = null;
}

document.getElementById('vgm').addEventListener('click', function(e) {
    if (e.target === this) vgClose();
});

function vgLoadLikes() {
    if (!VG.vid) return;
    fetch('/videos/' + VG.vid + '/likes', { headers: { 'Accept': 'application/json' }})
    .then(function(r){ return r.json(); })
    .then(function(d) {
        document.getElementById('vgm-like-count').textContent = d.count || 0;
        document.getElementById('vgm-views-label').textContent = (d.views || 0) + ' reproducciones';
        var btn = document.getElementById('vgm-like-btn');
        btn.classList.toggle('liked', !!d.liked);
        var svg = btn.querySelector('svg');
        if (svg) svg.setAttribute('fill', d.liked ? 'currentColor' : 'none');
    }).catch(function(){});
}

function vgLike(btn) {
    var vid = btn.dataset.vid;
    if (!vid) return;
    fetch('/videos/' + vid + '/likes', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': VG.csrf, 'Accept': 'application/json' }
    }).then(function(r){ return r.json(); })
    .then(function(d) {
        var countEl = btn.querySelector('.vg-like-count');
        if (countEl) countEl.textContent = d.count || 0;
        btn.classList.toggle('liked', !!d.liked);
        var svg = btn.querySelector('svg');
        if (svg) svg.setAttribute('fill', d.liked ? 'currentColor' : 'none');
    }).catch(function(){});
}

function vgLikeModal() {
    if (!VG.vid) return;
    fetch('/videos/' + VG.vid + '/likes', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': VG.csrf, 'Accept': 'application/json' }
    }).then(function(r){ return r.json(); })
    .then(function(d) {
        document.getElementById('vgm-like-count').textContent = d.count || 0;
        var btn = document.getElementById('vgm-like-btn');
        btn.classList.toggle('liked', !!d.liked);
        var svg = btn.querySelector('svg');
        if (svg) svg.setAttribute('fill', d.liked ? 'currentColor' : 'none');
    }).catch(function(){});
}

function vgLoadComments() {
    if (!VG.vid) return;
    fetch('/videos/' + VG.vid + '/comments', { headers: { 'Accept': 'application/json' }})
    .then(function(r){ return r.json(); })
    .then(function(d) {
        var list = document.getElementById('vgm-comments-list');
        var comments = Array.isArray(d) ? d : (d.data || []);
        if (comments.length === 0) {
            list.innerHTML = '<p style="font-size:.8rem;color:var(--text-muted,#888)">Sin comentarios aún.</p>';
            return;
        }
        list.innerHTML = '';
        comments.forEach(function(c) { list.appendChild(vgMkComment(c)); });
    }).catch(function(){});
}

function vgMkComment(c) {
    var w = document.createElement('div');
    w.className = 'vgm-comment-item';
    var nick   = c.nickname || 'Usuario';
    var clrs   = ['#e91e8c','#9c27b0','#3f51b5','#00bcd4','#ff5722'];
    var hsh    = nick.split('').reduce(function(a,b){return a+b.charCodeAt(0);},0);
    var clr    = clrs[Math.abs(hsh) % clrs.length];
    var ini    = nick.charAt(0).toUpperCase();
    var avHtml = c.avatar_url
        ? '<img src="' + vgE(c.avatar_url) + '" style="width:30px;height:30px;border-radius:50%;object-fit:cover;flex-shrink:0">'
        : '<div style="width:30px;height:30px;border-radius:50%;background:' + clr + ';display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.75rem;color:#fff;flex-shrink:0">' + ini + '</div>';
    var canDel = c.can_delete ? '<button class="vgm-comment-del" onclick="vgDelComment(' + c.id + ')">&#x2715;</button>' : '';
    w.innerHTML = avHtml
        + '<div class="vgm-comment-body" style="flex:1">'
        + '<b>' + vgE(nick) + '</b>'
        + '<p style="margin:0;line-height:1.5;font-size:.77rem">' + vgE(c.body) + '</p>'
        + '<span style="opacity:.4;font-size:.65rem">' + vgT(c.created_at) + '</span>'
        + '</div>' + canDel;
    return w;
}

function vgComment() {
    var inp = document.getElementById('vgm-cinput');
    var btn = inp.nextElementSibling;
    var body = inp.value.trim();
    if (!body || !VG.vid) return;
    var orig = btn.textContent;
    btn.textContent = 'Enviando...';
    btn.disabled = true;
    inp.disabled = true;
    fetch('/videos/' + VG.vid + '/comments', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': VG.csrf, 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ body: body })
    }).then(function(r){ return r.json(); })
    .then(function(d) {
        inp.value = "";
        btn.textContent = orig;
        btn.disabled = false;
        inp.disabled = false;
        // Feedback exito
        var fb = document.getElementById("vgm-comment-feedback");
        if (fb) fb.remove();
        fb = document.createElement("div");
        fb.id = "vgm-comment-feedback";
        fb.className = "vgm-feedback ok";
        fb.textContent = "\u2714 Comentario enviado";
        inp.closest(".vgm-comment-form").insertAdjacentElement("afterend", fb);
        setTimeout(function(){ if (fb.parentNode) fb.remove(); }, 3000);
        // Recargar y scroll
        vgLoadComments();
        setTimeout(function(){
            var list = document.getElementById("vgm-comments-list");
            if (list) list.scrollTop = list.scrollHeight;
        }, 400);
    })
    .catch(function(){
        btn.textContent = orig;
        btn.style.background = "";
        btn.disabled = false;
        inp.disabled = false;
        var fb = document.getElementById("vgm-comment-feedback");
        if (fb) fb.remove();
        fb = document.createElement("div");
        fb.id = "vgm-comment-feedback";
        fb.className = "vgm-feedback err";
        fb.textContent = "\u2716 Error al enviar. Intenta de nuevo.";
        inp.closest(".vgm-comment-form").insertAdjacentElement("afterend", fb);
        setTimeout(function(){ if (fb.parentNode) fb.remove(); }, 4000);
    });
}

function vgDelComment(cid) {
    if (!confirm('Eliminar comentario?')) return;
    fetch('/videos/' + VG.vid + '/comments/' + cid, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': VG.csrf, 'Accept': 'application/json' }
    }).then(function(){ vgLoadComments(); }).catch(function(){});
}

function vgE(s) {
    return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function vgT(ts) {
    if (!ts) return '';
    var d = new Date(String(ts).replace(' ', 'T'));
    return isNaN(d) ? ts : d.toLocaleDateString('es-MX', { day: '2-digit', month: 'short' });
}
</script>
@endpush

