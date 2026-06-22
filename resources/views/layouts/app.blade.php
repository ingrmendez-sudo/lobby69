<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'LOBBY69') | LOBBY69</title>
    <meta name="description" content="LOBBY69 - La comunidad swinger más discreta de México">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Montserrat:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/00-vivid-nights.css') }}">
    @stack('styles')
    <style>
    /* ── Modal crítico — inline para garantizar carga ── */
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(44, 62, 80, 0.75);
        z-index: 99999;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }
    .modal-overlay.is-open { display: flex; }
    .modal {
        background: #ffffff;
        border-radius: 20px;
        max-width: 560px;
        width: 100%;
        max-height: 88vh;
        overflow-y: auto;
        box-shadow: 0 24px 64px rgba(0,0,0,0.3);
        position: relative;
    }
    .modal__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem 1.5rem;
        border-bottom: 1px solid rgba(44,62,80,0.08);
        position: sticky;
        top: 0;
        background: #fff;
        z-index: 1;
    }
    .modal__body { padding: 1.5rem; line-height: 1.7; }
    .modal__body h3 { margin: 1rem 0 0.4rem; font-size: 1rem; font-weight: 600; }
    .modal__body p, .modal__body li { font-size: 0.9rem; color: #4a5568; }
    .modal__body ul { padding-left: 1.5rem; list-style: disc; }
    .modal__footer {
        display: flex;
        justify-content: flex-end;
        padding: 1rem 1.5rem;
        border-top: 1px solid rgba(44,62,80,0.08);
        position: sticky;
        bottom: 0;
        background: #fff;
    }
    /* ── Toast ── */
    .toast {
        position: fixed;
        top: 1.5rem;
        right: 1.5rem;
        z-index: 99998;
        padding: 1rem 1.5rem;
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        max-width: 420px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.15);
        animation: toastIn 0.3s ease;
    }
    .toast--success { background: #27AE60; color: #fff; }
    .toast--error   { background: #E74C3C; color: #fff; }
    @keyframes toastIn {
        from { transform: translateX(120%); opacity: 0; }
        to   { transform: translateX(0);    opacity: 1; }
    }
    </style>
<style>[x-cloak] { display: none !important; }</style>
</head>
<body>
    @include('components.navbar')

    <main>
        @if(session('success'))
            <div class="toast toast--success" id="toast-success">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="toast toast--error" id="toast-error">
                <i class="fas fa-exclamation-circle"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @yield('content')
    </main>

    @include('components.footer')
    @include('components.legal-modals')

    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')

    <script>
    (function () {

        function openModal(id) {
            var modal = document.getElementById(id);
            if (!modal) return;
            modal.style.display = "flex";
            document.body.style.overflow = "hidden";
        }

        function closeModal(id) {
            var modal = document.getElementById(id);
            if (!modal) return;
            modal.style.display = "none";
            document.body.style.overflow = "";
        }

        window.openModal  = openModal;
        window.closeModal = closeModal;

        document.addEventListener("DOMContentLoaded", function () {

            // Abrir modales
            document.querySelectorAll("[data-modal]").forEach(function (el) {
                el.addEventListener("click", function (e) {
                    e.preventDefault();
                    openModal(el.getAttribute("data-modal"));
                });
            });

            // Cerrar modales
            document.querySelectorAll("[data-close]").forEach(function (el) {
                el.addEventListener("click", function (e) {
                    e.preventDefault();
                    closeModal(el.getAttribute("data-close"));
                });
            });

            // Cerrar al hacer clic en el fondo overlay
            document.querySelectorAll(".modal-overlay").forEach(function (overlay) {
                overlay.addEventListener("click", function (e) {
                    if (e.target === overlay) {
                        overlay.style.display = "none";
                        document.body.style.overflow = "";
                    }
                });
            });

            // Escape
            document.addEventListener("keydown", function (e) {
                if (e.key === "Escape") {
                    document.querySelectorAll(".modal-overlay").forEach(function (m) {
                        if (m.style.display === "flex") {
                            m.style.display = "none";
                        }
                    });
                    document.body.style.overflow = "";
                }
            });

            // Toast auto-hide
            ["toast-success", "toast-error"].forEach(function (id) {
                var el = document.getElementById(id);
                if (el) {
                    setTimeout(function () {
                        el.style.transition = "opacity 0.5s";
                        el.style.opacity = "0";
                        setTimeout(function () { el.remove(); }, 500);
                    }, 5000);
                }
            });
        });

    }());
    </script>
</body>
</html>