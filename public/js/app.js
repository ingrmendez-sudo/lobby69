// LOBBY69 - App.js
document.addEventListener('DOMContentLoaded', function () {
    initNavbar();
    initToasts();
});

function initNavbar() {
    const toggle = document.getElementById('navbarToggle');
    const menu = document.getElementById('navbarMenu');

    if (toggle && menu) {
        toggle.addEventListener('click', function () {
            menu.classList.toggle('navbar__menu--open');
        });

        document.addEventListener('click', function (e) {
            if (!toggle.contains(e.target) && !menu.contains(e.target)) {
                menu.classList.remove('navbar__menu--open');
            }
        });
    }
}

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
