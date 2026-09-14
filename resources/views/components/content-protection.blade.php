{{-- ═══════════════════════════════════════════════════════════════
     CONTENT PROTECTION LAYER — Lobby69
     Protege imágenes y videos contra descarga casual y scraping.
     IMPORTANTE: No previene capturas de pantalla a nivel OS.
     Nivel de protección: Anti-casual (80% de usuarios no técnicos)
═══════════════════════════════════════════════════════════════════ --}}
<style>
/* ── Protección base para todas las imágenes protegidas ── */
.l69-protected,
.l69-protected img,
.l69-media-wrap img {
    user-select: none;
    -webkit-user-select: none;
    -webkit-touch-callout: none; /* iOS — deshabilita menú "Guardar imagen" */
    pointer-events: none;
}

/* ── Wrapper relativo necesario para el overlay ── */
.l69-media-wrap {
    position: relative;
    display: inline-block;
    overflow: hidden;
}

/* ── Overlay transparente — intercepta clic derecho en desktop ── */
.l69-media-wrap::after {
    content: '';
    position: absolute;
    inset: 0;
    z-index: 10;
    background: transparent;
    cursor: default;
}

/* ── Videos protegidos ── */
.l69-protected video,
.l69-media-wrap video {
    user-select: none;
    -webkit-user-select: none;
    pointer-events: none;
}

/* ── Deshabilitar selección global en áreas de contenido ── */
.l69-content-area {
    user-select: none;
    -webkit-user-select: none;
}

/* ── Watermark visible sutil ── */
.l69-watermark {
    position: absolute;
    bottom: 8px;
    right: 10px;
    z-index: 20;
    font-size: 0.65rem;
    color: rgba(255,255,255,0.45);
    font-weight: 600;
    letter-spacing: 0.05em;
    pointer-events: none;
    text-shadow: 0 1px 3px rgba(0,0,0,0.8);
    user-select: none;
    -webkit-user-select: none;
}
</style>

<script>
(function() {
    'use strict';

    // ── 1. Bloquear clic derecho global en imágenes y videos ──
    document.addEventListener('contextmenu', function(e) {
        const tag = e.target.tagName.toLowerCase();
        const isProtected = e.target.closest('.l69-media-wrap, .l69-protected');
        if (tag === 'img' || tag === 'video' || isProtected) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
    }, true);

    // ── 2. Bloquear drag & drop de imágenes ──
    document.addEventListener('dragstart', function(e) {
        const tag = e.target.tagName.toLowerCase();
        if (tag === 'img' || tag === 'video' || e.target.closest('.l69-media-wrap')) {
            e.preventDefault();
            return false;
        }
    }, true);

    // ── 3. Bloquear atajos de teclado de captura/guardado ──
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + S (guardar página)
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            return false;
        }
        // Ctrl/Cmd + U (ver fuente)
        if ((e.ctrlKey || e.metaKey) && e.key === 'u') {
            e.preventDefault();
            return false;
        }
        // F12 (DevTools) — solo disuasorio, no bloqueable realmente
        // No lo bloqueamos — genera mala UX sin beneficio real
    });

    // ── 4. Bloquear Print Screen — detección y oscurecimiento ──
    document.addEventListener('keyup', function(e) {
        // PrintScreen key
        if (e.key === 'PrintScreen' || e.keyCode === 44) {
            // Oscurecer brevemente el contenido como disuasivo
            const overlay = document.createElement('div');
            overlay.style.cssText = [
                'position:fixed', 'inset:0', 'z-index:999999',
                'background:rgba(0,0,0,0.95)', 'pointer-events:none',
                'transition:opacity .3s'
            ].join(';');
            document.body.appendChild(overlay);
            setTimeout(() => {
                overlay.style.opacity = '0';
                setTimeout(() => overlay.remove(), 300);
            }, 200);
            // Limpiar clipboard — el screenshot ya se tomó pero
            // intentamos sobrescribir el portapapeles
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText('').catch(() => {});
            }
        }
    });

    // ── 5. Detección de DevTools (solo registra, no bloquea) ──
    // Solo para analytics de seguridad — no bloquear DevTools
    // es contraproducente y frustra usuarios legítimos
    let devtoolsOpen = false;
    const threshold = 160;
    setInterval(function() {
        const widthDiff  = window.outerWidth  - window.innerWidth  > threshold;
        const heightDiff = window.outerHeight - window.innerHeight > threshold;
        if ((widthDiff || heightDiff) && !devtoolsOpen) {
            devtoolsOpen = true;
            // Solo log — no acción agresiva
            console.warn('[L69] DevTools detectado');
        } else if (!widthDiff && !heightDiff) {
            devtoolsOpen = false;
        }
    }, 1000);

    // ── 6. Bloquear Print (Ctrl+P) ──
    window.addEventListener('beforeprint', function(e) {
        e.preventDefault();
        // Ocultar todo el contenido si se intenta imprimir
        const style = document.createElement('style');
        style.id = 'l69-print-block';
        style.innerHTML = '@media print { body { display: none !important; } }';
        document.head.appendChild(style);
    });

    window.addEventListener('afterprint', function() {
        const s = document.getElementById('l69-print-block');
        if (s) s.remove();
    });

    // ── 7. Proteger elementos dinámicos (MutationObserver) ──
    // Aplica atributos de protección a imgs y videos añadidos dinámicamente
    const protectElement = function(el) {
        if (el.tagName === 'IMG') {
            el.setAttribute('draggable', 'false');
            el.setAttribute('oncontextmenu', 'return false');
        }
        if (el.tagName === 'VIDEO') {
            el.setAttribute('controlslist', 'nodownload');
            el.setAttribute('oncontextmenu', 'return false');
            el.disablePictureInPicture = true;
        }
    };

    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            mutation.addedNodes.forEach(function(node) {
                if (node.nodeType !== 1) return;
                if (node.tagName === 'IMG' || node.tagName === 'VIDEO') {
                    protectElement(node);
                }
                node.querySelectorAll && node.querySelectorAll('img, video').forEach(protectElement);
            });
        });
    });

    observer.observe(document.body, { childList: true, subtree: true });

    // Proteger elementos ya existentes al cargar
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('img').forEach(function(img) {
            img.setAttribute('draggable', 'false');
        });
        document.querySelectorAll('video').forEach(function(video) {
            video.setAttribute('controlslist', 'nodownload');
            video.disablePictureInPicture = true;
        });
    });

})();
</script>
