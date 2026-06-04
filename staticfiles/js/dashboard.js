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
function openPhotoModal_comments(photoId) {
    if (!photoId || photoId === 'undefined') {
        console.error('❌ photoId is undefined');
        return;
    }

    console.log('Abriendo modal para foto:', photoId);
    currentPhotoId = photoId;
    const modal = document.getElementById('photoModal');

    // Obtener imagen y caption del DOM
    const photoElement = document.querySelector(`[data-photo-id="${photoId}"]`);
    if (photoElement) {
        const img = photoElement.querySelector('.post-image');
        if (img) {
            document.getElementById('modalImage').src = img.src;
            document.getElementById('modalCaption').textContent = img.dataset.caption || 'Sin título';
        }
    }

    modal.style.display = 'flex';
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
