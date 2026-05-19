/* GALLERY MODAL - CARRUSEL Y COMENTARIOS */

let currentPhotoIndex = 0;
let allPhotos = [];

document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ gallery-modal.js: Inicializando...');

    initializeGalleryItems();
});

function initializeGalleryItems() {
    console.log('Inicializando items de galería...');

    const galleryItems = document.querySelectorAll('.gallery-item');
    allPhotos = Array.from(galleryItems);

    console.log(`📸 Total de fotos encontradas: ${allPhotos.length}`);

    galleryItems.forEach((item, index) => {
        item.addEventListener('click', function(e) {
            if (e.target.closest('.overlay-btn')) return; // No abrir si hace clic en botón

            console.log(`Abriendo foto ${index + 1}/${allPhotos.length}`);
            openPhotoModal(index);
        });
    });
}

function openPhotoModal(index) {
    currentPhotoIndex = index;
    const photo = allPhotos[index];
    const photoId = photo.dataset.id;
    const photoImg = photo.querySelector('img');
    const photoUrl = photoImg.src;

    console.log(`🖼️  Abriendo modal para foto ID: ${photoId}`);

    // Crear modal
    let modal = document.getElementById('photoModal');
    if (!modal) {
        modal = createPhotoModal();
        document.body.appendChild(modal);
    }

    // Actualizar contenido
    updatePhotoModal(photoId, photoUrl, index);
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';

    // Cargar comentarios
    loadPhotoComments(photoId);
}

