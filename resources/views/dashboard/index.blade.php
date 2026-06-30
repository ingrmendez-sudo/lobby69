@push('scripts')
<script>
(function(){
var CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

// ── Abrir modal al click en tarjeta (no en botones) ──
document.addEventListener('click', function(e) {
    var card = e.target.closest('.dsb-photo-card');
    if (!card) return;
    if (e.target.closest('.dsb-like-btn')    ||
        e.target.closest('.dsb-comment-btn') ||
        e.target.closest('.dsb-profile-btn') ||
        e.target.closest('.dsb-photo-card__owner')) return;
    openModal(card.dataset.photoId);
});

// ── Like en feed ──
document.addEventListener('click', function(e) {
    var btn = e.target.closest('.dsb-like-btn');
    if (!btn || !btn.dataset.photoId) return;
    e.preventDefault(); e.stopPropagation();
    toggleLike(btn.dataset.photoId, btn);
});

// ── Comentario en feed (abre modal con foco) ──
document.addEventListener('click', function(e) {
    var btn = e.target.closest('.dsb-comment-btn');
    if (!btn) return;
    e.preventDefault();
    openModal(btn.dataset.photoId, true);
});

// ── Toggle Like ──
function toggleLike(photoId, btn) {
    btn.disabled = true;
    fetch('/fotos/' + photoId + '/like', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    })
    .then(function(r) {
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.json();
    })
    .then(function(d) {
        // Actualizar TODOS los botones de like con este photoId (feed + modal)
        document.querySelectorAll('.dsb-like-btn[data-photo-id="' + photoId + '"]')
            .forEach(function(b) {
                b.querySelector('i').className = d.liked ? 'fas fa-heart' : 'far fa-heart';
                d.liked ? b.classList.add('is-liked') : b.classList.remove('is-liked');
                var sp = b.querySelector('span');
                if (sp) sp.textContent = d.count;
            });
        // Sync contador modal si está abierto
        var mc = document.getElementById('modalLikeCount');
        if (mc && document.getElementById('modalLikeBtn')?.dataset.photoId === photoId) {
            mc.textContent = d.count;
        }
    })
    .catch(function(err) {
        console.warn('toggleLike error:', err);
    })
    .finally(function() {
        btn.disabled = false;
    });
}

// ── Abrir Modal ──
function openModal(photoId, focusComment) {
    if (!photoId) return;
    var modal = document.getElementById('photoModal');
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';

    // Reset visual
    document.getElementById('modalPhoto').src = '';
    document.getElementById('modalOwnerAvatar').src = '';
    document.getElementById('modalOwnerNick').textContent = '';
    document.getElementById('modalOwnerMeta').textContent = '';
    document.getElementById('modalLikeCount').textContent = '0';
    document.getElementById('modalCommentCount').textContent = '0';
    document.getElementById('modalCaption').style.display = 'none';
    document.getElementById('commentsList').innerHTML =
        '<div class="dsb-empty-state">' +
        '<i class="fas fa-spinner fa-spin"></i>' +
        '<span>Cargando...</span></div>';

    fetch('/fotos/' + photoId + '/info', {
        headers: { 'Accept': 'application/json' }
    })
    .then(function(r) {
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.json();
    })
    .then(function(d) {
        document.getElementById('modalPhoto').src            = d.photo.url;
        document.getElementById('modalLikeCount').textContent = d.photo.likes_count;
        document.getElementById('modalCommentCount').textContent = d.comments.length;

        var lb = document.getElementById('modalLikeBtn');
        lb.dataset.photoId = d.photo.id;
        lb.querySelector('i').className = d.photo.liked ? 'fas fa-heart' : 'far fa-heart';
        d.photo.liked ? lb.classList.add('is-liked') : lb.classList.remove('is-liked');

        var cap = document.getElementById('modalCaption');
        if (d.photo.caption) {
            cap.textContent    = d.photo.caption;
            cap.style.display  = 'block';
        }

        document.getElementById('modalOwnerAvatar').src        = d.owner.avatar;
        document.getElementById('modalOwnerNick').textContent  = d.owner.nick;
        document.getElementById('modalOwnerLink').href         = d.owner.profile_url;
        document.getElementById('modalProfileLink').href       = d.owner.profile_url;
        document.getElementById('commentPhotoId').value        = d.photo.id;

        var typeLabel = d.owner.profile_type === 'pareja'    ? '👫 Pareja'    :
                        d.owner.profile_type === 'unicornio' ? '⭐ Unicornio' : '👤 Single';
        document.getElementById('modalOwnerMeta').textContent =
            typeLabel + (d.owner.city ? ' · ' + d.owner.city : '');

        var list = document.getElementById('commentsList');
        if (d.comments.length === 0) {
            list.innerHTML =
                '<div class="dsb-empty-state">' +
                '<i class="far fa-comment"></i>' +
                '<span>Sé el primero en comentar</span></div>';
        } else {
            list.innerHTML = d.comments.map(function(c) {
                return '<div class="dsb-comment-item">' +
                    '<img src="' + c.user_avatar + '" ' +
                         'onerror="this.src=\'' + '{{ asset('img/default-avatar.svg') }}' + '\'">' +
                    '<div>' +
                    '<span class="dsb-comment-nick">'  + c.user_nick  + '</span>' +
                    '<span class="dsb-comment-time">'  + c.created_at + '</span>' +
                    '<p class="dsb-comment-body">'     + c.body       + '</p>' +
                    '</div></div>';
            }).join('');
        }

        if (focusComment) {
            setTimeout(function() {
                var inp = document.getElementById('commentBody');
                if (inp) inp.focus();
            }, 120);
        }
    })
    .catch(function(err) {
        console.error('openModal error:', err);
        document.getElementById('commentsList').innerHTML =
            '<div class="dsb-empty-state">' +
            '<i class="fas fa-exclamation-circle"></i>' +
            '<span>Error al cargar la foto. Intenta de nuevo.</span></div>';
    });
}

// ── Cerrar modal ──
document.getElementById('photoModalClose').addEventListener('click', closeModal);
document.getElementById('photoModalOverlay').addEventListener('click', closeModal);
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeModal();
});
function closeModal() {
    document.getElementById('photoModal').style.display = 'none';
    document.body.style.overflow = '';
}

