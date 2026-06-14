// Toggle menú móvil
function toggleMobileMenu() {
    const menu = document.getElementById('navMenu');
    const hamburger = document.getElementById('hamburgerBtn');
    menu.classList.toggle('active');
    hamburger.classList.toggle('active');
}

// Toggle menú de notificaciones
function toggleNotificationMenu(event) {
    event.stopPropagation();
    const dropdown = document.getElementById('notificationDropdown');
    dropdown.classList.toggle('active');
}

// Toggle menú de usuario
function toggleUserMenu(event) {
    event.stopPropagation();
    const dropdown = document.getElementById('userDropdown');
    dropdown.classList.toggle('active');
}

// Cerrar dropdowns al hacer clic fuera
document.addEventListener('click', function(event) {
    const notificationDropdown = document.getElementById('notificationDropdown');
    const userDropdown = document.getElementById('userDropdown');
    
    if (notificationDropdown && !notificationDropdown.contains(event.target)) {
        notificationDropdown.classList.remove('active');
    }
    if (userDropdown && !userDropdown.contains(event.target)) {
        userDropdown.classList.remove('active');
    }
});

// Toggle tema
function toggleTheme() {
    const body = document.body;
    body.classList.toggle('dark-mode');
    localStorage.setItem('theme', body.classList.contains('dark-mode') ? 'dark' : 'light');
}

// Marcar notificaciones como leídas
function markAllAsRead() {
    const badge = document.querySelector('.badge-notification');
    if (badge) badge.style.display = 'none';
}

// Highlight activo según URL
document.addEventListener('DOMContentLoaded', function() {
    const currentPath = window.location.pathname;
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        if (link.getAttribute('href') === currentPath) {
            link.classList.add('active');
        }
    });
});
