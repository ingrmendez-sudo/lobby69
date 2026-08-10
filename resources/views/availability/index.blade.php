<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Disponibles Ahora</title>
@include('layouts.head-assets')
<style>
/* ══ LAYOUT BASE ══ */
.dp-page {
    display: grid;
    grid-template-columns: 180px 1fr;
    gap: 1.5rem;
    max-width: 1100px;
    margin: 0 auto;
    padding: 1.5rem 1rem;
    align-items: start;
}
.dp-sidebar { position: sticky; top: 80px; }

/* ══ GRID DE TARJETAS ══ */
.dp-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 1.25rem;
}

/* ══ TARJETA ══ */
.dp-card {
    background: rgba(255,255,255,.04);
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 14px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    cursor: pointer;
    transition: transform .2s, box-shadow .2s, border-color .2s;
    position: relative;
}
.dp-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 32px rgba(108,63,197,.25);
    border-color: rgba(108,63,197,.4);
}

/* ── Foto ── */
.dp-card__photo-wrap {
    width: 100%;
    aspect-ratio: 3/4;
    overflow: hidden;
    background: rgba(108,63,197,.15);
    position: relative;
    flex-shrink: 0;
}
.dp-card__photo-wrap img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform .3s;
}
.dp-card:hover .dp-card__photo-wrap img {
    transform: scale(1.05);
}
.dp-card__photo-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, rgba(108,63,197,.2), rgba(224,86,160,.2));
}
.dp-card__photo-placeholder i {
    font-size: 3.5rem;
    color: rgba(255,255,255,.2);
}

/* Badge slot arriba derecha */
.dp-card__slot-badge {
    position: absolute;
    top: .6rem;
    right: .6rem;
    background: rgba(0,0,0,.65);
    backdrop-filter: blur(8px);
    border-radius: 20px;
    padding: .2rem .55rem;
    font-size: .68rem;
    font-weight: 700;
    color: #fff;
    display: flex;
    align-items: center;
    gap: .25rem;
    border: 1px solid rgba(255,255,255,.12);
}

/* ── Cuerpo: mensaje + datos ── */
.dp-card__body {
    padding: .9rem .85rem .85rem;
    display: flex;
    flex-direction: column;
    gap: .55rem;
    flex: 1;
}

/* Mensaje grande */
.dp-card__message {
    font-size: .95rem;
    font-weight: 500;
    color: rgba(226,217,243,.92);
    line-height: 1.45;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    font-style: italic;
    min-height: 1.45em;
}
.dp-card__message-empty {
    font-size: .82rem;
    color: rgba(226,217,243,.35);
    font-style: italic;
}

/* Separador */
.dp-card__divider {
    height: 1px;
    background: rgba(255,255,255,.07);
    margin: .1rem 0;
}

/* Datos del usuario */
.dp-card__info {
    display: flex;
    flex-direction: column;
    gap: .28rem;
}
.dp-card__nick {
    font-size: .88rem;
    font-weight: 700;
    color: var(--theme-text, #e2d9f3);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.dp-card__meta {
    display: flex;
    align-items: center;
    gap: .4rem;
    flex-wrap: wrap;
}
.dp-card__meta-tag {
    font-size: .68rem;
    color: rgba(226,217,243,.5);
    background: rgba(255,255,255,.05);
    border-radius: 4px;
    padding: .1rem .35rem;
}
.dp-card__expires {
    font-size: .68rem;
    color: rgba(108,197,140,.75);
    display: flex;
    align-items: center;
    gap: .25rem;
    margin-top: .1rem;
}

/* Botón mensaje */
.dp-card__cta {
    margin-top: auto;
    padding-top: .55rem;
}
.dp-card__btn {
    width: 100%;
    padding: .45rem;
    background: linear-gradient(135deg, rgba(108,63,197,.35), rgba(224,86,160,.25));
    border: 1px solid rgba(108,63,197,.4);
    border-radius: 8px;
    color: rgba(226,217,243,.9);
    font-size: .78rem;
    font-weight: 600;
    cursor: pointer;
    transition: background .2s, border-color .2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: .4rem;
}
.dp-card__btn:hover {
    background: linear-gradient(135deg, rgba(108,63,197,.6), rgba(224,86,160,.45));
    border-color: rgba(108,63,197,.7);
    color: #fff;
}

/* ══ ESTADO VACÍO ══ */
.dp-empty {
    grid-column: 1 / -1;
    text-align: center;
    padding: 4rem 2rem;
    color: rgba(226,217,243,.4);
}
.dp-empty i { font-size: 3rem; margin-bottom: 1rem; display: block; }
.dp-empty p { font-size: .9rem; margin: 0; }

/* ══ MODAL MENSAJE ══ */
.dp-modal-backdrop {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.6);
    backdrop-filter: blur(4px);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}
