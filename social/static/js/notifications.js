/**
 * notifications.js - Lógica para la página de Notificaciones
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ notifications.js cargado');
    
    initializeFilters();
    initializePreferences();
});

/**
 * Inicializar filtros
 */
function initializeFilters() {
    console.log('🔧 Inicializando filtros');
    const filterSelect = document.getElementById('filter-select');
    
    if (filterSelect) {
        filterSelect.addEventListener('change', function() {
            filterNotifications(this.value);
        });
    }
}

/**
 * Filtrar notificaciones
 */
function filterNotifications(filter) {
    console.log(`🔍 Filtrando notificaciones por: ${filter}`);
    
    const notificationItems = document.querySelectorAll('.notification-item');
    let visibleCount = 0;
    
    notificationItems.forEach(item => {
        let show = false;
        
        if (filter === 'todas') {
            show = true;
        } else if (filter === 'no_leidas') {
            show = item.dataset.unread === 'true';
        } else if (filter === 'importantes') {
            // Aquí podrías añadir lógica para notificaciones importantes
            show = true;
        }
        
        item.style.display = show ? 'flex' : 'none';
        if (show) visibleCount++;
    });
    
    console.log(`✅ Mostradas ${visibleCount} notificaciones`);
    showAlert(`Filtro aplicado: ${filter === 'todas' ? 'Todas' : filter === 'no_leidas' ? 'No leídas' : 'Importantes'}`, 'info');
}

/**
 * Marcar todas como leídas
 */
function markAllAsRead() {
    console.log('✓ Marcando todas las notificaciones como leídas');
    
    const notificationItems = document.querySelectorAll('.notification-item[data-unread="true"]');
    
    notificationItems.forEach(item => {
        markAsRead(item.dataset.id);
    });
    
    showAlert('✓ Todas las notificaciones marcadas como leídas', 'success');
}

/**
 * Marcar una notificación como leída
 */
function markAsRead(notificationId) {
    console.log(`✓ Marcando notificación ${notificationId} como leída`);
    
    const item = document.querySelector(`[data-id="${notificationId}"]`);
    if (item) {
        item.dataset.unread = 'false';
        item.style.opacity = '0.6';
        
        // Aquí podrías hacer una llamada AJAX a Django
        // fetch(`/api/notification/${notificationId}/mark-read/`, { method: 'POST' })
    }
}

/**
 * Limpiar todas las notificaciones
 */
function clearAllNotifications() {
    if (confirm('¿Estás seguro de que deseas limpiar todas las notificaciones?')) {
        console.log('🗑️ Limpiando todas las notificaciones');
        
        const notificationsList = document.querySelector('.notifications-list');
        if (notificationsList) {
            notificationsList.innerHTML = `
                <div class="empty-state">
                    <div class="empty-icon">🔔</div>
                    <h3>Sin notificaciones</h3>
                    <p>Cuando recibas notificaciones, aparecerán aquí</p>
                </div>
            `;
        }
        
        showAlert('🗑️ Notificaciones limpiadas', 'success');
    }
}

/**
 * Inicializar preferencias
 */
function initializePreferences() {
    console.log('⚙️ Inicializando preferencias');
    
    const preferenceCheckboxes = document.querySelectorAll('.preference-item input[type="checkbox"]');
    
    preferenceCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            savePreference(this);
        });
    });
}

/**
 * Guardar preferencia
 */
function savePreference(checkbox) {
    const label = checkbox.previousElementSibling;
    const isChecked = checkbox.checked;
    
    console.log(`💾 Guardando preferencia: ${label.textContent} = ${isChecked}`);
    
    // Aquí podrías hacer una llamada AJAX a Django
    // fetch(`/api/preferences/`, {
    //     method: 'POST',
    //     body: JSON.stringify({ preference: label.textContent, value: isChecked }),
    //     headers: { 'Content-Type': 'application/json' }
    // })
    
    showAlert('💾 Preferencia guardada', 'success');
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
