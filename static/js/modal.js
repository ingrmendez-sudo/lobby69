// ========== MODAL DE FOTO CON COMENTARIOS ==========
let currentPhotoId = null;

function getCookie(name) {
    let cookieValue = null;
    if (document.cookie && document.cookie !== '') {
        const cookies = document.cookie.split(';');
        for (let i = 0; i < cookies.length; i++) {
            const cookie = cookies[i].trim();
            if (cookie.substring(0, name.length + 1) === (name + '=')) {
                cookieValue = decodeURIComponent(cookie.substring(name.length + 1));
                break;
            }
        }
    }
    return cookieValue;
}

function getCsrfToken() {
    const token = document.querySelector('[name=csrfmiddlewaretoken]')?.value;
    if (token) return token;
    return getCookie('csrftoken');
}

// ========== FUNCIONES DEL MODAL ==========
function openPhotoModal(photoId, event) {
    if (event) event.preventDefault();

    photoId = String(photoId).trim();

    if (!photoId || photoId === 'undefined' || photoId === '') {
        console.error('❌ photoId es inválido:', photoId);
        return;
    }

    console.log('📸 Abriendo modal para foto:', photoId);
    currentPhotoId = photoId;
    window.currentPhotoId = photoId;

    const modal = document.getElementById('photoModal');
    const modalImg = document.getElementById('modalImage');

    // Buscar la foto en el DOM por data-photo-id
    const photoPost = document.querySelector(`[data-photo-id="${photoId}"]`);

    if (photoPost) {
        const img = photoPost.querySelector('.post-image');
        if (img && modalImg) {
            console.log('📸 Foto encontrada, src:', img.src);
            modalImg.src = img.src;
            const caption = img.dataset.caption || 'Sin título';
            document.getElementById('modalCaption').textContent = caption;
        }
    } else {
        console.warn('⚠️ No se encontró la foto con ID:', photoId);
    }

    if (modal) {
        modal.classList.add('active');  // Usa CLASE, no style
        document.body.style.overflow = 'hidden';
        loadComments(photoId);
        console.log('✅ Modal abierto. currentPhotoId =', currentPhotoId);
    }
}

function closePhotoModal() {
    const modal = document.getElementById('photoModal');
    if (modal) {
        modal.classList.remove('active');  // Usa CLASE, no style
        document.body.style.overflow = 'auto';
    }
    const commentText = document.getElementById('commentText');
    if (commentText) commentText.value = '';
    const charCount = document.getElementById('charCount');
    if (charCount) charCount.textContent = '0/500';
}

// ========== COMENTARIOS ==========
function loadComments(photoId) {
    console.log('💬 Cargando comentarios para foto:', photoId);
    fetch(`/galeria/foto/${photoId}/comentarios/`)
        .then(r => r.json())
        .then(data => {
            console.log('✅ Comentarios cargados:', data);
            const commentsList = document.getElementById('commentsList');
            if (!commentsList) return;

            const commentCount = document.getElementById('commentCount');
            if (commentCount) {
                commentCount.textContent = `💬 ${data.comments ? data.comments.length : 0}`;
            }

            if (!data.comments || data.comments.length === 0) {
                commentsList.innerHTML = '<p style="text-align: center; color: var(--text-secondary);">No hay comentarios aún</p>';
            } else {
                commentsList.innerHTML = data.comments.map(c => `
                    <div class="comment-item">
                        <div class="comment-header">
                            <strong>${c.user_nick || 'Usuario'}</strong>
                            <small>${new Date(c.created_at).toLocaleDateString()}</small>
                        </div>
                        <p>${c.comment_text}</p>
                    </div>
                `).join('');
            }
        })
        .catch(e => {
            console.error('❌ Error cargando comentarios:', e);
            const commentsList = document.getElementById('commentsList');
            if (commentsList) commentsList.innerHTML = '<p style="color: red;">Error cargando comentarios</p>';
        });
}

function submitComment() {
    const text = document.getElementById('commentText').value.trim();
    if (!text || text.length < 2) {
        alert('❌ El comentario debe tener al menos 2 caracteres');
        return;
    }
    if (!currentPhotoId) {
        alert('❌ Error: No hay foto seleccionada');
        return;
    }

    const csrfToken = getCsrfToken();
    if (!csrfToken) {
        alert('❌ Error: Token CSRF no encontrado');
        return;
    }

    console.log(`📤 Enviando comentario para foto: ${currentPhotoId}`);

    fetch(`/galeria/comentar/${currentPhotoId}/`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRFToken': csrfToken
        },
        body: JSON.stringify({ comment_text: text })
    })
    .then(r => {
        if (!r.ok) {
            return r.json().then(d => {
                throw new Error(d.error || `HTTP ${r.status}`);
            });
        }
        return r.json();
    })
    .then(data => {
        if (data.success) {
            console.log('✅ Comentario publicado');
            document.getElementById('commentText').value = '';
            document.getElementById('charCount').textContent = '0/500';
            loadComments(currentPhotoId);
            alert('✅ Comentario publicado');
        }
    })
    .catch(e => {
        console.error('❌ Error:', e);
        alert('❌ Error: ' + e.message);
    });
}

// ========== EVENTOS DEL MODAL ==========
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('photoModal');
    if (modal) {
        // Cerrar al hacer clic fuera
        modal.addEventListener('click', function(e) {
            if (e.target.id === 'photoModal') {
                closePhotoModal();
            }
        });
    }

    // Cerrar con ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closePhotoModal();
        }
    });

    // Contador de caracteres
    const commentText = document.getElementById('commentText');
    if (commentText) {
        commentText.addEventListener('keyup', function() {
            document.getElementById('charCount').textContent = this.value.length + '/500';
        });
    }
});