.dp-modal-backdrop.is-open { display: flex; }
.dp-modal {
    background: var(--theme-bg, #1a1025);
    border: 1px solid rgba(108,63,197,.35);
    border-radius: 16px;
    padding: 1.5rem;
    width: 90%;
    max-width: 420px;
    position: relative;
}
.dp-modal__close {
    position: absolute;
    top: .75rem; right: .85rem;
    background: none; border: none;
    color: rgba(226,217,243,.5);
    font-size: 1.1rem;
    cursor: pointer;
}
.dp-modal__title {
    font-size: .95rem;
    font-weight: 700;
    color: var(--theme-text);
    margin: 0 0 1rem;
}
.dp-modal__to {
    font-size: .8rem;
    color: rgba(226,217,243,.55);
    margin-bottom: .75rem;
}
.dp-modal__to strong { color: rgba(226,217,243,.85); }
.dp-modal__textarea {
    width: 100%;
    background: rgba(255,255,255,.05);
    border: 1px solid rgba(108,63,197,.3);
    border-radius: 8px;
    color: var(--theme-text);
    padding: .6rem .75rem;
    font-size: .85rem;
    resize: vertical;
    min-height: 90px;
    box-sizing: border-box;
}
.dp-modal__send {
    width: 100%;
    margin-top: .75rem;
    padding: .55rem;
    background: linear-gradient(135deg, #6c3fc5, #e056a0);
    border: none;
    border-radius: 8px;
    color: #fff;
    font-size: .88rem;
    font-weight: 700;
    cursor: pointer;
    transition: opacity .2s;
}
.dp-modal__send:hover { opacity: .88; }
.dp-modal__feedback {
    margin-top: .5rem;
    font-size: .8rem;
    text-align: center;
    min-height: 1.2em;
    color: #22c55e;
}

/* ══ RESPONSIVE ══ */
@media (max-width: 900px) {
    .dp-page { grid-template-columns: 1fr; }
    .dp-sidebar { display: none; }
}
@media (max-width: 600px) {
    .dp-grid { grid-template-columns: repeat(2, 1fr); gap: .75rem; }
}
@media (max-width: 380px) {
    .dp-grid { grid-template-columns: 1fr; }
}
</style>
</head>
<body>
@include('layouts.navbar')

<div class="dp-page">

    {{-- Sidebar izquierdo --}}
    <aside class="dp-sidebar">
        @include('layouts.sidebar-left')
    </aside>

    {{-- Contenido principal --}}
    <div>
        <h1 style="font-size:1.25rem;font-weight:700;color:var(--theme-text,#e2d9f3);
                   margin:0 0 1.25rem;display:flex;align-items:center;gap:.5rem;">
            <span style="display:inline-block;width:9px;height:9px;border-radius:50%;
                         background:#22c55e;box-shadow:0 0 8px #22c55e;flex-shrink:0;"></span>
            Disponibles ahora
            <span style="font-size:.78rem;font-weight:400;color:rgba(226,217,243,.4);margin-left:.25rem;">
                {{ $users->count() }} {{ $users->count() === 1 ? 'persona' : 'personas' }}
            </span>
        </h1>

        <div class="dp-grid">
        @forelse($users as $u)
        @php
            $avNick    = $u->nickname    ?? $u->display_name ?? 'Usuario';
            $avMsg     = trim($u->message ?? $u->bio ?? '');
            $avSlot    = $u->slot ?? 'hoy';
            $avExpires = $u->expires_at ?? null;
            $avPartner = (string)($u->user_id ?? $u->id ?? '');
            $avatarRaw = trim($u->avatar_path ?? '');
            $avPhoto   = $avatarRaw
                ? (config('filesystems.supabase_public_url') . '/' . ltrim($avatarRaw, '/'))
                : null;

            $slotLabels = [
                'hoy'          => ['label'=>'Hoy',           'icon'=>'📅'],
                'entre_semana' => ['label'=>'Entre semana',  'icon'=>'💼'],
                'viernes'      => ['label'=>'Viernes',       'icon'=>'🍹'],
                'finde'        => ['label'=>'Fin de semana', 'icon'=>'🎉'],
                'sabado'       => ['label'=>'Sábado',        'icon'=>'🌙'],
                'domingo'      => ['label'=>'Domingo',       'icon'=>'☀️'],
            ];
            $slotInfo = $slotLabels[$avSlot] ?? $slotLabels['hoy'];
            $profileType = $u->profile_type ?? null;
            $city        = $u->city ?? null;
        @endphp

        <div class="dp-card"
             data-partner="{{ $avPartner }}"
             data-nick="{{ e($avNick) }}"
             data-slot="{{ $avSlot }}"
             data-msg="{{ e($avMsg) }}"
             data-avatar="{{ $avPhoto ?? '' }}"
             onclick="dpOpenModal(this)">

            {{-- FOTO --}}
            <div class="dp-card__photo-wrap">
                @if($avPhoto)
                <img src="{{ $avPhoto }}"
                     alt="{{ $avNick }}"
                     loading="lazy"
                     onerror="this.parentElement.innerHTML='<div class=\'dp-card__photo-placeholder\'><i class=\'fas fa-user\'></i></div>'">
                @else
                <div class="dp-card__photo-placeholder">
                    <i class="fas fa-user"></i>
                </div>
                @endif

                {{-- Badge slot --}}
                <div class="dp-card__slot-badge">
                    {{ $slotInfo['icon'] }} {{ $slotInfo['label'] }}
                </div>
            </div>

            {{-- CUERPO --}}
            <div class="dp-card__body">

                {{-- MENSAJE (grande, protagonista) --}}
                @if($avMsg)
                <p class="dp-card__message">"{{ $avMsg }}"</p>
                @else
                <p class="dp-card__message-empty">Sin mensaje</p>
                @endif

                <div class="dp-card__divider"></div>

                {{-- DATOS --}}
                <div class="dp-card__info">
                    <div class="dp-card__nick">{{ $avNick }}</div>
                    <div class="dp-card__meta">
                        @if($profileType)
                        <span class="dp-card__meta-tag">
                            @if($profileType==='pareja') 💑
                            @elseif($profileType==='unicornio') 🦄
                            @else 👤 @endif
                            {{ ucfirst($profileType) }}
                        </span>
                        @endif
                        @if($city)
                        <span class="dp-card__meta-tag">📍 {{ $city }}</span>
                        @endif
                    </div>
                    @if($avExpires)
                    <div class="dp-card__expires">
                        <i class="far fa-clock"></i>
                        Hasta {{ \Carbon\Carbon::parse($avExpires)->translatedFormat('H:i') }}
                    </div>
                    @endif
                </div>

                {{-- BOTÓN --}}
                <div class="dp-card__cta">
                    <button class="dp-card__btn" onclick="event.stopPropagation();dpOpenModal(this.closest('.dp-card'))">
                        <i class="fas fa-paper-plane"></i> Enviar mensaje
                    </button>
                </div>

            </div>{{-- /.dp-card__body --}}
        </div>{{-- /.dp-card --}}
        @empty
        <div class="dp-empty">
            <i class="fas fa-moon"></i>
            <p>Nadie disponible en este momento.<br>
               <a href="{{ route('availability.activate') }}" style="color:#a78bfa;">¡Sé el primero en activarte!</a>
            </p>
        </div>
        @endforelse
        </div>{{-- /.dp-grid --}}
    </div>{{-- /main col --}}

</div>{{-- /.dp-page --}}

{{-- ══ MODAL ENVIAR MENSAJE ══ --}}
<div class="dp-modal-backdrop" id="dpModalBackdrop">
    <div class="dp-modal" role="dialog" aria-modal="true">
        <button class="dp-modal__close" onclick="dpCloseModal()" aria-label="Cerrar">&times;</button>
        <p class="dp-modal__title"><i class="fas fa-paper-plane"></i> Enviar mensaje</p>
        <p class="dp-modal__to">Para: <strong id="dpModalNick">—</strong></p>
        <input type="hidden" id="dpModalPartner">
        <textarea class="dp-modal__textarea"
                  id="dpModalBody"
                  placeholder="Escribe tu mensaje…"
                  maxlength="500"></textarea>
        <button class="dp-modal__send" onclick="dpSendMessage()">Enviar</button>
        <div class="dp-modal__feedback" id="dpModalFeedback"></div>
    </div>
</div>

<script>
function dpOpenModal(card) {
    document.getElementById('dpModalNick').textContent    = card.dataset.nick    || '—';
    document.getElementById('dpModalPartner').value       = card.dataset.partner || '';
    document.getElementById('dpModalBody').value          = '';
    document.getElementById('dpModalFeedback').textContent = '';
    document.getElementById('dpModalBackdrop').classList.add('is-open');
    document.getElementById('dpModalBody').focus();
}
function dpCloseModal() {
    document.getElementById('dpModalBackdrop').classList.remove('is-open');
}
document.getElementById('dpModalBackdrop').addEventListener('click', function(e){
    if (e.target === this) dpCloseModal();
});
document.addEventListener('keydown', function(e){
    if (e.key === 'Escape') dpCloseModal();
});
async function dpSendMessage() {
    const receiver = document.getElementById('dpModalPartner').value;
    const body     = document.getElementById('dpModalBody').value.trim();
    const fb       = document.getElementById('dpModalFeedback');

    if (!body) { fb.style.color='#f87171'; fb.textContent='Escribe un mensaje primero.'; return; }
    if (!receiver) { fb.style.color='#f87171'; fb.textContent='Error: destinatario no encontrado.'; return; }

    const btn = document.querySelector('.dp-modal__send');
    btn.disabled = true;
    fb.style.color = 'rgba(226,217,243,.5)';
    fb.textContent = 'Enviando…';

    try {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
        const res  = await fetch('{{ route("messages.send") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ receiver_id: receiver, body: body })
        });
        const data = await res.json();
        if (res.ok && (data.success || data.message)) {
            fb.style.color = '#22c55e';
            fb.textContent = '¡Mensaje enviado!';
            setTimeout(dpCloseModal, 1400);
        } else {
            fb.style.color = '#f87171';
            fb.textContent = data.error || data.message || 'Error al enviar.';
            btn.disabled = false;
        }
    } catch(err) {
        fb.style.color = '#f87171';
        fb.textContent = 'Error de red. Intenta de nuevo.';
        btn.disabled = false;
    }
}
</script>
</body>
</html>