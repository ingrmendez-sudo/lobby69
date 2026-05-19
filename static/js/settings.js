/* SETTINGS PAGE - FUNCIONES ESPECÍFICAS */

document.addEventListener('DOMContentLoaded', function() {
  console.log('settings.js: Inicializando...');

  initializeMenus();
  initializeFormValidation();
  initializeToggleSwitches();
  loadDefaultSection();
});

/**
 * MENÚ DE SECCIONES
 */
function initializeMenus() {
  document.querySelectorAll('.settings-menu a').forEach(link => {
    link.addEventListener('click', function(e) {
      e.preventDefault();
      const section = this.dataset.section;
      switchSettings(section);
    });
  });
}

function switchSettings(section) {
  console.log('Cambiando a sección:', section);

  // Ocultar todas las secciones
  document.querySelectorAll('.settings-section').forEach(sec => {
    sec.classList.remove('active');
  });

  // Mostrar sección seleccionada
  const selectedSection = document.getElementById(section);
  if (selectedSection) {
    selectedSection.classList.add('active');
  }

  // Actualizar menú activo
  document.querySelectorAll('.settings-menu a').forEach(link => {
    link.classList.remove('active');
    if (link.dataset.section === section) {
      link.classList.add('active');
    }
  });
}

function loadDefaultSection() {
  const firstLink = document.querySelector('.settings-menu a');
  if (firstLink) {
    firstLink.click();
  }
}

/**
 * VALIDACIÓN DE FORMULARIOS
 */
function initializeFormValidation() {
  document.querySelectorAll('.btn-primary').forEach(btn => {
    if (btn.textContent.includes('Guardar')) {
      btn.addEventListener('click', saveSettings);
    } else if (btn.textContent.includes('Descartar')) {
      btn.addEventListener('click', resetSettings);
    }
  });

  // Botón cambiar contraseña
  const changePasswordBtn = document.querySelector('[data-action="change-password"]');
  if (changePasswordBtn) {
    changePasswordBtn.addEventListener('click', changePassword);
  }

  // Botones de danger zone
  const logoutAllBtn = document.querySelector('[data-action="logout-all"]');
  if (logoutAllBtn) {
    logoutAllBtn.addEventListener('click', logoutAllDevices);
  }

  const deleteAccountBtn = document.querySelector('[data-action="delete-account"]');
  if (deleteAccountBtn) {
    deleteAccountBtn.addEventListener('click', deleteAccount);
  }
}

/**
 * TOGGLE SWITCHES
 */
function initializeToggleSwitches() {
  document.querySelectorAll('.toggle-switch input[type="checkbox"]').forEach(toggle => {
    toggle.addEventListener('change', function() {
      const label = this.closest('.toggle-item').querySelector('.toggle-label-main').textContent;
      console.log('Toggle:', label, '=', this.checked);

      // Aquí se guardaría la preferencia
    });
  });
}

/**
 * GUARDAR CONFIGURACIÓN
 */
function saveSettings() {
  console.log('Guardando configuración...');

  const activeSection = document.querySelector('.settings-section.active');
  if (!activeSection) return;

  const formData = {
    section: activeSection.id,
    data: {}
  };

  // Recopilar datos del formulario
  activeSection.querySelectorAll('input, select, textarea').forEach(input => {
    if (input.type === 'checkbox') {
      formData.data[input.name || input.id] = input.checked;
    } else if (input.name || input.id) {
      formData.data[input.name || input.id] = input.value;
    }
  });

  const csrfToken = getCookie('csrftoken');

  fetch('/api/save-settings/', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRFToken': csrfToken,
    },
    body: JSON.stringify(formData)
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      showAlert('Configuración guardada exitosamente', 'success');
      console.log('Guardado exitoso');
    } else {
      showAlert(data.error || 'Error al guardar', 'error');
    }
  })
  .catch(error => {
    console.error('Error:', error);
    showAlert('Error al guardar', 'error');
  });
}

