// ========== MODAL DE FOTO CON COMENTARIOS ==========
let currentPhotoId = null;

function openPhotoModal(eventOrImageSrc, captionParam) {
    let imageSrc, caption, photoId;
    
    // Si es un evento
    if (eventOrImageSrc && typeof eventOrImageSrc === 'object' && eventOrImageSrc.currentTarget) {
        const element = eventOrImageSrc.currentTarget;
        imageSrc = element.dataset.image;
        caption = element.dataset.caption;
        photoId = element.dataset.id;
    } else {
        imageSrc = eventOrImageSrc;
        caption = captionParam;
    }
    
    currentPhotoId = photoId;
    const modal = document.getElementById('photoModal');
    const modalImg = document.getElementById('modalImage');
    const modalCaption = document.getElementById('modalCaption');
    
    if (modalImg) modalImg.src = imageSrc;
    if (modalCaption) modalCaption.textContent = caption || '';
    
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        if (photoId) loadComments(photoId);
    }
}

function closePhotoModal() {
    const modal = document.getElementById('photoModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
    const commentText = document.getElementById('commentText');
    if (commentText) commentText.value = '';
    const charCount = document.getElementById('charCount');
    if (charCount) charCount.textContent = '0/500';
}

function loadComments(photoId) {
    console.log('Cargando comentarios para:', photoId);
    fetch(`/galeria/foto/${photoId}/comentarios/`)
        .then(r => r.json())
        .then(data => {
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
            console.error('Error cargando comentarios:', e);
            const commentsList = document.getElementById('commentsList');
            if (commentsList) commentsList.innerHTML = '<p style="color: red;">Error cargando comentarios</p>';
        });
}
