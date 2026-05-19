/* GALLERY PAGE - FUNCIONES ESPECÍFICAS */

document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ gallery.js: Inicializando...');

    initializeUploadForm();
    initializeFilterTabs();
    initializeGalleryActions();
    initializeImagePreview();
});

/**
 * FORMULARIO DE CARGA
 */
function initializeUploadForm() {
    console.log('Inicializando formulario...');

    const uploadBtn = document.querySelector('.btn-upload');
    const uploadForm = document.getElementById('uploadForm');
    const uploadInput = document.getElementById('uploadInput');

    console.log('uploadBtn:', uploadBtn);
    console.log('uploadForm:', uploadForm);

    if (uploadBtn) {
        uploadBtn.addEventListener('click', function(e) {
            e.preventDefault();
            triggerUpload();
        });
        console.log('✅ Botón de upload vinculado');
    }

    if (uploadForm) {
        uploadForm.addEventListener('submit', handleUpload);
        console.log('✅ Formulario vinculado');
    }

    if (uploadInput) {
        uploadInput.addEventListener('change', handleFileSelect);
    }
}

function triggerUpload() {
    console.log('triggerUpload() llamado');
    const uploadForm = document.getElementById('uploadForm');
    if (uploadForm) {
        const isHidden = uploadForm.style.display === 'none';
        uploadForm.style.display = isHidden ? 'block' : 'none';
        console.log('Formulario:', isHidden ? 'mostrado' : 'ocultado');
    } else {
        console.error('uploadForm no encontrado');
    }
}

function closeUploadForm() {
    console.log('closeUploadForm() llamado');
    const uploadForm = document.getElementById('uploadForm');
    if (uploadForm) {
        uploadForm.style.display = 'none';
    }
}

function handleFileSelect(event) {
    const file = event.target.files[0];
    if (file) {
        console.log('Archivo seleccionado:', file.name);

        if (!file.type.startsWith('image/')) {
            alert('Por favor selecciona una imagen válida');
            event.target.value = '';
            return;
        }

        const maxSize = 10 * 1024 * 1024;
        if (file.size > maxSize) {
            alert('El archivo es demasiado grande (máximo 10MB)');
            event.target.value = '';
            return;
        }
    }
}

function handleUpload(event) {
    event.preventDefault();
    console.log('handleUpload() llamado');

    const formData = new FormData(this);
    const progressContainer = document.getElementById('progressContainer');
    const progressBar = document.getElementById('progressBar');
    const csrfToken = getCookie('csrftoken');

    if (!csrfToken) {
        console.error('CSRF token no encontrado');
        showAlert('Error: Token CSRF no encontrado', 'error');
        return;
    }

    formData.append('csrfmiddlewaretoken', csrfToken);

    if (progressContainer) {
        progressContainer.style.display = 'block';
    }

    console.log('Subiendo foto...');
    console.log('FormData contenido:', {
        photo: formData.get('photo'),
        description: formData.get('description'),
        visibility: formData.get('visibility')
    });

    fetch('/galeria/', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRFToken': csrfToken,
        }
    })
    .then(response => {
        console.log('Respuesta status:', response.status);

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        return response.json();
    })
    .then(data => {
        console.log('Respuesta del servidor:', data);

        if (data.success) {
            console.log('✅ Foto subida exitosamente');
            showAlert('✅ Foto subida correctamente. Pendiente de validación.', 'success');

            // Limpiar formulario
            if (event.target && event.target.reset) {
                event.target.reset();
            }

            closeUploadForm();

            // Recargar después de 2 segundos
            setTimeout(() => {
                location.reload();
            }, 2000);
        } else {
            console.error('Error del servidor:', data.error);
            showAlert('❌ ' + (data.error || 'Error al subir la foto'), 'error');
        }
    })
    .catch(error => {
        console.error('Error en la solicitud:', error);
        console.error('Stack:', error.stack);
        showAlert('❌ Error de conexión: ' + error.message, 'error');
    })
    .finally(() => {
        if (progressContainer) {
            progressContainer.style.display = 'none';
        }
    });
}

/**
 * FILTROS Y TABS
 */
function initializeFilterTabs() {
    console.log('Inicializando filtros...');

    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const filterValue = this.dataset.filter;
            console.log('Filtro seleccionado:', filterValue);

            filterGallery(filterValue);

            document.querySelectorAll('.tab-btn').forEach(b => {
                b.classList.remove('active');
                b.style.color = 'var(--text-secondary)';
                b.style.borderBottomColor = 'transparent';
            });

            this.classList.add('active');
            this.style.color = 'var(--text-primary)';
            this.style.borderBottomColor = 'var(--primary-color)';
        });
    });
}

function filterGallery(filterValue) {
    console.log('Filtrando por:', filterValue);
    const url = new URL(window.location);
    url.searchParams.set('filter', filterValue);
    window.history.replaceState({}, '', url);
}

/**
 * ACCIONES DE GALERÍA
 */