function resetSettings() {
  console.log('Descartando cambios...');
  if (confirm('¿Descartar todos los cambios?')) {
    location.reload();
  }
}

/**
 * CAMBIAR CONTRASEÑA
 */
function changePassword() {
  console.log('Cambiando contraseña...');

  const currentPassword = document.getElementById('current-password')?.value;
  const newPassword = document.getElementById('new-password')?.value;
  const confirmPassword = document.getElementById('confirm-password')?.value;

  if (!currentPassword || !newPassword || !confirmPassword) {
    showAlert('Completa todos los campos', 'warning');
    return;
  }

  if (newPassword !== confirmPassword) {
    showAlert('Las contraseñas no coinciden', 'error');
    return;
  }

  if (newPassword.length < 8) {
    showAlert('La contraseña debe tener al menos 8 caracteres', 'warning');
    return;
  }

  const csrfToken = getCookie('csrftoken');

  fetch('/api/change-password/', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRFToken': csrfToken,
    },
    body: JSON.stringify({
      current_password: currentPassword,
      new_password: newPassword
    })
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      showAlert('Contraseña actualizada exitosamente', 'success');
      document.getElementById('current-password').value = '';
      document.getElementById('new-password').value = '';
      document.getElementById('confirm-password').value = '';
    } else {
      showAlert(data.error || 'Error al cambiar contraseña', 'error');
    }
  })
  .catch(error => {
    console.error('Error:', error);
    showAlert('Error al cambiar contraseña', 'error');
  });
}

/**
 * SEGURIDAD - LOGOUT DE TODOS LOS DISPOSITIVOS
 */
function logoutAllDevices() {
  console.log('Logout de todos los dispositivos...');

  if (confirm('¿Cerrar sesión en todos los dispositivos? Tendrás que iniciar sesión nuevamente.')) {
    const csrfToken = getCookie('csrftoken');

    fetch('/api/logout-all-devices/', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRFToken': csrfToken,
      }
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        showAlert('Sesión cerrada en todos los dispositivos', 'success');
        setTimeout(() => {
          window.location.href = '/login/';
        }, 1500);
      } else {
        showAlert(data.error || 'Error al cerrar sesión', 'error');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      showAlert('Error al cerrar sesión', 'error');
    });
  }
}

/**
 * DESBLOQUEAR USUARIO
 */
function unblockUser(userId) {
  console.log('Desbloqueando usuario:', userId);

  if (confirm('¿Desbloquear este usuario?')) {
    const csrfToken = getCookie('csrftoken');

    fetch('/api/unblock-user/', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRFToken': csrfToken,
      },
      body: JSON.stringify({ user_id: userId })
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        showAlert('Usuario desbloqueado', 'success');
        // Recargar la lista de bloqueados
        location.reload();
      } else {
        showAlert(data.error || 'Error al desbloquear', 'error');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      showAlert('Error al desbloquear', 'error');
    });
  }
}

/**
 * ELIMINAR CUENTA
 */
function deleteAccount() {
  console.log('Eliminando cuenta...');

  const confirmText = prompt('Escribe tu nombre de usuario para confirmar la eliminación de tu cuenta:');
  const username = document.getElementById('username')?.value;

  if (!confirmText) {
    return;
  }

  if (confirmText !== username) {
    showAlert('Nombre de usuario incorrecto', 'error');
    return;
  }

  if (confirm('⚠️ ADVERTENCIA: Esta acción es irreversible. ¿Continuar?')) {
    const csrfToken = getCookie('csrftoken');

    fetch('/api/delete-account/', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRFToken': csrfToken,
      },
      body: JSON.stringify({ confirm: true })
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        showAlert('Cuenta eliminada. Redirigiendo...', 'success');
        setTimeout(() => {
          window.location.href = '/';
        }, 1500);
      } else {
        showAlert(data.error || 'Error al eliminar cuenta', 'error');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      showAlert('Error al eliminar cuenta', 'error');
    });
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

console.log('settings.js: Cargado exitosamente ✓');
