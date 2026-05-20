document.addEventListener("DOMContentLoaded", function() {
    console.log("✅ explore.js cargado");
    
    // Búsqueda de perfiles
    setupSearch('#searchInput', '.profile-item', ['profile-name', 'profile-city']);
    
    // Filtros
    setupFilter('.filter-btn', '.profile-item', 'data-filter');
    
    // Like profile
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('like-btn')) {
            const profileId = e.target.getAttribute('data-profile-id');
            likeProfile(profileId, e.target);
        }
    });
    
    // Add friend
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('add-friend-btn')) {
            const profileId = e.target.getAttribute('data-profile-id');
            addFriend(profileId, e.target);
        }
    });
});

function likeProfile(profileId, btn) {
    fetch(`/explorar/like/${profileId}/`, {
        method: 'POST',
        headers: {
            'X-CSRFToken': getCookie('csrftoken'),
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ action: 'like' })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            btn.textContent = data.liked ? '❤️' : '🤍';
            btn.style.color = data.liked ? '#e74c3c' : '#999';
            showAlert(data.message, 'success');
        }
    })
    .catch(err => showAlert('Error: ' + err, 'error'));
}

function addFriend(profileId, btn) {
    fetch(`/explorar/add-friend/${profileId}/`, {
        method: 'POST',
        headers: { 'X-CSRFToken': getCookie('csrftoken') }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            btn.textContent = '✓ Agregado';
            btn.disabled = true;
            showAlert('✅ Solicitud enviada', 'success');
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
