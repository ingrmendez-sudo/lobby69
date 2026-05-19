console.log('✅ explore.js cargado');

document.addEventListener('DOMContentLoaded', function() {
    console.log('🔧 Inicializando explore.js...');
    initializeUI();
    initializeSearch();
    initializeLikes();
    initializeProfileLinks();
    initializeConnectedUsers();
});

// ============ BÚSQUEDA (agregar esta función si falta) ============
function initializeSearch() {
    console.log('🔎 Inicializando búsqueda');
    const searchInput = document.getElementById('search-profiles');
    if (searchInput) {
        searchInput.addEventListener('keyup', filterProfiles);
    }
}


// ============ UI ============
function initializeUI() {
    console.log('🎨 Inicializando UI');
    const leftSidebar = document.querySelector('.explore-sidebar-left');
    const rightSidebar = document.querySelector('.explore-sidebar-right');

    if (leftSidebar) leftSidebar.style.visibility = 'visible';
    if (rightSidebar) rightSidebar.style.visibility = 'visible';
}

// ============ FILTROS ============
function applyFilters() {
    console.log('🔍 Aplicando filtros...');

    const profileType = document.getElementById('profile-type')?.value || '';
    const state = document.getElementById('state')?.value || '';
    const ageMin = document.getElementById('age-min')?.value || '';
    const ageMax = document.getElementById('age-max')?.value || '';
    const membership = document.getElementById('membership')?.value || '';
    const rating = document.getElementById('rating')?.value || '';

    console.log('📋 Valores de filtros:', { profileType, state, ageMin, ageMax, membership, rating });

    // Filtrar perfiles en el DOM
    filterProfilesLocally({
        profile_type: profileType,
        state: state,
        age_min: ageMin,
        age_max: ageMax,
        membership: membership,
        rating: rating
    });

    showAlert('✅ Filtros aplicados', 'success');
}

function filterProfilesLocally(filters) {
    const profileCards = document.querySelectorAll('.profile-card');
    let visibleCount = 0;

    console.log(`🔎 Filtrando ${profileCards.length} perfiles...`);

    profileCards.forEach(card => {
        const profileType = card.dataset.profileType?.toLowerCase() || '';
        const state = card.dataset.state?.toLowerCase() || '';
        const age = parseInt(card.dataset.age) || 0;
        const membership = card.dataset.membership?.toLowerCase() || '';
        const rating = parseFloat(card.dataset.rating) || 0;

        let showProfile = true;

        // Aplicar filtros
        if (filters.profile_type && profileType !== filters.profile_type.toLowerCase()) {
            showProfile = false;
        }
        if (filters.state && !state.includes(filters.state.toLowerCase())) {
            showProfile = false;
        }
        if (filters.age_min && age < parseInt(filters.age_min)) {
            showProfile = false;
        }
        if (filters.age_max && age > parseInt(filters.age_max)) {
            showProfile = false;
        }
        if (filters.membership && membership !== filters.membership.toLowerCase()) {
            showProfile = false;
        }
        if (filters.rating && rating < parseFloat(filters.rating)) {
            showProfile = false;
        }

        card.style.display = showProfile ? 'block' : 'none';
        if (showProfile) visibleCount++;
    });

    console.log(`✅ Resultado: ${visibleCount} perfiles visibles`);

    if (visibleCount === 0) {
        showAlert('⚠️ No hay perfiles con esos criterios', 'info');
    }
}

// ============ BÚSQUEDA ============
function filterProfiles() {
    const searchInput = document.getElementById('search-profiles');
    if (!searchInput) return;

    const searchTerm = searchInput.value.toLowerCase();
    const profileCards = document.querySelectorAll('.profile-card');

    let visibleCount = 0;

    profileCards.forEach(card => {
        const name = (card.dataset.name || '').toLowerCase();
        const city = (card.dataset.city || '').toLowerCase();

        const matches = name.includes(searchTerm) || city.includes(searchTerm);
        card.style.display = matches ? 'block' : 'none';
        if (matches) visibleCount++;
    });

    console.log(`🔎 Búsqueda: "${searchTerm}" → ${visibleCount} resultados`);
}

