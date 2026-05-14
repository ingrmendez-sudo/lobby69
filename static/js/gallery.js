// GALLERY - FUNCIONES ESPECÍFICAS

const uploadInput = document.getElementById('uploadInput');
const uploadForm = document.getElementById('uploadForm');

function triggerUpload() {
    uploadForm.style.display = 'block';
    uploadInput.focus();
}

function closeUploadForm() {
    uploadForm.style.display = 'none';
}

// Manejar envío del formulario
if (uploadForm) {
    uploadForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const file = uploadInput.files[0];
        if (!file) {
            alert('Por favor selecciona una foto');
            return;
        }

        const formData = new FormData();
        formData.append('photo', file);
        formData.append('caption', document.querySelector('textarea[name="caption"]').value || '');
        formData.append('visibility', document.querySelector('select[name="visibility"]').value || 'public');

        const xhr = new XMLHttpRequest();

        xhr.upload.addEventListener('progress', (e) => {
            if (e.lengthComputable) {
                const percentComplete = (e.loaded / e.total) * 100;
                document.getElementById('progressBar').style.width = percentComplete + '%';
                document.getElementById('uploadStatus').textContent = `Subiendo: ${Math.round(percentComplete)}%`;
            }
        });

        xhr.addEventListener('load', () => {
            document.getElementById('progressContainer').style.display = 'none';
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                alert(response.message || '✅ Foto subida correctamente');
                location.reload();
            } else {
                alert('❌ Error en la carga');
            }
        });

        xhr.addEventListener('error', () => {
            alert('❌ Error de conexión');
            document.getElementById('progressContainer').style.display = 'none';
        });

        document.getElementById('progressContainer').style.display = 'block';
        xhr.open('POST', window.location.href);
        xhr.setRequestHeader('X-CSRFToken', document.querySelector('[name=csrfmiddlewaretoken]').value);
        xhr.send(formData);
    });
}

// Modal de imagen
window.openModal = (imageSrc) => {
    const modal = document.getElementById('imageModal');
    const modalImg = document.getElementById('modalImage');
    if (modal && modalImg) {
        modalImg.src = imageSrc;
        modal.classList.add('active');
    }
};

window.closeModal = () => {
    const modal = document.getElementById('imageModal');
    if (modal) {
        modal.classList.remove('active');
    }
};

// Cerrar modal al hacer click fuera de la imagen
const imageModal = document.getElementById('imageModal');
if (imageModal) {
    imageModal.addEventListener('click', (e) => {
        if (e.target.id === 'imageModal') {
            window.closeModal();
        }
    });
}

// Cerrar modal con tecla ESC
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        window.closeModal();
    }
});

// Alternar visibilidad
window.toggleVisibility = (photoId, currentVisibility) => {
    const newVisibility = currentVisibility === 'public' ? 'private' : 'public';
    console.log('[DEBUG] Cambiando', photoId, 'de', currentVisibility, 'a', newVisibility);

    fetch(`/galeria/cambiar-visibilidad/${photoId}/`, {
        method: 'POST',
        headers: {
            'X-CSRFToken': document.querySelector('[name=csrfmiddlewaretoken]').value,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ visibility: newVisibility })
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            alert('✅ Visibilidad actualizada a: ' + (newVisibility === 'public' ? 'Pública' : 'Privada'));
            location.reload();
        } else {
            alert('❌ Error: ' + d.error);
        }
    })
    .catch(e => {
        alert('❌ Error de conexión: ' + e);
    });
};

// Toggle like
window.toggleLike = (photoId) => {
    console.log('[DEBUG] Toggle like para:', photoId);

    fetch(`/galeria/like/${photoId}/`, {
        method: 'POST',
        headers: {
            'X-CSRFToken': document.querySelector('[name=csrfmiddlewaretoken]').value
        }
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            const likesElement = document.getElementById('likes-' + photoId);
            if (likesElement) {
                likesElement.textContent = d.likes_count;
            }
        } else {
            alert('❌ Error: ' + d.error);
        }
    })
    .catch(e => {
        alert('❌ Error: ' + e);
    });
};

// Eliminar foto
window.deletePhoto = (id) => {
    if (confirm('⚠️ ¿Estás seguro de que quieres eliminar esta foto?')) {
        fetch(`/galeria/eliminar/${id}/`, {
            method: 'POST',
            headers: {
                'X-CSRFToken': document.querySelector('[name=csrfmiddlewaretoken]').value
            }
        })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                alert('✅ Foto eliminada correctamente');
                location.reload();
            } else {
                alert('❌ Error: ' + d.error);
            }
        })
        .catch(e => {
            alert('❌ Error: ' + e);
        });
    }
};

// Filtrar galería
window.filterGallery = (filter) => {
    console.log('📊 Filtro aplicado:', filter);
    // Aquí puedes agregar lógica para filtrar las fotos
};

// Configurar clicks en fotos
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        document.querySelectorAll('.gallery-item').forEach((item, index) => {
            const img = item.querySelector('img');
            if (img && !img.onclick) {
                img.onclick = function(e) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    console.log('[DEBUG] Abriendo foto #' + index);
                    window.openModal(this.src);
                    return false;
                };
            }
        });
        console.log('[DEBUG] ' + document.querySelectorAll('.gallery-item').length + ' fotos configuradas');
    }, 100);

    // Actualizar estadísticas
    updateGalleryStats();
});

// Actualizar estadísticas
function updateGalleryStats() {
    const totalPhotos = document.querySelectorAll('.gallery-item').length;
    console.log('📊 Total de fotos:', totalPhotos);
    // Aquí puedes actualizar los valores en el sidebar derecho
}
