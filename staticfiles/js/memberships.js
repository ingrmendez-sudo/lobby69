console.log('👑 memberships.js initializing...');

let allPlans = [];

document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ memberships.js loaded - DOMContentLoaded');
    loadMembershipPlans();
    initializePlansNavigation();
    initializeTabNavigation();
});

/**
 * Load membership plans from API
 */
function loadMembershipPlans() {
    console.log('🔄 Cargando planes de membresía...');

    fetch('/api/membership-plans/')
        .then(response => {
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            return response.json();
        })
        .then(data => {
            console.log('✅ Planes cargados:', data);
            allPlans = data.plans || [];
            renderPlans(allPlans);
        })
        .catch(error => {
            console.error('❌ Error loading plans:', error);
            showAlert('Error al cargar planes de membresía', 'error');
        });
}

/**
 * Render plans in the DOM
 */
function renderPlans(plans) {
    console.log(`🎨 Renderizando ${plans.length} planes...`);

    const plansContainer = document.querySelector('[data-plans-container]') ||
                          document.querySelector('.plans-grid') ||
                          document.querySelector('.membership-plans');

    if (!plansContainer) {
        console.warn('⚠️ Plans container not found');
        return;
    }

    plansContainer.innerHTML = '';

    plans.forEach(plan => {
        const planCard = document.createElement('div');
        planCard.className = 'plan-card';
        planCard.innerHTML = `
            <div class="plan-header">
                <h3 class="plan-name">${plan.name}</h3>
                <p class="plan-duration">${plan.duration_days} días</p>
            </div>
            <div class="plan-price">
                ${plan.discount_percent ? `
                    <span class="original-price">$${plan.original_price}</span>
                    <span class="discount-badge">-${plan.discount_percent}%</span>
                ` : ''}
                <span class="current-price">$${plan.price.toFixed(2)}</span>
            </div>
            <div class="plan-features">
                ${plan.features ? JSON.stringify(plan.features).substring(0, 100) + '...' : 'Premium features'}
            </div>
            <button class="btn-checkout" onclick="goToCheckout('${plan.id}', '${plan.name}', ${plan.price})">
                Contratar Ahora
            </button>
        `;
        plansContainer.appendChild(planCard);
    });

    console.log('✅ Planes renderizados');
}

/**
 * Go to checkout
 */
function goToCheckout(planId, planName, price) {
    console.log(`💳 Ir a checkout - Plan: ${planName}, Precio: $${price}`);
    showAlert(`Plan ${planName} seleccionado. Procede al pago.`, 'success');
    // TODO: Implementar checkout con Conekta
}

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
            const planName = this.textContent.trim();
            selectPlan(planName);
        });
    });
}

/**
 * Initialize tab navigation
 */
function initializeTabNavigation() {
    console.log('🔧 Initializing tab navigation...');

    const tabButtons = document.querySelectorAll('[data-tab-button]');
    console.log(`Found ${tabButtons.length} tab buttons`);

    tabButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const tabName = this.getAttribute('data-tab-button');
            switchMembershipTab(tabName);
        });
    });

    // First tab is active by default
    if (tabButtons.length > 0) {
        tabButtons[0].classList.add('active');
    }
}

/**
 * Switch membership tab
 */
function switchMembershipTab(tabName) {
    console.log(`📑 Switching to tab: ${tabName}`);

    // Hide all tabs
    document.querySelectorAll('[data-tab-content]').forEach(tab => {
        tab.style.display = 'none';
        tab.classList.remove('active');
    });

    // Remove active class from all buttons
    document.querySelectorAll('[data-tab-button]').forEach(btn => {
        btn.classList.remove('active');
    });

    // Show selected tab
    const selectedTab = document.querySelector(`[data-tab-content="${tabName}"]`);
    if (selectedTab) {
        selectedTab.style.display = 'block';
        selectedTab.classList.add('active');
    }

    // Mark button as active
    const selectedButton = document.querySelector(`[data-tab-button="${tabName}"]`);
    if (selectedButton) {
        selectedButton.classList.add('active');
    }
}

/**
 * Select a plan
 */
function selectPlan(planName) {
    console.log(`👑 Selected plan: ${planName}`);
    showAlert(`✅ Plan ${planName} seleccionado. Procede al pago.`, 'success');
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
 * Show alert message
 */
function showAlert(message, type = 'info') {
    console.log(`[${type.toUpperCase()}] ${message}`);
    // TODO: Implementar UI de alertas
    alert(message);
}

console.log('✅ memberships.js fully loaded');
