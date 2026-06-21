// LOBBY69 - App.js
// Funcionalidades base del frontend

document.addEventListener('DOMContentLoaded', function () {
    initNavbar();
    initToasts();
});

// ── Navbar Toggle (responsive) ──
function initNavbar() {
    const toggle = document.getElementById('navbarToggle');
    const menu = document.getElementById('navbarMenu');

    if (toggle && menu) {
        toggle.addEventListener('click', function () {
            menu.classList.toggle('navbar__menu--open');
        });

        // Cerrar menú al hacer click fuera
        document.addEventListener('click', function (e) {
            if (!toggle.contains(e.target) && !menu.contains(e.target)) {
                menu.classList.remove('navbar__menu--open');
            }
        });
    }
}

// ── Auto-ocultar toasts ──
function initToasts() {
    const toasts = document.querySelectorAll('.toast');
    toasts.forEach(function (toast) {
        setTimeout(function () {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.3s ease';
            setTimeout(function () {
                toast.remove();
            }, 300);
        }, 5000);
    });
}
