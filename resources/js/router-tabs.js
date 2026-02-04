/**
 * Gestión de pestañas del router usando jQuery/Bootstrap
 * Sin dependencias de Alpine.js
 * NOTA: Solo aplica a pestañas con ID que empiecen con "router-tabs"
 */

(function () {
  'use strict';

  const logDebug = (...args) => {
    if (window.logger && typeof window.logger.debug === 'function') {
      window.logger.debug(...args);
      return;
    }
    if (console && typeof console.debug === 'function') {
      console.debug(...args);
    }
  };

  // Función para esperar a que jQuery esté disponible
  function waitForJQuery(callback) {
    logDebug('⏳ [router-tabs] Esperando jQuery...', {
      'jQuery': typeof jQuery,
      'window.$': typeof window.$,
      'window.jQuery': typeof window.jQuery
    });

    if (typeof jQuery !== 'undefined' && typeof window.$ !== 'undefined') {
      const $ = window.jQuery || window.$;
      logDebug('✅ [router-tabs] jQuery disponible, ejecutando callback');
      callback($);
    } else if (typeof window.jQuery !== 'undefined') {
      window.$ = window.jQuery;
      logDebug('✅ [router-tabs] jQuery disponible (window.jQuery), ejecutando callback');
      callback(window.jQuery);
    } else {
      logDebug('⏳ [router-tabs] jQuery no disponible aún, reintentando en 50ms...');
      setTimeout(() => waitForJQuery(callback), 50);
    }
  }

  logDebug('🔧 [router-tabs] Inicializando, esperando jQuery...');
  waitForJQuery(function ($) {
    logDebug('✅ [router-tabs] jQuery recibido en callback, configurando $(document).ready...');
    $(document).ready(function () {
    // Inicializar pestañas de Bootstrap SOLO para tabs del router
    // Usamos un selector más específico para evitar conflictos con otras pestañas (ej: cliente)
    $('#router-tabs a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
      const target = $(e.target).attr('href');
      const tabName = $(e.target).data('tab') || $(e.target).attr('href').replace('#tab-', '');

      // Ocultar solo los contenidos de pestañas del router, no todos
      const $tabContainer = $(e.target).closest('.card').find('.tab-content');
      $tabContainer.find('.tab-pane').hide();

      // Mostrar el contenido de la pestaña activa
      $(target).show();

      // Actualizar URL hash sin recargar la página
      if (tabName) {
        window.location.hash = tabName;
      }
    });

    // Activar pestaña desde hash de URL (solo para router-tabs)
    const hash = window.location.hash.replace('#', '');
    if (hash && hash.startsWith('tab-')) {
      const $tabLink = $(
        `#router-tabs .nav-link[data-tab="${hash}"], #router-tabs .nav-link[href="#${hash}"]`
      );
      if ($tabLink.length) {
        $tabLink.tab('show');
      }
    }

      logDebug('✅ Tabs del router inicializadas (jQuery/Bootstrap)');
    });
  }); // Fin de waitForJQuery
})();
