/**
 * explore.js - Funcionalidades mejoradas de Explorar
 * Usa los endpoints API reales
 */

document.addEventListener("DOMContentLoaded", function() {
    console.log("✅ explore.js v2 cargado");
    
    setupSearch('#searchInput', '.profile-card', ['data-profile-nick']);
    setupFilter('.filter-btn', '.profile-card', 'data-filter');
    
    // Like profile
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('like-btn')) {
            const profileId = e.target.getAttribute('data-profile-id');
            likeProfileAPI(profileId, e.target);
        }
    });
    
    // Add friend
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('add-friend-btn')) {
            const nickname = e.target.getAttribute('data-nickname');
            addFriendAPI(nickname, e.target);
        }
    });
});

function likeProfileAPI(profileId, btn) {
    fetch(`/api/profile/${profileId}/like/`, {
        method: 'POST',
        headers: {
            'X-CSRFToken': getCookie('csrftoken'),
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({})
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            btn.textContent = data.liked ? '❤️' : '🤍';
            btn.style.color = data.liked ? '#e74c3c' : '#999';
            showAlert(data.message, 'success');
        } else {
            showAlert(data.message || 'Error', 'error');
        }
    })
    .catch(err => showAlert('Error: ' + err, 'error'));
}

function addFriendAPI(nickname, btn) {
    fetch(`/api/friend/${nickname}/add/`, {
        method: 'POST',
        headers: { 'X-CSRFToken': getCookie('csrftoken') }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            btn.textContent = '✓ Agregado';
            btn.disabled = true;
            showAlert(data.message, 'success');
        } else {
            showAlert(data.message || 'Error', 'error');
        }
    })
    .catch(err => showAlert('Error: ' + err, 'error'));
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
