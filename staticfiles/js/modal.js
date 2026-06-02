// ========== MODAL DE FOTO CON COMENTARIOS ==========
let currentPhotoId = null;

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

function getCsrfToken() {
    return document.querySelector('[name=csrfmiddlewaretoken]')?.value || getCookie('csrftoken');
}

function loadComments(photoId) {
    console.log('💬 Cargando comentarios para foto:', photoId);
    fetch(`/galeria/foto/${photoId}/comentarios/`)
        .then(r => r.json())
        .then(data => {
            console.log('✅ Comentarios cargados:', data);
            const list = document.getElementById('commentsList');
            const count = document.getElementById('commentCount');

            if (count) count.textContent = `💬 ${data.comments?.length || 0}`;

            if (!data.comments || !data.comments.length) {
                list.innerHTML = '<p style="text-align:center;color:var(--text-secondary);">No hay comentarios aún</p>';
            } else {
                list.innerHTML = data.comments.map(c => `
                    <div class="comment-item">
                        <div style="font-weight:600;color:var(--primary-color);margin-bottom:5px;">
                            ${c.user_nick || 'Usuario'}
                        </div>
                        <div style="font-size:12px;color:var(--text-secondary);margin-bottom:5px;">
                            ${new Date(c.created_at).toLocaleDateString()}
                        </div>
                        <p style="margin:0;color:var(--text-primary);">${c.comment_text}</p>
                    </div>
                `).join('');
            }
        })
        .catch(e => {
            console.error('❌ Error:', e);
            const list = document.getElementById('commentsList');
            list.innerHTML = '<p style="color:red;">Error cargando comentarios</p>';
        });
}

function submitComment() {
    console.log('📝 submitComment llamada. currentPhotoId:', currentPhotoId);

    const textarea = document.getElementById('commentText');
    const txt = textarea.value.trim();

    if (txt.length < 2) {
        alert('El comentario debe tener al menos 2 caracteres');
        return;
    }
    if (!currentPhotoId) {
        alert('❌ Error: No hay foto seleccionada. currentPhotoId es null');
        console.error('currentPhotoId es null');
        return;
    }

    const token = getCsrfToken();
    if (!token) {
        alert('Error: Token CSRF no encontrado');
        return;
    }

    console.log('📤 Enviando comentario para foto:', currentPhotoId);
    console.log('📤 Texto:', txt);

    fetch(`/galeria/comentar/${currentPhotoId}/`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRFToken': token
        },
        body: JSON.stringify({ comment_text: txt })
    })
    .then(r => {
        console.log('📊 Respuesta status:', r.status);
        if (!r.ok) {
            return r.json().then(d => {
                throw new Error(d.error || `HTTP ${r.status}`);
            });
        }
        return r.json();
    })
    .then(d => {
        console.log('✅ Respuesta del servidor:', d);
        if (d.success) {
            textarea.value = '';
            document.getElementById('charCount').textContent = '0/500';

            // Actualizar contador en la página
            const counter = document.querySelector(`.comments-count[data-photo-id="${currentPhotoId}"]`);
            if (counter) {
                let count = parseInt(counter.textContent) || 0;
                counter.textContent = count + 1;
                console.log('📊 Contador actualizado a:', count + 1);
            }

            loadComments(currentPhotoId);
            alert('✅ Comentario publicado');
        }
    })
    .catch(e => {
        console.error('❌ Error:', e);
        alert('❌ Error: ' + e.message);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    console.log('✅ modal.js cargado');

    const modal = document.getElementById('photoModal');
    if (modal) {
        // Cerrar al hacer click fuera
        modal.addEventListener('click', (e) => {
            if (e.target.id === 'photoModal') {
                closePhotoModal();
            }
        });
    }

    // Cerrar con ESC
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closePhotoModal();
        }
    });

    // Contador de caracteres
    const textarea = document.getElementById('commentText');
    if (textarea) {
        textarea.addEventListener('keyup', () => {
            document.getElementById('charCount').textContent = textarea.value.length + '/500';
        });
    }
});
