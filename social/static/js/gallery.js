/**
 * gallery.js - Funcionalidades mejoradas de Galería
 * Usa los endpoints API reales
 */

document.addEventListener("DOMContentLoaded", function() {
    console.log("✅ gallery.js v2 cargado");
    
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('gallery-photo')) {
            openPhotoModal(e.target);
        }
        if (e.target.classList.contains('photo-like-btn')) {
            likePhotoAPI(e.target);
        }
        if (e.target.classList.contains('photo-delete-btn')) {
            deletePhotoAPI(e.target);
        }
    });
});

function likePhotoAPI(btn) {
    const photoId = btn.getAttribute('data-photo-id');
    
    fetch(`/api/photo/${photoId}/like/`, {
        method: 'POST',
        headers: { 'X-CSRFToken': getCookie('csrftoken') }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            btn.textContent = data.liked ? '❤️' : '🤍';
            if (data.likes_count) {
                const counter = btn.closest('.photo-actions').querySelector('.likes-count');
                if (counter) counter.textContent = data.likes_count;
            }
            showAlert(data.message, 'success');
        } else {
            showAlert(data.message || 'Error', 'error');
        }
    })
    .catch(err => showAlert('Error: ' + err, 'error'));
}

function deletePhotoAPI(btn) {
    const photoId = btn.getAttribute('data-photo-id');
    
    if (!confirm('¿Eliminar esta foto?')) return;
    
    fetch(`/api/photo/${photoId}/delete/`, {
        method: 'POST',
        headers: { 'X-CSRFToken': getCookie('csrftoken') }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            btn.closest('.gallery-item').remove();
            showAlert(data.message, 'success');
        } else {
            showAlert(data.message || 'Error', 'error');
        }
    })
    .catch(err => showAlert('Error: ' + err, 'error'));
}

function openPhotoModal(photoEl) {
    showModal('Foto', `<img src="${photoEl.src}" style="width:100%; border-radius: 8px;">`);
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
