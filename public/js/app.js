// LOBBY69 — app.js

// ── Modales ──────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {

    // Abrir modal al hacer clic en [data-modal="id"]
    document.querySelectorAll('[data-modal]').forEach(function (trigger) {
        trigger.addEventListener('click', function (e) {
            e.preventDefault();
            var id = this.getAttribute('data-modal');
            var modal = document.getElementById(id);
            if (modal) {
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        });
    });

    // Cerrar modal al hacer clic en el overlay (fuera del contenido)
    document.querySelectorAll('.modal-overlay').forEach(function (overlay) {
        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) {
                overlay.style.display = 'none';
                document.body.style.overflow = '';
            }
        });
    });

    // Cerrar modal con tecla Escape
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay').forEach(function (m) {
                m.style.display = 'none';
            });
            document.body.style.overflow = '';
        }
    });

    // ── Toast auto-hide ───────────────────────────────────
    var toasts = document.querySelectorAll('.toast');
    toasts.forEach(function (toast) {
        setTimeout(function () {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.5s ease';
            setTimeout(function () {
                toast.remove();
            }, 500);
        }, 5000);
    });

});