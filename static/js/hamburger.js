/**
 * HAMBURGER MENU TOGGLE - SAFE VERSION
 */

function toggleMobileMenu() {
    const navMenu = document.querySelector('.nav-menu');
    const hamburgerBtn = document.getElementById('hamburgerBtn');
    const body = document.body;

    // Validar que existen
    if (!navMenu || !hamburgerBtn) {
        console.warn('hamburger.js: nav-menu o hamburgerBtn no encontrados');
        return;
    }

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
 * INIT - Esperar a que el DOM esté listo
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('hamburger.js: DOMContentLoaded');

    const navMenu = document.querySelector('.nav-menu');
    const hamburgerBtn = document.getElementById('hamburgerBtn');
    const navLinks = document.querySelectorAll('.nav-menu a:not(.dropdown-menu a)');
    const body = document.body;

    // Validar elementos
    if (!navMenu) {
        console.warn('hamburger.js: .nav-menu no encontrado');
        return;
    }
    if (!hamburgerBtn) {
        console.warn('hamburger.js: #hamburgerBtn no encontrado');
        return;
    }

    console.log('hamburger.js: Elementos encontrados, inicializando...');

    // Cerrar menú al hacer click en un link
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            navMenu.classList.remove('active');
            hamburgerBtn.classList.remove('active');
            body.style.overflow = 'auto';
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

    console.log('hamburger.js: Inicialización completada');
});
