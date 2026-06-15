/**
 * main.js - Script principal limpio y compatible
 * Compatible con navbar-premium.html
 */

console.log('✅ main.js cargado correctamente');

document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ DOM completamente cargado');
    
    // Inicializar navbar dropdown
    initializeNavbarDropdown();
    
    // Inicializar tema oscuro/claro
    initializeTheme();
});

/**
 * Inicializar dropdown del usuario en navbar
 */
function initializeNavbarDropdown() {
    const userMenuBtn = document.getElementById('user-menu-btn');
    const userDropdown = document.getElementById('user-dropdown');
    
    // Verificar que los elementos existan
    if (!userMenuBtn || !userDropdown) {
        console.warn('⚠️ Elementos del dropdown no encontrados');
        return;
    }
    
    // Toggle dropdown al hacer click
    userMenuBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        userDropdown.classList.toggle('active');
    });
    
    // Cerrar dropdown al hacer click fuera
    document.addEventListener('click', function(e) {
        if (!userMenuBtn.contains(e.target) && !userDropdown.contains(e.target)) {
            userDropdown.classList.remove('active');
        }
    });
    
    console.log('✅ Navbar dropdown inicializado');
}

/**
 * Inicializar sistema de tema (oscuro/claro)
 */
function initializeTheme() {
    const themeToggle = document.getElementById('theme-toggle');
    
    if (!themeToggle) {
        console.warn('⚠️ Botón de tema no encontrado');
        return;
    }
    
    // Cargar tema guardado
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', savedTheme);
    
    // Toggle tema
    themeToggle.addEventListener('click', function() {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        
        console.log('🌓 Tema cambiado a:', newTheme);
    });
    
    console.log('✅ Sistema de tema inicializado');
}

/**
 * Obtener CSRF token para peticiones AJAX
 */
function getCsrfToken() {
    return document.querySelector('[name=csrfmiddlewaretoken]')?.value || '';
}

console.log('✅ main.js listo para usar');
