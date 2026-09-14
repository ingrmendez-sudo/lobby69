@extends('layouts.app')

@section('title', 'Anuncios — LOBBY69')

@push('sidebar-left')
    @include('layouts.sidebar-left')
@endpush
@push('sidebar-right')
    @include('layouts.sidebar-right')
@endpush

@push('styles')
<style>
.an-header { margin-bottom: 1.5rem; }
.an-header h1 { font-size:1.5rem; font-weight:800; color:var(--theme-text); margin:0 0 .25rem; }
.an-header p  { color:var(--theme-muted); margin:0; font-size:.9rem; }

.an-tabs {
    display:flex; gap:.5rem; margin-bottom:1.5rem;
    border-bottom:2px solid var(--theme-border); padding-bottom:0;
}
.an-tab {
    padding:.55rem 1.2rem; border-radius:8px 8px 0 0;
    border:none; background:none; font-size:.875rem; font-weight:600;
    color:var(--theme-muted); cursor:pointer; transition:all .2s;
    border-bottom:3px solid transparent; margin-bottom:-2px;
}
.an-tab.active { color:#e056a0; border-bottom-color:#e056a0; }
.an-tab:hover:not(.active) { color:var(--theme-text); background:var(--theme-surface-2); }
.an-tab-content { display:none; }
.an-tab-content.active { display:block; }

.an-grid {
    display:grid;
    grid-template-columns:repeat(auto-fill, minmax(280px,1fr));
    gap:1rem;
}
.an-card {
    background:var(--theme-card); border:1px solid var(--theme-border);
    border-radius:16px; overflow:hidden;
    transition:transform .18s, box-shadow .18s; cursor:pointer;
}
.an-card:hover { transform:translateY(-3px); box-shadow:0 8px 24px rgba(0,0,0,.18); }
.an-card.expired { opacity:.55; }
.an-card__header { display:flex; align-items:center; gap:.75rem; padding:1rem 1rem .75rem; }
.an-card__avatar { width:48px; height:48px; border-radius:50%; object-fit:cover; flex-shrink:0; border:2px solid rgba(224,86,160,.3); }
.an-card__avatar-placeholder {
    width:48px; height:48px; border-radius:50%; flex-shrink:0;
    background:linear-gradient(135deg,#e056a0,#7c3aed);
    display:flex; align-items:center; justify-content:center;
    font-size:1.1rem; color:#fff; font-weight:700;
}
.an-card__info { min-width:0; flex:1; }
.an-card__name { font-weight:700; font-size:.9rem; color:var(--theme-text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.an-card__meta { font-size:.75rem; color:var(--theme-muted); }
.an-card__body { padding:0 1rem 1rem; }
.an-card__title { font-size:.95rem; font-weight:700; color:var(--theme-text); margin:0 0 .5rem; line-height:1.35; }
.an-card__desc { font-size:.82rem; color:var(--theme-muted); margin:0 0 .75rem; line-height:1.5; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
.an-tags { display:flex; flex-wrap:wrap; gap:.35rem; margin-bottom:.75rem; }
.an-tag { font-size:.7rem; font-weight:600; padding:.2rem .55rem; border-radius:20px; white-space:nowrap; }
.an-tag--pink   { background:rgba(224,86,160,.12); color:#e056a0; border:1px solid rgba(224,86,160,.25); }
.an-tag--purple { background:rgba(124,58,237,.12); color:#a78bfa; border:1px solid rgba(124,58,237,.25); }
.an-tag--green  { background:rgba(46,204,113,.1);  color:#2ecc71; border:1px solid rgba(46,204,113,.25); }
.an-card__footer {
    display:flex; align-items:center; justify-content:space-between;
    padding:.65rem 1rem; border-top:1px solid var(--theme-border);
    font-size:.72rem; color:var(--theme-muted);
}
.an-card__cta {
    padding:.35rem .9rem; border-radius:8px;
    background:linear-gradient(135deg,#e056a0,#7c3aed);
    color:#fff; font-size:.78rem; font-weight:600;
    border:none; cursor:pointer; text-decoration:none; transition:opacity .2s;
}
.an-card__cta:hover { opacity:.85; color:#fff; }

.an-btn-create {
    display:inline-flex; align-items:center; gap:.5rem;
    padding:.6rem 1.4rem; border-radius:10px;
    background:linear-gradient(135deg,#e056a0,#7c3aed);
    color:#fff; font-weight:700; font-size:.875rem;
    border:none; cursor:pointer; text-decoration:none;
    transition:opacity .2s; margin-bottom:1.25rem;
}
.an-btn-create:hover { opacity:.88; color:#fff; }

.an-upgrade-notice {
    display:inline-flex; align-items:center; gap:.6rem;
    padding:.65rem 1.2rem; border-radius:10px;
    background:rgba(212,175,55,.08); border:1px dashed rgba(212,175,55,.3);
    margin-bottom:1.25rem;
}

.an-empty {
    text-align:center; padding:4rem 2rem;
    background:var(--theme-surface-2);
    border-radius:16px; border:1px solid rgba(180,60,120,.12);
}
.an-empty i { font-size:2.5rem; color:rgba(224,86,160,.3); display:block; margin-bottom:1rem; }
.an-empty p  { color:var(--theme-muted); margin:0; }

.an-my-card {
    background:var(--theme-card); border:1px solid var(--theme-border);
    border-radius:12px; padding:1rem 1.25rem;
    display:flex; align-items:flex-start; justify-content:space-between; gap:1rem;
    margin-bottom:.75rem;
}
.an-my-card.expired { opacity:.55; }
.an-my-card__title { font-weight:700; font-size:.9rem; color:var(--theme-text); margin:0 0 .3rem; }
.an-my-card__meta  { font-size:.78rem; color:var(--theme-muted); }
.an-my-card__close {
    padding:.3rem .75rem; border-radius:7px;
    border:1px solid rgba(231,76,60,.4); color:#e74c3c;
    background:transparent; font-size:.78rem; cursor:pointer; white-space:nowrap; flex-shrink:0;
}

.an-modal-backdrop {
    display:none; position:fixed; inset:0;
    background:rgba(0,0,0,.65); z-index:1050;
    align-items:center; justify-content:center; padding:1rem;
}
.an-modal-backdrop.open { display:flex; }
.an-modal {
    background:var(--theme-card); border:1px solid var(--theme-border);
    border-radius:18px; width:100%; max-width:520px;
    max-height:90vh; overflow-y:auto; padding:2rem; position:relative;
}
.an-modal h2 { font-size:1.2rem; font-weight:800; color:var(--theme-text); margin:0 0 1.5rem; }
.an-modal__close {
    position:absolute; top:1rem; right:1rem;
    background:none; border:none; font-size:1.3rem;
    color:var(--theme-muted); cursor:pointer; line-height:1;
}
.an-field { margin-bottom:1.1rem; }
.an-field label { display:block; font-size:.85rem; font-weight:600; color:var(--theme-text); margin-bottom:.4rem; }
.an-field input, .an-field textarea {
    width:100%; padding:.6rem .85rem;
    background:var(--theme-card); color:var(--theme-text);
    border:1px solid var(--theme-border); border-radius:9px;
    font-size:.875rem; box-sizing:border-box;
}
.an-field textarea { resize:vertical; min-height:80px; }
.an-checkbox-group { display:flex; flex-wrap:wrap; gap:.5rem; }
.an-checkbox-item { display:flex; align-items:center; gap:.35rem; }
.an-checkbox-item label { font-size:.82rem; color:var(--theme-text); font-weight:400; margin:0; cursor:pointer; }
.an-submit {
    width:100%; padding:.75rem; border-radius:10px;
    background:linear-gradient(135deg,#e056a0,#7c3aed);
    color:#fff; font-weight:700; font-size:.95rem;
    border:none; cursor:pointer; margin-top:.5rem; transition:opacity .2s;
}
.an-submit:hover { opacity:.88; }
@media (max-width:640px) { .an-grid { grid-template-columns:1fr; } }
</style>
@endpush

@section('content')

@php
    $authUser = auth()->user();
    $tier     = app(\App\Services\MembershipAccessService::class)->tier($authUser);
    $isAdmin  = app(\Illuminate\Contracts\Auth\Access\Gate::class)->allows('admin');
    $canPost  = $isAdmin || !in_array($tier, ['invitado', 'explorer']);

    $directedLabels = [
        'singles'   => 'Singles',
        'parejas'   => 'Parejas',
        'unicornio' => 'Unicornio',
        'hombres'   => 'Hombres',
        'mujeres'   => 'Mujeres',
    ];
    $lookingLabels = [
        'intercambios'   => 'Intercambios',
        'cuckold'        => 'Cuckold',
        'trio_mhm'       => 'Trío MHM',
        'trio_mmh'       => 'Trío MMH',
        'exhibicionismo' => 'Exhibicionismo',
        'voyeurismo'     => 'Voyeurismo',
        'bdsm'           => 'BDSM',
        'solo_charlar'   => 'Solo charlar',
    ];
    $supabaseUrl = config('services.supabase.url');
    $bucket      = 'photos';
@endphp

<div class="an-header">
    <h1>📣 Anuncios</h1>
    <p>Miembros buscando plan para hoy — anuncios activos de la comunidad</p>
</div>

<div class="an-tabs">
    <button class="an-tab active" data-tab="todos">
        Todos
        <span style="background:rgba(224,86,160,.15);color:#e056a0;font-size:.7rem;padding:.1rem .45rem;border-radius:10px;margin-left:.35rem;">{{ $announcements->count() }}</span>
    </button>
    <button class="an-tab" data-tab="mis-anuncios">
        Mis anuncios
        <span style="background:rgba(124,58,237,.15);color:#a78bfa;font-size:.7rem;padding:.1rem .45rem;border-radius:10px;margin-left:.35rem;">{{ $myAnnouncements->count() }}</span>
    </button>
</div>

{{-- ── TAB TODOS ── --}}
<div class="an-tab-content active" id="tab-todos">

    @if($canPost)
        <button class="an-btn-create" onclick="document.getElementById('modalCrear').classList.add('open')">
            <i class="fas fa-plus"></i> Publicar anuncio
        </button>
    @else
        <div class="an-upgrade-notice">
            <i class="fas fa-lock" style="color:#d4af37;"></i>
            <span style="font-size:.875rem;color:var(--theme-muted);">Solo miembros con membresía de pago pueden publicar.</span>
            <a href="/membresias" style="color:#d4af37;font-weight:700;font-size:.825rem;text-decoration:none;">Ver planes →</a>
        </div>
    @endif

    @if($announcements->isEmpty())
        <div class="an-empty">
            <i class="fas fa-bullhorn"></i>
            <p>No hay anuncios activos en este momento.<br>¡Sé el primero en publicar!</p>
        </div>
    @else
        <div class="an-grid">
            @foreach($announcements as $a)
            @php
                $avatarUrl = $a->avatar_photo_id
                    ? "{$supabaseUrl}/storage/v1/object/public/{$bucket}/{$a->user_id}/{$a->avatar_photo_id}.jpg"
                    : null;
                $initial = strtoupper(substr($a->display_name ?? 'U', 0, 1));
            @endphp
            <div class="an-card {{ $a->is_expired ? 'expired' : '' }}">
                <div class="an-card__header">
                    @if($avatarUrl)
                        <img src="{{ $avatarUrl }}" alt="{{ $a->display_name }}" class="an-card__avatar"
                             onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                        <div class="an-card__avatar-placeholder" style="display:none;">{{ $initial }}</div>
                    @else
                        <div class="an-card__avatar-placeholder">{{ $initial }}</div>
                    @endif
                    <div class="an-card__info">
                        <div class="an-card__name">{{ $a->display_name ?? $a->nickname ?? 'Miembro' }}</div>
                        <div class="an-card__meta">
                            @if($a->profile_type){{ ucfirst($a->profile_type) }}@endif
                            @if($a->city) · {{ $a->city }}@endif
                            @if($a->verified_profile) · <span style="color:#2ecc71;">✓</span>@endif
                        </div>
                    </div>
                </div>
                <div class="an-card__body">
                    <div class="an-card__title">{{ $a->title }}</div>
                    @if($a->looking_for)
                        <div class="an-card__desc">{{ $a->looking_for }}</div>
                    @endif
                    <div class="an-tags">
                        @foreach(($a->directed_to ?? []) as $d)
                            <span class="an-tag an-tag--pink">{{ $directedLabels[$d] ?? $d }}</span>
                        @endforeach
                        @foreach(($a->what_looking ?? []) as $w)
                            <span class="an-tag an-tag--purple">{{ $lookingLabels[$w] ?? $w }}</span>
                        @endforeach
                        @if($a->event_date)
                            <span class="an-tag an-tag--green">
                                <i class="fas fa-calendar" style="font-size:.65rem;"></i>
                                {{ \Carbon\Carbon::parse($a->event_date)->format('d M') }}
                            </span>
                        @endif
                    </div>
                </div>
                <div class="an-card__footer">
                    <span><i class="fas fa-clock" style="margin-right:.3rem;"></i>{{ \Carbon\Carbon::parse($a->created_at)->diffForHumans() }}</span>
                    <a href="{{ route('messages.index') }}?open={{ $a->user_id }}" class="an-card__cta">
                        <i class="fas fa-comment" style="margin-right:.3rem;"></i>Responder
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    @endif

</div>

{{-- ── TAB MIS ANUNCIOS ── --}}
<div class="an-tab-content" id="tab-mis-anuncios">

    @if($canPost)
        <button class="an-btn-create" onclick="document.getElementById('modalCrear').classList.add('open')">
            <i class="fas fa-plus"></i> Publicar nuevo anuncio
        </button>
    @endif

    @if($myAnnouncements->isEmpty())
        <div class="an-empty">
            <i class="fas fa-megaphone"></i>
            <p>No tienes anuncios publicados aún.</p>
        </div>
    @else
        @foreach($myAnnouncements as $a)
        @php
            $expires   = $a->expires_at ? \Carbon\Carbon::parse($a->expires_at) : \Carbon\Carbon::parse($a->created_at)->addDays(4);
            $hoursLeft = max(0, round(\Carbon\Carbon::now()->diffInHours($expires, false)));
        @endphp
        <div class="an-my-card {{ $a->is_expired ? 'expired' : '' }}">
            <div style="min-width:0;flex:1;">
                <div class="an-my-card__title">{{ $a->title }}</div>
                <div class="an-my-card__meta">
                    @if($a->is_expired)
                        <span style="color:#e74c3c;">Expirado</span>
                    @elseif($a->status === 'closed')
                        <span style="color:#e67e22;">Cerrado</span>
                    @else
                        <span style="color:#2ecc71;">Activo</span> · Expira en {{ $hoursLeft }}h
                    @endif
                    · Publicado {{ \Carbon\Carbon::parse($a->created_at)->diffForHumans() }}
                </div>
            </div>
            @if(!$a->is_expired && $a->status !== 'closed')
            <form method="POST" action="{{ route('messages.announcement.close', $a->id) }}"
                  onsubmit="return confirm('¿Cerrar este anuncio?')">
                @csrf @method('PATCH')
                <button type="submit" class="an-my-card__close">Cerrar</button>
            </form>
            @endif
        </div>
        @endforeach
    @endif

</div>

{{-- ══ MODAL CREAR ══ --}}
@if($canPost)
<div class="an-modal-backdrop" id="modalCrear">
    <div class="an-modal">
        <button class="an-modal__close" onclick="document.getElementById('modalCrear').classList.remove('open')">✕</button>
        <h2>📣 Publicar anuncio</h2>
        <form method="POST" action="{{ route('messages.announcement.store') }}">
            @csrf
            <div class="an-field">
                <label>Título <small style="color:var(--theme-muted);font-weight:400;">(qué buscas en pocas palabras)</small></label>
                <input type="text" name="title" required maxlength="120" placeholder="Ej: Pareja busca unicornio para esta noche">
            </div>
            <div class="an-field">
                <label>Descripción <small style="color:var(--theme-muted);font-weight:400;">(opcional)</small></label>
                <textarea name="looking_for" rows="3" maxlength="500" placeholder="Cuéntanos más sobre lo que buscas..."></textarea>
            </div>
            <div class="an-field">
                <label>¿A quién va dirigido?</label>
                <div class="an-checkbox-group">
                    @foreach(['singles'=>'Singles','parejas'=>'Parejas','unicornio'=>'Unicornio','hombres'=>'Hombres','mujeres'=>'Mujeres'] as $val => $label)
                    <div class="an-checkbox-item">
                        <input type="checkbox" name="directed_to[]" value="{{ $val }}" id="dt_{{ $val }}">
                        <label for="dt_{{ $val }}">{{ $label }}</label>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="an-field">
                <label>¿Qué buscas?</label>
                <div class="an-checkbox-group">
                    @foreach(['intercambios'=>'Intercambios','cuckold'=>'Cuckold','trio_mhm'=>'Trío MHM','trio_mmh'=>'Trío MMH','exhibicionismo'=>'Exhibicionismo','voyeurismo'=>'Voyeurismo','bdsm'=>'BDSM','solo_charlar'=>'Solo charlar'] as $val => $label)
                    <div class="an-checkbox-item">
                        <input type="checkbox" name="what_looking[]" value="{{ $val }}" id="wl_{{ $val }}">
                        <label for="wl_{{ $val }}">{{ $label }}</label>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="an-field">
                <label>Fecha del encuentro <small style="color:var(--theme-muted);font-weight:400;">(opcional)</small></label>
                <input type="date" name="event_date" min="{{ date('Y-m-d') }}">
            </div>
            <div class="an-field">
                <label>Propuesta adicional <small style="color:var(--theme-muted);font-weight:400;">(opcional)</small></label>
                <textarea name="proposal" rows="2" maxlength="300" placeholder="Detalles extra, lugar, horario..."></textarea>
            </div>
            <small style="display:block;color:var(--theme-muted);margin-bottom:1rem;">
                <i class="fas fa-info-circle" style="color:#e056a0;margin-right:.3rem;"></i>
                El anuncio expira automáticamente en 4 días.
            </small>
            <button type="submit" class="an-submit">Publicar anuncio</button>
        </form>
    </div>
</div>
@endif

<script>
document.querySelectorAll('.an-tab').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.an-tab').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.an-tab-content').forEach(c => c.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById('tab-' + btn.dataset.tab).classList.add('active');
    });
});

const modalCrear = document.getElementById('modalCrear');
if (modalCrear) {
    modalCrear.addEventListener('click', function(e) {
        if (e.target === this) this.classList.remove('open');
    });
}
</script>
@endsection





