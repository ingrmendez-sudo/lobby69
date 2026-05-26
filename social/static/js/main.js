/**
 * main.js - Script principal centralizado
 * Punto de entrada para toda la aplicación
 */

console.log('>> main.js iniciando...');

document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ DOM completamente cargado');
    
    // Inicializar componentes globales
    initializeGlobalListeners();
    initializeSidebars();
});

/**
 * Inicializar listeners globales
 */
function initializeGlobalListeners() {
    // Click fuera de modales
    document.addEventListener('click', function(e) {
        if (e.target.classList?.contains('photo-modal')) {
            closePhotoModal?.();
        }
    });
    
    console.log('✅ Global listeners inicializados');
}

/**
 * Inicializar sidebars
 */
function initializeSidebars() {
    const hamburger = document.querySelector('.hamburger');
    const drawer = document.querySelector('.drawer');
    
    if (hamburger && drawer) {
        hamburger.addEventListener('click', function() {
            drawer.classList.toggle('open');
        });
    }
}

// Funciones globales seguras
window.getCurrentTime = function() {
    return new Date().toLocaleTimeString('es-ES', {
        hour: '2-digit',
        minute: '2-digit'
    });
};

window.showAlert = function(message, type = 'info', duration = 3000) {
    console.log(`[${type.toUpperCase()}] ${message}`);
};

window.getCookie = function(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
    return '';
};

console.log('✅ main.js cargado correctamente');
