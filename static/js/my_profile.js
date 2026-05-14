// MY PROFILE - FUNCIONES ESPECÍFICAS

document.addEventListener('DOMContentLoaded', function() {
    // Cerrar alertas automáticamente
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.5s ease';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });

    // Agregar interactividad a los badges
    const badges = document.querySelectorAll('.badge');
    badges.forEach(badge => {
        badge.addEventListener('click', function() {
            const badgeName = this.getAttribute('data-badge') || 'Insignia';
            alert('🏆 ' + badgeName);
        });
    });

    // Agregar interactividad a los perfiles mini
    const profileCardsMini = document.querySelectorAll('.profile-card-mini');
    profileCardsMini.forEach(card => {
        card.addEventListener('click', function() {
            const profileName = this.querySelector('.profile-name-mini').textContent;
            window.location.href = '/usuario/' + profileName.toLowerCase() + '/';
        });
    });
});

// Función para ver todos los likes
function viewAllLikes() {
    alert('👁️ Ver todos los perfiles que te dieron like');
    // Aquí puedes agregar lógica para mostrar una página de likes
}

// Función para ver todos los visitantes
function viewAllVisitors() {
    alert('👥 Ver todos los visitantes de tu perfil');
    // Aquí puedes agregar lógica para mostrar una página de visitantes
}

// Función para bloquear a un usuario
function blockUser(username) {
    if (confirm('¿Estás seguro de que quieres bloquear a ' + username + '?')) {
        alert('✅ ' + username + ' ha sido bloqueado');
    }
}

// Función para reportar a un usuario
function reportUser(username) {
    alert('📢 Reportar a: ' + username);
    // Aquí puedes agregar lógica para reportar un usuario
}
