let currentPhotoId = null;

// Toggle tema
function toggleTheme() {
    const body = document.body;
    const icon = document.getElementById('theme-icon');
    body.classList.toggle('dark-mode');
    if (body.classList.contains('dark-mode')) {
        localStorage.setItem('theme', 'dark');
        icon.textContent = '☀️';
    } else {
        localStorage.setItem('theme', 'light');
        icon.textContent = '🌙';
    }
}

// Cargar tema guardado
window.addEventListener('DOMContentLoaded', function() {
    const theme = localStorage.getItem('theme') || 'light';
    const icon = document.getElementById('theme-icon');
    if (theme === 'dark') {
        document.body.classList.add('dark-mode');
        icon.textContent = '☀️';
    } else {
        icon.textContent = '🌙';
    }
});

// Abrir modal de foto
function openPhotoModal(event) {
    event.stopPropagation();

    const element = event.currentTarget;
    const image = element.dataset.image;
    const caption = element.dataset.caption;
    const photoId = element.dataset.id;

    currentPhotoId = photoId;

    const modal = document.getElementById('photoModal');
    const modalImg = document.getElementById('modalImage');
    const modalCaption = document.getElementById('modalCaption');

    modalImg.src = image;
    modalCaption.textContent = caption || 'Sin título';
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';

    // Cargar comentarios
    loadComments(photoId);
}

// Cerrar modal
function closePhotoModal() {
    const modal = document.getElementById('photoModal');
    modal.style.display = 'none';
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

            if (data.comments.length === 0) {
                commentsList.innerHTML = '<p style="text-align: center; color: #999; font-size: 13px;">No hay comentarios aún</p>';
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
            document.getElementById('commentsList').innerHTML = '<p style="color: red; font-size: 13px;">Error cargando comentarios</p>';
        });
}

// Enviar comentario
function submitComment() {
    const text = document.getElementById('commentText').value.trim();

    if (!text) {
        alert('Escribe un comentario');
        return;
    }

    const formData = new FormData();
    formData.append('comment_text', text);

    fetch(`/galeria/foto/${currentPhotoId}/comentar/`, {
        method: 'POST',
        headers: {'X-CSRFToken': getCookie('csrftoken')},
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.getElementById('commentText').value = '';
            document.getElementById('charCount').textContent = '0/500';
            loadComments(currentPhotoId);
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(e => {
        console.error(e);
        alert('Error al enviar comentario');
    });
}

// Contar caracteres
document.addEventListener('DOMContentLoaded', function() {
    const textarea = document.getElementById('commentText');
    if (textarea) {
        textarea.addEventListener('keyup', function() {
            document.getElementById('charCount').textContent = this.value.length + '/500';
        });
    }
});

// Like en foto
function toggleLike(photoId) {
    fetch(`/galeria/like/${photoId}/`, {
        method: 'POST',
        headers: {
            'X-CSRFToken': getCookie('csrftoken')
        }
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            // Recargar galería
            location.reload();
        } else {
            alert('Error: ' + d.error);
        }
    })
    .catch(e => alert('Error: ' + e));
}

// Agregar amigo
function agregarAmigo(nick) {
    const btn = event.target;
    const csrftoken = getCookie('csrftoken');

    btn.disabled = true;
    btn.textContent = 'Cargando...';

    fetch(`/usuario/${nick}/agregar-amigo/`, {
        method: 'POST',
        headers: {'X-CSRFToken': csrftoken}
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            btn.textContent = '✓ Solicitud Enviada';
            btn.disabled = true;
        } else {
            btn.textContent = '➕ Agregar Amigo';
            btn.disabled = false;
            alert('Error: ' + data.error);
        }
    })
    .catch(e => {
        btn.textContent = '➕ Agregar Amigo';
        btn.disabled = false;
        alert('Error: ' + e);
    });
}

// Obtener cookie CSRF
function getCookie(name) {
    let value = null;
    if (document.cookie && document.cookie !== '') {
        const cookies = document.cookie.split(';');
        for (let i = 0; i < cookies.length; i++) {
            const cookie = cookies[i].trim();
            if (cookie.substring(0, name.length + 1) === (name + '=')) {
                value = decodeURIComponent(cookie.substring(name.length + 1));
                break;
            }
        }
    }
    return value;
}

// Cerrar modal con ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closePhotoModal();
    }
});

// Cerrar modal al hacer click fuera
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('photoModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closePhotoModal();
            }
        });
    }
});
