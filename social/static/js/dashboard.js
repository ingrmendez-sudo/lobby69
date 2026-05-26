/**
 * dashboard.js - Dashboard y feed
 */

let currentPhotoId = null;

document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ dashboard.js cargado');
    initializeDashboard();
});

function initializeDashboard() {
    // Listeners para imágenes
    document.querySelectorAll('.post-image')?.forEach(img => {
        img.addEventListener('click', function(e) {
            openPhotoModal(e);
        });
    });
    
    // Listener para textarea
    const commentText = document.getElementById('commentText');
    if (commentText) {
        commentText.addEventListener('input', updateCharCount);
    }
}

function openPhotoModal(event) {
    if (!event.target.classList.contains('post-image')) return;
    
    currentPhotoId = event.target.getAttribute('data-id');
    const imageUrl = event.target.getAttribute('data-image');
    const caption = event.target.getAttribute('data-caption');
    
    const modal = document.getElementById('photoModal');
    if (modal) {
        document.getElementById('modalImage').src = imageUrl;
        document.getElementById('modalCaption').textContent = caption;
        modal.style.display = 'flex';
    }
}

function closePhotoModal() {
    const modal = document.getElementById('photoModal');
    if (modal) modal.style.display = 'none';
}

function updateCharCount() {
    const textarea = document.getElementById('commentText');
    const counter = document.getElementById('charCount');
    if (textarea && counter) {
        counter.textContent = textarea.value.length + '/500';
    }
}

console.log('✅ dashboard.js listo');