// ── Like en modal ──
document.getElementById('modalLikeBtn').addEventListener('click', function() {
    toggleLike(this.dataset.photoId, this);
});

// ── Enviar comentario ──
document.getElementById('commentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var photoId = document.getElementById('commentPhotoId').value;
    var bodyEl  = document.getElementById('commentBody');
    var body    = bodyEl.value.trim();
    var note    = document.getElementById('commentNote');
    var sendBtn = this.querySelector('.dsb-modal__comment-send');
    if (!body) return;

    sendBtn.disabled = true;
    sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    fetch('/fotos/' + photoId + '/comentario', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN':  CSRF,
            'Content-Type':  'application/json',
            'Accept':        'application/json'
        },
        body: JSON.stringify({ body: body })
    })
    .then(function(r) {
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.json();
    })
    .then(function(d) {
        bodyEl.value      = '';
        note.style.color  = '#34d399';
        note.innerHTML    = '<i class="fas fa-check-circle"></i> ' + d.message;
        setTimeout(function() {
            note.style.color = '';
            note.innerHTML   =
                '<i class="fas fa-info-circle"></i> ' +
                'Los comentarios se publican tras revisión del admin';
        }, 3500);
        // Actualizar contador
        var mc = document.getElementById('modalCommentCount');
        if (mc) mc.textContent = parseInt(mc.textContent || '0') + 1;
    })
    .catch(function(err) {
        note.style.color = '#ef4444';
        note.innerHTML   = '<i class="fas fa-times-circle"></i> Error al enviar. Intenta de nuevo.';
        console.error('comentario error:', err);
    })
    .finally(function() {
        sendBtn.disabled  = false;
        sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i>';
    });
});

// ── Cargar más fotos ──
var loadBtn = document.getElementById('loadMoreBtn');
if (loadBtn) {
    loadBtn.addEventListener('click', function() {
        var page = this.dataset.page;
        var tab  = this.dataset.tab;
        var self = this;
        self.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cargando...';
        self.disabled  = true;

        fetch('/dashboard/feed?tab=' + tab + '&page=' + page, {
            headers: {
                'Accept':           'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function(r) {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json();
        })
        .then(function(d) {
            document.getElementById('feedGrid')
                    .insertAdjacentHTML('beforeend', d.html);
            if (d.hasMore) {
                self.dataset.page = d.nextPage;
                self.innerHTML    = '<i class="fas fa-chevron-down"></i> Cargar más fotos';
                self.disabled     = false;
            } else {
                var wrap = document.getElementById('loadMoreWrap');
                if (wrap) wrap.style.display = 'none';
            }
        })
        .catch(function(err) {
            self.innerHTML = '<i class="fas fa-exclamation-circle"></i> Error al cargar';
            self.disabled  = false;
            console.error('loadMore error:', err);
        });
    });
}

})();
</script>
@endpush
