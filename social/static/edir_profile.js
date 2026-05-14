console.log('[EDIT_PROFILE] Script cargado');

document.addEventListener('DOMContentLoaded', function() {
    console.log('[EDIT_PROFILE] DOM cargado - buscando elementos');

    const profileType = document.getElementById('profile_type');
    const parejaSection = document.getElementById('parejaSection');
    const estado = document.getElementById('estado');
    const ciudadGroup = document.getElementById('ciudad-group');
    const descripcion = document.getElementById('descripcion');
    const charCount = document.getElementById('charCount');
    const btnSubmit = document.getElementById('submitBtn');

    // Verificar que todos los elementos existen
    console.log('[EDIT_PROFILE] profileType:', profileType ? '✓' : '✗');
    console.log('[EDIT_PROFILE] parejaSection:', parejaSection ? '✓' : '✗');
    console.log('[EDIT_PROFILE] estado:', estado ? '✓' : '✗');
    console.log('[EDIT_PROFILE] ciudadGroup:', ciudadGroup ? '✓' : '✗');
    console.log('[EDIT_PROFILE] descripcion:', descripcion ? '✓' : '✗');

    if (!profileType || !parejaSection || !estado || !ciudadGroup || !descripcion) {
        console.error('[EDIT_PROFILE] ✗ Faltan elementos en el DOM');
        return;
    }

    // ===== MOSTRAR/OCULTAR PAREJA =====
    function mostrarOcultarPareja() {
        const valor = profileType.value;
        console.log('[PAREJA] Valor seleccionado:', valor);

        if (valor === 'pareja') {
            parejaSection.classList.add('visible');
            console.log('[PAREJA] ✓ MOSTRADA');
        } else {
            parejaSection.classList.remove('visible');
            console.log('[PAREJA] ✗ OCULTADA');
        }
    }

    // ===== MOSTRAR/OCULTAR ALCALDIA =====
    function mostrarOcultarAlcaldia() {
        const valor = estado.value.toLowerCase();
        console.log('[ALCALDIA] Valor seleccionado:', valor);

        if (valor === 'cdmx') {
            ciudadGroup.classList.add('visible');
            console.log('[ALCALDIA] ✓ MOSTRADA');
        } else {
            ciudadGroup.classList.remove('visible');
            console.log('[ALCALDIA] ✗ OCULTADA');
        }
    }

    // ===== CONTAR CARACTERES =====
    function contarCaracteres() {
        const length = descripcion.value.trim().length;

        if (length < 50) {
            charCount.textContent = `${length} / 50 caracteres (Falta ${50 - length})`;
            charCount.className = 'char-count error';
            btnSubmit.disabled = true;
        } else {
            charCount.textContent = `${length} caracteres ✓`;
            charCount.className = 'char-count';
            btnSubmit.disabled = false;
        }
    }

    // ===== EVENT LISTENERS =====
    profileType.addEventListener('change', mostrarOcultarPareja);
    estado.addEventListener('change', mostrarOcultarAlcaldia);
    descripcion.addEventListener('input', contarCaracteres);

    console.log('[EDIT_PROFILE] ✓ Event listeners configurados');

    // ===== EJECUTAR AL CARGAR =====
    mostrarOcultarPareja();
    mostrarOcultarAlcaldia();
    contarCaracteres();

    console.log('[EDIT_PROFILE] ✓ Funciones iniciales ejecutadas');

    // ===== AUTO-HIDE ALERTS =====
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.5s';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });

    console.log('[EDIT_PROFILE] ✓ Inicialización completada');
});
