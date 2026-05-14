// DASHBOARD - FUNCIONES ESPECÍFICAS

let currentPhotoId = null;

// Like a foto
function toggleLike(photoId) {
    const csrftoken = document.querySelector('[name=csrfmiddlewaretoken]').value;
    fetch(`/galeria/like/${photoId}/`, {
        method: 'POST',
        headers: {'X-CSRFToken': csrftoken}
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            location.reload();
        } else {
            alert('Error: ' + d.error);
        }
    })
    .catch(e => alert('Error: ' + e));
}

// Abrir modal de foto
function openPhotoModal(event) {
    const element = event.currentTarget;
    const image = element.dataset.image;
    const caption = element.dataset.caption;
    const photoId = element.dataset.id;
    currentPhotoId = photoId;
    const modal = document.getElementById('photoModal');
    document.getElementById('modalImage').src = image;
    document.getElementById('modalCaption').textContent = caption || 'Sin título';
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
    loadComments(photoId);
}

// Cerrar modal de foto
function closePhotoModal() {
    const modal = document.getElementById('photoModal');
    modal.classList.remove('active');
    document.body.style.overflow = 'auto';
    document.getElementById('commentText').value = '';
    document.getElementById('charCount').textContent = '0/500';
}

// Cargar comentarios
function loadComments(photoId) {
    fetch(`/galeria/foto/${photoId}/comentarios/`)
        .then(r => r.json())
        .then(data => {
            const commentsList = document.getElementById('commentsList');
            const commentCount = document.getElementById('commentCount');

            commentCount.textContent = `💬 ${data.comments ? data.comments.length : 0}`;

            if (!data.comments || data.comments.length === 0) {
                commentsList.innerHTML = '<p style="text-align: center; color: var(--text-secondary);">No hay comentarios aún</p>';
            } else {
                commentsList.innerHTML = data.comments.map(c => `
                    <div class="comment-item">
                        <div class="comment-header">
                            <strong>${c.user_nick}</strong>
                            <small>${new Date(c.created_at).toLocaleDateString()}</small>
                        </div>
                        <p>${c.comment_text}</p>
                    </div>
                `).join('');
            }
        })
        .catch(e => {
            console.error(e);
            document.getElementById('commentsList').innerHTML = '<p style="color: red;">Error cargando comentarios</p>';
        });
}

// Enviar comentario
function submitComment() {
    const text = document.getElementById('commentText').value.trim();
    if (!text) { alert('Escribe un comentario'); return; }

    const csrftoken = document.querySelector('[name=csrfmiddlewaretoken]').value;
    const formData = new FormData();
    formData.append('comment_text', text);

    fetch(`/galeria/comentar/${currentPhotoId}/`, {
        method: 'POST',
        headers: {'X-CSRFToken': csrftoken},
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.getElementById('commentText').value = '';
            document.getElementById('charCount').textContent = '0/500';
            loadComments(currentPhotoId);
            const msg = document.createElement('div');
            msg.textContent = '✓ Comentario publicado';
            msg.style.cssText = 'padding: 10px; background: #27ae60; color: white; border-radius: 4px; margin-bottom: 10px; text-align: center;';
            document.getElementById('commentsList').parentElement.insertBefore(msg, document.getElementById('commentsList'));
            setTimeout(() => msg.remove(), 3000);
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(e => { console.error(e); alert('Error: ' + e); });
}

// Guardar post
function savePost(photoId) {
    const csrftoken = document.querySelector('[name=csrfmiddlewaretoken]').value;
    fetch(`/galeria/guardar/${photoId}/`, {
        method: 'POST',
        headers: {'X-CSRFToken': csrftoken}
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const el = document.getElementById(`saves-${photoId}`);
            if (el) {
                el.textContent = data.count;
            }
            loadSavesCounts();
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(e => alert('Error: ' + e));
}

// Cargar contadores de saves
function loadSavesCounts() {
    const photos = document.querySelectorAll('[data-id]');
    photos.forEach(photo => {
        const photoId = photo.dataset.id;
        fetch(`/galeria/guardar/${photoId}/contador/`)
            .then(r => r.json())
            .then(data => {
                const el = document.getElementById(`saves-${photoId}`);
                if (el) {
                    el.textContent = data.count;
                }
            })
            .catch(e => console.error(e));
    });
}

// Ver perfil
function viewProfile(userId) {
    window.location.href = `/usuario/${userId}/`;
}

// Cargar contadores al cargar página
document.addEventListener('DOMContentLoaded', function() {
    loadSavesCounts();

    const photos = document.querySelectorAll('[data-id]');
    photos.forEach(photo => {
        const photoId = photo.dataset.id;
        fetch(`/galeria/foto/${photoId}/comentarios/`)
            .then(r => r.json())
            .then(data => {
                const el = document.getElementById(`comments-${photoId}`);
                if (el) {
                    el.textContent = ` ${data.comments ? data.comments.length : 0} Comentarios`;
                }
            })
            .catch(e => console.error(e));
    });

    // Cerrar modal con ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closePhotoModal();
        }
    });
});

// Auto-resize textarea comentarios
document.getElementById('commentText')?.addEventListener('keyup', function() {
    document.getElementById('charCount').textContent = this.value.length + '/500';
});

// Cerrar modal al hacer clic fuera
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('photoModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target.id === 'photoModal') {
                closePhotoModal();
            }
        });
    }
});
