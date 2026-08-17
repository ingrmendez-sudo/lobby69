<!DOCTYPE html>
<html lang="es">
<head>
    <script>
        (function(){
            var t = localStorage.getItem("lobby69-theme");
            if (!t) t = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
            document.documentElement.setAttribute("data-theme", t);
        })();
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/favicon.png">
    <title>@yield('title', 'LOBBY69') | LOBBY69</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Montserrat:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/00-vivid-nights.css') }}">
    @stack('styles')
    <style>
    body { background: var(--theme-bg, #f8f7ff); min-height: 100vh; }
    .modal-overlay {
        display:none; position:fixed; top:0; left:0; right:0; bottom:0;
        background:rgba(44,62,80,.75); z-index:99999;
        align-items:center; justify-content:center; padding:1rem;
    }
    .modal-overlay.is-open { display:flex; }
    .modal {
        background:#fff; border-radius:16px; max-width:540px; width:100%;
        max-height:85vh; overflow-y:auto;
        box-shadow:0 24px 64px rgba(0,0,0,.3); position:relative;
    }
    .modal__header {
        display:flex; align-items:center; justify-content:space-between;
        padding:1rem 1.5rem; border-bottom:1px solid rgba(44,62,80,.08);
        position:sticky; top:0; background:#fff; z-index:1;
    }
    .modal__header h3 { margin:0; font-size:1rem; font-weight:700; }
    .modal__body { padding:1.5rem; line-height:1.7; font-size:.9rem; color:#4a5568; }
    .modal__body h4 { font-size:.95rem; font-weight:600; color:#1a202c; margin:1rem 0 .3rem; }
    .modal__footer { padding:1rem 1.5rem; display:flex; justify-content:flex-end; border-top:1px solid rgba(44,62,80,.08); }
    [data-theme="dark"] .modal,
    [data-theme="dark"] .modal__header,
    [data-theme="dark"] .modal__footer { background:#1e1b2e; }
    [data-theme="dark"] .modal__header h3 { color:#e2e8f0; }
    [data-theme="dark"] .modal__body { color:#94a3b8; }
    [data-theme="dark"] .modal__body h4 { color:#e2e8f0; }
    </style>
</head>
<body>

@yield('content')

{{-- ══ MODAL: Términos y Condiciones ══ --}}
<div class="modal-overlay" id="modalTerminos">
    <div class="modal">
        <div class="modal__header">
            <h3>📋 Términos y Condiciones</h3>
            <button data-modal-close style="background:none;border:none;font-size:1.3rem;cursor:pointer;color:inherit;">✕</button>
        </div>
        <div class="modal__body">
            <h4>1. Aceptación</h4>
            <p>Al registrarte en LOBBY69 aceptas estos términos en su totalidad. Si no estás de acuerdo, no utilices la plataforma.</p>
            <h4>2. Edad mínima</h4>
            <p>Debes tener al menos 18 años para usar LOBBY69. Al registrarte confirmas que cumples este requisito.</p>
            <h4>3. Contenido</h4>
            <p>El contenido compartido es exclusivo para adultos. Queda prohibido publicar material ilegal, violento o que involucre menores de edad.</p>
            <h4>4. Privacidad</h4>
            <p>Nos comprometemos a proteger tu información personal conforme a nuestra Política de Privacidad.</p>
            <h4>5. Conducta</h4>
            <p>Los usuarios deben comportarse con respeto. Cualquier acoso, discriminación o uso indebido resultará en la suspensión de la cuenta.</p>
            <h4>6. Modificaciones</h4>
            <p>LOBBY69 se reserva el derecho de modificar estos términos. Los cambios serán notificados con anticipación.</p>
        </div>
        <div class="modal__footer">
            <button data-modal-close class="btn btn--primary" style="padding:.5rem 1.5rem;border-radius:8px;">Entendido</button>
        </div>
    </div>
</div>

{{-- ══ MODAL: Política de Privacidad ══ --}}
<div class="modal-overlay" id="modalPrivacidad">
    <div class="modal">
        <div class="modal__header">
            <h3>🔒 Política de Privacidad</h3>
            <button data-modal-close style="background:none;border:none;font-size:1.3rem;cursor:pointer;color:inherit;">✕</button>
        </div>
        <div class="modal__body">
            <h4>1. Datos que recopilamos</h4>
            <p>Recopilamos nombre, correo electrónico, edad, ubicación y preferencias para personalizar tu experiencia.</p>
            <h4>2. Uso de la información</h4>
            <p>Tu información se utiliza exclusivamente para operar la plataforma y nunca será vendida a terceros.</p>
            <h4>3. Seguridad</h4>
            <p>Implementamos medidas de seguridad estándar para proteger tus datos contra acceso no autorizado.</p>
            <h4>4. Cookies</h4>
            <p>Utilizamos cookies para mejorar la experiencia. Puedes desactivarlas en la configuración de tu navegador.</p>
            <h4>5. Tus derechos</h4>
            <p>Puedes solicitar la eliminación de tu cuenta y datos en cualquier momento contactando al equipo de LOBBY69.</p>
        </div>
        <div class="modal__footer">
            <button data-modal-close class="btn btn--primary" style="padding:.5rem 1.5rem;border-radius:8px;">Entendido</button>
        </div>
    </div>
</div>

@stack('scripts')
        });
    });
    document.querySelectorAll('.modal-overlay').forEach(function(o) {
        o.addEventListener('click', function(e) { if (e.target === this) this.classList.remove('is-open'); });
    });
    document.querySelectorAll('[data-modal-close]').forEach(function(b) {
        b.addEventListener('click', function() { this.closest('.modal-overlay').classList.remove('is-open'); });
    });

    // ── Municipios / Alcaldías de CDMX ──
    const alcaldiasCDMX = [
        'Álvaro Obregón','Azcapotzalco','Benito Juárez','Coyoacán',
        'Cuajimalpa','Cuauhtémoc','Gustavo A. Madero','Iztacalco',
        'Iztapalapa','La Magdalena Contreras','Miguel Hidalgo','Milpa Alta',
        'Tláhuac','Tlalpan','Venustiano Carranza','Xochimilco'
    ];
    const estadoSelect = document.getElementById('estado_mx');
    const municipioInput = document.getElementById('municipio');

    if (estadoSelect && municipioInput) {
        // Crear datalist para municipio
        var dl = document.createElement('datalist');
        dl.id = 'alcaldias-list';
        document.body.appendChild(dl);
        municipioInput.setAttribute('list', 'alcaldias-list');

        function updateMunicipioSuggestions() {
            dl.innerHTML = '';
            if (estadoSelect.value === 'CDMX') {
                alcaldiasCDMX.forEach(function(a) {
                    var opt = document.createElement('option');
                    opt.value = a;
                    dl.appendChild(opt);
                });
                municipioInput.placeholder = 'Ej: Coyoacán, Benito Juárez...';
            } else {
                municipioInput.placeholder = 'Ej: Benito Juárez';
            }
        }
        estadoSelect.addEventListener('change', updateMunicipioSuggestions);
        updateMunicipioSuggestions();
    }
});
</script>
</body>
</html>
