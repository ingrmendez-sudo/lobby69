document.addEventListener("DOMContentLoaded", function() {
    console.log("✅ friend_requests.js cargado");
    
    setupSearch('#searchRequests', '.request-item', ['request-name']);
    
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('accept-btn')) {
            acceptFriendRequest(e.target);
        }
        if (e.target.classList.contains('reject-btn')) {
            rejectFriendRequest(e.target);
        }
    });
});

function acceptFriendRequest(btn) {
    const requestId = btn.getAttribute('data-request-id');
    fetch(`/amistades/accept/${requestId}/`, {
        method: 'POST',
        headers: { 'X-CSRFToken': getCookie('csrftoken') }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            btn.closest('.request-item').remove();
            showAlert('✅ Solicitud aceptada', 'success');
        }
    });
}

function rejectFriendRequest(btn) {
    const requestId = btn.getAttribute('data-request-id');
    fetch(`/amistades/reject/${requestId}/`, {
        method: 'POST',
        headers: { 'X-CSRFToken': getCookie('csrftoken') }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            btn.closest('.request-item').remove();
            showAlert('✅ Solicitud rechazada', 'success');
        }
    });
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
