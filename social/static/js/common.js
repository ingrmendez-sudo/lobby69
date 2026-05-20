/**
 * common.js - Funciones compartidas para todas las páginas
 * Búsqueda, filtros, acciones, validaciones
 */

// =======================
// BÚSQUEDA Y FILTROS
// =======================

window.setupSearch = function(inputSelector, itemSelector, searchFields = ['text-content']) {
    const input = document.querySelector(inputSelector);
    if (!input) return;
    
    input.addEventListener('keyup', debounce(function(e) {
        const query = e.target.value.toLowerCase();
        const items = document.querySelectorAll(itemSelector);
        let visibleCount = 0;
        
        items.forEach(item => {
            let matches = false;
            searchFields.forEach(field => {
                const content = (item.getAttribute(`data-${field}`) || item.textContent || '').toLowerCase();
                if (content.includes(query)) matches = true;
            });
            
            item.style.display = matches ? '' : 'none';
            if (matches) visibleCount++;
        });
        
        console.log(`🔍 Búsqueda: ${query} - ${visibleCount} resultados`);
    }, 300));
};

// =======================
// FILTROS GENERALES
// =======================

window.setupFilter = function(filterSelector, itemSelector, filterAttribute = 'data-filter') {
    const filters = document.querySelectorAll(filterSelector);
    
    filters.forEach(filter => {
        filter.addEventListener('click', function() {
            const filterValue = this.getAttribute(filterAttribute);
            const items = document.querySelectorAll(itemSelector);
            let visibleCount = 0;
            
            items.forEach(item => {
                const itemFilter = item.getAttribute(filterAttribute);
                const show = !filterValue || itemFilter === filterValue || itemFilter.includes(filterValue);
                item.style.display = show ? '' : 'none';
                if (show) visibleCount++;
            });
            
            // Actualizar estado activo del filtro
            filters.forEach(f => f.classList.remove('active'));
            this.classList.add('active');
            
            console.log(`📊 Filtro: ${filterValue} - ${visibleCount} resultados`);
        });
    });
};

// =======================
// MODAL/POPUP
// =======================

window.showModal = function(title, content, buttons = []) {
    const modal = document.createElement('div');
    modal.style.cssText = `
        position: fixed; top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.5); display: flex; align-items: center;
        justify-content: center; z-index: 10000;
    `;
    
    const box = document.createElement('div');
    box.style.cssText = `
        background: white; border-radius: 12px; padding: 24px;
        max-width: 500px; width: 90%; box-shadow: 0 4px 16px rgba(0,0,0,0.2);
    `;
    
    let html = `<h2 style="margin: 0 0 16px 0; font-size: 18px;">${title}</h2>`;
    html += `<div style="margin-bottom: 20px; color: #666;">${content}</div>`;
    
    if (buttons.length > 0) {
        html += `<div style="display: flex; gap: 10px; justify-content: flex-end;">`;
        buttons.forEach(btn => {
            html += `<button data-action="${btn.action}" style="padding: 10px 20px; border: 1px solid #ddd; border-radius: 6px; cursor: pointer; background: ${btn.color || '#f0f0f0'}; color: ${btn.textColor || '#333'}; font-weight: 600;">${btn.text}</button>`;
        });
        html += `</div>`;
    }
    
    box.innerHTML = html;
    modal.appendChild(box);
    document.body.appendChild(modal);
    
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.remove();
        }
    });
    
    box.querySelectorAll('button').forEach(btn => {
        btn.addEventListener('click', function() {
            const action = this.getAttribute('data-action');
            if (action === 'close') {
                modal.remove();
            } else if (typeof window[action] === 'function') {
                window[action]();
                modal.remove();
            }
        });
    });
    
    return modal;
};

// =======================
// TOAST/ALERTA
// =======================

window.showAlert = function(message, type = 'info', duration = 3000) {
    const colors = {
        success: ['#d4edda', '#155724'],
        error: ['#f8d7da', '#721c24'],
        warning: ['#fff3cd', '#856404'],
        info: ['#d1ecf1', '#0c5460']
    };
    
    const [bg, color] = colors[type] || colors.info;
    
    const alert = document.createElement('div');
    alert.style.cssText = `
        position: fixed; top: 20px; right: 20px; padding: 16px 20px;
        background: ${bg}; color: ${color}; border-radius: 8px;
        z-index: 9999; font-weight: 500; box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        animation: slideIn 0.3s ease-out;
    `;
    alert.textContent = message;
    
    document.body.appendChild(alert);
    
    setTimeout(() => {
        alert.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => alert.remove(), 300);
    }, duration);
};

// =======================
// PAGINACIÓN
// =======================

window.setupPagination = function(containerSelector, itemsPerPage = 10) {
    const container = document.querySelector(containerSelector);
    if (!container) return;
    
    const items = Array.from(container.children);
    let currentPage = 1;
    const totalPages = Math.ceil(items.length / itemsPerPage);
    
    function showPage(page) {
        const start = (page - 1) * itemsPerPage;
        const end = start + itemsPerPage;
        
        items.forEach((item, index) => {
            item.style.display = (index >= start && index < end) ? '' : 'none';
        });
    }
    
    showPage(1);
    return { showPage, totalPages, currentPage: () => currentPage };
};

// =======================
// VALIDACIONES
// =======================

window.validateEmail = function(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
};

window.validateUrl = function(url) {
    try {
        new URL(url);
        return true;
    } catch {
        return false;
    }
};

window.validatePassword = function(password) {
    return password.length >= 8;
};

// =======================
// FORMULARIOS
// =======================

window.submitForm = function(formSelector, onSuccess) {
    const form = document.querySelector(formSelector);
    if (!form) return;
    
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);
        
        try {
            const response = await fetch(form.action || location.pathname, {
                method: form.method || 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRFToken': getCookie('csrftoken')
                },
                body: JSON.stringify(data)
            });
            
            if (response.ok) {
                showAlert('✅ Guardado correctamente', 'success');
                if (typeof onSuccess === 'function') onSuccess();
            } else {
                showAlert('❌ Error al guardar', 'error');
            }
        } catch (err) {
            showAlert(`❌ ${err.message}`, 'error');
        }
    });
};

// =======================
// UTILIDADES
// =======================

function debounce(func, wait) {
    let timeout;
    return function(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func(...args), wait);
    };
}

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

// =======================
// ANIMACIONES GLOBALES
// =======================

if (!document.querySelector('style[data-common-animations]')) {
    const style = document.createElement('style');
    style.setAttribute('data-common-animations', 'true');
    style.textContent = `
        @keyframes slideIn {
            from { transform: translateX(400px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(400px); opacity: 0; }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .active { background: rgba(70, 130, 180, 0.1) !important; border-left: 3px solid #4682B4 !important; }
    `;
    document.head.appendChild(style);
}

console.log('✅ common.js cargado');
