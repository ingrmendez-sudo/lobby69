/**
 * MEMBERSHIPS.JS - Memberships/Subscription management
 */

console.log('👑 memberships.js initializing...');

document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ memberships.js loaded - DOMContentLoaded');

    initializePlansNavigation();
    initializeTabNavigation();
});

/**
 * Initialize plans sidebar navigation
 */
function initializePlansNavigation() {
    console.log('🔧 Initializing plans navigation...');

    const planItems = document.querySelectorAll('.plan-nav-item');
    console.log(`Found ${planItems.length} plan navigation items`);

    planItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();

            // Remove active class from all
            planItems.forEach(i => i.classList.remove('active'));

            // Add active class to clicked
            this.classList.add('active');

            const plan = this.textContent.trim().toLowerCase();
            console.log(`📌 Selected plan: ${plan}`);
        });
    });
}

/**
 * Switch between tabs
 */
function switchMembershipTab(tabName, event) {
    if (event) {
        event.preventDefault();
    }

    console.log(`📋 Switching to tab: ${tabName}`);

    // Hide all tabs
    const tabs = document.querySelectorAll('.tab-content');
    tabs.forEach(tab => tab.classList.remove('active'));

    // Remove active class from all buttons
    const buttons = document.querySelectorAll('.tab-btn');
    buttons.forEach(btn => btn.classList.remove('active'));

    // Show selected tab
    const selectedTab = document.getElementById(`${tabName}-tab`);
    if (selectedTab) {
        selectedTab.classList.add('active');
        console.log(`✅ Tab ${tabName} activated`);
    }

    // Set active button
    event?.target?.classList.add('active');
}

/**
 * Initialize tab navigation
 */
function initializeTabNavigation() {
    console.log('🔧 Initializing tab navigation...');

    const tabButtons = document.querySelectorAll('.tab-btn');
    console.log(`Found ${tabButtons.length} tab buttons`);

    // First tab is active by default
    if (tabButtons.length > 0) {
        tabButtons[0].classList.add('active');
    }
}

/**
 * Select a plan
 */
function selectPlan(planName, price) {
    console.log(`👑 Selected plan: ${planName} - $${price}`);

    showAlert(`✅ Plan ${planName} seleccionado. Procede al pago.`, 'success');

    // TODO: Redirect to checkout
    // window.location.href = `/checkout/${planId}/`;
}

/**
 * Change current plan
 */
function changePlan() {
    console.log('🔄 Change plan clicked');

    switchMembershipTab('plans');
    showAlert('👑 Selecciona un nuevo plan', 'info');
}

/**
 * Manage billing
 */
function manageBilling() {
    console.log('💳 Manage billing clicked');

    switchMembershipTab('billing');
    showAlert('💳 Sección de facturación abierta', 'info');
}

/**
 * Cancel subscription
 */
function cancelSubscription() {
    const confirmed = confirm('⚠️ ¿Estás seguro de que deseas cancelar tu suscripción? Esta acción es irreversible.');

    if (confirmed) {
        console.log('❌ Subscription cancelled');
        showAlert('❌ Tu suscripción ha sido cancelada', 'error');

        // TODO: Send cancel request to server
    } else {
        console.log('❌ Cancellation aborted');
    }
}

/**
 * Upgrade plan
 */
function upgradePlan() {
    console.log('🚀 Upgrade to VIP clicked');

    showAlert('🚀 Procede con la mejora a VIP', 'success');

    // TODO: Redirect to checkout for VIP
    // window.location.href = '/checkout/vip/';
}

/**
 * View receipt
 */
function viewReceipt(receiptId) {
    console.log(`📄 Viewing receipt: ${receiptId}`);

    showAlert(`📄 Cargando recibo #${receiptId}`, 'info');

    // TODO: Open receipt modal or download
}

/**
 * Show alert
 */
function showAlert(message, type = 'info') {
    const colors = {
        success: '#27ae60',
        error: '#e74c3c',
        warning: '#f39c12',
        info: '#3498db'
    };

    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.textContent = message;
    alertDiv.style.cssText = `
        position: fixed;
        top: 100px;
        right: 20px;
        padding: 12px 18px;
        background: ${colors[type] || colors.info};
        color: white;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        z-index: 1000;
        animation: slideIn 0.3s ease;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    `;

    document.body.appendChild(alertDiv);
    console.log(`📢 Alert: ${message}`);

    setTimeout(() => {
        alertDiv.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => alertDiv.remove(), 300);
    }, 3000);
}

/**
 * Add CSS animations
 */
// Crear estilos de animaciones sin conflictos
(function() {
    const animationStyle = document.createElement('style');
    animationStyle.textContent = `
        @keyframes slideIn {
            from { transform: translateX(-50%) translateY(-100%); opacity: 0; }
            to { transform: translateX(-50%) translateY(0); opacity: 1; }
        }
        @keyframes slideOut {
            from { transform: translateX(-50%) translateY(0); opacity: 1; }
            to { transform: translateX(-50%) translateY(-100%); opacity: 0; }
        }
    `;
    document.head.appendChild(animationStyle);
})();


console.log('✅ memberships.js fully loaded');
