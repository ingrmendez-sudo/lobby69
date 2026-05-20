document.addEventListener("DOMContentLoaded", function() {
    console.log("✅ gallery.js cargado");
    
    // Click en foto para modal
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('gallery-photo')) {
            openPhotoModal(e.target);
        }
    });
    
    // Like foto
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('photo-like-btn')) {
            likePhoto(e.target);
        }
    });
    
    // Delete foto
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('photo-delete-btn')) {
            deletePhoto(e.target);
        }
    });
});

function openPhotoModal(photoEl) {
    const photoId = photoEl.getAttribute('data-photo-id');
    const photoSrc = photoEl.src;
    const photoTitle = photoEl.getAttribute('data-title');
    
    showModal(photoTitle || 'Foto', `
        <img src="${photoSrc}" style="width: 100%; border-radius: 8px; margin-bottom: 16px;">
        <div style="display: flex; gap: 10px; justify-content: center;">
            <button onclick="likePhotoFromModal('${photoId}')" style="padding: 10px 20px; background: #e74c3c; color: white; border: none; border-radius: 6px; cursor: pointer;">❤️ Like</button>
            <button onclick="sharePhoto('${photoId}')" style="padding: 10px 20px; background: #3498db; color: white; border: none; border-radius: 6px; cursor: pointer;">📤 Compartir</button>
        </div>
    `, [{ text: 'Cerrar', action: 'close' }]);
}

function likePhoto(btn) {
    const photoId = btn.getAttribute('data-photo-id');
    fetch(`/galeria/like/${photoId}/`, {
        method: 'POST',
        headers: { 'X-CSRFToken': getCookie('csrftoken') }
    })
    .then(r => r.json())
    .then(data => {
        btn.textContent = data.liked ? '❤️' : '🤍';
        showAlert(data.message, 'success');
    })
    .catch(err => showAlert('Error: ' + err, 'error'));
}

function deletePhoto(btn) {
    if (confirm('¿Eliminar esta foto?')) {
        const photoId = btn.getAttribute('data-photo-id');
        fetch(`/galeria/delete/${photoId}/`, {
            method: 'POST',
            headers: { 'X-CSRFToken': getCookie('csrftoken') }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                btn.closest('.gallery-item').remove();
                showAlert('✅ Foto eliminada', 'success');
            }
        });
    }
}

function getCookie(name) {
    let v = null;
    if (document.cookie) {
        document.cookie.split(';').forEach(c => {
            c = c.trim();
            if (c.startsWith(name + '=')) v = decodeURIComponent(c.substring(name.length + 1));
        });
    }
    return v;
}
