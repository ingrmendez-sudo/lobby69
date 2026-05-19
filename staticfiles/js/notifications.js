/* NOTIFICATIONS PAGE - FUNCIONES ESPECÍFICAS */

document.addEventListener('DOMContentLoaded', function() {
  console.log('notifications.js: Inicializando...');

  initializeFilters();
  initializeNotificationActions();
});

/**
 * FILTROS
 */
function initializeFilters() {
  document.querySelectorAll('input[name="filter"]').forEach(radio => {
    radio.addEventListener('change', function() {
      filterNotifications(this.value);
    });
  });
}

function filterNotifications(filterType) {
  console.log('Filtrando notificaciones:', filterType);

  const url = new URL(window.location);
  url.searchParams.set('filter', filterType);
  window.history.replaceState({}, '', url);

  // Aquí se implementaría filtrado con AJAX
  // Por ahora solo actualizamos la URL

  document.querySelectorAll('.notification-item').forEach(item => {
    const isUnread = item.dataset.unread === 'true';

    if (filterType === 'todas') {
      item.style.display = 'block';
    } else if (filterType === 'no_leidas' && isUnread) {
      item.style.display = 'block';
    } else if (filterType === 'no_leidas' && !isUnread) {
      item.style.display = 'none';
    } else if (filterType === 'importantes') {
      // Aquí se verificaría si la notificación es importante
      item.style.display = 'block';
    }
  });
}

/**
 * ACCIONES DE NOTIFICACIONES
 */
function initializeNotificationActions() {
  // Botón marcar como leído
  document.querySelectorAll('.notification-item').forEach(item => {
    const readBtn = item.querySelector('[data-action="mark-read"]');
    if (readBtn) {
      readBtn.addEventListener('click', function(e) {
        e.preventDefault();
        const notificationId = item.dataset.id;
        markAsRead(notificationId);
      });
    }
  });

  // Botones de acción
  document.querySelectorAll('.btn-action').forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      console.log('Acción:', this.textContent);
    });
  });
}

function markAsRead(notificationId) {
  console.log('Marcando como leído:', notificationId);

  const csrfToken = getCookie('csrftoken');

  fetch('/api/mark-notification-read/', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRFToken': csrfToken,
    },
    body: JSON.stringify({ notification_id: notificationId })
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      const item = document.querySelector(`[data-id="${notificationId}"]`);
      if (item) {
        item.dataset.unread = 'false';
        item.classList.remove('unread');
        console.log('Notificación marcada como leída');
      }
    }
  })
  .catch(error => console.error('Error:', error));
}

function markAllAsRead() {
  console.log('Marcando todas como leídas...');

  const csrfToken = getCookie('csrftoken');

  fetch('/api/mark-all-notifications-read/', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRFToken': csrfToken,
    }
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      document.querySelectorAll('.notification-item').forEach(item => {
        item.dataset.unread = 'false';
        item.classList.remove('unread');
      });
      showAlert('Todas las notificaciones marcadas como leídas', 'success');
      console.log('Todas marcadas como leídas');
    }
  })
  .catch(error => console.error('Error:', error));
}

function clearAllNotifications() {
  if (confirm('¿Deseas eliminar todas las notificaciones?')) {
    console.log('Eliminando todas las notificaciones...');

    const csrfToken = getCookie('csrftoken');

    fetch('/api/delete-all-notifications/', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRFToken': csrfToken,
      }
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        document.querySelectorAll('.notification-item').forEach(item => {
          item.remove();
        });
        showAlert('Notificaciones eliminadas', 'success');
        console.log('Todas eliminadas');
      }
    })
    .catch(error => console.error('Error:', error));
  }
}

/**
 * UTILIDADES
 */
function getCookie(name) {
  let cookieValue = null;
  if (document.cookie && document.cookie !== '') {
    const cookies = document.cookie.split(';');
    for (let i = 0; i < cookies.length; i++) {
      const cookie = cookies[i].trim();
      if (cookie.substring(0, name.length + 1) === (name + '=')) {
        cookieValue = decodeURIComponent(cookie.substring(name.length + 1));
        break;
      }
    }
  }
  return cookieValue;
}

function showAlert(message, type = 'info') {
  const alert = document.createElement('div');
  alert.className = `alert alert-${type}`;
  alert.textContent = message;
  alert.style.position = 'fixed';
  alert.style.top = '100px';
  alert.style.right = '20px';
  alert.style.zIndex = '9999';
  alert.style.maxWidth = '300px';
  alert.style.padding = '15px';
  alert.style.borderRadius = '6px';

  document.body.appendChild(alert);

  setTimeout(() => {
    alert.remove();
  }, 3000);
}

console.log('notifications.js: Cargado exitosamente ✓');
