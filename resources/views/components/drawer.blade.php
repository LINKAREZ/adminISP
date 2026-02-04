<!-- Overlay para Drawer -->
<div
    id="drawer-overlay"
    class="overlay"
    style="z-index: 1040; display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5);"
></div>

<!-- Drawer -->
<div
    id="drawer"
    class="drawer"
    style="display: none; position: fixed; bottom: 0; left: 0; right: 0; background: white; z-index: 1050; max-height: 90vh; border-top-left-radius: 10px; border-top-right-radius: 10px; box-shadow: 0 -4px 6px rgba(0,0,0,0.1);"
>
    <!-- Encabezado Drawer -->
    <div class="drawer-header d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
        <div class="flex-grow-1" style="min-width: 0;">
            <div class="small text-muted mb-1" id="drawer-subtitle"></div>
            <div class="font-weight-bold text-truncate" id="drawer-title"></div>
        </div>
        <button
            type="button"
            id="drawer-close-btn"
            class="btn btn-default btn-sm ml-2"
            aria-label="Cerrar"
        >
            <i class="fas fa-times"></i>
        </button>
    </div>

    <!-- Cuerpo Drawer (Scrollable) -->
    <div id="drawer-content" class="drawer-body overflow-y-auto px-3 py-3" style="max-height: calc(90vh - 120px);">
        @yield('drawer-content')
    </div>

    <!-- Pie Drawer (Botones) -->
    <div id="drawer-footer" class="drawer-footer d-flex align-items-center px-3 py-2 border-top" style="display: none;">
        <button
            type="button"
            id="drawer-cancel-btn"
            class="btn btn-secondary flex-fill flex-md-none"
        >
            <i class="fas fa-times mr-1"></i> Cancelar
        </button>
        <button
            type="button"
            id="drawer-save-btn"
            class="btn btn-primary flex-fill flex-md-none"
        >
            <i class="fas fa-save mr-1"></i> Guardar
        </button>
    </div>

    <div id="drawer-footer-view" class="drawer-footer d-flex align-items-center justify-content-end px-3 py-2 border-top" style="display: none;">
        <button
            type="button"
            id="drawer-close-view-btn"
            class="btn btn-secondary"
        >
            <i class="fas fa-times mr-1"></i> Cerrar
        </button>
    </div>
</div>

<script>
// Esperar a que jQuery esté disponible antes de inicializar el drawer
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
const logError = (...args) => {
    if (window.logger && typeof window.logger.error === 'function') {
        window.logger.error(...args);
        return;
    }
    if (console && typeof console.error === 'function') {
        console.error(...args);
    }
};

logDebug('🔵 Iniciando script del drawer...');
(function() {
    'use strict';

    function initDrawer() {
        logDebug('🔵 initDrawer llamado');
        if (typeof jQuery === 'undefined') {
            logWarn('Drawer: jQuery no disponible, reintentando...');
            setTimeout(initDrawer, 100);
            return;
        }

        logDebug('🔵 jQuery disponible, definiendo DrawerManager...');
        var $ = jQuery;

    window.DrawerManager = {
        isOpen: false,
        mode: 'create',
        title: '',
        subtitle: '',

        init: function() {
            const self = this;

            // Cerrar drawer
            $('#drawer-close-btn, #drawer-close-view-btn, #drawer-overlay').on('click', function() {
                self.close();
            });

            // Cancelar
            $('#drawer-cancel-btn').on('click', function() {
                self.close();
            });

            // Guardar
            $('#drawer-save-btn').on('click', function() {
                const form = document.querySelector('#drawer-content form');
                if (form) {
                    form.requestSubmit();
                } else {
                    self.close();
                    if (window.ToastManager) {
                        window.ToastManager.error('No se encontró el formulario');
                    }
                }
            });

            // Cerrar con ESC
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && self.isOpen) {
                    self.close();
                }
            });

            // Verificar paso actual para mostrar botones (para formularios multi-paso)
            this.checkStep();
            setInterval(() => this.checkStep(), 100);
        },

        open: function(mode, title, subtitle) {
            this.mode = mode || 'create';
            this.title = title || '';
            this.subtitle = subtitle || '';

            $('#drawer-title').text(this.title);
            $('#drawer-subtitle').text(this.subtitle);

            // Mostrar/ocultar footers según el modo
            if (this.mode === 'view') {
                $('#drawer-footer').hide();
                $('#drawer-footer-view').show();
            } else {
                $('#drawer-footer').show();
                $('#drawer-footer-view').hide();
            }

            // Mostrar drawer y overlay
            $('#drawer-overlay').fadeIn(300);
            $('#drawer').slideDown(300);

            // Prevenir scroll del body
            $('body').addClass('overflow-hidden');

            this.isOpen = true;
        },

        close: function() {
            $('#drawer-overlay').fadeOut(300);
            $('#drawer').slideUp(300, function() {
                $('#drawer-content').empty();
            });

            $('body').removeClass('overflow-hidden');
            this.isOpen = false;
        },

        checkStep: function() {
            if (!this.isOpen) return;

            const drawerContent = document.getElementById('drawer-content');
            if (!drawerContent) return;

            // Buscar indicador de paso actual (puede ser un input hidden, data attribute, etc.)
            const pasoInput = drawerContent.querySelector('input[name="paso_actual"], [data-paso-actual]');
            let pasoActual = null;

            if (pasoInput) {
                pasoActual = pasoInput.value || pasoInput.getAttribute('data-paso-actual');
            }

            // Si hay un paso actual y es 3, mostrar botones
            const mostrarBotones = pasoActual === '3' || pasoActual === 3;

            if (this.mode !== 'view') {
                if (mostrarBotones) {
                    $('#drawer-footer').show();
                } else {
                    // Si no hay paso definido, mostrar botones por defecto
                    if (!pasoInput) {
                        $('#drawer-footer').show();
                    } else {
                        $('#drawer-footer').hide();
                    }
                }
            }
        },

        setContent: function(html) {
            $('#drawer-content').html(html);
            this.checkStep();
        }
    };

        // Inicializar cuando el DOM esté listo
        $(document).ready(function() {
            window.DrawerManager.init();
        });

        // Función global para abrir drawer (compatibilidad con código existente)
        window.openDrawer = function(mode, title, subtitle) {
            if (window.DrawerManager && window.DrawerManager.open) {
                window.DrawerManager.open(mode, title, subtitle);
            } else {
                logError('DrawerManager no está disponible');
            }
        };

        window.closeDrawer = function() {
            if (window.DrawerManager && window.DrawerManager.close) {
                window.DrawerManager.close();
            }
        };

        logDebug('✅ Drawer inicializado correctamente');
        logDebug('✅ window.openDrawer definido:', typeof window.openDrawer);
        logDebug('✅ window.DrawerManager definido:', typeof window.DrawerManager);
    }

    // Iniciar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            logDebug('📦 DOM cargado, inicializando drawer...');
            initDrawer();
        });
    } else {
        logDebug('📦 DOM ya está listo, inicializando drawer...');
        initDrawer();
    }
})();
</script>
