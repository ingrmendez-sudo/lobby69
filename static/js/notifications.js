// NOTIFICATIONS - FUNCIONES ESPECÍFICAS

function filterNotifications(type) {
    console.log('📊 Filtro aplicado:', type);
    window.location.href = `?filter=${type}`;
}

function markAsRead(notificationId) {
    console.log('[DEBUG] Marcando como leída:', notificationId);

    fetch(`/notificacion/${notificationId}/leer/`, {
        method: 'POST',
        headers: {
            'X-CSRFToken': getCookie('csrftoken'),
            'Content-Type': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            console.log('✅ Notificación marcada como leída');
            location.reload();
        } else {
            alert('❌ Error: ' + data.error);
        }
    })
    .catch(e => {
        console.error('❌ Error:', e);
        alert('Error al marcar como leída');
    });
}

function markAllAsRead() {
    if (confirm('¿Marcar todas las notificaciones como leídas?')) {
        const items = document.querySelectorAll('.notification-item[data-unread="true"]');
        console.log(`Marcando ${items.length} notificaciones como leídas...`);

        let count = 0;
        items.forEach(item => {
            const id = item.dataset.id;
            fetch(`/notificacion/${id}/leer/`, {
                method: 'POST',
                headers: {
                    'X-CSRFToken': getCookie('csrftoken'),
                    'Content-Type': 'application/json'
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) count++;
                if (count === items.length) {
                    console.log(`✅ ${count} notificaciones marcadas como leídas`);
                    location.reload();
                }
            })
            .catch(e => console.error(e));
        });
    }
}

function clearAllNotifications() {
    if (confirm('⚠️ ¿Eliminar todas las notificaciones? Esta acción no se puede deshacer.')) {
        console.log('🗑️ Limpiando todas las notificaciones...');
        window.location.href = '/notificaciones/limpiar/';
    }
}

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

function toggleNotificationPreference(type, element) {
    const isChecked = element.checked;
    console.log(`Preferencia de ${type}: ${isChecked ? 'habilitada' : 'deshabilitada'}`);
    // Aquí puedes agregar lógica para guardar la preferencia
}

document.addEventListener('DOMContentLoaded', function() {
    console.log('📢 Notificaciones cargadas');

    // Agregar listeners a los checkboxes de preferencias
    document.querySelectorAll('.preference-item input[type="checkbox"]').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const label = this.previousElementSibling?.textContent || 'Preferencia';
            toggleNotificationPreference(label, this);
        });
    });

    // Agregar listeners a los items de notificación
    document.querySelectorAll('.notification-item').forEach(item => {
        item.addEventListener('mouseover', function() {
            this.style.cursor = 'pointer';
        });
    });
});
