function switchSettings(section, event) {
    if (event) {
        event.preventDefault();
    }

    // Ocultar todas las secciones
    document.querySelectorAll('.settings-section').forEach(s => {
        s.classList.remove('active');
    });

    // Desactivar todos los links
    document.querySelectorAll('.settings-menu a').forEach(link => {
        link.classList.remove('active');
    });

    // Mostrar sección seleccionada
    const section_elem = document.getElementById(section);
    if (section_elem) {
        section_elem.classList.add('active');
    }

    // Activar el link correspondiente
    const links = document.querySelectorAll('.settings-menu a');
    links.forEach(link => {
        if (link.getAttribute('data-section') === section) {
            link.classList.add('active');
        }
    });
}

function saveSettings() {
    // Mostrar alerta de éxito
    showAlert('success', 'Configuración guardada exitosamente');
    setTimeout(() => {
        hideAlert();
    }, 3000);
}

function resetSettings() {
    showAlert('warning', 'Cambios descartados');
    setTimeout(() => {
        hideAlert();
    }, 2000);
}

function changePassword() {
    const current = document.getElementById('current-password').value;
    const newPass = document.getElementById('new-password').value;
    const confirm = document.getElementById('confirm-password').value;

    if (!current || !newPass || !confirm) {
        showAlert('danger', 'Por favor completa todos los campos');
        return;
    }

    if (newPass.length < 8) {
        showAlert('danger', 'La contraseña debe tener al menos 8 caracteres');
        return;
    }

    if (newPass !== confirm) {
        showAlert('danger', 'Las contraseñas no coinciden');
        return;
    }

    showAlert('success', 'Contraseña cambiada exitosamente');
    document.getElementById('current-password').value = '';
    document.getElementById('new-password').value = '';
    document.getElementById('confirm-password').value = '';

    setTimeout(() => {
        hideAlert();
    }, 3000);
}

function logoutAllDevices() {
    if (confirm('¿Cerrar sesión en todos los dispositivos?')) {
        showAlert('success', 'Todas las sesiones han sido cerradas');
        setTimeout(() => {
            hideAlert();
        }, 2000);
    }
}

function unblockUser(username) {
    if (confirm('¿Desbloquear este usuario?')) {
        showAlert('success', 'Usuario desbloqueado exitosamente');
        // Aquí iría la llamada AJAX para desbloquear
        setTimeout(() => {
            hideAlert();
        }, 2000);
    }
}

function deleteAccount() {
    if (confirm('¿Estás seguro de que quieres eliminar tu cuenta?')) {
        if (confirm('Esta acción es irreversible. ¿Continuar?')) {
            showAlert('warning', 'Tu cuenta será eliminada en 30 días. Puedes revertir esto iniciando sesión.');
            setTimeout(() => {
                hideAlert();
            }, 4000);
        }
    }
}

function showAlert(type, message) {
    const main = document.querySelector('.settings-main');
    if (main) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type}`;
        alertDiv.textContent = message;
        main.insertBefore(alertDiv, main.firstChild);
    }
}

function hideAlert() {
    const alert = document.querySelector('.alert');
    if (alert) {
        alert.remove();
    }
}

// Inicializar la página
document.addEventListener('DOMContentLoaded', function() {
    // Cargar tema guardado
    const savedTheme = localStorage.getItem('theme') || 'light';
    if (savedTheme === 'dark') {
        document.body.classList.add('dark-mode');
        updateThemeIcon();
    }

    // Activar primera sección por defecto
    switchSettings('account');

    // Auto-hide alerts después de 4 segundos
    setTimeout(() => {
        const alert = document.querySelector('.alert');
        if (alert) {
            alert.remove();
        }
    }, 4000);
});
