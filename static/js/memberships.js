// MEMBERSHIPS - FUNCIONES ESPECÍFICAS

function switchMembershipTab(tabName) {
    // Ocultar todos los tabs
    const tabs = document.querySelectorAll('.tab-content');
    tabs.forEach(tab => tab.style.display = 'none');

    // Desactivar todos los botones
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));

    // Mostrar el tab seleccionado
    const selectedTab = document.getElementById(tabName);
    if (selectedTab) {
        selectedTab.style.display = 'block';
    }

    // Activar el botón
    event.target.classList.add('active');
}

function selectPlan(planName, price) {
    alert(`✅ Vas a seleccionar el plan: ${planName} ($${price}/mes)\n\nRedirigiendo a pago...`);
    // Aquí iría la lógica de pago
    console.log(`Plan seleccionado: ${planName} - $${price}/mes`);
}

function changePlan() {
    alert('🔄 Redirigiendo a cambio de plan...');
    // Aquí iría la lógica para cambiar plan
}

function manageBilling() {
    alert('💳 Abriendo gestión de facturación...');
    // Aquí iría la lógica para gestionar facturación
}

function cancelSubscription() {
    if (confirm('⚠️ ¿Estás seguro de que deseas cancelar tu suscripción?\n\nTendrás acceso hasta el final del período pagado.')) {
        alert('✅ Suscripción cancelada. Te enviaremos un correo de confirmación.');
        // Aquí iría la lógica para cancelar suscripción
    }
}

function upgradePlan() {
    alert('🚀 Iniciando proceso de mejora...');
    // Aquí iría la lógica para mejorar plan
}

function viewReceipt(receiptId) {
    alert(`📄 Abriendo recibo #${receiptId}...`);
    // Aquí iría la lógica para ver el recibo
}

// Cargar tema al abrir la página
document.addEventListener('DOMContentLoaded', function() {
    const savedTheme = localStorage.getItem('theme') || 'light';
    if (savedTheme === 'dark') {
        document.documentElement.classList.add('dark-mode');
    }
    updateThemeIcon();

    // Hacer que los links del sidebar funcionen como tabs
    const sidebarLinks = document.querySelectorAll('.sidebar-link');
    sidebarLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            sidebarLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');
        });
    });
});

function updateThemeIcon() {
    const icon = document.querySelector('.theme-toggle');
    const isDark = document.documentElement.classList.contains('dark-mode');
    if (icon) {
        icon.textContent = isDark ? '☀️' : '🌙';
    }
}
