console.log('memberships.js loaded');

let allPlans = [];

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOMContentLoaded - Cargando planes...');
    loadMembershipPlans();
});

// CARGAR PLANES
async function loadMembershipPlans() {
    try {
        const response = await fetch('/api/membership-plans/');
        if (!response.ok) throw new Error('Error: ' + response.status);
        
        const data = await response.json();
        console.log('PLANES CARGADOS:', data.plans);
        
        allPlans = data.plans ? data.plans.sort(function(a, b) { return a.price - b.price; }) : [];
        if (allPlans.length > 0) {
            renderPlansTable(allPlans);
        }
    } catch (err) {
        console.error('Error cargando planes:', err);
    }
}

// RENDERIZAR TABLA
function renderPlansTable(plans) {
    console.log('Renderizando tabla con ' + plans.length + ' planes');
    
    const table = document.getElementById('plans-comparison');
    if (!table) {
        console.error('Tabla no encontrada');
        return;
    }

    const colorMap = {
        'EXPLORER': { color: '#1976d2', bg: '#e3f2fd', textColor: '#0d47a1' },
        'CONNECTORS': { color: '#757575', bg: '#f5f5f5', textColor: '#424242' },
        'INFLUENCER': { color: '#f57f17', bg: '#fff3e0', textColor: '#e65100' },
        'VIP ELITE': { color: '#6a1b9a', bg: '#f3e5f5', textColor: '#4a148c' },
        'VITALICIO': { color: '#ff6f00', bg: '#ffe0b2', textColor: '#e65100', border: '3px solid #ff6f00' }
    };

    // ENCABEZADO
    let html = '<thead><tr style="background: #333; color: white;">';
    html += '<th style="padding: 12px 8px; text-align: left; font-weight: 900; border-bottom: 2px solid #1976d2;">CARACTERISTICA</th>';

    plans.forEach(function(plan) {
        const info = colorMap[plan.name] || { color: '#999', bg: '#fff', textColor: '#333' };
        let badge = '';
        let borderStyle = '';
        
        if (plan.name === 'INFLUENCER') {
            badge = '<br><small style="background: #f57f17; color: white; padding: 2px 6px; border-radius: 3px; font-weight: 900;">RECOMENDADO</small>';
        } else if (plan.name === 'VIP ELITE') {
            badge = '<br><small style="background: #6a1b9a; color: white; padding: 2px 6px; border-radius: 3px; font-weight: 900;">ELITE</small>';
        } else if (plan.name === 'VITALICIO') {
            badge = '<br><small style="background: #ff6f00; color: white; padding: 2px 6px; border-radius: 3px; font-weight: 900;">PERMANENTE</small>';
            borderStyle = 'border: 3px solid #ff6f00; box-shadow: 0 0 10px rgba(255, 111, 0, 0.3);';
        }
        
        const duration = plan.duration_days ? plan.duration_days + ' dias' : 'ILIMITADO';
        
        html += '<th style="padding: 12px 8px; text-align: center; font-weight: 900; border-bottom: 2px solid ' + info.color + '; background: ' + info.bg + '; color: ' + info.textColor + '; font-size: 11px; ' + borderStyle + '">';
        html += '<div style="font-weight: 900; font-size: 13px; margin-bottom: 4px;">' + plan.name + '</div>';
        html += '<div style="font-size: 12px; font-weight: 600;">$' + parseFloat(plan.price).toFixed(0) + '</div>';
        html += '<div style="font-size: 10px; color: #666; margin-top: 3px;">' + duration + '</div>';
        html += badge;
        html += '</th>';
    });

    html += '</tr></thead><tbody>';

    // FILAS DE CARACTERISTICAS
    const features = [
        'Acceso Plataforma',
        'Galerias Privadas',
        'Videollamadas',
        'Chat Exclusivo',
        'Soporte Prioritario',
        'Badge Verificado',
        'Estadisticas',
        'Beta Features'
    ];

    const featuresByPlan = {
        'EXPLORER': [1, 0, 0, 0, 0, 0, 0, 0],
        'CONNECTORS': [1, 1, 0, 0, 0, 0, 0, 0],
        'INFLUENCER': [1, 1, 1, 1, 1, 1, 1, 0],
        'VIP ELITE': [1, 1, 1, 1, 1, 1, 1, 1],
        'VITALICIO': [1, 1, 1, 1, 1, 1, 1, 1]
    };

    let rowIndex = 0;
    features.forEach(function(feature) {
        const bgColor = rowIndex % 2 === 0 ? '#fff' : '#f9f9f9';
        html += '<tr style="border-bottom: 1px solid #eee; background: ' + bgColor + ';">';
        html += '<td style="padding: 10px 8px; font-weight: 600; color: #333; font-size: 11px;">' + feature + '</td>';

        plans.forEach(function(plan) {
            const info = colorMap[plan.name] || { color: '#999', bg: '#fff' };
            const hasFeature = featuresByPlan[plan.name] ? featuresByPlan[plan.name][rowIndex] : 0;
            const symbol = hasFeature ? 'SI' : 'NO';
            const textColor = hasFeature ? info.color : '#ccc';
            const fontWeight = hasFeature ? '900' : '400';

            html += '<td style="padding: 10px 8px; text-align: center; color: ' + textColor + '; font-weight: ' + fontWeight + '; font-size: 12px;">' + symbol + '</td>';
        });

        html += '</tr>';
        rowIndex++;
    });

    // FILA DE BOTONES
    html += '<tr style="background: #f5f5f5; border-top: 3px solid #ddd;"><td style="padding: 12px 8px; font-weight: 900; color: #333; font-size: 11px;">CONTRATAR</td>';
    
    plans.forEach(function(plan) {
        const info = colorMap[plan.name] || { color: '#999' };
        let btnStyle = 'padding: 8px 10px; background: ' + info.color + '; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 900; font-size: 11px; transition: all 0.2s;';
        
        if (plan.name === 'VITALICIO') {
            btnStyle += 'box-shadow: 0 0 8px rgba(255, 111, 0, 0.5);';
        }
        
        html += '<td style="padding: 12px 8px; text-align: center;">';
        html += '<button onclick="goToCheckout(\'' + plan.id + '\', \'' + plan.name + '\', ' + plan.price + ')" style="' + btnStyle + '" onmouseover="this.style.opacity=\'0.8\'" onmouseout="this.style.opacity=\'1\'">CONTRATAR</button>';
        html += '</td>';
    });

    html += '</tr></tbody>';

    table.innerHTML = html;
    console.log('Tabla renderizada correctamente');
}

// FUNCIONES DE NAVEGACION
function switchTab(tabName) {
    console.log('Cambiando a tab: ' + tabName);
    
    document.querySelectorAll('.tab-content').forEach(function(el) { el.style.display = 'none'; });
    document.querySelectorAll('.tab-filter').forEach(function(el) {
        el.style.border = '2px solid transparent';
        el.style.background = 'transparent';
        el.style.color = '#666';
    });

    const tabElement = document.getElementById(tabName + '-tab');
    if (tabElement) {
        tabElement.style.display = 'block';
    }

    if (event && event.target) {
        event.target.style.border = '2px solid #4682b4';
        event.target.style.background = '#e3f2fd';
        event.target.style.color = '#4682b4';
    }
}

function goToCheckout(planId, planName, price) {
    console.log('Checkout: ' + planId + ', ' + planName + ', ' + price);
    alert('Plan: ' + planName + '\nPrecio: $' + parseFloat(price).toFixed(2) + ' MXN\n\n(Checkout - proximamente)');
}

function changePlan() {
    console.log('Cambiar plan');
    switchTab('plans');
}

function cancelSubscription() {
    if (confirm('Cancelar tu suscripcion?')) {
        alert('Funcion en desarrollo');
    }
}

