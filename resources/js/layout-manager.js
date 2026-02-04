/**
 * Layout Manager - Gestión centralizada del layout (sidebar, navbar, main)
 * Estandariza y simplifica la lógica de actualización del layout
 */

const logDebug = (...args) => {
  if (window.logger && typeof window.logger.debug === 'function') {
    window.logger.debug(...args);
    return;
  }
  if (console && typeof console.debug === 'function') {
    console.debug(...args);
  }
};
const logWarn = (...args) => {
  if (window.logger && typeof window.logger.warn === 'function') {
    window.logger.warn(...args);
    return;
  }
  if (console && typeof console.warn === 'function') {
    console.warn(...args);
  }
};

const LayoutManager = {
  // Breakpoint para desktop (1024px - estándar Bootstrap/AdminLTE)
  DESKTOP_BREAKPOINT: 1024,

  // Flag para evitar inicialización múltiple
  initialized: false,

  // Cache para evitar actualizaciones innecesarias
  _lastLayoutCache: null,

  // Referencias a elementos del DOM
  elements: {
    header: null,
    main: null,
    sidebar: null,
  },

  /**
   * Inicializar el LayoutManager
   * Optimizado para evitar múltiples inicializaciones y timeouts innecesarios
   */
  init() {
    // Evitar inicialización múltiple
    if (this.initialized) {
      return;
    }

    // Obtener referencias a elementos del DOM
    this.elements.header = document.querySelector('header[role="banner"]');
    this.elements.main = document.querySelector('main');
    this.elements.sidebar = document.getElementById('sidebar');

    // Verificar que todos los elementos existan
    if (!this.elements.header || !this.elements.main || !this.elements.sidebar) {
      logWarn('⚠️ LayoutManager: Elementos del layout no encontrados');
      return;
    }

    // Escuchar cambios en el tamaño de ventana (debounced)
    // Usar requestAnimationFrame para mejor rendimiento
    let resizeTimer;
    const handleResize = () => {
      cancelAnimationFrame(resizeTimer);
      resizeTimer = requestAnimationFrame(() => {
        this.updateLayout();
      });
    };
    window.addEventListener('resize', handleResize, { passive: true });

    // Escuchar cambios en el estado del sidebar
    // Usar MutationObserver para detectar cambios en CSS variables y clases
    const observer = new MutationObserver(() => {
      this.updateLayout();
    });

    // Observar cambios en el elemento sidebar
    observer.observe(this.elements.sidebar, {
      attributes: true,
      attributeFilter: ['class', 'style', 'data-sidebar-width'],
    });

    // Guardar referencia al observer para cleanup si es necesario
    this._observer = observer;

    // Inicializar layout inmediatamente
    this.updateLayout();

    this.initialized = true;
    logDebug('✅ LayoutManager inicializado');
  },

  /**
   * Obtener el estado actual del sidebar
   */
  getSidebarState() {
    const appState = window.appStateInstance;
    return appState ? appState.sidebarOpen : false;
  },

  /**
   * Obtener el ancho del sidebar
   */
  getSidebarWidth() {
    if (!this.elements.sidebar) return 0;
    // Primero intentar obtener del atributo data-sidebar-width
    let width = this.elements.sidebar.getAttribute('data-sidebar-width');
    if (width) {
      return parseInt(width);
    }
    // Si no existe, intentar obtener del ancho real del elemento
    if (this.elements.sidebar.offsetWidth > 0) {
      return this.elements.sidebar.offsetWidth;
    }
    return 0;
  },

  /**
   * Verificar si estamos en desktop
   */
  isDesktop() {
    return window.innerWidth >= this.DESKTOP_BREAKPOINT;
  },

  /**
   * Forzar ancho completo para header y main
   */
  forceFullWidth() {
    const { header, main } = this.elements;

    // Aplicar estilos de forma síncrona para evitar layout shift
    if (header) {
      header.style.left = '0';
      header.style.right = '0';
      header.style.width = '100%';
      header.style.maxWidth = '100%';
      header.style.boxSizing = 'border-box';
      header.style.marginLeft = '0';
    }

    if (main) {
      main.style.marginLeft = '0';
      main.style.width = '100%';
      main.style.maxWidth = '100%';
      main.style.boxSizing = 'border-box';
    }
  },

  /**
   * Actualizar layout según estado del sidebar
   */
  updateLayout(isOpen = null) {
    // Verificar que esté inicializado
    if (!this.initialized) {
      return;
    }

    // Verificar que los elementos sigan existiendo
    if (!this.elements.header || !this.elements.main || !this.elements.sidebar) {
      // Re-obtener elementos si se perdieron
      this.elements.header = document.querySelector('header[role="banner"]');
      this.elements.main = document.querySelector('main');
      this.elements.sidebar = document.getElementById('sidebar');
      if (!this.elements.header || !this.elements.main || !this.elements.sidebar) {
        return;
      }
    }

    // Si no se proporciona el estado, obtenerlo
    if (isOpen === null) {
      isOpen = this.getSidebarState();
    }

    // Cache del estado y ancho para evitar actualizaciones innecesarias
    const currentCacheKey = `${isOpen}-${this.isDesktop()}-${this.getSidebarWidth()}`;
    if (this._lastLayoutCache === currentCacheKey) {
      return; // No ha cambiado nada, no actualizar
    }
    this._lastLayoutCache = currentCacheKey;

    const sidebarWidth = this.getSidebarWidth();
    const isDesktop = this.isDesktop();

    if (isDesktop) {
      // Desktop: sidebar empuja el contenido
      if (isOpen && sidebarWidth > 0) {
        this.applySidebarOpenLayout(sidebarWidth);
      } else {
        this.forceFullWidth();
      }
    } else {
      // Móvil: siempre ancho completo (sidebar se superpone)
      this.forceFullWidth();
    }
  },

  /**
   * Aplicar layout cuando sidebar está abierto (desktop)
   */
  applySidebarOpenLayout(sidebarWidth) {
    const { header, main } = this.elements;

    if (!sidebarWidth || sidebarWidth <= 0) {
      // Si no hay ancho válido, forzar ancho completo
      this.forceFullWidth();
      return;
    }

    // Aplicar estilos de forma síncrona para evitar layout shift
    if (header) {
      header.style.left = `${sidebarWidth}px`;
      header.style.width = `calc(100% - ${sidebarWidth}px)`;
      header.style.right = '0';
      header.style.maxWidth = 'none';
      header.style.boxSizing = 'border-box';
      header.style.marginLeft = '0';
    }

    if (main) {
      main.style.marginLeft = `${sidebarWidth}px`;
      main.style.width = `calc(100% - ${sidebarWidth}px)`;
      main.style.maxWidth = 'none';
      main.style.boxSizing = 'border-box';
    }
  },
};

// Exponer globalmente primero
window.LayoutManager = LayoutManager;

// La inicialización se maneja cuando el DOM está listo