function createPhotoModal() {
    const modal = document.createElement('div');
    modal.id = 'photoModal';
    modal.style.cssText = `
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.9);
        z-index: 10000;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        font-family: inherit;
    `;

    modal.innerHTML = `
        <div style="
            width: 95%;
            max-width: 1400px;
            height: 90vh;
            background: var(--card-bg);
            border-radius: 12px;
            display: grid;
            grid-template-columns: 1fr 320px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        ">
            <!-- LADO IZQUIERDO: FOTO + CARRUSEL -->
            <div style="
                display: flex;
                flex-direction: column;
                background: #000;
                position: relative;
                overflow: hidden;
            ">
                <!-- FOTO PRINCIPAL -->
                <div style="
                    flex: 1;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    position: relative;
                    min-height: 0;
                ">
                    <img id="modalPhotoImg" src="" alt="Foto" style="
                        max-width: 100%;
                        max-height: 100%;
                        object-fit: contain;
                        width: 100%;
                        height: 100%;
                    ">

                    <!-- CONTADOR -->
                    <div style="
                        position: absolute;
                        top: 12px;
                        right: 12px;
                        background: rgba(0, 0, 0, 0.7);
                        color: white;
                        padding: 6px 12px;
                        border-radius: 20px;
                        font-size: 12px;
                        font-weight: 600;
                    " id="photoCounter">1/1</div>

                    <!-- BOTÓN ANTERIOR -->
                    <button onclick="previousPhoto()" style="
                        position: absolute;
                        left: 12px;
                        top: 50%;
                        transform: translateY(-50%);
                        background: rgba(255, 255, 255, 0.2);
                        border: 1px solid white;
                        color: white;
                        width: 44px;
                        height: 44px;
                        border-radius: 50%;
                        cursor: pointer;
                        font-size: 20px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        transition: all 0.2s;
                        z-index: 100;
                    " onmouseover="this.style.background='rgba(255, 255, 255, 0.4)'" onmouseout="this.style.background='rgba(255, 255, 255, 0.2)'">
                        ←
                    </button>

                    <!-- BOTÓN SIGUIENTE -->
                    <button onclick="nextPhoto()" style="
                        position: absolute;
                        right: 12px;
                        top: 50%;
                        transform: translateY(-50%);
                        background: rgba(255, 255, 255, 0.2);
                        border: 1px solid white;
                        color: white;
                        width: 44px;
                        height: 44px;
                        border-radius: 50%;
                        cursor: pointer;
                        font-size: 20px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        transition: all 0.2s;
                        z-index: 100;
                    " onmouseover="this.style.background='rgba(255, 255, 255, 0.4)'" onmouseout="this.style.background='rgba(255, 255, 255, 0.2)'">
                        →
                    </button>
                </div>

                <!-- INFO FOTO -->
                <div style="
                    padding: 16px;
                    background: rgba(0, 0, 0, 0.7);
                    color: white;
                    font-size: 13px;
                    border-top: 1px solid rgba(255, 255, 255, 0.1);
                    flex-shrink: 0;
                ">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px; gap: 10px;">
                        <span id="photoCaption" style="flex: 1; white-space: pre-wrap; word-break: break-word;">Sin descripción</span>
                        <span id="photoDate" style="flex-shrink: 0;">Hoy</span>
                    </div>
                    <div style="display: flex; gap: 20px; font-size: 12px;">
                        <span>👁️ <span id="photoViews">0</span></span>
                        <span>❤️ <span id="photoLikes">0</span></span>
                        <span>💬 <span id="photoCommentsCount">0</span></span>
                    </div>
                </div>
            </div>

            <!-- LADO DERECHO: COMENTARIOS -->
            <div style="
                display: flex;
                flex-direction: column;
                background: var(--card-bg);
                border-left: 1px solid var(--border-color);
                overflow: hidden;
            ">
                <!-- HEADER COMENTARIOS -->
                <div style="
                    padding: 16px;
                    border-bottom: 1px solid var(--border-color);
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    flex-shrink: 0;
                ">
                    <h3 style="margin: 0; font-size: 14px; font-weight: 700; color: var(--text-primary);">
                        💬 Comentarios
                    </h3>
                    <button onclick="closePhotoModal()" style="
                        background: none;
                        border: none;
                        font-size: 24px;
                        cursor: pointer;
                        color: var(--text-primary);
                        padding: 0;
                        width: 28px;
                        height: 28px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    ">✕</button>
                </div>

                <!-- LISTA DE COMENTARIOS -->
                <div id="commentsList" style="
                    flex: 1;
                    overflow-y: auto;
                    padding: 12px;
                    min-height: 0;
                ">
                    <p style="
                        text-align: center;
                        color: var(--text-secondary);
                        font-size: 12px;
                        padding: 20px 0;
                    ">Cargando comentarios...</p>
                </div>

                <!-- FORMULARIO NUEVO COMENTARIO -->
                <div style="
                    padding: 12px;
                    border-top: 1px solid var(--border-color);
                    flex-shrink: 0;
                ">
                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                        <input type="text" id="newCommentInput" placeholder="Escribe un comentario..." style="
                            flex: 1;
                            min-width: 100px;
                            padding: 8px 12px;
                            border: 1px solid var(--border-color);
                            border-radius: 4px;
                            background: var(--bg-primary);
                            color: var(--text-primary);
                            font-size: 12px;
                        ">
                        <button onclick="submitComment()" style="
                            padding: 8px 12px;
                            background: var(--primary-color);
                            color: white;
                            border: none;
                            border-radius: 4px;
                            cursor: pointer;
                            font-size: 12px;
                            font-weight: 600;
                            transition: all 0.2s;
                            flex-shrink: 0;
                        " onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'">
                            Enviar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;

    return modal;
}


function updatePhotoModal(photoId, photoUrl, index) {
    document.getElementById('modalPhotoImg').src = photoUrl;
    document.getElementById('photoCounter').textContent = `${index + 1}/${allPhotos.length}`;
    document.getElementById('newCommentInput').dataset.photoId = photoId;

    // Obtener detalles de la foto
    const photoItem = allPhotos[index];
    const caption = photoItem.querySelector('img').alt || 'Sin descripción';
    const likes = photoItem.querySelector('.overlay-btn[data-action="like"]')?.textContent.match(/\d+/)?.[0] || '0';

    document.getElementById('photoCaption').textContent = caption;
    document.getElementById('photoLikes').textContent = likes;
    document.getElementById('photoDate').textContent = 'Hace poco';
}

function loadPhotoComments(photoId) {
    console.log(`📝 Cargando comentarios para foto: ${photoId}`);

    const commentsList = document.getElementById('commentsList');
    commentsList.innerHTML = '<p style="text-align: center; color: var(--text-secondary); font-size: 12px; padding: 20px 0;">Cargando comentarios...</p>';

    fetch(`/galeria/foto/${photoId}/comentarios/`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        console.log('✅ Comentarios cargados:', data);

        if (data.comments && data.comments.length > 0) {
            renderComments(data.comments, photoId);
            document.getElementById('photoCommentsCount').textContent = data.total;
        } else {
            commentsList.innerHTML = '<p style="text-align: center; color: var(--text-secondary); font-size: 12px; padding: 20px 0;">Sin comentarios aún. ¡Sé el primero!</p>';
        }
    })
    .catch(error => {
        console.error('Error cargando comentarios:', error);
        commentsList.innerHTML = '<p style="text-align: center; color: #ef4444; font-size: 12px; padding: 20px 0;">Error al cargar comentarios</p>';
    });
}

function renderComments(comments, photoId) {
    const commentsList = document.getElementById('commentsList');
    commentsList.innerHTML = '';

    comments.forEach(comment => {
        const commentDiv = document.createElement('div');
        commentDiv.style.cssText = `
            padding: 12px;
            margin-bottom: 8px;
            background: var(--bg-primary);
            border-radius: 6px;
            border-left: 3px solid var(--primary-color);
        `;

        const avatar = comment.user_nick?.[0]?.toUpperCase() || '?';
        const date = new Date(comment.created_at).toLocaleDateString('es-ES', {
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });

        commentDiv.innerHTML = `
            <div style="display: flex; gap: 8px; margin-bottom: 8px;">
                <div style="
                    width: 32px;
                    height: 32px;
                    border-radius: 50%;
                    background: var(--primary-color);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    color: white;
                    font-weight: 700;
                    font-size: 14px;
                    flex-shrink: 0;
                    cursor: pointer;
                " onclick="visitProfile('${comment.user_nick}')" title="Ver perfil">
                    ${avatar}
                </div>

                <div style="flex: 1; min-width: 0;">
                    <div style="display: flex; justify-content: space-between; align-items: baseline;">
                        <a href="javascript:visitProfile('${comment.user_nick}')" style="
                            font-size: 12px;
                            font-weight: 600;
                            color: var(--primary-color);
                            text-decoration: none;
                            cursor: pointer;
                        ">
                            ${comment.user_nick}
                        </a>
                        <span style="font-size: 11px; color: var(--text-secondary);">
                            ${date}
                        </span>
                    </div>

                    <p style="
                        margin: 4px 0 0 0;
                        font-size: 12px;
                        color: var(--text-primary);
                        word-wrap: break-word;
                        white-space: pre-wrap;
                    ">
                        ${comment.comment_text}
                    </p>
                </div>
            </div>
        `;

        commentsList.appendChild(commentDiv);
    });
}

function submitComment() {
    const input = document.getElementById('newCommentInput');
    const photoId = input.dataset.photoId;
    const commentText = input.value.trim();

    if (!commentText || commentText.length < 2) {
        showAlert('❌ El comentario debe tener al menos 2 caracteres', 'error');
        return;
    }

    if (commentText.length > 500) {
        showAlert('❌ El comentario no puede exceder 500 caracteres', 'error');
        return;
    }

    console.log(`📝 Enviando comentario para foto: ${photoId}`);

    const csrfToken = getCookie('csrftoken') || document.querySelector('[name=csrfmiddlewaretoken]')?.value;

    fetch(`/galeria/comentar/${photoId}/`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRFToken': csrfToken,
        },
        body: `comment_text=${encodeURIComponent(commentText)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('✅ Comentario enviado');
            input.value = '';
            loadPhotoComments(photoId);
            showAlert('✅ Comentario publicado', 'success');
        } else {
            showAlert('❌ ' + (data.error || 'Error al publicar'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('❌ Error al enviar comentario', 'error');
    });
}

function nextPhoto() {
    if (currentPhotoIndex < allPhotos.length - 1) {
        currentPhotoIndex++;
        const photo = allPhotos[currentPhotoIndex];
        const photoId = photo.dataset.id;
        const photoImg = photo.querySelector('img');
        openPhotoModal(currentPhotoIndex);
    }
}

function previousPhoto() {
    if (currentPhotoIndex > 0) {
        currentPhotoIndex--;
        const photo = allPhotos[currentPhotoIndex];
        const photoId = photo.dataset.id;
        const photoImg = photo.querySelector('img');
        openPhotoModal(currentPhotoIndex);
    }
}

function closePhotoModal() {
    const modal = document.getElementById('photoModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

function visitProfile(nickname) {
    console.log(`Visitando perfil: ${nickname}`);
    window.location.href = `/usuario/${nickname}/`;
}

// Cerrar modal con ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closePhotoModal();
    }
});

// Cerrar modal al hacer clic fuera
document.addEventListener('click', function(e) {
    const modal = document.getElementById('photoModal');
    if (modal && modal.style.display === 'flex' && e.target === modal) {
        closePhotoModal();
    }
});

console.log('✅ gallery-modal.js: Cargado exitosamente');
