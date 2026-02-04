/**
 * Gestión de conexiones PPPoE usando jQuery/Bootstrap
 * Sin dependencias de Alpine.js
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
  const logError = (...args) => {
    if (window.logger && typeof window.logger.error === 'function') {
      window.logger.error(...args);
      return;
    }
    if (console && typeof console.error === 'function') {
      console.error(...args);
    }
  };

  // Función para esperar a que jQuery esté disponible
  function waitForJQuery(callback) {
    logDebug('⏳ [pppoe-connections] Esperando jQuery...', {
      'jQuery': typeof jQuery,
      'window.$': typeof window.$,
      'window.jQuery': typeof window.jQuery
    });

    if (typeof jQuery !== 'undefined' && typeof window.$ !== 'undefined') {
      const $ = window.jQuery || window.$;
      logDebug('✅ [pppoe-connections] jQuery disponible, ejecutando callback');
      callback($);
    } else if (typeof window.jQuery !== 'undefined') {
      window.$ = window.jQuery;
      logDebug('✅ [pppoe-connections] jQuery disponible (window.jQuery), ejecutando callback');
      callback(window.jQuery);
    } else {
      logDebug('⏳ [pppoe-connections] jQuery no disponible aún, reintentando en 50ms...');
      setTimeout(() => waitForJQuery(callback), 50);
    }
  }

  logDebug('🔧 [pppoe-connections] Inicializando, esperando jQuery...');
  waitForJQuery(function ($) {
    logDebug('✅ [pppoe-connections] jQuery recibido en callback, configurando $(document).ready...');
    $(document).ready(function () {
    const routerId = $('#pppoe-connections-container').data('router-id');
    if (!routerId) return;

    let todasLasConexiones = [];
    let mostrarTodasLasConexiones = false;

    // Toggle mostrar/ocultar conexiones
    $(document).on('click', '#toggle-pppoe-connections', function () {
      mostrarTodasLasConexiones = !mostrarTodasLasConexiones;
      const $container = $('#pppoe-connections-list');

      if (mostrarTodasLasConexiones) {
        $container.slideDown();
        if (todasLasConexiones.length === 0) {
          cargarTodasLasConexiones();
        } else {
          renderConexiones(todasLasConexiones);
        }
        $(this).find('.fa-chevron-down').removeClass('fa-chevron-down').addClass('fa-chevron-up');
        $(this).find('span').text('Ocultar');
      } else {
        $container.slideUp();
        $(this).find('.fa-chevron-up').removeClass('fa-chevron-up').addClass('fa-chevron-down');
        $(this).find('span').text('Ver todas las conexiones PPPoE');
        $('#pppoe-search').val('');
      }
    });

    // Búsqueda
    $(document).on('input', '#pppoe-search', function () {
      const termino = $(this).val().toLowerCase().trim();
      $('#pppoe-search-append').toggle(termino.length > 0);
      filtrarConexiones(termino);
    });

    // Botón limpiar búsqueda
    $(document).on('click', '#pppoe-search-clear', function () {
      $('#pppoe-search').val('').trigger('input');
    });

    // Botón ver detalle
    $(document).on('click', '.open-pppoe-detail', function () {
      const routerId = $(this).data('router-id');
      const sessionId = $(this).data('session-id');

      logDebug('Botón ver detalle clickeado:', { routerId, sessionId });

      if (routerId && sessionId) {
        // Intentar múltiples formas de abrir el drawer
        if (window.openPPPoEDetail) {
          window.openPPPoEDetail({ routerId: routerId, sessionId: sessionId });
        } else if (window.pppoeDetailDrawer) {
          window.pppoeDetailDrawer.open({ routerId: routerId, sessionId: sessionId });
        } else {
          // Disparar evento como fallback
          window.dispatchEvent(
            new CustomEvent('open-pppoe-detail', {
              detail: { routerId: routerId, sessionId: sessionId },
            })
          );
        }
      }
    });

    function cargarTodasLasConexiones() {
      $('#pppoe-loading').show();
      $('#pppoe-error').hide();
      $('#pppoe-connections-table-body, #pppoe-connections-mobile').empty();

      $.ajax({
        url: `/red/routers/${routerId}/conexiones-pppoe`,
        method: 'GET',
        success: function (response) {
          $('#pppoe-loading').hide();
          if (response.success && response.conexiones) {
            todasLasConexiones = response.conexiones;
            renderConexiones(todasLasConexiones);
          } else {
            $('#pppoe-error')
              .text(response.message || 'Error al cargar conexiones')
              .show();
          }
        },
        error: function (xhr) {
          $('#pppoe-loading').hide();
          $('#pppoe-error').text('Error al conectar con el servidor').show();
        },
      });
    }

    function filtrarConexiones(termino) {
      if (!termino) {
        renderConexiones(todasLasConexiones);
        return;
      }

      const filtradas = todasLasConexiones.filter(function (conexion) {
        const usuario = (conexion.name || '').toLowerCase();
        const ip = (conexion.address || '').toLowerCase();
        const callerId = (conexion['caller-id'] || '').toLowerCase();
        return usuario.includes(termino) || ip.includes(termino) || callerId.includes(termino);
      });

      renderConexiones(filtradas);
      $('#pppoe-results-count').text(`${filtradas.length} de ${todasLasConexiones.length}`);
      $('#pppoe-results-count').toggle(filtradas.length !== todasLasConexiones.length);
    }

    function renderConexiones(conexiones) {
      const $tableBody = $('#pppoe-connections-table-body');
      const $mobileList = $('#pppoe-connections-mobile');

      $tableBody.empty();
      $mobileList.empty();

      if (conexiones.length === 0) {
        $tableBody.html(
          '<tr><td colspan="5" class="text-center py-4"><i class="fas fa-search fa-2x text-muted mb-2"></i><p class="small text-muted mb-0">No se encontraron conexiones</p></td></tr>'
        );
        return;
      }

      conexiones.forEach(function (conexion) {
        // Vista desktop (tabla)
        const row = `
                    <tr>
                        <td>
                            <div class="font-weight-bold">${conexion.name || '-'}</div>
                            <small class="text-muted font-monospace">${
                              conexion['caller-id'] || '-'
                            }</small>
                        </td>
                        <td>
                            ${
                              conexion.address && conexion.address !== '-'
                                ? `<div class="d-flex align-items-center">
                                     <span class="text-primary mr-2">${conexion.address}</span>
                                     <button
                                       type="button"
                                       class="btn btn-xs btn-primary abrir-onu-btn"
                                       data-ip="${(conexion.address || '').replace(/"/g, '&quot;')}"
                                       data-router-id="${routerId}"
                                       title="Abrir interfaz web de la ONU"
                                       style="padding: 0.1rem 0.4rem; font-size: 0.7em;"
                                     >
                                       <i class="fas fa-external-link-alt fa-xs"></i>
                                     </button>
                                   </div>`
                                : '-'
                            }
                        </td>
                        <td>${conexion.uptime || '-'}</td>
                        <td class="text-right">
                            <button
                                type="button"
                                class="btn btn-sm btn-default open-pppoe-detail"
                                data-router-id="${routerId}"
                                data-session-id="${conexion['.id']}"
                                aria-label="Ver detalle"
                            >
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                `;
        $tableBody.append(row);

        // Vista móvil (cards)
        const card = `
                    <div class="card mb-2">
                        <div class="card-body p-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="flex-grow-1" style="min-width: 0;">
                                    <div class="font-weight-bold small text-truncate">${
                                      conexion.name || '-'
                                    }</div>
                                    <div class="small font-monospace text-muted">${
                                      conexion['caller-id'] || '-'
                                    }</div>
                                </div>
                                <button
                                    type="button"
                                    class="btn btn-sm btn-default ml-2 open-pppoe-detail"
                                    data-router-id="${routerId}"
                                    data-session-id="${conexion['.id']}"
                                    aria-label="Ver detalle"
                                >
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
        $mobileList.append(card);
      });
    }

    // Agregar event delegation para botones de abrir ONU
    $(document).off('click', '.abrir-onu-btn');

    $(document).on('click', '.abrir-onu-btn', function (e) {
      e.preventDefault();
      e.stopPropagation();
      const $btn = $(this);
      const routerId = $btn.data('router-id');
      const ip = $btn.data('ip');

      logDebug('🖱️ Botón Abrir ONU clickeado (desde tabla):', { routerId, ip });

      if (!routerId || !ip || ip === '-') {
        logError('❌ Parámetros inválidos para abrir interfaz ONU', { routerId, ip });
        return;
      }

      if (typeof window.abrirInterfazOnu === 'function') {
        logDebug('✅ Llamando a abrirInterfazOnu con:', { routerId, ip });
        window.abrirInterfazOnu(routerId, ip);
      } else {
        logError('❌ abrirInterfazOnu no está disponible');
        window.showAlert(
          'Error: La función para abrir la interfaz ONU no está disponible. Por favor recarga la página.',
          'error'
        );
      }
    });

      logDebug('✅ [pppoe-connections] Event handler para .abrir-onu-btn registrado');
    });
  }); // Fin de waitForJQuery
})();