// ============ LIKES ============
function initializeLikes() {
    console.log('❤️ Inicializando likes');

    const likeButtons = document.querySelectorAll('.btn-like');
    likeButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            toggleLike(e);
        });
    });
}

function toggleLike(event) {
    event.preventDefault();
    event.stopPropagation();

    const btn = event.currentTarget;
    const profileId = btn.dataset.profileId;

    console.log(`❤️ Toggling like para perfil: ${profileId}`);

    btn.classList.toggle('liked');
    btn.style.color = btn.classList.contains('liked') ? '#e91e63' : '#999';

    showAlert('❤️ ¡Te gusta este perfil!', 'success');
}

// ============ NAVEGACIÓN A PERFIL ============
function initializeProfileLinks() {
    console.log('👤 Inicializando links de perfil');

    const profileCards = document.querySelectorAll('.profile-card');
    profileCards.forEach(card => {
        card.style.cursor = 'pointer';

        // Click en la tarjeta (evitando botones)
        card.addEventListener('click', function(e) {
            if (e.target.closest('button')) return;

            const nickname = this.dataset.profileNick;
            console.log(`👤 Abriendo perfil desde tarjeta: ${nickname}`);

            if (nickname) {
                window.location.href = `/usuario/${nickname}/`;
            }
        });
    });
}

function viewProfile(nickname) {
    console.log(`👤 viewProfile() llamado con nickname: ${nickname}`);

    if (nickname) {
        window.location.href = `/usuario/${nickname}/`;
    } else {
        console.error('❌ Profile nickname no encontrado');
        showAlert('❌ Error: No se encontró el perfil', 'error');
    }
}

// ============ USUARIOS CONECTADOS ============
function initializeConnectedUsers() {
    console.log('🟢 Inicializando usuarios conectados');

    const connectedUsers = document.querySelectorAll('.connected-user');
    connectedUsers.forEach(user => {
        user.style.cursor = 'pointer';

        user.addEventListener('click', function() {
            // Obtener nickname del nombre mostrado
            const nameEl = this.querySelector('.connected-name');
            if (nameEl) {
                const displayName = nameEl.textContent.trim();
                console.log(`🟢 Click en usuario conectado: ${displayName}`);

                // Buscar en los perfiles disponibles
                const profileCard = Array.from(document.querySelectorAll('.profile-card'))
                    .find(card => card.dataset.name?.toLowerCase() === displayName.toLowerCase());

                if (profileCard) {
                    const nickname = profileCard.dataset.profileNick;
                    if (nickname) {
                        window.location.href = `/usuario/${nickname}/`;
                    }
                }
            }
        });
    });
}

// ============ ALERTAS ============
function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.textContent = message;
    alertDiv.style.cssText = `
        position: fixed;
        top: 120px;
        left: 50%;
        transform: translateX(-50%);
        padding: 15px 25px;
        border-radius: 8px;
        background: ${type === 'success' ? '#4caf50' : type === 'error' ? '#f44336' : '#2196f3'};
        color: white;
        z-index: 9999;
        animation: slideIn 0.3s ease-in-out;
        max-width: 500px;
        text-align: center;
        font-weight: 500;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    `;

    document.body.appendChild(alertDiv);

    setTimeout(() => {
        alertDiv.style.animation = 'slideOut 0.3s ease-in-out';
        setTimeout(() => alertDiv.remove(), 300);
    }, 2000);
}

// ============ ANIMACIONES ============
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


console.log('✅ explore.js inicializado correctamente');

function navigateToProfile(nickname) {
    console.log(`🟢 Navegando a perfil: ${nickname}`);
    if (nickname) {
        window.location.href = `/usuario/${nickname}/`;
    } else {
        console.error('❌ Nickname no encontrado');
        showAlert('❌ Error: No se pudo navegar al perfil', 'error');
    }
}
