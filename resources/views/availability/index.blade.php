@extends('layouts.app')

@section('title', 'Disponibles ahora')

@push('styles')
<style>
/* ══ PÁGINA DISPONIBLES — prefijo dp- para evitar colisiones con vivid-nights.css ══ */
.dp-page {
    display: grid !important;
    grid-template-columns: 190px 1fr 200px !important;
    gap: 1.4rem;
    padding: 1.2rem 1rem;
    width: 100% !important;
    box-sizing: border-box;
    align-items: start;
}
.dp-sidebar {
    position: sticky;
    top: 70px;
    background: #fff;
    border-radius: 12px;
    padding: 1rem;
    box-shadow: 0 2px 8px rgba(0,0,0,.08);
}
.dp-sidebar h3 {
    font-size:.78rem; font-weight:700; color:#888;
    text-transform:uppercase; margin:0 0 .7rem;
}
.dp-filter-btn {
    display:block; width:100%; text-align:left;
    padding:.55rem .9rem; border-radius:8px; border:none; background:none;
    font-size:.88rem; cursor:pointer; margin-bottom:4px; transition:background .15s;
}
.dp-filter-btn:hover  { background:#f0f0f5; }
.dp-filter-btn.active { background:#7c3aed; color:#fff; font-weight:600; }
.dp-search { margin-top:1.2rem; }
.dp-search input {
    width:100%; padding:.5rem .75rem; border:1px solid #ddd;
    border-radius:8px; font-size:.85rem; box-sizing:border-box;
}
.dp-search button {
    width:100%; margin-top:.5rem; padding:.5rem; background:#7c3aed;
    color:#fff; border:none; border-radius:8px; cursor:pointer; font-size:.85rem;
}
.dp-main { min-width:0; width:100%; }
.dp-header { display:flex; align-items:center; gap:.75rem; margin-bottom:1rem; }
.dp-header h1 { font-size:1.4rem; font-weight:700; margin:0; }
.dp-count {
    background:#7c3aed; color:#fff; font-size:.72rem;
    padding:.2rem .65rem; border-radius:20px; font-weight:600;
}
.dp-grid {
    display: grid !important;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)) !important;
    gap: .85rem;
    width: 100% !important;
}
.dp-card {
    background:#fff; border-radius:14px; overflow:hidden; min-width:0;
    box-shadow:0 2px 10px rgba(0,0,0,.09);
    transition:transform .18s, box-shadow .18s;
    cursor:pointer;
}
.dp-card:hover { transform:translateY(-4px); box-shadow:0 6px 20px rgba(0,0,0,.13); }
.dp-card__img {
    width: 100%; aspect-ratio: 1/1; object-fit: cover; display: block;
}
.dp-card__placeholder {
    width: 100%; aspect-ratio: 1/1; background: #f0eeff;
    display: flex; align-items: center; justify-content: center; font-size: 2.5rem;
}
.dp-card__body { padding:.75rem; }
.dp-card__nick { font-weight:700; font-size:.9rem; }
.dp-card__sub  { color:#888; font-size:.75rem; margin-bottom:.45rem; }
.dp-card__slot {
    display:inline-flex; align-items:center; gap:.3rem;
    font-size:.72rem; background:#f0eeff; color:#7c3aed;
    padding:.2rem .55rem; border-radius:20px; font-weight:600; margin-bottom:.5rem;
}
.dp-card__timer { font-size:.7rem; color:#aaa; }
.dp-card__bio {
    font-size:.75rem; color:#555; margin-top:.4rem; font-style:italic;
    white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
}
.dp-card__message {
    font-size: .75rem; color: #555; margin-top: .4rem;
    font-style: italic; display: -webkit-box;
    -webkit-line-clamp: 2; -webkit-box-orient: vertical;
    overflow: hidden; line-height: 1.4;
}
.dp-msg-btn {
    display:block; width:100%; margin-top:.65rem; padding:.5rem;
    background:#7c3aed; color:#fff; border:none; border-radius:8px;
    font-size:.82rem; font-weight:600; cursor:pointer; transition:background .15s;
}
.dp-msg-btn:hover { background:#6d28d9; }
.dp-cta {
    position:sticky; top:70px;
    background:#fff; border-radius:12px; padding:1.2rem;
    box-shadow:0 2px 8px rgba(0,0,0,.08); text-align:center;
}
.dp-cta h4 { margin:0 0 .4rem; font-size:.9rem; }
.dp-cta p  { font-size:.78rem; color:#666; margin:0 0 .9rem; }
.dp-cta-btn {
    display:block; padding:.65rem 1rem; background:#7c3aed; color:#fff;
    border-radius:10px; font-weight:700; text-decoration:none;
    font-size:.85rem; border:none; cursor:pointer; width:100%; box-sizing:border-box;
}
.dp-how { margin-top:1rem; text-align:left; }
.dp-how h5 {
    font-size:.78rem; font-weight:700; color:#888;
    text-transform:uppercase; margin:0 0 .6rem;
}
.dp-how li { font-size:.75rem; color:#555; margin-bottom:.4rem; padding-left:.3rem; }
/* ══ MODAL ══ */
.dp-modal-overlay {
    display:none; position:fixed; inset:0; background:rgba(0,0,0,.55);
    z-index:9999; align-items:center; justify-content:center;
}
.dp-modal-overlay.open { display:flex; }
.dp-modal {
    background:#fff; border-radius:16px; padding:1.5rem;
    width:min(460px, 94vw); box-shadow:0 8px 32px rgba(0,0,0,.18);
}
.dp-modal__header { display:flex; align-items:center; gap:.75rem; margin-bottom:1rem; }
.dp-modal__avatar {
    width:52px; height:52px; border-radius:50%;
    background:#f0eeff; display:flex; align-items:center; justify-content:center;
    font-size:1.4rem; overflow:hidden; flex-shrink:0;
}
.dp-modal__name  { font-weight:700; font-size:1rem; }
.dp-modal__slot  { font-size:.75rem; color:#7c3aed; }
.dp-modal__close { margin-left:auto; background:none; border:none; font-size:1.4rem; cursor:pointer; color:#888; }
.dp-modal__hint  { font-size:.78rem; color:#888; margin-bottom:.5rem; }
.dp-modal textarea {
    width:100%; box-sizing:border-box; border:1.5px solid #ddd; border-radius:10px;
    padding:.75rem; font-size:.88rem; resize:vertical; min-height:90px; font-family:inherit;
}
.dp-modal textarea:focus { outline:none; border-color:#7c3aed; }
.dp-modal__actions { display:flex; gap:.6rem; margin-top:.75rem; justify-content:flex-end; }
.dp-modal__send {
    padding:.6rem 1.4rem; background:#7c3aed; color:#fff;
    border:none; border-radius:8px; font-weight:700; cursor:pointer; font-size:.88rem;
}
.dp-modal__send:hover { background:#6d28d9; }
.dp-modal__cancel {
    padding:.6rem 1rem; background:#f5f5f5; color:#333;
    border:none; border-radius:8px; cursor:pointer; font-size:.88rem;
}
.dp-modal__toast {
    display:none; margin-top:.75rem; padding:.6rem .9rem;
    border-radius:8px; font-size:.82rem; text-align:center;
}
.dp-modal__toast.ok  { display:block; background:#ecfdf5; color:#059669; }
.dp-modal__toast.err { display:block; background:#fef2f2; color:#dc2626; }
/* ══ RESPONSIVE ══ */
@media(max-width:900px) {
    .dp-page { grid-template-columns: 180px 1fr !important; }
    .dp-cta  { display:none; }
}
@media(max-width:640px) {
    .dp-page { grid-template-columns: 1fr !important; padding:.75rem; }
    .dp-sidebar { position:static; }
}
</style>
@endpush

@section('content')

<div class="dp-modal-overlay" id="dpMsgModal">
    <div class="dp-modal">
        <div class="dp-modal__header">
            <div class="dp-modal__avatar" id="dpModalAvatar">👤</div>
            <div>
                <div class="dp-modal__name" id="dpModalNick">—</div>
                <div class="dp-modal__slot" id="dpModalSlot"></div>
            </div>
            <button class="dp-modal__close" id="dpCloseModal">✕</button>
        </div>
        <div class="dp-modal__hint">
            Escribe un mensaje. Le llegará a su bandeja de Mensajes.
        </div>
        <textarea id="dpMsgText" placeholder="Ej: Hola! Vi que estás disponible hoy 😊"></textarea>
        <div class="dp-modal__actions">
            <button class="dp-modal__cancel" id="dpCancelModal">Cancelar</button>
            <button class="dp-modal__send" id="dpSendMsg">✉ Enviar mensaje</button>
        </div>
        <div class="dp-modal__toast" id="dpModalToast"></div>
    </div>
</div>

<div class="dp-page">

    <aside class="dp-sidebar">
        <h3>Filtrar</h3>
        <form method="GET" action="/disponibles" id="dpFilterForm">
            @foreach($slotLabels as $key => $info)
            <button type="submit" name="slot" value="{{ $key }}"
                class="dp-filter-btn {{ $slotFilter === $key ? 'active' : '' }}">
                {{ $info['icon'] }} {{ $info['label'] }}
            </button>
            @endforeach
            <button type="submit" name="slot" value=""
                class="dp-filter-btn {{ !$slotFilter ? 'active' : '' }}">
                🌐 Todos
            </button>
            <div class="dp-search">
                <h3>Buscar</h3>
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Nick o ciudad...">
                <button type="submit">Buscar</button>
            </div>
        </form>
    </aside>

    <div class="dp-main">
        <div class="dp-header">
            <h1>Disponibles ahora</h1>
            <span class="dp-count">{{ $total }} {{ $total == 1 ? 'persona' : 'personas' }}</span>
            @if(request()->has('slot') || request()->has('search'))
                <a href="/disponibles" style="margin-left:auto;font-size:.8rem;color:#7c3aed;">✕ Limpiar</a>
            @endif
        </div>

        @if($available->count())
        <div class="dp-grid">
            @foreach($available as $u)
            @php
                $nick    = $u->nickname ?? $u->name ?? 'Usuario';
                $city    = $u->city ?? '';
                $slotKey = $u->slot ?? '';
                $slotLbl = $slotLabels[$slotKey]['label'] ?? $slotKey;
                $slotIco = $slotLabels[$slotKey]['icon']  ?? '📅';
                $photoId = $u->avatar_path ?? null;
                $expires = $u->expires_at ? \Carbon\Carbon::parse($u->expires_at) : null;
                $mins    = $expires ? max(0, (int) now()->diffInMinutes($expires, false)) : null;
                $hrs     = $mins !== null ? floor($mins / 60) : null;
                $minRest = $mins !== null ? ($mins % 60) : null;
                $msgDef  = 'Hola ' . $nick . '! Vi que estás disponible (' . $slotLbl . ') y me gustaría platicar 😊';
                $avMessage = $u->message ?? null;
                $avatarUrl = $photoId ? config('filesystems.supabase_public_url') . '/' . $photoId : null;
            @endphp
            <div class="dp-card"
                 data-partner="{{ $u->user_id }}"
                 data-nick="{{ e($nick) }}"
                 data-slot="{{ e($slotIco . ' ' . $slotLbl) }}"
                 data-msg="{{ e($msgDef) }}"
                 data-avatar="{{ $avatarUrl ?? '' }}">

                @if($avatarUrl)
                    <img class="dp-card__img" src="{{ $avatarUrl }}" alt="{{ $nick }}" loading="lazy">
                @else
                    <div class="dp-card__placeholder">👤</div>
                @endif

                <div class="dp-card__body">
                    <div class="dp-card__nick">{{ $nick }}</div>
                    @if($city)
                        <div class="dp-card__sub">📍 {{ $city }}</div>
                    @endif
                    <span class="dp-card__slot">{{ $slotIco }} {{ $slotLbl }}</span>
                    @if($mins !== null)
                        <div class="dp-card__timer">⏱ {{ $hrs }}h {{ $minRest }}m restantes</div>
                    @if($avMessage)
                        <div class="dp-card__message">"{{ $avMessage }}"</div>
                    @endif
                    @endif
                    <button class="dp-msg-btn">✉ Enviar mensaje</button>
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
    </div>

    <aside class="dp-cta">
        <h4>🔥 TU DISPONIBILIDAD</h4>
        <p>Activa tu disponibilidad para que otros te encuentren aquí.</p>
        <a href="{{ route('dashboard') }}#disponibilidad" class="dp-cta-btn">Activar ahora</a>
        <div class="dp-how">
            <h5>¿Cómo funciona?</h5>
            <ul>
                <li>Activa desde el panel lateral</li>
                <li>Elige tu slot de tiempo</li>
                <li>Otros te verán en esta página</li>
                <li>Se desactiva al expirar</li>
            </ul>
        </div>
    </aside>
</div>

@endsection

@push('scripts')
<script>
(function () {
    var csrfToken = "{{ csrf_token() }}";
    var overlay   = document.getElementById('dpMsgModal');
    var modalNick = document.getElementById('dpModalNick');
    var modalSlot = document.getElementById('dpModalSlot');
    var modalAvat = document.getElementById('dpModalAvatar');
    var msgText   = document.getElementById('dpMsgText');
    var toast     = document.getElementById('dpModalToast');
    var currentPartner = null;

    function openModal(card) {
        currentPartner = card.dataset.partner;
        modalNick.textContent = card.dataset.nick;
        modalSlot.textContent = card.dataset.slot;
        msgText.value = decodeURIComponent(card.dataset.msg || '');
        var avatar = card.dataset.avatar;
        if (avatar) {
            modalAvat.innerHTML = '<img src="' + avatar + '" style="width:100%;height:100%;object-fit:cover;">';
        } else {
            modalAvat.textContent = '👤';
        }
        toast.className = 'dp-modal__toast';
        toast.textContent = '';
        overlay.classList.add('open');
        msgText.focus();
    }

    function closeModal() {
        overlay.classList.remove('open');
        currentPartner = null;
    }

    document.querySelectorAll('.dp-card').forEach(function(card) {
        card.addEventListener('click', function() { openModal(card); });
    });

    document.getElementById('dpCloseModal').addEventListener('click', closeModal);
    document.getElementById('dpCancelModal').addEventListener('click', closeModal);
    overlay.addEventListener('click', function(e) { if (e.target === overlay) closeModal(); });

    document.getElementById('dpSendMsg').addEventListener('click', function() {
        var text = msgText.value.trim();
        if (!text || !currentPartner) return;
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
            body: JSON.stringify({ receiver_id: currentPartner, body: '📍 Desde Disponibles ahora: ' + text, source: 'availability' })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            btn.disabled = false;
            btn.textContent = '✉ Enviar mensaje';
            if (data.ok || data.message_id) {
                toast.className = 'dp-modal__toast ok';
                toast.textContent = '✅ Mensaje enviado correctamente';
                msgText.value = '';
                setTimeout(closeModal, 1800);
            } else {
                toast.className = 'dp-modal__toast err';
                toast.textContent = '⚠ ' + (data.message || data.error || 'No se pudo enviar.');
            }
        })
        .catch(function() {
            btn.disabled = false;
            btn.textContent = '✉ Enviar mensaje';
            toast.className = 'dp-modal__toast err';
            toast.textContent = '⚠ Error de red. Intenta de nuevo.';
        });
    });

    msgText.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.key === 'Enter') document.getElementById('dpSendMsg').click();
    });
})();
</script>
@endpush
