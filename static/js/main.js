// TEMA DÍA/NOCHE
function toggleTheme() {
    const body = document.body;
    const icon = document.getElementById('theme-icon');

    body.classList.toggle('dark-mode');

    if (body.classList.contains('dark-mode')) {
        localStorage.setItem('theme', 'dark');
        icon.textContent = '☀️';
    } else {
        localStorage.setItem('theme', 'light');
        icon.textContent = '🌙';
    }
}

// DROPDOWN NOTIFICACIONES
function toggleNotificationMenu() {
    const menu = document.getElementById('notificationMenu');
    if (menu) {
        menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
    }
}

// CERRAR DROPDOWN AL HACER CLIC FUERA
document.addEventListener('click', function(e) {
    const notifBtn = document.querySelector('[onclick="toggleNotificationMenu()"]');
    const menu = document.getElementById('notificationMenu');
    if (notifBtn && menu && !notifBtn.contains(e.target) && !menu.contains(e.target)) {
        menu.style.display = 'none';
    }
});

// HOVER EN DROPDOWN
document.querySelectorAll('.dropdown-menu a').forEach(link => {
    link.addEventListener('mouseover', function() {
        this.style.background = 'var(--hover-bg)';
    });
    link.addEventListener('mouseout', function() {
        this.style.background = 'transparent';
    });
});

// MOBILE MENU
function toggleMobileMenu() {
    const drawer = document.getElementById('mobileMenuDrawer');
    const overlay = document.getElementById('mobileMenuOverlay');

    if (drawer && overlay) {
        drawer.classList.toggle('active');
        overlay.classList.toggle('active');
    }
}

// CARGAR TEMA GUARDADO
window.addEventListener('DOMContentLoaded', function() {
    const theme = localStorage.getItem('theme') || 'light';
    const icon = document.getElementById('theme-icon');

    if (theme === 'dark') {
        document.body.classList.add('dark-mode');
        icon.textContent = '☀️';
    } else {
        icon.textContent = '🌙';
    }
});

// UTILIDADES
function getCookie(name) {
    let value = null;
    if (document.cookie && document.cookie !== '') {
        const cookies = document.cookie.split(';');
        for (let i = 0; i < cookies.length; i++) {
            const cookie = cookies[i].trim();
            if (cookie.substring(0, name.length + 1) === (name + '=')) {
                value = decodeURIComponent(cookie.substring(name.length + 1));
                break;
            }
        }
    }
    return value;
}

/**
 * HAMBURGER MENU TOGGLE
 */
function toggleMobileMenu() {
    const navMenu = document.querySelector('.nav-menu');
    const hamburgerBtn = document.getElementById('hamburgerBtn');
    const body = document.body;

    navMenu.classList.toggle('active');
    hamburgerBtn.classList.toggle('active');

    // Prevenir scroll cuando el menú está abierto
    if (navMenu.classList.contains('active')) {
        body.style.overflow = 'hidden';
    } else {
        body.style.overflow = 'auto';
    }
}

/**
 * CERRAR MENÚ AL HACER CLICK EN UN LINK
 */
document.addEventListener('DOMContentLoaded', function() {
    const navMenu = document.querySelector('.nav-menu');
    const hamburgerBtn = document.getElementById('hamburgerBtn');
    const navLinks = document.querySelectorAll('.nav-menu a:not(.dropdown-menu a)');
    const body = document.body;

    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            // No cerrar si es un dropdown
            if (!link.parentElement.querySelector('.dropdown-menu')) {
                navMenu.classList.remove('active');
                hamburgerBtn.classList.remove('active');
                body.style.overflow = 'auto';
            }
        });
    });

    // Cerrar menú al hacer click fuera
    document.addEventListener('click', function(event) {
        const isClickInsideNav = navMenu.contains(event.target);
        const isClickOnHamburger = hamburgerBtn.contains(event.target);

        if (!isClickInsideNav && !isClickOnHamburger && navMenu.classList.contains('active')) {
            navMenu.classList.remove('active');
            hamburgerBtn.classList.remove('active');
            body.style.overflow = 'auto';
        }
    });

    // Cerrar menú con ESC
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && navMenu.classList.contains('active')) {
            navMenu.classList.remove('active');
            hamburgerBtn.classList.remove('active');
            body.style.overflow = 'auto';
        }
    });

    // Cerrar menú cuando se redimensiona la ventana
    window.addEventListener('resize', function() {
        if (window.innerWidth > 1023) {
            navMenu.classList.remove('active');
            hamburgerBtn.classList.remove('active');
            body.style.overflow = 'auto';
        }
    });
});

/**
 * TOGGLE NOTIFICATION DROPDOWN
 */
function toggleNotificationMenu(event) {
    event.stopPropagation();
    const notificationMenu = document.getElementById('notificationMenu');
    notificationMenu.classList.toggle('active');
}

/**
 * CERRAR DROPDOWN AL HACER CLICK FUERA
 */
document.addEventListener('click', function(event) {
    const notificationMenu = document.getElementById('notificationMenu');
    const menuIcon = document.querySelector('.menu-icon');

    if (notificationMenu && !notificationMenu.contains(event.target) && !menuIcon.contains(event.target)) {
        notificationMenu.classList.remove('active');
    }
});

/**
 * TOGGLE THEME (Si no existe ya)
 */
function toggleTheme() {
    const body = document.body;
    const icon = document.getElementById('theme-icon');

    body.classList.toggle('dark-mode');

    if (body.classList.contains('dark-mode')) {
        localStorage.setItem('theme', 'dark');
        icon.textContent = '☀️';
    } else {
        localStorage.setItem('theme', 'light');
        icon.textContent = '🌙';
    }
}

/**
 * CARGAR TEMA AL INICIAR
 */
window.addEventListener('DOMContentLoaded', function() {
    const savedTheme = localStorage.getItem('theme') || 'light';
    const icon = document.getElementById('theme-icon');

    if (savedTheme === 'dark') {
        document.body.classList.add('dark-mode');
        icon.textContent = '☀️';
    } else {
        icon.textContent = '🌙';
    }
});