function initializeGalleryActions() {
    console.log('Inicializando acciones de galería...');

    document.querySelectorAll('.overlay-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const action = this.dataset.action;
            const photoId = this.dataset.photoId;

            console.log('Acción:', action, 'Foto ID:', photoId);

            if (action === 'like') {
                toggleLike(photoId, this);
            } else if (action === 'comment') {
                viewPhoto(photoId);
            }
        });
    });

    document.querySelectorAll('.gallery-item').forEach(item => {
        item.addEventListener('mouseenter', function() {
            const overlay = this.querySelector('.gallery-overlay');
            if (overlay) overlay.style.opacity = '1';
        });

        item.addEventListener('mouseleave', function() {
            const overlay = this.querySelector('.gallery-overlay');
            if (overlay) overlay.style.opacity = '0';
        });
    });
}

function toggleLike(photoId, button) {
    const csrfToken = getCookie('csrftoken');

    fetch(`/galeria/like/${photoId}/`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRFToken': csrfToken,
        },
        body: JSON.stringify({ photo_id: photoId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Like actualizado:', data);
            const isLiked = data.action === 'like';
            button.textContent = isLiked ? '❤️ ' + data.likes_count : '🤍 ' + data.likes_count;
            button.classList.toggle('liked');
        }
    })
    .catch(error => console.error('Error:', error));
}

function viewPhoto(photoId) {
    console.log('Ver foto:', photoId);
    window.location.href = `/galeria/foto/${photoId}/`;
}

/**
 * PREVIEW DE IMAGEN
 */
function initializeImagePreview() {
    const uploadInput = document.getElementById('uploadInput');
    if (uploadInput) {
        uploadInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    console.log('Preview de imagen cargado');
                };
                reader.readAsDataURL(file);
            }
        });
    }
}

/**
 * UTILIDADES
 */
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
    console.log(`🔍 CSRF Token encontrado: ${cookieValue ? '✅ SÍ' : '❌ NO'}`);
    return cookieValue;
}

function handleUpload(event) {
    event.preventDefault();
    console.log('✅ handleUpload() llamado');

    // 1. Obtener el token CSRF del DOM (más seguro)
    const csrfTokenInput = document.querySelector('[name=csrfmiddlewaretoken]');
    let csrfToken = null;

    if (csrfTokenInput) {
        csrfToken = csrfTokenInput.value;
        console.log('✅ CSRF token desde INPUT:', csrfToken.substring(0, 10) + '...');
    } else {
        csrfToken = getCookie('csrftoken');
        console.log('ℹ️  CSRF token desde COOKIE:', csrfToken ? 'Encontrado' : 'NO ENCONTRADO');
    }

    if (!csrfToken) {
        console.error('❌ CSRF token no encontrado en INPUT ni en COOKIE');
        showAlert('❌ Error de seguridad: Token CSRF no encontrado. Recarga la página.', 'error');
        return;
    }

    const formData = new FormData(this);
    console.log('📦 FormData:', {
        photo: formData.get('photo')?.name || 'SIN ARCHIVO',
        description: formData.get('description') || 'SIN DESCRIPCIÓN',
        visibility: formData.get('visibility') || 'public'
    });

    const progressContainer = document.getElementById('progressContainer');
    if (progressContainer) progressContainer.style.display = 'block';

    console.log('🚀 Iniciando solicitud POST a /galeria/');

    fetch('/galeria/', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRFToken': csrfToken,
        }
    })
    .then(response => {
        console.log('📡 Respuesta recibida - Status:', response.status);

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        return response.json();
    })
    .then(data => {
        console.log('✅ Respuesta JSON:', data);

        if (data.success) {
            console.log('🎉 ¡Foto subida correctamente!');
            showAlert('✅ Foto subida correctamente. Pendiente de validación.', 'success');

            if (this && this.reset) {
                this.reset();
            }

            closeUploadForm();

            setTimeout(() => {
                location.reload();
            }, 2000);
        } else {
            console.error('❌ Error del servidor:', data.error);
            showAlert('❌ ' + (data.error || 'Error al subir la foto'), 'error');
        }
    })
    .catch(error => {
        console.error('❌ Error en fetch:', error.message);
        console.error('Stack:', error.stack);
        showAlert('❌ Error: ' + error.message, 'error');
    })
    .finally(() => {
        if (progressContainer) progressContainer.style.display = 'none';
    });
}


function showAlert(message, type = 'info') {
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.textContent = message;
    alert.style.position = 'fixed';
    alert.style.top = '100px';
    alert.style.right = '20px';
    alert.style.zIndex = '9999';
    alert.style.maxWidth = '300px';
    alert.style.padding = '12px 16px';
    alert.style.borderRadius = '4px';
    alert.style.background = type === 'success' ? '#10b981' : '#ef4444';
    alert.style.color = 'white';
    alert.style.fontSize = '13px';

    document.body.appendChild(alert);

    setTimeout(() => {
        alert.remove();
    }, 3000);
}

console.log('✅ gallery.js: Cargado exitosamente');
