/**
 * DRAWER MANAGEMENT - RESPONSIVE SIDEBARS
 * Maneja apertura/cierre de drawers en móvil/tablet
 */

class DrawerManager {
    constructor() {
        this.drawers = {
            left: null,
            right: null
        };
        this.overlay = null;
        this.init();
    }

    init() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setupDrawers());
        } else {
            this.setupDrawers();
        }
    }

    setupDrawers() {
        console.log('DrawerManager: setupDrawers iniciado');

        // Crear overlay
        this.createOverlay();

        // Obtener sidebars existentes
        this.extractSidebars();

        // Crear botones toggle
        this.createToggleButtons();

        // Attach event listeners
        this.attachEventListeners();

        console.log('DrawerManager: Inicialización completada');
    }

    createOverlay() {
        if (!document.querySelector('.drawer-overlay')) {
            this.overlay = document.createElement('div');
            this.overlay.className = 'drawer-overlay';
            this.overlay.addEventListener('click', () => this.closeAllDrawers());
            document.body.appendChild(this.overlay);
            console.log('DrawerManager: Overlay creado');
        } else {
            this.overlay = document.querySelector('.drawer-overlay');
        }
    }

    extractSidebars() {
        // Buscar sidebar izquierdo
        const sidebarLeftSelectors = [
            '.sidebar-left',
            '.left-sidebar',
            '.profile-sidebar-left',
            '.settings-sidebar-left',
            '.dashboard-sidebar-left',
            '[class*="sidebar-left"]'
        ];

        let sidebarLeft = null;
        for (let selector of sidebarLeftSelectors) {
            sidebarLeft = document.querySelector(selector);
            if (sidebarLeft) {
                console.log('DrawerManager: Sidebar izquierdo encontrado:', selector);
                break;
            }
        }

        if (sidebarLeft) {
            this.drawers.left = this.createDrawer('left', sidebarLeft);
        }

        // Buscar sidebar derecho
        const sidebarRightSelectors = [
            '.sidebar-right',
            '.right-sidebar',
            '.profile-sidebar-right',
            '.settings-sidebar-right',
            '.dashboard-sidebar-right',
            '[class*="sidebar-right"]'
        ];

        let sidebarRight = null;
        for (let selector of sidebarRightSelectors) {
            sidebarRight = document.querySelector(selector);
            if (sidebarRight) {
                console.log('DrawerManager: Sidebar derecho encontrado:', selector);
                break;
            }
        }

        if (sidebarRight) {
            this.drawers.right = this.createDrawer('right', sidebarRight);
        }
    }

    createDrawer(side, sidebarElement) {
        const drawer = document.createElement('div');
        drawer.className = `drawer-container ${side}`;
        drawer.id = `drawer-${side}`;
        drawer.innerHTML = `
            <button class="drawer-close-btn" aria-label="Cerrar menú">✕</button>
            <div class="drawer-content"></div>
        `;

        // Copiar contenido del sidebar original
        const content = drawer.querySelector('.drawer-content');
        content.innerHTML = sidebarElement.innerHTML;

        // Ocultar sidebar original
        sidebarElement.style.display = 'none';

        // Agregar drawer al body
        document.body.appendChild(drawer);

        // Event listener para botón cerrar
        drawer.querySelector('.drawer-close-btn').addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.closeDrawer(side);
        });

        console.log(`DrawerManager: Drawer ${side} creado`);
        return drawer;
    }

    createToggleButtons() {
        const navbar = document.querySelector('nav');
        if (!navbar) {
            console.warn('DrawerManager: Navbar no encontrado');
            return;
        }

        const navbarLeft = navbar.querySelector('.navbar-left');
        const navbarRight = navbar.querySelector('.navbar-right');

        // Botón drawer izquierdo
        if (this.drawers.left && navbarLeft) {
            if (!document.querySelector('.drawer-toggle-left')) {
                const btnLeft = document.createElement('button');
                btnLeft.className = 'drawer-toggle-btn drawer-toggle-left';
                btnLeft.innerHTML = '☰';
                btnLeft.title = 'Menú lateral izquierdo';
                btnLeft.setAttribute('aria-label', 'Menú lateral izquierdo');
                btnLeft.type = 'button';

                btnLeft.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    console.log('DrawerManager: Click en toggle izquierdo');
                    this.toggleDrawer('left');
                });

                navbarLeft.appendChild(btnLeft);
                console.log('DrawerManager: Botón toggle izquierdo creado');
            }
        }

        // Botón drawer derecho
        if (this.drawers.right && navbarRight) {
            if (!document.querySelector('.drawer-toggle-right')) {
                const btnRight = document.createElement('button');
                btnRight.className = 'drawer-toggle-btn drawer-toggle-right';
                btnRight.innerHTML = '⋮';
                btnRight.title = 'Menú lateral derecho';
                btnRight.setAttribute('aria-label', 'Menú lateral derecho');
                btnRight.type = 'button';

                btnRight.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    console.log('DrawerManager: Click en toggle derecho');
                    this.toggleDrawer('right');
                });

                // Insertar ANTES del theme-toggle
                const themeToggle = navbarRight.querySelector('.theme-toggle');
                if (themeToggle) {
                    navbarRight.insertBefore(btnRight, themeToggle);
                } else {
                    navbarRight.appendChild(btnRight);
                }

                console.log('DrawerManager: Botón toggle derecho creado');
            }
        }
    }

    attachEventListeners() {
        // Cerrar drawers con ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeAllDrawers();
            }
        });

        // Cerrar drawers al hacer click en un link (pero no en botones)
        document.addEventListener('click', (e) => {
            if (e.target.tagName === 'A' && !e.target.closest('.drawer-toggle-btn')) {
                // Delay para permitir navegación
                setTimeout(() => this.closeAllDrawers(), 100);
            }
        });

        // Cerrar drawers al redimensionar
        let resizeTimer;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => {
                if (window.innerWidth >= 1024) {
                    this.closeAllDrawers();
                    // Mostrar sidebars originales
                    const sidebarLeft = document.querySelector('.sidebar-left, .left-sidebar, [class*="sidebar-left"]');
                    const sidebarRight = document.querySelector('.sidebar-right, .right-sidebar, [class*="sidebar-right"]');
                    if (sidebarLeft) sidebarLeft.style.display = '';
                    if (sidebarRight) sidebarRight.style.display = '';
                }
            }, 250);
        });
    }

    toggleDrawer(side) {
        const drawer = this.drawers[side];

        if (!drawer) {
            console.warn(`DrawerManager: Drawer ${side} no existe`);
            return;
        }

        if (drawer.classList.contains('active')) {
            this.closeDrawer(side);
        } else {
            this.openDrawer(side);
        }
    }

    openDrawer(side) {
        const drawer = this.drawers[side];

        if (!drawer) return;

        console.log(`DrawerManager: Abriendo drawer ${side}`);

        drawer.classList.add('active');
        if (this.overlay) {
            this.overlay.classList.add('active');
        }
        document.body.style.overflow = 'hidden';
    }

    closeDrawer(side) {
        const drawer = this.drawers[side];

        if (!drawer) return;

        console.log(`DrawerManager: Cerrando drawer ${side}`);

        drawer.classList.remove('active');

        // Solo cerrar overlay si ambos drawers están cerrados
        const leftActive = this.drawers.left && this.drawers.left.classList.contains('active');
        const rightActive = this.drawers.right && this.drawers.right.classList.contains('active');

        if (!leftActive && !rightActive) {
            if (this.overlay) {
                this.overlay.classList.remove('active');
            }
            document.body.style.overflow = 'auto';
        }
    }

    closeAllDrawers() {
        console.log('DrawerManager: Cerrando todos los drawers');

        if (this.drawers.left) {
            this.drawers.left.classList.remove('active');
        }
        if (this.drawers.right) {
            this.drawers.right.classList.remove('active');
        }
        if (this.overlay) {
            this.overlay.classList.remove('active');
        }
        document.body.style.overflow = 'auto';
    }
}

// Inicializar DrawerManager
console.log('DrawerManager: Script cargado');
const drawerManager = new DrawerManager();
