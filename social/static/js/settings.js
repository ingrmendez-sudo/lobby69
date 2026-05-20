/**
 * settings.js - Lógica para la página de Configuración
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ settings.js cargado');
    
    initializeMenus();
    initializeToggles();
    initializeFormHandlers();
});

/**
 * Inicializar menú de configuración
 */
function initializeMenus() {
    console.log('🔧 Inicializando menús');
    
    const menuItems = document.querySelectorAll('.settings-menu-item');
    
    menuItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const sectionId = this.dataset.section;
            switchSettings(sectionId);
        });
    });
}

/**
 * Cambiar sección de configuración
 */
function switchSettings(sectionId, event = null) {
    if (event) event.preventDefault();
    
    console.log(`📋 Cambiando a sección: ${sectionId}`);
    
    // Ocultar todas las secciones
    const sections = document.querySelectorAll('.settings-section');
    sections.forEach(section => {
        section.classList.remove('active');
    });
    
    // Deseleccionar todos los menús
    const menuItems = document.querySelectorAll('.settings-menu-item');
    menuItems.forEach(item => {
        item.classList.remove('active');
    });
    
    // Mostrar sección activa
    const activeSection = document.getElementById(sectionId);
    if (activeSection) {
        activeSection.classList.add('active');
    }
    
    // Marcar menú como activo
    const activeMenu = document.querySelector(`[data-section="${sectionId}"]`);
    if (activeMenu) {
        activeMenu.classList.add('active');
    }
    
    console.log(`✅ Sección ${sectionId} activada`);
    showAlert(`Abierto: ${sectionId.charAt(0).toUpperCase() + sectionId.slice(1)}`, 'info');
}

/**
 * Inicializar toggles (switches)
 */
function initializeToggles() {
    console.log('🔲 Inicializando toggles');
    
    const toggles = document.querySelectorAll('.toggle-switch input[type="checkbox"]');
    
    toggles.forEach(toggle => {
        toggle.addEventListener('change', function() {
            const label = this.closest('.toggle-item').querySelector('.toggle-label-main').textContent;
            const isChecked = this.checked;
            
            console.log(`🔲 Toggle: ${label} = ${isChecked}`);
            saveToggleSetting(label, isChecked);
        });
    });
}

/**
 * Guardar configuración de toggle
 */
function saveToggleSetting(label, value) {
    console.log(`💾 Guardando: ${label} = ${value}`);
    
    // Aquí podrías hacer una llamada AJAX a Django
    // fetch(`/api/settings/toggle/`, {
    //     method: 'POST',
    //     body: JSON.stringify({ setting: label, value: value }),
    //     headers: { 'Content-Type': 'application/json' }
    // })
    
    showAlert(`💾 ${label} actualizado`, 'success');
}

/**
 * Inicializar manejadores de formularios
 */
function initializeFormHandlers() {
    console.log('📝 Inicializando formularios');
    
    // Guardar cambios de cuenta
    const saveAccountBtn = document.querySelector('#account .btn-primary');
    if (saveAccountBtn) {
        saveAccountBtn.addEventListener('click', saveAccountSettings);
    }
}

/**
 * Guardar configuración de cuenta
 */
function saveAccountSettings() {
    console.log('💾 Guardando configuración de cuenta');
    
    const username = document.getElementById('username')?.value;
    const firstname = document.getElementById('firstname')?.value;
    const lastname = document.getElementById('lastname')?.value;
    const email = document.getElementById('email')?.value;
    const phone = document.getElementById('phone')?.value;
    
    const settings = {
        username,
        firstname,
        lastname,
        email,
        phone
    };
    
    console.log('📦 Datos a guardar:', settings);
    
    // Aquí podrías hacer una llamada AJAX a Django
    // fetch(`/api/account/settings/`, {
    //     method: 'POST',
    //     body: JSON.stringify(settings),
    //     headers: { 'Content-Type': 'application/json' }
    // })
    //     .then(response => response.json())
    //     .then(data => {
    //         showAlert('✅ Configuración guardada correctamente', 'success');
    //     })
    //     .catch(error => {
    //         showAlert('❌ Error al guardar', 'error');
    //         console.error('Error:', error);
    //     });
    
    showAlert('✅ Configuración guardada correctamente', 'success');
}

/**
 * Guardar configuración genérica
 */
function saveSettings() {
    console.log('💾 Guardando configuración');
    showAlert('✅ Configuración guardada', 'success');
}

/**
 * Resetear configuración
 */
function resetSettings() {
    console.log('↻ Reseteando configuración');
    showAlert('↻ Cambios descartados', 'info');
}

/**
 * Cambiar contraseña
 */
function changePassword() {
    const currentPassword = document.getElementById('current-password')?.value;
    const newPassword = document.getElementById('new-password')?.value;
    
    if (!currentPassword || !newPassword) {
        showAlert('❌ Por favor completa todos los campos', 'error');
        return;
    }
    
    if (newPassword.length < 8) {
        showAlert('❌ La contraseña debe tener al menos 8 caracteres', 'error');
        return;
    }
    
    console.log('🔐 Cambiando contraseña');
    showAlert('✅ Contraseña cambiada correctamente', 'success');
    
    // Limpiar campos
    document.getElementById('current-password').value = '';
    document.getElementById('new-password').value = '';
    document.getElementById('confirm-password').value = '';
}

/**
 * Desbloquear usuario
 */
function unblockUser(username) {
    if (confirm(`¿Desbloquear a ${username}?`)) {
        console.log(`🔓 Desbloqueando usuario: ${username}`);
        showAlert(`✅ ${username} desbloqueado`, 'success');
    }
}

/**
 * Eliminar cuenta
 */
function deleteAccount() {
    if (confirm('⚠️ ¿Estás seguro? Esta acción es irreversible')) {
        console.log('🗑️ Eliminando cuenta');
        showAlert('❌ Cuenta eliminada', 'success');
    }
}

/**
 * Logout de todos los dispositivos
 */
function logoutAllDevices() {
    if (confirm('¿Cerrar sesión en todos los dispositivos?')) {
        console.log('🚪 Cerrando todas las sesiones');
        showAlert('🚪 Sesiones cerradas', 'success');
    }
}

/**
 * Mostrar alerta
 */
function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.textContent = message;
    alertDiv.style.cssText = `
        position: fixed;
        top: 120px;
        left: 50%;
        transform: translateX(-50%);
        padding: 15px 20px;
        border-radius: 8px;
        background: ${type === 'success' ? '#4caf50' : type === 'error' ? '#f44336' : '#2196f3'};
        color: white;
        z-index: 9999;
        animation: slideIn 0.3s ease-in-out;
        max-width: 400px;
        text-align: center;
        font-weight: 600;
    `;
    
    document.body.appendChild(alertDiv);
    
    setTimeout(() => {
        alertDiv.style.animation = 'slideOut 0.3s ease-in-out';
        setTimeout(() => alertDiv.remove(), 300);
    }, 2000);
}
