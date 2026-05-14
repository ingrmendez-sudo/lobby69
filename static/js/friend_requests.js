// FRIEND REQUESTS - FUNCIONES ESPECÍFICAS

function filterFriendRequests(filter) {
    console.log('🔍 Filtro aplicado:', filter);
    window.location.href = `?filter=${filter}`;
}

function acceptFriendRequest(friendshipId, friendName) {
    if (confirm(`¿Aceptar solicitud de amistad de ${friendName}?`)) {
        console.log('✅ Aceptando solicitud de:', friendName);
        // El formulario se envía automáticamente
        document.querySelector(`form[data-friendship="${friendshipId}"][data-action="accept"]`).submit();
    }
}

function rejectFriendRequest(friendshipId, friendName) {
    if (confirm(`¿Rechazar solicitud de amistad de ${friendName}?`)) {
        console.log('❌ Rechazando solicitud de:', friendName);
        // El formulario se envía automáticamente
        document.querySelector(`form[data-friendship="${friendshipId}"][data-action="reject"]`).submit();
    }
}

function unfriendUser(userId, friendName) {
    if (confirm(`¿Eliminar de amigos a ${friendName}?`)) {
        console.log('🗑️ Eliminando amigo:', friendName);
        alert('✅ ' + friendName + ' ha sido eliminado de tus amigos');
    }
}

function suggestFriend(userId, friendName) {
    console.log('💬 Enviando solicitud a:', friendName);
    alert('✅ Solicitud de amistad enviada a ' + friendName);
}

function viewFriendProfile(userId) {
    console.log('👁️ Ver perfil de amigo');
}

document.addEventListener('DOMContentLoaded', function() {
    console.log('👥 Solicitudes de amistad cargadas');

    // Agregar listeners a los cards de solicitudes
    document.querySelectorAll('.request-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.cursor = 'pointer';
        });
    });

    // Agregar listeners a los amigos mini
    document.querySelectorAll('.friend-mini-card').forEach(card => {
        card.addEventListener('click', function() {
            const friendName = this.querySelector('.friend-mini-name').textContent;
            console.log('👁️ Viendo perfil de:', friendName);
        });
    });

    // Agregar listeners a las sugerencias
    document.querySelectorAll('.suggestion-card').forEach(card => {
        card.addEventListener('click', function() {
            const friendName = this.querySelector('.suggestion-name').textContent;
            console.log('💬 Enviando solicitud a:', friendName);
        });
    });
});
