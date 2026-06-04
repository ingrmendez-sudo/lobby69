console.log('👑 memberships.js initializing...');

let allPlans = [];

document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ memberships.js loaded');
    loadMembershipPlans();
});

/**
 * Cargar planes desde la API
 */
function loadMembershipPlans() {
    console.log('🔄 Cargando planes...');

    fetch('/api/membership-plans/')
        .then(r => r.json())
        .then(data => {
            console.log('✅ Planes recibidos:', data);
            allPlans = data.plans || [];
            renderAllPlans(allPlans);
        })
        .catch(e => {
            console.error('❌ Error:', e);
            const container = document.getElementById('plans-grid');
            if (container) {
                container.innerHTML = '<p style="color:red;">Error cargando planes</p>';
            }
        });
}

/**
 * Renderizar todos los planes en tarjetas
 */
function renderAllPlans(plans) {
    const container = document.getElementById('plans-grid');
    if (!container) {
        console.error('❌ Container plans-grid no encontrado');
        return;
    }

    container.innerHTML = `
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; padding: 20px; grid-column: 1/-1;">
            ${plans.map(plan => `
                <div style="border: 2px solid #ddd; border-radius: 8px; padding: 20px; text-align: center; background: white;">
                    <h3 style="margin: 0 0 10px; color: #333;">${plan.name}</h3>
                    <p style="margin: 0 0 10px; font-size: 12px; color: #666;">${plan.duration_days} días</p>

                    ${plan.discount_percent ? `
                        <p style="margin: 0; text-decoration: line-through; color: #999;">$${plan.original_price.toFixed(2)} MXN</p>
                        <p style="margin: 5px 0; background: #ff6b6b; color: white; padding: 5px; border-radius: 4px; font-size: 12px; font-weight: bold;">
                            -${plan.discount_percent}%
                        </p>
                    ` : ''}

                    <h2 style="margin: 10px 0; color: #4682b4;">$${plan.price.toFixed(2)} MXN</h2>

                    <button onclick="goToCheckout('${plan.id}', '${plan.name}', ${plan.price})"
                            style="width: 100%; padding: 10px; background: #4682b4; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; margin-top: 10px;">
                        Contratar
                    </button>
                </div>
            `).join('')}
        </div>
    `;

    console.log('✅ Planes renderizados');
}

/**
 * Ir a checkout
 */
function goToCheckout(planId, planName, price) {
    console.log(`💳 Checkout: ${planName} - $${price} MXN`);
    alert(`Plan: ${planName}\nPrecio: $${price.toFixed(2)} MXN\n\nCheckout con Conekta (próximamente)`);
    // TODO: Abrir modal de Conekta
}

console.log('✅ memberships.js fully loaded');

/**
 * Cambiar entre tabs
 */
function initializeTabNavigation() {
    const tabButtons = document.querySelectorAll('[data-tab-button]');

    tabButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const tabName = this.getAttribute('data-tab-button');
            switchTab(tabName);
        });
    });
}

function switchTab(tabName) {
    console.log(`📑 Switch tab: ${tabName}`);

    // Ocultar todos los tabs
    document.querySelectorAll('[data-tab-content]').forEach(tab => {
        tab.style.display = 'none';
    });

    // Desactivar todos los botones
    document.querySelectorAll('[data-tab-button]').forEach(btn => {
        btn.classList.remove('active');
    });

    // Mostrar tab seleccionado
    const selectedTab = document.querySelector(`[data-tab-content="${tabName}"]`);
    if (selectedTab) {
        selectedTab.style.display = 'block';
    }

    // Activar botón seleccionado
    const selectedButton = document.querySelector(`[data-tab-button="${tabName}"]`);
    if (selectedButton) {
        selectedButton.classList.add('active');
    }
}

console.log('✅ memberships.js fully loaded');
