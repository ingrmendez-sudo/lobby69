// EXPLORE - FUNCIONES ESPECÍFICAS

// Buscar y filtrar perfiles
document.getElementById('search-profiles')?.addEventListener('keyup', function(e) {
    console.log('Buscando:', e.target.value);
    // TODO: Implementar búsqueda en tiempo real
});

// Aplicar filtros
document.querySelector('.btn-apply-filters')?.addEventListener('click', function() {
    const filters = {
        gender: document.getElementById('gender').value,
        ageMin: document.getElementById('age-min').value,
        ageMax: document.getElementById('age-max').value,
        city: document.getElementById('city').value,
        profileType: document.getElementById('profile-type').value,
        membership: document.getElementById('membership').value,
    };
    console.log('Aplicando filtros:', filters);
    // TODO: Implementar lógica de filtros
});

// Like a perfil
function likeProfile(profileId) {
    console.log('Like a perfil:', profileId);
    // TODO: Implementar like
}
