@extends('layouts.app')

@section('title', 'Disponibles ahora')

@push('styles')
<style>
/* ══ RESET ancho total ══ */
.l69-layout, .l69-layout__content, .l69-layout__main,
.content-wrapper { max-width: 100% !important; width: 100% !important; }

/* ══ WRAPPER ══ */
.avail-page {
    display: grid;
    grid-template-columns: 190px 1fr 200px;
    gap: 1.4rem;
    padding: 1.2rem 1rem;
    width: 100%;
    box-sizing: border-box;
    align-items: start;
}

/* ══ SIDEBAR IZQUIERDO ══ */
.avail-sidebar {
    position: sticky;
    top: 70px;
    background: #fff;
    border-radius: 12px;
    padding: 1rem;
    box-shadow: 0 2px 8px rgba(0,0,0,.08);
}
.avail-sidebar h3 { font-size:.78rem; font-weight:700; color:#888; text-transform:uppercase; margin:0 0 .7rem; }
.avail-filter-btn {
    display:block; width:100%; text-align:left;
    padding:.55rem .9rem; border-radius:8px; border:none; background:none;
    font-size:.88rem; cursor:pointer; margin-bottom:4px; transition:background .15s;
}
.avail-filter-btn:hover  { background:#f0f0f5; }
.avail-filter-btn.active { background:#7c3aed; color:#fff; font-weight:600; }
.avail-search { margin-top:1.2rem; }
.avail-search input {
    width:100%; padding:.5rem .75rem; border:1px solid #ddd;
    border-radius:8px; font-size:.85rem; box-sizing:border-box;
}
.avail-search button {
    width:100%; margin-top:.5rem; padding:.5rem; background:#7c3aed;
    color:#fff; border:none; border-radius:8px; cursor:pointer; font-size:.85rem;
}

/* ══ GRID CENTRAL ══ */
.avail-header { display:flex; align-items:center; gap:.75rem; margin-bottom:1rem; }
.avail-header h1 { font-size:1.4rem; font-weight:700; margin:0; }
.avail-badge {
    background:#7c3aed; color:#fff; font-size:.72rem;
    padding:.2rem .65rem; border-radius:20px; font-weight:600;
}
.avail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 1rem;
}
.avail-card {
    background:#fff; border-radius:14px; overflow:hidden;
    box-shadow:0 2px 10px rgba(0,0,0,.09); transition:transform .18s, box-shadow .18s;
    cursor: pointer;
}
.avail-card:hover { transform:translateY(-4px); box-shadow:0 6px 20px rgba(0,0,0,.13); }
.avail-card__img { width:100%; aspect-ratio:3/4; object-fit:cover; display:block; }
.avail-card__img-placeholder {
    width:100%; aspect-ratio:3/4; background:#f0eeff;
    display:flex; align-items:center; justify-content:center; font-size:3rem;
}
.avail-card__body { padding:.75rem; }
.avail-card__nick { font-weight:700; font-size:.9rem; }
.avail-card__sub  { color:#888; font-size:.75rem; margin-bottom:.45rem; }
.avail-card__slot {
    display:inline-flex; align-items:center; gap:.3rem;
    font-size:.72rem; background:#f0eeff; color:#7c3aed;
    padding:.2rem .55rem; border-radius:20px; font-weight:600; margin-bottom:.5rem;
}
.avail-card__timer { font-size:.7rem; color:#aaa; }
.avail-card__bio   { font-size:.75rem; color:#555; margin-top:.4rem; font-style:italic; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.avail-msg-btn {
    display:block; width:100%; margin-top:.65rem; padding:.5rem;
    background:#7c3aed; color:#fff; border:none; border-radius:8px;
    font-size:.82rem; font-weight:600; cursor:pointer; transition:background .15s;
}
.avail-msg-btn:hover { background:#6d28d9; }

/* ══ SIDEBAR DERECHO ══ */
.avail-cta {
    position:sticky; top:70px;
    background:#fff; border-radius:12px; padding:1.2rem;
    box-shadow:0 2px 8px rgba(0,0,0,.08); text-align:center;
}
.avail-cta h4 { margin:0 0 .4rem; font-size:.9rem; }
.avail-cta p  { font-size:.78rem; color:#666; margin:0 0 .9rem; }
.avail-cta-btn {
    display:block; padding:.65rem 1rem; background:#7c3aed; color:#fff;
    border-radius:10px; font-weight:700; text-decoration:none;
    font-size:.85rem; border:none; cursor:pointer; width:100%; box-sizing:border-box;
}
.avail-how { margin-top:1rem; text-align:left; }
.avail-how h5 { font-size:.78rem; font-weight:700; color:#888; text-transform:uppercase; margin:0 0 .6rem; }
.avail-how li { font-size:.75rem; color:#555; margin-bottom:.4rem; padding-left:.3rem; }

/* ══ MODAL DE MENSAJE ══ */
.avail-modal-overlay {
    display:none; position:fixed; inset:0; background:rgba(0,0,0,.55);
    z-index:9999; align-items:center; justify-content:center;
}
.avail-modal-overlay.open { display:flex; }
.avail-modal {
    background:#fff; border-radius:16px; padding:1.5rem;
    width:min(460px, 94vw); box-shadow:0 8px 32px rgba(0,0,0,.18);
}
.avail-modal__header { display:flex; align-items:center; gap:.75rem; margin-bottom:1rem; }
.avail-modal__avatar {
    width:52px; height:52px; border-radius:50%; object-fit:cover;
    background:#f0eeff; display:flex; align-items:center; justify-content:center;
    font-size:1.4rem; overflow:hidden; flex-shrink:0;
}
.avail-modal__name  { font-weight:700; font-size:1rem; }
.avail-modal__slot  { font-size:.75rem; color:#7c3aed; }
.avail-modal__close { margin-left:auto; background:none; border:none; font-size:1.4rem; cursor:pointer; color:#888; }
.avail-modal__hint  { font-size:.78rem; color:#888; margin-bottom:.5rem; }
.avail-modal textarea {
    width:100%; box-sizing:border-box; border:1.5px solid #ddd; border-radius:10px;
    padding:.75rem; font-size:.88rem; resize:vertical; min-height:90px; font-family:inherit;
}
.avail-modal textarea:focus { outline:none; border-color:#7c3aed; }
.avail-modal__actions { display:flex; gap:.6rem; margin-top:.75rem; justify-content:flex-end; }
.avail-modal__send {
    padding:.6rem 1.4rem; background:#7c3aed; color:#fff;
    border:none; border-radius:8px; font-weight:700; cursor:pointer; font-size:.88rem;
}
.avail-modal__send:hover { background:#6d28d9; }
.avail-modal__cancel {
    padding:.6rem 1rem; background:#f5f5f5; color:#333;
    border:none; border-radius:8px; cursor:pointer; font-size:.88rem;
}
.avail-modal__toast {
    display:none; margin-top:.75rem; padding:.6rem .9rem;
    border-radius:8px; font-size:.82rem; text-align:center;
}
.avail-modal__toast.ok  { display:block; background:#ecfdf5; color:#059669; }
.avail-modal__toast.err { display:block; background:#fef2f2; color:#dc2626; }

/* ══ RESPONSIVE ══ */
@media(max-width:900px) {
    .avail-page { grid-template-columns: 180px 1fr; }
    .avail-cta  { display:none; }
}
@media(max-width:640px) {
    .avail-page { grid-template-columns: 1fr; padding:.75rem; }
    .avail-sidebar { position:static; }
}
</style>
@endpush

@section('content')

{{-- ══ MODAL DE MENSAJE ══ --}}
<div class="avail-modal-overlay" id="availMsgModal">
    <div class="avail-modal">
        <div class="avail-modal__header">
            <div class="avail-modal__avatar" id="modalAvatar">👤</div>
            <div>
                <div class="avail-modal__name" id="modalNick">—</div>
                <div class="avail-modal__slot" id="modalSlot"></div>
            </div>
            <button class="avail-modal__close" id="closeModal">✕</button>
        </div>
        <div class="avail-modal__hint">
            Escribe un mensaje. Le llegará a su bandeja de Mensajes vinculado a su anuncio de disponibilidad.
        </div>
        <textarea id="availMsgText" placeholder="Ej: Hola! Vi que estás disponible hoy, me gustaría platicar 😊"></textarea>
        <div class="avail-modal__actions">
            <button class="avail-modal__cancel" id="cancelModal">Cancelar</button>
            <button class="avail-modal__send" id="sendAvailMsg">✉ Enviar mensaje</button>
        </div>
        <div class="avail-modal__toast" id="modalToast"></div>
    </div>
</div>

<div class="avail-page">

    {{-- ══ SIDEBAR IZQUIERDO ══ --}}
    <aside class="avail-sidebar">
        <h3>Filtrar</h3>
        <form method="GET" action="{{ '/disponibles' }}" id="filterForm">
            @foreach($slotLabels as $key => $info)
            <button type="submit" name="slot" value="{{ $key }}"
                class="avail-filter-btn {{ $slotFilter === $key ? 'active' : '' }}">
                {{ $info['icon'] }} {{ $info['label'] }}
            </button>
            @endforeach
            <button type="submit" name="slot" value=""
                class="avail-filter-btn {{ !$slotFilter ? 'active' : '' }}">
                🌐 Todos
            </button>

            <div class="avail-search">
                <h3>Buscar</h3>
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Nick o ciudad...">
                <button type="submit">Buscar</button>
            </div>
        </form>
    </aside>

    {{-- ══ COLUMNA CENTRAL ══ --}}
    <main>
        <div class="avail-header">
            <h1>Disponibles ahora</h1>
            <span class="avail-badge">{{ $total }} {{ $total == 1 ? 'persona' : 'personas' }}</span>
            @if(request()->has('slot') || request()->has('search'))
                <a href="{{ '/disponibles' }}" style="margin-left:auto;font-size:.8rem;color:#7c3aed;">✕ Limpiar filtros</a>
            @endif
        </div>

        @if($available->count())
        <div class="avail-grid">
            @foreach($available as $u)
            @php
                $nick    = $u->nickname ?? $u->name ?? 'Usuario';
                $city    = $u->city ?? '';
                $slotKey = $u->slot ?? '';
                $slotLbl = $slotLabels[$slotKey]['label'] ?? $slotKey;
                $slotIco = $slotLabels[$slotKey]['icon']  ?? '📅';
                $photoId = $u->profile_photo_id ?? null;
                $expires = $u->expires_at ? \Carbon\Carbon::parse($u->expires_at) : null;
                $mins    = $expires ? max(0, (int) now()->diffInMinutes($expires, false)) : null;
                $hrs     = $mins !== null ? floor($mins / 60) : null;
                $minRest = $mins !== null ? ($mins % 60) : null;
                $msgDefault = 'Hola ' . $nick . '! Vi que estás disponible' . ($slotLbl ? ' (' . $slotLbl . ')' : '') . ' y me gustaría platicar 😊';
                $avatarUrl = $photoId
                    ? route('photo.show.uuid', ['photoUuid' => $photoId])
                    : null;
            @endphp
            <div class="avail-card"
                 data-partner="{{ $u->user_id }}"
                 data-nick="{{ e($nick) }}"
                 data-slot="{{ e($slotIco . ' ' . $slotLbl) }}"
                 data-msg="{{ e($msgDefault) }}"
                 data-avatar="{{ $avatarUrl ?? '' }}">

                @if($avatarUrl)
                    <img class="avail-card__img" src="{{ $avatarUrl }}" alt="{{ $nick }}" loading="lazy">
                @else
                    <div class="avail-card__img-placeholder">👤</div>
                @endif

                <div class="avail-card__body">
                    <div class="avail-card__nick">{{ $nick }}</div>
                    @if($city)
                    <div class="avail-card__sub">📍 {{ $city }}</div>
                    @endif
                    <span class="avail-card__slot">{{ $slotIco }} {{ $slotLbl }}</span>
                    @if($mins !== null)
                    <div class="avail-card__timer">⏱ {{ $hrs }}h {{ $minRest }}m restantes</div>
                    @endif
                    @if($u->note ?? null)
                    <div class="avail-card__bio">"{{ $u->note }}"</div>
                    @endif

                    <button class="avail-msg-btn open-msg-modal">
                        ✉ Enviar mensaje
                    </button>
                </div>
            </div>
            @endforeach
        </div>

        <div style="margin-top:1.5rem;">
            {{ $available->appends(request()->query())->links() }}
        </div>

        @else
        <div style="text-align:center;padding:4rem 0;color:#aaa;">
            <div style="font-size:3rem;margin-bottom:1rem;">🔍</div>
            <p>No hay nadie disponible con ese filtro en este momento.</p>
        </div>
        @endif
    </main>

    {{-- ══ SIDEBAR DERECHO ══ --}}
    <aside class="avail-cta">
        <h4>🔥 TU DISPONIBILIDAD</h4>
        <p>Activa tu disponibilidad para que otros te encuentren aquí.</p>
        <a href="{{ route('dashboard') }}#disponibilidad" class="avail-cta-btn">Activar ahora</a>

        <div class="avail-how">
            <h5>¿Cómo funciona?</h5>
            <ul>
                <li>Activa tu disponibilidad desde el panel lateral</li>
                <li>Elige el slot de tiempo que mejor te va</li>
                <li>Otros miembros podrán verte en esta página</li>
                <li>Se desactiva automáticamente al expirar</li>
            </ul>
        </div>
    </aside>
</div>

@endsection

@push('scripts')
<script>
(function () {
    /* ── datos del usuario autenticado pasados por Laravel ── */
    var authId   = "{{ auth()->id() ?? '' }}";
    var csrfToken = "{{ csrf_token() }}";

    /* ── elementos del modal ── */
    var overlay   = document.getElementById('availMsgModal');
    var modalNick = document.getElementById('modalNick');
    var modalSlot = document.getElementById('modalSlot');
    var modalAvat = document.getElementById('modalAvatar');
    var msgText   = document.getElementById('availMsgText');
    var toast     = document.getElementById('modalToast');
    var currentPartner = null;

    function openModal(card) {
        currentPartner = card.dataset.partner;
        var nick   = card.dataset.nick;
        var slot   = card.dataset.slot;
        var avatar = card.dataset.avatar;
        var msg    = decodeURIComponent(card.dataset.msg || '');

        modalNick.textContent = nick;
        modalSlot.textContent = slot;
        msgText.value = msg;

        if (avatar) {
            modalAvat.innerHTML = '<img src="' + avatar + '" style="width:100%;height:100%;object-fit:cover;">';
        } else {
            modalAvat.textContent = '👤';
        }

        toast.className = 'avail-modal__toast';
        toast.textContent = '';
        overlay.classList.add('open');
        msgText.focus();
    }

    function closeModal() {
        overlay.classList.remove('open');
        currentPartner = null;
    }

    /* ── abrir modal al click en tarjeta o botón ── */
    document.querySelectorAll('.avail-card').forEach(function(card) {
        card.addEventListener('click', function(e) {
            /* evitar double-fire si el click fue directo en el botón */
            openModal(card);
        });
    });

    /* ── cerrar ── */
    document.getElementById('closeModal').addEventListener('click', closeModal);
    document.getElementById('cancelModal').addEventListener('click', closeModal);
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) closeModal();
    });

    /* ── enviar mensaje ── */
    document.getElementById('sendAvailMsg').addEventListener('click', function() {
        var text = msgText.value.trim();
        if (!text) { msgText.focus(); return; }
        if (!currentPartner) return;

        var btn = this;
        btn.disabled = true;
        btn.textContent = 'Enviando...';

        fetch('{{ route("messages.send") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                receiver_id: currentPartner,
                body: text,
                source: 'availability'
            })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            btn.disabled = false;
            btn.textContent = '✉ Enviar mensaje';

            if (data.success || data.id || data.message_id) {
                toast.className = 'avail-modal__toast ok';
                toast.textContent = '✅ Mensaje enviado correctamente';
                msgText.value = '';
                setTimeout(closeModal, 1800);
            } else {
                toast.className = 'avail-modal__toast err';
                toast.textContent = '⚠ ' + (data.error || data.message || 'No se pudo enviar el mensaje.');
            }
        })
        .catch(function(err) {
            btn.disabled = false;
            btn.textContent = '✉ Enviar mensaje';
            toast.className = 'avail-modal__toast err';
            toast.textContent = '⚠ Error de red. Intenta de nuevo.';
        });
    });

    /* ── CTRL+ENTER para enviar ── */
    msgText.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.key === 'Enter') {
            document.getElementById('sendAvailMsg').click();
        }
    });

})();
</script>
@endpush
