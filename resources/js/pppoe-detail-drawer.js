/**
 * Drawer de detalle de conexión PPPoE usando jQuery/Bootstrap
 * Sin dependencias de Alpine.js
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
const logError = (...args) => {
  if (window.logger && typeof window.logger.error === 'function') {
    window.logger.error(...args);
    return;
  }
  if (console && typeof console.error === 'function') {
    console.error(...args);
  }
};

/**
 * Función global para abrir la interfaz web de una ONU
 * Crea una regla NAT en el MikroTik y abre la URL en una nueva ventana
 * Se define al inicio para estar disponible antes de cualquier uso
 */
window.abrirInterfazOnu = function (routerId, ip) {
  if (!routerId || !ip || ip === '-') {
    logError('Parámetros inválidos para abrir interfaz ONU', { routerId, ip });
    return;
  }

  // Asegurar que jQuery esté disponible
  const $ = window.jQuery || window.$;
  if (typeof $ === 'undefined') {
    logError('jQuery no está disponible');
    window.showAlert('Error: jQuery no está disponible. Por favor recarga la página.', 'error');
    return;
  }

  // Mostrar indicador de carga
  const loadingHtml = `
    <div class="modal fade" id="onu-loading-modal" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-body text-center py-4">
            <i class="fas fa-spinner fa-spin fa-2x text-primary mb-3"></i>
            <p class="mb-0">Creando regla NAT y abriendo interfaz de la ONU...</p>
            <small class="text-muted">IP: ${ip}</small>
          </div>
        </div>
      </div>
    </div>
  `;

  // Agregar modal si no existe
  if ($('#onu-loading-modal').length === 0) {
    $('body').append(loadingHtml);
  }
  $('#onu-loading-modal').modal('show');

  // Obtener token CSRF
  const csrfToken =
    $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val() || '';

  // Función auxiliar para cerrar el modal de forma segura
  const closeLoadingModal = function () {
    try {
      const $modal = $('#onu-loading-modal');
      if ($modal.length) {
        // Cerrar el modal usando Bootstrap
        $modal.modal('hide');

        // Eliminar backdrop y clases del body inmediatamente
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
        $('body').css({ 'padding-right': '', overflow: '' });

        // Forzar eliminación del modal del DOM
        $modal.remove();

        // Verificación adicional: si aún existe, eliminarlo directamente
        setTimeout(function () {
          $('#onu-loading-modal').remove();
          $('.modal-backdrop').remove();
          $('body').removeClass('modal-open');
          $('body').css({ 'padding-right': '', overflow: '' });
        }, 100);
      } else {
        // Si no existe el modal, asegurar que no haya backdrop ni clases
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
        $('body').css({ 'padding-right': '', overflow: '' });
      }
    } catch (e) {
      logError('Error al cerrar modal:', e);
      // Forzar limpieza en caso de error
      $('#onu-loading-modal').remove();
      $('.modal-backdrop').remove();
      $('body').removeClass('modal-open');
      $('body').css({ 'padding-right': '', overflow: '' });
    }
  };

  // Llamar al endpoint para crear la regla NAT
  $.ajax({
    url: `/red/routers/${routerId}/crear-nat-onu`,
    method: 'POST',
    timeout: 30000, // 30 segundos de timeout
    headers: {
      'X-CSRF-TOKEN': csrfToken,
    },
    data: {
      ip: ip,
      _token: csrfToken,
    },
    success: function (response) {
      // Cerrar el modal inmediatamente
      closeLoadingModal();

      // Procesar la respuesta
      if (response && response.success && response.url) {
        // Guardar información de la regla para poder eliminarla después
        const ruleInfo = {
          routerId: routerId,
          ruleId: response.rule_id || null,
          comment: response.comment || null,
          port: response.port || null,
          ip: ip,
        };

        // Función para eliminar la regla NAT
        const eliminarReglaNat = function () {
          const csrfToken =
            $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val() || '';

          $.ajax({
            url: `/red/routers/${ruleInfo.routerId}/eliminar-nat-onu`,
            method: 'POST',
            timeout: 10000,
            headers: {
              'X-CSRF-TOKEN': csrfToken,
            },
            data: {
              rule_id: ruleInfo.ruleId,
              comment: ruleInfo.comment,
              port: ruleInfo.port,
              _token: csrfToken,
            },
            success: function (deleteResponse) {
              if (deleteResponse && deleteResponse.success) {
                logDebug('Regla NAT eliminada correctamente:', ruleInfo);
              } else {
                logWarn('No se pudo eliminar la regla NAT:', deleteResponse);
              }
            },
            error: function (xhr) {
              logError('Error al eliminar regla NAT:', {
                status: xhr.status,
                response: xhr.responseJSON || xhr.responseText,
                ruleInfo: ruleInfo,
              });
            },
          });
        };

        // Abrir la URL en una nueva ventana
        const newWindow = window.open(
          response.url,
          '_blank',
          'width=1200,height=800,scrollbars=yes,resizable=yes'
        );

        // Si el popup fue bloqueado, mostrar mensaje y no programar eliminación
        if (!newWindow || newWindow.closed || typeof newWindow.closed === 'undefined') {
          if (typeof toastr !== 'undefined') {
            toastr.warning(
              'Por favor, permite ventanas emergentes y abre manualmente: ' +
                response.url +
                '. La regla NAT se eliminará automáticamente después de 5 minutos.',
              'Ventana bloqueada',
              { timeOut: 7000 }
            );
          } else {
            window.showAlert('Por favor, permite ventanas emergentes. URL: ' + response.url, 'warning');
          }
          // Programar eliminación después de 5 minutos si la ventana fue bloqueada
          setTimeout(eliminarReglaNat, 5 * 60 * 1000); // 5 minutos
        } else {
          // Monitorear cuando se cierre la ventana
          const checkWindowClosed = setInterval(function () {
            if (newWindow.closed) {
              clearInterval(checkWindowClosed);
              eliminarReglaNat();

              if (typeof toastr !== 'undefined') {
                toastr.info(
                  'Ventana cerrada. Regla NAT eliminada automáticamente.',
                  'Interfaz ONU',
                  {
                    timeOut: 3000,
                  }
                );
              }
            }
          }, 1000); // Verificar cada segundo

          // Timeout de seguridad: eliminar la regla después de 5 minutos aunque la ventana siga abierta
          setTimeout(function () {
            clearInterval(checkWindowClosed);
            if (!newWindow.closed) {
              // Intentar cerrar la ventana si aún está abierta
              try {
                newWindow.close();
              } catch (e) {
                // Ignorar errores al cerrar (puede fallar si el usuario ya la cerró)
              }
            }
            eliminarReglaNat();

            if (typeof toastr !== 'undefined') {
              toastr.info(
                'Tiempo máximo alcanzado. Regla NAT eliminada automáticamente.',
                'Interfaz ONU',
                { timeOut: 3000 }
              );
            }
          }, 5 * 60 * 1000); // 5 minutos
        }

        // Mostrar notificación de éxito
        if (typeof toastr !== 'undefined') {
          toastr.success(
            response.exists
              ? `Regla NAT ya existía. Abriendo interfaz de la ONU en puerto ${response.port}. Se eliminará automáticamente al cerrar.`
              : `Regla NAT creada. Abriendo interfaz de la ONU en puerto ${response.port}. Se eliminará automáticamente al cerrar.`,
            'Interfaz ONU',
            { timeOut: 4000 }
          );
        } else {
          window.showAlert(`Interfaz ONU abierta en puerto ${response.port}`, 'success');
        }
      } else {
        // Respuesta sin éxito o sin URL
        const errorMsg = response?.message || 'Error desconocido al crear regla NAT';
        if (typeof toastr !== 'undefined') {
          toastr.error(errorMsg, 'Error', { timeOut: 5000 });
        } else {
          window.showAlert('Error: ' + errorMsg, 'error');
        }
        logError('Respuesta inválida del servidor:', response);
      }
    },
    error: function (xhr, textStatus, errorThrown) {
      closeLoadingModal();

      let errorMessage = 'Error al crear regla NAT';

      if (textStatus === 'timeout') {
        errorMessage =
          'La petición tardó demasiado. Por favor verifica la conexión al router y vuelve a intentar.';
      } else if (xhr.responseJSON && xhr.responseJSON.message) {
        errorMessage = xhr.responseJSON.message;
      } else if (xhr.status === 0) {
        errorMessage = 'No se pudo conectar al servidor. Verifica tu conexión.';
      } else if (xhr.status >= 500) {
        errorMessage =
          'Error del servidor. Por favor intenta nuevamente o contacta al administrador.';
      } else if (xhr.status === 404) {
        errorMessage = 'El endpoint no fue encontrado. Por favor recarga la página.';
      }

      if (typeof toastr !== 'undefined') {
        toastr.error(errorMessage, 'Error', { timeOut: 7000 });
      } else {
        window.showAlert('Error: ' + errorMessage, 'error');
      }

      logError('Error al crear NAT para ONU:', {
        status: xhr.status,
        statusText: xhr.statusText,
        textStatus: textStatus,
        errorThrown: errorThrown,
        response: xhr.responseJSON || xhr.responseText,
      });
    },
  });
};

(function ($) {
  'use strict';

  if (typeof $ === 'undefined' || $ === null) {
    logError('jQuery no está disponible para pppoe-detail-drawer.js');
    return;
  }

  let drawerInstance = null;

  function PPPoeDetailDrawer() {
    this.isOpen = false;
    this.routerId = null;
    this.sessionId = null;
    this.data = null;
    this.lastData = null;
    this.lastUpdate = null;
    this.updateInterval = null;
    this.activeAjaxRequest = null; // Referencia a la petición AJAX activa
    this.isClosing = false; // Bandera para prevenir nuevas peticiones al cerrar
    this.consecutiveErrors = 0; // Contador de errores consecutivos
    this.maxConsecutiveErrors = 5; // Máximo de errores antes de pausar
    this.charts = {
      traffic: null,
      cumulative: null,
    };
    // Datos históricos para el gráfico de tráfico en tiempo real
    this.trafficHistory = {
      labels: [],
      txData: [],
      rxData: [],
      maxPoints: 60, // Mantener últimos 60 puntos (1 minuto a 1 segundo por punto)
    };
  }

  PPPoeDetailDrawer.prototype.init = function () {
    const self = this;

    // Crear el HTML del drawer si no existe
    if ($('#pppoe-detail-drawer').length === 0) {
      this.createDrawer();
    }

    // Escuchar eventos para abrir el drawer (compatible con CustomEvent)
    $(document).on('open-pppoe-detail', function (e, detail) {
      const eventDetail = detail || (e.originalEvent && e.originalEvent.detail);
      if (eventDetail) {
        self.routerId = eventDetail.routerId;
        self.sessionId = eventDetail.sessionId;
        self.open();
      }
    });

    // También escuchar eventos nativos de JavaScript
    window.addEventListener('open-pppoe-detail', function (e) {
      if (e.detail) {
        self.routerId = e.detail.routerId;
        self.sessionId = e.detail.sessionId;
        self.open();
      }
    });

    // Método público para abrir desde otros scripts
    window.openPPPoEDetail = function (detail) {
      self.routerId = detail.routerId;
      self.sessionId = detail.sessionId;
      self.open();
    };

    logDebug('✅ Drawer PPPoE inicializado (jQuery/Bootstrap)');
  };

  PPPoeDetailDrawer.prototype.createDrawer = function () {
    const drawerHTML = `
            <!-- Overlay -->
            <div id="pppoe-drawer-overlay" class="modal-backdrop fade" style="display: none; z-index: 1040;"></div>

            <!-- Drawer -->
            <div id="pppoe-detail-drawer" class="pppoe-drawer" style="display: none; z-index: 1050;">
                <div class="pppoe-drawer-content">
                    <!-- Header -->
                    <div class="pppoe-drawer-header">
                        <h5 class="mb-0"><i class="fas fa-info-circle mr-2"></i>Información de Conexión PPPoE</h5>
                        <button type="button" class="close" id="pppoe-drawer-close">
                            <span>&times;</span>
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="pppoe-drawer-body" id="pppoe-drawer-body">
                        <div class="text-center py-5">
                            <i class="fas fa-spinner fa-spin fa-2x text-muted mb-2"></i>
                            <p class="small text-muted">Cargando detalles...</p>
                        </div>
                    </div>
                </div>
            </div>
        `;

    $('body').append(drawerHTML);

    // Eventos
    const self = this;

    // Solo cerrar si se hace clic directamente en el overlay (no si se propaga desde el drawer)
    $('#pppoe-drawer-overlay').on('click', function (e) {
      // Verificar que el clic fue directamente en el overlay, no en un elemento hijo
      if (e.target === this && !$(e.target).closest('#pppoe-detail-drawer').length) {
        self.close();
      }
    });

    $('#pppoe-drawer-close').on('click', function (e) {
      e.preventDefault();
      e.stopPropagation();
      self.close();
    });

    // Prevenir que los clics dentro del drawer cierren el overlay
    $(document).on('click', '#pppoe-detail-drawer', function (e) {
      e.stopPropagation();
    });

    // Cerrar con Escape
    $(document).on('keydown', function (e) {
      if (e.key === 'Escape' && self.isOpen) {
        self.close();
      }
    });
  };

  PPPoeDetailDrawer.prototype.open = function (detail) {
    // Si se pasa detail como parámetro, usarlo
    if (detail) {
      this.routerId = detail.routerId;
      this.sessionId = detail.sessionId;
    }

    if (!this.routerId || !this.sessionId) {
      logError('Router ID o Session ID no proporcionados', {
        routerId: this.routerId,
        sessionId: this.sessionId,
      });
      return;
    }

    // Resetear datos anteriores
    this.lastData = null;
    this.lastUpdate = null;
    this.consecutiveErrors = 0; // Resetear contador de errores

    // Asegurarse de que no haya actualizaciones pendientes
    if (this.updateInterval) {
      clearInterval(this.updateInterval);
      this.updateInterval = null;
    }
    if (this.activeAjaxRequest) {
      this.activeAjaxRequest.abort();
      this.activeAjaxRequest = null;
    }

    // Resetear estados ANTES de abrir
    this.isClosing = false;
    this.isOpen = true;

    logDebug('🔓 Abriendo drawer:', {
      isOpen: this.isOpen,
      isClosing: this.isClosing,
      routerId: this.routerId,
      sessionId: this.sessionId,
    });
    $('#pppoe-drawer-overlay').fadeIn(200);
    $('#pppoe-detail-drawer').fadeIn(200).css('display', 'block');
    $('body').css('overflow', 'hidden');

    this.loadDetails();
  };

  PPPoeDetailDrawer.prototype.close = function () {
    if (!this.isOpen && !this.isClosing) return; // Ya está cerrado

    logDebug('🔒 Cerrando drawer y deteniendo todas las actualizaciones...');

    // Marcar que se está cerrando para prevenir nuevas peticiones (PRIMERO)
    this.isClosing = true;
    this.isOpen = false;

    // Cancelar TODAS las peticiones AJAX activas
    if (this.activeAjaxRequest) {
      logDebug('🚫 Cancelando petición AJAX activa...');
      try {
        this.activeAjaxRequest.abort();
      } catch (e) {
        logWarn('Error al cancelar petición AJAX:', e);
      }
      this.activeAjaxRequest = null;
    }

    // Limpiar intervalos INMEDIATAMENTE (antes de cualquier animación)
    // Usar múltiples verificaciones para asegurar que se detenga
    if (this.updateInterval) {
      logDebug('⏹️ Deteniendo intervalo de actualización...', this.updateInterval);
      try {
        clearInterval(this.updateInterval);
      } catch (e) {
        logWarn('Error al limpiar intervalo:', e);
      }
      this.updateInterval = null;
    }

    // Verificación adicional: limpiar cualquier intervalo que pueda quedar
    // Esto es una medida de seguridad adicional
    const intervalId = this.updateInterval;
    if (intervalId) {
      clearInterval(intervalId);
      this.updateInterval = null;
    }

    // Ocultar drawer
    $('#pppoe-drawer-overlay').fadeOut(200, function () {
      $(this).hide();
    });
    $('#pppoe-detail-drawer').fadeOut(200, function () {
      $(this).hide();
    });
    $('body').css('overflow', '');

    // Destruir gráficos
    if (this.charts.traffic) {
      this.charts.traffic.destroy();
      this.charts.traffic = null;
    }
    if (this.charts.cumulative) {
      this.charts.cumulative.destroy();
      this.charts.cumulative = null;
    }

    // Resetear historial de tráfico
    this.trafficHistory = {
      labels: [],
      txData: [],
      rxData: [],
      maxPoints: 60,
    };

    // Limpiar datos
    this.routerId = null;
    this.sessionId = null;
    this.data = null;
    this.lastData = null;
    this.lastUpdate = null;

    // Resetear bandera después de un breve delay
    const self = this;
    setTimeout(function () {
      self.isClosing = false;
    }, 500);
    this.lastData = null;
    this.lastUpdate = null;
  };

  PPPoeDetailDrawer.prototype.loadDetails = function () {
    const self = this;
    const url = `/red/routers/${this.routerId}/conexiones-pppoe/${encodeURIComponent(
      this.sessionId
    )}`;

    $('#pppoe-drawer-body').html(`
            <div class="text-center py-5">
                <i class="fas fa-spinner fa-spin fa-2x text-muted mb-2"></i>
                <p class="small text-muted">Cargando detalles...</p>
            </div>
        `);

    $.ajax({
      url: url,
      method: 'GET',
      success: function (response) {
        logDebug('📥 Respuesta completa del servidor:', response);
        logDebug('📊 Datos de conexión recibidos:', response.conexion);

        if (response.success && response.conexion) {
          const conexion = response.conexion;

          // Log de campos de tráfico disponibles - ver TODOS los campos
          logDebug('🔍 OBJETO COMPLETO DE CONEXIÓN:', conexion);
          logDebug('🔍 TODAS LAS CLAVES:', Object.keys(conexion));
          logDebug('🔍 Información SNMP:', conexion.snmp_info);
          logDebug('🔍 snmp_info existe?', 'snmp_info' in conexion);
          logDebug('🔍 snmp_info valor:', conexion.snmp_info);
          logDebug('🔍 snmp_info tipo:', typeof conexion.snmp_info);
          logDebug('🔍 Campos de tráfico específicos:', {
            'tx-byte': conexion['tx-byte'],
            'tx-bytes': conexion['tx-bytes'],
            'rx-byte': conexion['rx-byte'],
            'rx-bytes': conexion['rx-bytes'],
            'tx/rate': conexion['tx/rate'],
            'rx/rate': conexion['rx/rate'],
            'tx-rate': conexion['tx-rate'],
            'rx-rate': conexion['rx-rate'],
          });

          self.data = conexion;
          self.renderDetails(conexion);
        } else {
          $('#pppoe-drawer-body').html(`
                        <div class="alert alert-danger m-3">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            ${response.message || 'Error al cargar los detalles de la conexión'}
                        </div>
                    `);
        }
      },
      error: function (xhr) {
        logError('❌ Error en la petición:', {
          status: xhr.status,
          statusText: xhr.statusText,
          responseText: xhr.responseText,
        });
        $('#pppoe-drawer-body').html(`
                    <div class="alert alert-danger m-3">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Error al cargar los detalles: ${xhr.statusText}
                    </div>
                `);
      },
    });
  };

  PPPoeDetailDrawer.prototype.renderDetails = function (conexion) {
    // Declarar todas las variables al inicio con 'var' para evitar problemas de hoisting
    var self = this;
    var userName, callerId, profile, uptimeValue, interfaceName;
    var ipHtml, ipEscaped, routerId, ip, uptime;
    var txBytes, rxBytes, txRate, rxRate, allKeys, key, lowerKey;
    var txBytesData, rxBytesData, txSpeedData, rxSpeedData;
    var k1, sizes1, i1, k2, sizes2, i2;
    var txRateValue, rxRateValue, bitsPerSecond1, mbps1, gbps1, kbps1;
    var bitsPerSecond2, mbps2, gbps2, kbps2;
    var txSpeedClass, rxSpeedClass, hasRates, ratesAlert, trafficSection;
    var htmlStart,
      htmlUser,
      htmlIp,
      htmlCallerId,
      htmlProfile,
      htmlUptime,
      htmlInterface,
      htmlEnd,
      html;
    var weekMatch, dayMatch, hourMatch, minuteMatch, secondMatch;
    var weeks, days, hours, minutes, seconds, totalDays, parts, e;
    var htmlInsertado, $botonesInmediatos;

    logDebug('🎨 renderDetails llamado con:', {
      address: conexion.address,
      routerId: this.routerId,
      hasAddress: !!conexion.address,
    });

    // Establecer tiempo inicial para calcular velocidad en la próxima actualización
    this.lastUpdate = Date.now();
    this.data = conexion;

    // Asegurar que el drawer esté marcado como abierto
    this.isOpen = true;
    this.isClosing = false;

    // Verificar que la función esté disponible
    if (typeof window.abrirInterfazOnu !== 'function') {
      logWarn('⚠️ abrirInterfazOnu no está disponible al renderizar detalles');
    } else {
      logDebug('✅ abrirInterfazOnu está disponible');
    }

    // Información SNMP removida según solicitud del usuario

    // Generar HTML para IP con botón de abrir ONU
    logDebug('🔍 INICIANDO GENERACIÓN DE HTML PARA IP');
    logDebug('🔍 conexion.address:', conexion.address);
    logDebug('🔍 typeof conexion.address:', typeof conexion.address);
    logDebug('🔍 conexion.address !== "-":', conexion.address !== '-');
    logDebug(
      '🔍 conexion.address && conexion.address !== "-":',
      conexion.address && conexion.address !== '-'
    );

    // Extraer todas las variables ANTES de construir el HTML
    // Definir cada variable por separado para evitar problemas de inicialización
    var userName = conexion.name || '-';
    var callerId = conexion['caller-id'] || '-';
    var profile = conexion.profile || conexion['profile'] || '-';
    var uptimeValue = conexion.uptime || conexion['uptime'] || '-';
    var interfaceName = conexion['interface-name'] || conexion.interface || '-';

    // Generar HTML para IP con botón de abrir ONU
    var ipHtml = '<code class="text-muted" style="font-size: 0.9em; font-weight: 600;">-</code>';

    if (conexion.address && conexion.address !== '-') {
      logDebug('✅ CONDICIÓN CUMPLIDA - Generando botón para IP');

      ipEscaped = conexion.address.replace(/"/g, '&quot;');
      routerId = self.routerId;
      ip = conexion.address;

      logDebug('🔍🔍🔍 GENERANDO HTML PARA IP:', {
        ip: ip,
        routerId: routerId,
        ipEscaped: ipEscaped,
      });

      // Usar concatenación de strings en lugar de template string anidado
      ipHtml =
        '<div class="d-flex align-items-center">' +
        '<code class="text-primary mr-2" style="font-size: 0.9em; font-weight: 600;">' +
        ip +
        '</code>' +
        '<button type="button" class="btn btn-sm btn-primary abrir-onu-btn" ' +
        'data-ip="' +
        ipEscaped +
        '" ' +
        'data-router-id="' +
        routerId +
        '" ' +
        'title="Abrir interfaz web de la ONU" ' +
        'style="padding: 0.15rem 0.5rem; font-size: 0.75em;">' +
        '<i class="fas fa-external-link-alt fa-xs mr-1"></i> ' +
        'Abrir ONU' +
        '</button>' +
        '</div>';

      logDebug('📝 HTML GENERADO PARA IP:', ipHtml);
    } else {
      logWarn('⚠️ CONDICIÓN NO CUMPLIDA - No se generará botón');
      logWarn('⚠️ Razón:', {
        hasAddress: !!conexion.address,
        addressValue: conexion.address,
        isDash: conexion.address === '-',
      });
    }

    logDebug('🔍 ipHtml final:', ipHtml);

    // Formatear uptime inline para evitar problemas de inicialización
    if (!uptimeValue || uptimeValue === '-' || typeof uptimeValue !== 'string') {
      uptime = uptimeValue || '-';
    } else {
      try {
        weekMatch = uptimeValue.match(/(\d+)w/);
        dayMatch = uptimeValue.match(/(\d+)d/);
        hourMatch = uptimeValue.match(/(\d+)h/);
        minuteMatch = uptimeValue.match(/(\d+)m/);
        secondMatch = uptimeValue.match(/(\d+)s/);
        weeks = weekMatch ? parseInt(weekMatch[1], 10) : 0;
        days = dayMatch ? parseInt(dayMatch[1], 10) : 0;
        hours = hourMatch ? parseInt(hourMatch[1], 10) : 0;
        minutes = minuteMatch ? parseInt(minuteMatch[1], 10) : 0;
        seconds = secondMatch ? parseInt(secondMatch[1], 10) : 0;
        totalDays = weeks * 7 + days;
        parts = [];
        if (totalDays > 0) {
          parts.push(totalDays + ' ' + (totalDays === 1 ? 'día' : 'días'));
        }
        if (hours > 0) {
          parts.push(hours + ' ' + (hours === 1 ? 'hora' : 'horas'));
        }
        if (minutes > 0) {
          parts.push(minutes + ' ' + (minutes === 1 ? 'minuto' : 'minutos'));
        }
        if (seconds > 0 || parts.length === 0) {
          parts.push(seconds + ' ' + (seconds === 1 ? 'segundo' : 'segundos'));
        }
        uptime = parts.join(', ');
      } catch (e) {
        logError('Error al formatear uptime:', e, 'uptime:', uptimeValue);
        uptime = uptimeValue;
      }
    }

    // Construir HTML en partes para evitar problemas de scope
    htmlStart =
      '<div class="p-2 p-md-3"><!-- Información básica en card -->' +
      '<div class="card card-primary card-outline mb-2 mb-md-3">' +
      '<div class="card-header py-2 py-md-3">' +
      '<h6 class="card-title mb-0" style="font-size: 1em; font-weight: 600;">' +
      '<i class="fas fa-network-wired mr-2"></i>Información de Conexión</h6></div>' +
      '<div class="card-body p-2 p-md-3"><div class="row">';

    htmlUser =
      '<div class="col-12 col-md-6 mb-2 mb-md-3">' +
      '<div class="info-box bg-light mb-0" style="min-height: 60px;">' +
      '<span class="info-box-icon bg-info" style="width: 50px; font-size: 1em; line-height: 50px;">' +
      '<i class="fas fa-user"></i></span>' +
      '<div class="info-box-content pl-2">' +
      '<span class="info-box-text" style="font-size: 0.75em; margin-bottom: 2px; font-weight: 500;">Usuario PPPoE</span>' +
      '<span class="info-box-number" style="font-size: 0.9em; line-height: 1.3;">' +
      '<code class="text-dark" style="font-size: 0.9em; font-weight: 600;">' +
      userName +
      '</code></span></div></div></div>';

    htmlIp =
      '<div class="col-12 col-md-6 mb-2 mb-md-3">' +
      '<div class="info-box bg-light mb-0" style="min-height: 60px;">' +
      '<span class="info-box-icon bg-warning" style="width: 50px; font-size: 1em; line-height: 50px;">' +
      '<i class="fas fa-network-wired"></i></span>' +
      '<div class="info-box-content pl-2">' +
      '<span class="info-box-text" style="font-size: 0.75em; margin-bottom: 2px; font-weight: 500;">IP Asignada</span>' +
      '<span class="info-box-number" style="font-size: 0.9em; line-height: 1.3;">' +
      ipHtml +
      '</span>' +
      '</div></div></div>';

    htmlCallerId =
      '<div class="col-12 col-md-6 mb-2 mb-md-3">' +
      '<div class="info-box bg-light mb-0" style="min-height: 60px;">' +
      '<span class="info-box-icon bg-secondary" style="width: 50px; font-size: 1em; line-height: 50px;">' +
      '<i class="fas fa-ethernet"></i></span>' +
      '<div class="info-box-content pl-2">' +
      '<span class="info-box-text" style="font-size: 0.75em; margin-bottom: 2px; font-weight: 500;">Caller-ID (MAC)</span>' +
      '<span class="info-box-number" style="font-size: 0.9em; line-height: 1.3;">' +
      '<code class="text-dark" style="font-size: 0.9em; font-weight: 600;">' +
      callerId +
      '</code></span></div></div></div>';

    htmlProfile =
      '<div class="col-12 col-md-6 mb-2 mb-md-3">' +
      '<div class="info-box bg-light mb-0" style="min-height: 60px;">' +
      '<span class="info-box-icon bg-success" style="width: 50px; font-size: 1em; line-height: 50px;">' +
      '<i class="fas fa-tag"></i></span>' +
      '<div class="info-box-content pl-2">' +
      '<span class="info-box-text" style="font-size: 0.75em; margin-bottom: 2px; font-weight: 500;">Perfil</span>' +
      '<span class="info-box-number" style="font-size: 0.9em; line-height: 1.3;">' +
      '<code class="text-success" style="font-size: 0.9em; font-weight: 600;">' +
      profile +
      '</code></span></div></div></div>';

    htmlUptime =
      '<div class="col-12 col-md-6 mb-2 mb-md-3">' +
      '<div class="info-box bg-light mb-0" style="min-height: 60px;">' +
      '<span class="info-box-icon bg-primary" style="width: 50px; font-size: 1em; line-height: 50px;">' +
      '<i class="fas fa-clock"></i></span>' +
      '<div class="info-box-content pl-2">' +
      '<span class="info-box-text" style="font-size: 0.75em; margin-bottom: 2px; font-weight: 500;">Tiempo Activo</span>' +
      '<span class="info-box-number" id="pppoe-uptime" style="font-size: 0.9em; line-height: 1.3;">' +
      '<strong style="font-size: 0.9em; font-weight: 600; color: #333;">' +
      uptime +
      '</strong></span></div></div></div>';

    htmlInterface =
      '<div class="col-12 col-md-6 mb-2 mb-md-3">' +
      '<div class="info-box bg-light mb-0" style="min-height: 60px;">' +
      '<span class="info-box-icon bg-dark" style="width: 50px; font-size: 1em; line-height: 50px;">' +
      '<i class="fas fa-plug"></i></span>' +
      '<div class="info-box-content pl-2">' +
      '<span class="info-box-text" style="font-size: 0.75em; margin-bottom: 2px; font-weight: 500;">Interfaz</span>' +
      '<span class="info-box-number" style="font-size: 0.9em; line-height: 1.3;">' +
      '<code class="text-dark" style="font-size: 0.9em; font-weight: 600;">' +
      interfaceName +
      '</code></span></div></div></div>';

    // Generar sección de tráfico INLINE para evitar problemas de inicialización
    // Extraer datos de tráfico directamente aquí
    txBytes = 0;
    rxBytes = 0;
    txRate = 0;
    rxRate = 0;
    allKeys = Object.keys(conexion);

    // Buscar campos de bytes transmitidos
    for (key = 0; key < allKeys.length; key++) {
      lowerKey = allKeys[key].toLowerCase();
      if (lowerKey.includes('tx') && lowerKey.includes('byte') && !lowerKey.includes('rate')) {
        txBytes = parseInt(conexion[allKeys[key]]) || 0;
        break;
      }
    }

    // Buscar campos de bytes recibidos
    for (key = 0; key < allKeys.length; key++) {
      lowerKey = allKeys[key].toLowerCase();
      if (lowerKey.includes('rx') && lowerKey.includes('byte') && !lowerKey.includes('rate')) {
        rxBytes = parseInt(conexion[allKeys[key]]) || 0;
        break;
      }
    }

    // Buscar campos de velocidad transmitida
    for (key = 0; key < allKeys.length; key++) {
      lowerKey = allKeys[key].toLowerCase();
      if (
        lowerKey === 'tx/rate' ||
        lowerKey === 'tx-rate' ||
        (lowerKey.includes('tx') && lowerKey.includes('rate'))
      ) {
        txRate = parseInt(conexion[allKeys[key]]) || 0;
        break;
      }
    }

    // Buscar campos de velocidad recibida
    for (key = 0; key < allKeys.length; key++) {
      lowerKey = allKeys[key].toLowerCase();
      if (
        lowerKey === 'rx/rate' ||
        lowerKey === 'rx-rate' ||
        (lowerKey.includes('rx') && lowerKey.includes('rate'))
      ) {
        rxRate = parseInt(conexion[allKeys[key]]) || 0;
        break;
      }
    }

    // Calcular valores formateados INLINE sin funciones helper
    // Formatear txBytes
    if (!txBytes || txBytes === 0) {
      txBytesData = { value: '0', unit: 'B' };
    } else {
      k1 = 1024;
      sizes1 = ['B', 'KB', 'MB', 'GB', 'TB'];
      i1 = Math.floor(Math.log(txBytes) / Math.log(k1));
      txBytesData = {
        value: Math.round((txBytes / Math.pow(k1, i1)) * 100) / 100,
        unit: sizes1[i1],
      };
    }

    // Formatear rxBytes
    if (!rxBytes || rxBytes === 0) {
      rxBytesData = { value: '0', unit: 'B' };
    } else {
      k2 = 1024;
      sizes2 = ['B', 'KB', 'MB', 'GB', 'TB'];
      i2 = Math.floor(Math.log(rxBytes) / Math.log(k2));
      rxBytesData = {
        value: Math.round((rxBytes / Math.pow(k2, i2)) * 100) / 100,
        unit: sizes2[i2],
      };
    }

    // Formatear txSpeed
    txRateValue = txRate > 0 ? txRate : 0;
    if (!txRateValue || txRateValue <= 0) {
      txSpeedData = { value: '0', unit: 'Kbps' };
    } else {
      bitsPerSecond1 = txRateValue * 8;
      mbps1 = bitsPerSecond1 / 1000000;
      if (mbps1 >= 1000) {
        gbps1 = mbps1 / 1000;
        txSpeedData = { value: Math.round(gbps1 * 100) / 100, unit: 'Gbps' };
      } else if (mbps1 >= 1) {
        txSpeedData = { value: Math.round(mbps1 * 100) / 100, unit: 'Mbps' };
      } else {
        kbps1 = bitsPerSecond1 / 1000;
        txSpeedData = { value: Math.round(kbps1 * 100) / 100, unit: 'Kbps' };
      }
    }

    // Formatear rxSpeed
    rxRateValue = rxRate > 0 ? rxRate : 0;
    if (!rxRateValue || rxRateValue <= 0) {
      rxSpeedData = { value: '0', unit: 'Kbps' };
    } else {
      bitsPerSecond2 = rxRateValue * 8;
      mbps2 = bitsPerSecond2 / 1000000;
      if (mbps2 >= 1000) {
        gbps2 = mbps2 / 1000;
        rxSpeedData = { value: Math.round(gbps2 * 100) / 100, unit: 'Gbps' };
      } else if (mbps2 >= 1) {
        rxSpeedData = { value: Math.round(mbps2 * 100) / 100, unit: 'Mbps' };
      } else {
        kbps2 = bitsPerSecond2 / 1000;
        rxSpeedData = { value: Math.round(kbps2 * 100) / 100, unit: 'Kbps' };
      }
    }
    txSpeedClass = txRate > 0 ? 'text-success' : 'text-muted';
    rxSpeedClass = rxRate > 0 ? 'text-danger' : 'text-muted';
    hasRates = txRate > 0 || rxRate > 0;
    ratesAlert = !hasRates
      ? '<div class="alert alert-info alert-sm mb-0 mt-2 py-2" style="font-size: 0.75em;"><i class="fas fa-info-circle fa-xs mr-1"></i>Las velocidades se mostrarán cuando haya tráfico activo.</div>'
      : '';

    // Construir HTML de tráfico directamente
    trafficSection = '';
    trafficSection += '<div class="card card-success card-outline mb-3">';
    trafficSection += '<div class="card-header">';
    trafficSection += '<h5 class="card-title mb-0">';
    trafficSection += '<i class="fas fa-chart-bar mr-2"></i>Tráfico en Tiempo Real';
    trafficSection += '</h5>';
    trafficSection += '</div>';
    trafficSection += '<div class="card-body">';
    trafficSection += '<div class="row">';

    // Columna Descarga (TX)
    trafficSection += '<div class="col-12 col-md-6 mb-2 mb-md-3">';
    trafficSection += '<div class="info-box shadow-sm mb-0" style="min-height: 110px;">';
    trafficSection +=
      '<span class="info-box-icon bg-success elevation-1" style="width: 70px; font-size: 1.1em; line-height: 70px; position: relative;">';
    trafficSection +=
      '<div id="pppoe-tx-bytes-icon" style="position: absolute; bottom: 4px; left: 0; right: 0; text-align: center; line-height: 1; color: rgba(255,255,255,0.95); font-weight: 600; font-size: 16px;">';
    trafficSection += '<div style="font-size: 18px;">' + txBytesData.value + '</div>';
    trafficSection +=
      '<div style="font-size: 13px; margin-top: 2px;">' + txBytesData.unit + '</div>';
    trafficSection += '</div>';
    trafficSection += '</span>';
    trafficSection +=
      '<div class="info-box-content" style="text-align: center !important; width: 100%; padding-left: 0; display: flex; flex-direction: column; align-items: center; justify-content: flex-start;">';
    trafficSection +=
      '<span class="info-box-text" id="pppoe-tx-label" style="font-size: 0.75em; margin-bottom: 8px; display: block; text-align: center !important; width: 100%;">';
    trafficSection +=
      '<strong style="text-align: center !important; display: block;">Descarga</strong>';
    trafficSection += '</span>';
    trafficSection +=
      '<div class="mb-2" id="pppoe-tx-rate-display" style="line-height: 1.3; min-height: 1.8em; text-align: center;">';
    trafficSection +=
      '<strong class="' +
      txSpeedClass +
      '" style="font-size: 1.6em; font-weight: 700; display: block; text-align: center;">' +
      txSpeedData.value +
      '</strong>';
    trafficSection +=
      '<span class="' +
      txSpeedClass +
      '" style="font-size: 0.9em; display: block; margin-top: 2px; text-align: center;">' +
      txSpeedData.unit +
      '</span>';
    trafficSection += '</div>';
    trafficSection += '</div>';
    trafficSection += '</div>';
    trafficSection += '</div>';

    // Columna Subida (RX)
    trafficSection += '<div class="col-12 col-md-6 mb-2 mb-md-3">';
    trafficSection += '<div class="info-box shadow-sm mb-0" style="min-height: 110px;">';
    trafficSection +=
      '<span class="info-box-icon bg-danger elevation-1" style="width: 70px; font-size: 1.1em; line-height: 70px; position: relative;">';
    trafficSection +=
      '<div id="pppoe-rx-bytes-icon" style="position: absolute; bottom: 4px; left: 0; right: 0; text-align: center; line-height: 1; color: rgba(255,255,255,0.95); font-weight: 600; font-size: 14px;">';
    trafficSection += '<div style="font-size: 15px;">' + rxBytesData.value + '</div>';
    trafficSection +=
      '<div style="font-size: 11px; margin-top: 2px;">' + rxBytesData.unit + '</div>';
    trafficSection += '</div>';
    trafficSection += '</span>';
    trafficSection +=
      '<div class="info-box-content" style="text-align: center !important; width: 100%; padding-left: 0; display: flex; flex-direction: column; align-items: center; justify-content: flex-start;">';
    trafficSection +=
      '<span class="info-box-text" id="pppoe-rx-label" style="font-size: 0.75em; margin-bottom: 8px; display: block; text-align: center !important; width: 100%;">';
    trafficSection +=
      '<strong style="text-align: center !important; display: block;">Subida</strong>';
    trafficSection += '</span>';
    trafficSection +=
      '<div class="mb-2" id="pppoe-rx-rate-display" style="line-height: 1.3; min-height: 1.8em; text-align: center;">';
    trafficSection +=
      '<strong class="' +
      rxSpeedClass +
      '" style="font-size: 1.6em; font-weight: 700; display: block; text-align: center;">' +
      rxSpeedData.value +
      '</strong>';
    trafficSection +=
      '<span class="' +
      rxSpeedClass +
      '" style="font-size: 0.9em; display: block; margin-top: 2px; text-align: center;">' +
      rxSpeedData.unit +
      '</span>';
    trafficSection += '</div>';
    trafficSection += '</div>';
    trafficSection += '</div>';
    trafficSection += '</div>';

    trafficSection += '</div>';
    trafficSection += ratesAlert;
    trafficSection += '</div>';
    trafficSection += '</div>';

    // Gráfico de tráfico
    trafficSection += '<div class="card card-primary card-outline">';
    trafficSection += '<div class="card-header py-2 py-md-3">';
    trafficSection += '<h6 class="card-title mb-0" style="font-size: 0.95em;">';
    trafficSection += '<i class="fas fa-chart-line fa-xs mr-1"></i>Gráfico de Velocidad';
    trafficSection += '</h6>';
    trafficSection += '</div>';
    trafficSection +=
      '<div class="card-body p-2 p-md-3" style="position: relative; height: 250px;">';
    trafficSection += '<canvas id="pppoe-traffic-chart"></canvas>';
    trafficSection += '</div>';
    trafficSection += '</div>';

    var htmlEnd =
      '</div></div></div><!-- Tráfico -->' +
      '<div id="pppoe-traffic-section">' +
      trafficSection +
      '</div>' +
      '<!-- Indicador de actualización en tiempo real -->' +
      '<div class="text-center mt-2 mt-md-3 mb-2">' +
      '<span class="badge badge-info" style="font-size: 0.75em; padding: 0.4em 0.8em;">' +
      '<i class="fas fa-sync-alt fa-spin fa-xs mr-1"></i> ' +
      '<span class="d-none d-sm-inline">Actualizando en tiempo real</span>' +
      '<span class="d-sm-none">Actualizando...</span></span></div></div>';

    // Concatenar todas las partes
    html =
      htmlStart +
      htmlUser +
      htmlIp +
      htmlCallerId +
      htmlProfile +
      htmlUptime +
      htmlInterface +
      htmlEnd;

    // Usar $ directamente (ya disponible en el scope de la función)
    $('#pppoe-drawer-body').html(html);

    // Verificar inmediatamente si el botón está en el HTML insertado
    var htmlInsertado = $('#pppoe-drawer-body').html();
    logDebug(
      '🔍 HTML INSERTADO EN DRAWER (primeros 2000 caracteres):',
      htmlInsertado.substring(0, 2000)
    );
    logDebug('🔍 Buscando "abrir-onu-btn" en HTML:', htmlInsertado.includes('abrir-onu-btn'));
    logDebug('🔍 Buscando "Abrir ONU" en HTML:', htmlInsertado.includes('Abrir ONU'));

    var $botonesInmediatos = $('#pppoe-drawer-body .abrir-onu-btn');
    logDebug(
      '🔍 Botones encontrados inmediatamente después de insertar HTML:',
      $botonesInmediatos.length
    );

    // Agregar event handler para botón de abrir ONU
    setTimeout(function () {
      var $botones = $('#pppoe-drawer-body .abrir-onu-btn');
      logDebug(
        '🔍🔍🔍 BUSCANDO BOTONES .abrir-onu-btn en drawer (después de timeout):',
        $botones.length
      );

      if ($botones.length > 0) {
        logDebug('✅ Botones encontrados:', $botones.length);
        $botones.each(function (index) {
          var $btn = $(this);
          logDebug('  Botón ' + (index + 1) + ':', {
            ip: $btn.data('ip'),
            routerId: $btn.data('router-id'),
            visible: $btn.is(':visible'),
            display: $btn.css('display'),
            width: $btn.width(),
            height: $btn.height(),
            html: $btn[0] ? $btn[0].outerHTML.substring(0, 200) : 'no element',
          });
        });
      } else {
        logWarn('⚠️⚠️⚠️ NO SE ENCONTRARON BOTONES .abrir-onu-btn en el drawer');
        var htmlCompleto = $('#pppoe-drawer-body').html();
        logWarn(
          'HTML completo del drawer (primeros 2000 caracteres):',
          htmlCompleto.substring(0, 2000)
        );
        logWarn('Buscando "IP Asignada" en HTML:', htmlCompleto.includes('IP Asignada'));
        logWarn('Buscando "10.10.9" en HTML:', htmlCompleto.includes('10.10.9'));
      }

      $botones.off('click.abrir-onu').on('click.abrir-onu', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var $btn = $(this);
        var routerIdBtn = $btn.data('router-id');
        var ipBtn = $btn.data('ip');

        logDebug('🖱️🖱️🖱️ Botón Abrir ONU clickeado:', { routerId: routerIdBtn, ip: ipBtn });

        if (routerIdBtn && ipBtn && typeof window.abrirInterfazOnu === 'function') {
          window.abrirInterfazOnu(routerIdBtn, ipBtn);
        } else {
          logError('❌ Error al abrir ONU:', {
            routerId: routerIdBtn,
            ip: ipBtn,
            functionAvailable: typeof window.abrirInterfazOnu,
          });
          window.showAlert('Error: No se pudo abrir la interfaz de la ONU. Por favor recarga la página.', 'error');
        }
      });
      logDebug('✅ Handler para botón Abrir ONU registrado en', $botones.length, 'botones');
    }, 200);

    // Inicializar gráfico de tráfico en tiempo real
    this.initTrafficChart();

    // Iniciar actualización en tiempo real (siempre reiniciar para asegurar que funcione)
    // Esperar un poco para que el DOM se renderice completamente
    setTimeout(function () {
      logDebug('🚀 Iniciando actualizaciones en tiempo real...', {
        isOpen: self.isOpen,
        isClosing: self.isClosing,
        routerId: self.routerId,
        sessionId: self.sessionId,
      });
      self.startRealTimeUpdates();
    }, 500);
  };

  PPPoeDetailDrawer.prototype.renderSnmpInfo = function (snmpInfo) {
    logDebug('🔍 renderSnmpInfo llamado con:', snmpInfo);
    if (!snmpInfo) {
      logDebug('⚠️ snmpInfo es null o undefined, mostrando estado por defecto');
      // Mostrar información básica incluso si snmpInfo es null
      return `
            <hr>
            <h6 class="mb-3">Información SNMP <i class="fas fa-question-circle text-warning"></i></h6>
            <dl class="row mb-4">
                <dt class="col-sm-5">Estado:</dt>
                <dd class="col-sm-7">Información SNMP no disponible</dd>
            </dl>
        `;
    }

    const statusIcon =
      snmpInfo.available && snmpInfo.configured
        ? '<i class="fas fa-check-circle text-success"></i>'
        : snmpInfo.available
        ? '<i class="fas fa-exclamation-triangle text-warning"></i>'
        : '<i class="fas fa-times-circle text-danger"></i>';

    const statusText =
      snmpInfo.available && snmpInfo.configured
        ? 'SNMP Disponible y Configurado'
        : snmpInfo.available
        ? 'SNMP Disponible (no configurado en router)'
        : 'SNMP No Disponible';

    let snmpDetails = '';

    if (snmpInfo.available && snmpInfo.configured) {
      const routerId = this.routerId || window.currentRouterId || 1;
      const interfaceName = encodeURIComponent(snmpInfo.interface || '');
      const detailUrl = `/red/routers/${routerId}/snmp-interface/${interfaceName}`;

      snmpDetails = `
                <dt class="col-sm-5">Puerto SNMP:</dt>
                <dd class="col-sm-7">${snmpInfo.port || '-'}</dd>
                <dt class="col-sm-5">Comunidad SNMP:</dt>
                <dd class="col-sm-7">${snmpInfo.community || '-'}</dd>
                <dt class="col-sm-5">Interfaz SNMP:</dt>
                <dd class="col-sm-7"><code>${snmpInfo.interface || '-'}</code></dd>
      `;

      if (snmpInfo.rates_obtained) {
        snmpDetails += `
                <dt class="col-sm-5">Tasas por SNMP:</dt>
                <dd class="col-sm-7">
                    <span class="badge badge-success">TX: ${this.formatSpeed(
                      snmpInfo.tx_rate || 0
                    )}</span>
                    <span class="badge badge-info ml-1">RX: ${this.formatSpeed(
                      snmpInfo.rx_rate || 0
                    )}</span>
                </dd>
        `;
      } else if (snmpInfo.error) {
        snmpDetails += `
                <dt class="col-sm-5">Estado SNMP:</dt>
                <dd class="col-sm-7">
                    <span class="badge badge-warning">${snmpInfo.error}</span>
                </dd>
        `;
      }

      // Agregar botón para ver información completa
      snmpDetails += `
                <dt class="col-sm-5">Información Completa:</dt>
                <dd class="col-sm-7">
                    <button
                        type="button"
                        class="btn btn-sm btn-info btn-view-snmp-details"
                        data-router-id="${routerId}"
                        data-interface="${snmpInfo.interface || ''}"
                        data-url="${detailUrl}"
                    >
                        <i class="fas fa-info-circle mr-1"></i>
                        Ver Todos los OIDs SNMP
                    </button>
                </dd>
      `;
    } else if (snmpInfo.error) {
      snmpDetails = `
                <dt class="col-sm-5">Error:</dt>
                <dd class="col-sm-7"><span class="text-danger small">${snmpInfo.error}</span></dd>
      `;
    } else if (!snmpInfo.available) {
      // Detectar si estamos en cli-server
      const isCliServer =
        window.location.port === '8000' || window.location.hostname === '127.0.0.1:8000';

      const debugInfo = snmpInfo.debug
        ? `
                <dt class="col-sm-5">Debug:</dt>
                <dd class="col-sm-7">
                    <small class="text-muted">
                        snmpget: ${snmpInfo.debug.has_snmpget ? '✓' : '✗'},
                        snmpwalk: ${snmpInfo.debug.has_snmpwalk ? '✓' : '✗'},
                        PHP: ${snmpInfo.debug.php_version || 'N/A'}
                    </small>
                </dd>
                <dt class="col-sm-5">Solución:</dt>
                <dd class="col-sm-7">
                    <small class="${isCliServer ? 'text-warning' : 'text-info'}">
                        ${
                          isCliServer
                            ? '⚠️ Estás usando php artisan serve (puerto 8000). Este servidor NO soporta SNMP. Para usar SNMP, debes usar Apache de XAMPP accediendo desde: http://localhost/adminISP/public (Admin ISP)'
                            : 'Verifica que extension=snmp esté habilitado en php.ini y reinicia Apache. Prueba en: <a href="/test-snmp.php" target="_blank">/test-snmp.php</a>'
                        }
                    </small>
                </dd>
      `
        : '';

      snmpDetails = `
                <dt class="col-sm-5">Detalle:</dt>
                <dd class="col-sm-7">
                    <small class="text-muted">La extensión SNMP de PHP no está disponible en el contexto web (Apache)</small>
                    <br>
                    <small class="text-info">
                        <i class="fas fa-info-circle"></i>
                        Verifica que la extensión esté habilitada en el php.ini que usa Apache
                    </small>
                </dd>
                ${debugInfo}
      `;
    } else if (!snmpInfo.configured) {
      snmpDetails = `
                <dt class="col-sm-5">Detalle:</dt>
                <dd class="col-sm-7">
                    <small class="text-muted">Configure el puerto SNMP (161) y la comunidad SNMP (ej: public) en la configuración del router</small>
                    <br>
                    <small class="text-info">
                        <i class="fas fa-info-circle"></i>
                        Edita el router y configura: Puerto SNMP = 161, Comunidad SNMP = public
                    </small>
                </dd>
      `;
    }

    const html = `
            <hr>
            <h6 class="mb-3">Información SNMP ${statusIcon}</h6>
            <dl class="row mb-4">
                <dt class="col-sm-5">Estado:</dt>
                <dd class="col-sm-7">${statusText}</dd>
                ${snmpDetails}
            </dl>
        `;

    logDebug('✅ HTML SNMP generado:', html.substring(0, 200) + '...');

    // Agregar manejador de eventos para el botón de ver detalles
    setTimeout(() => {
      $('.btn-view-snmp-details')
        .off('click')
        .on('click', function () {
          const routerId = $(this).data('router-id');
          const interfaceName = $(this).data('interface');
          const url = $(this).data('url');

          logDebug('🔍 Cargando información completa SNMP:', { routerId, interfaceName, url });

          // Mostrar loading
          const $btn = $(this);
          const originalHtml = $btn.html();
          $btn
            .prop('disabled', true)
            .html('<i class="fas fa-spinner fa-spin mr-1"></i> Cargando...');

          $.ajax({
            url: url,
            method: 'GET',
            success: function (response) {
              logDebug('📊 Información completa SNMP recibida:', response);

              if (response.success && response.info && response.info.interface_info) {
                // Crear modal con la información completa
                const info = response.info.interface_info;

                // Formatear información de manera más legible
                let formattedContent = '<div class="row">';

                // Información básica
                if (info.basica) {
                  formattedContent += `
                  <div class="col-12 mb-3">
                    <h6><i class="fas fa-info-circle mr-2"></i>Información Básica</h6>
                    <table class="table table-sm table-bordered">
                      <tr><th>Índice SNMP</th><td>${info.basica.index || '-'}</td></tr>
                      <tr><th>Nombre</th><td><code>${info.basica.name || '-'}</code>${
                    info.basica.name_raw && info.basica.name_raw !== info.basica.name
                      ? ` <small class="text-muted">(raw: ${info.basica.name_raw})</small>`
                      : ''
                  }</td></tr>
                      <tr><th>Tipo</th><td>${info.basica.type || '-'}${
                    info.basica.type_raw
                      ? ` <small class="text-muted">(raw: ${info.basica.type_raw})</small>`
                      : ''
                  }</td></tr>
                      <tr><th>MTU</th><td>${info.basica.mtu || '-'}${
                    info.basica.mtu_raw
                      ? ` <small class="text-muted">(raw: ${info.basica.mtu_raw})</small>`
                      : ''
                  }</td></tr>
                      <tr><th>Velocidad</th><td>${
                        info.basica.speed ? (info.basica.speed / 1000000).toFixed(2) + ' Mbps' : '-'
                      }${
                    info.basica.speed_raw
                      ? ` <small class="text-muted">(raw: ${info.basica.speed_raw})</small>`
                      : ''
                  }</td></tr>
                      <tr><th>Estado Admin</th><td>${info.basica.admin_status_text || '-'}${
                    info.basica.admin_status_raw
                      ? ` <small class="text-muted">(raw: ${info.basica.admin_status_raw})</small>`
                      : ''
                  }</td></tr>
                      <tr><th>Estado Operativo</th><td>${info.basica.oper_status_text || '-'}${
                    info.basica.oper_status_raw
                      ? ` <small class="text-muted">(raw: ${info.basica.oper_status_raw})</small>`
                      : ''
                  }</td></tr>
                    </table>
                  </div>
                `;
                }

                // Tráfico acumulado
                if (info.trafico_acumulado) {
                  formattedContent += `
                  <div class="col-12 mb-3">
                    <h6><i class="fas fa-chart-line mr-2"></i>Tráfico Acumulado</h6>
                    <table class="table table-sm table-bordered">
                      <tr><th>Bytes Recibidos</th><td>${
                        info.trafico_acumulado.bytes_recibidos_formatted ||
                        info.trafico_acumulado.bytes_recibidos ||
                        '-'
                      }${
                    info.trafico_acumulado.bytes_recibidos_raw
                      ? ` <small class="text-muted">(raw: ${info.trafico_acumulado.bytes_recibidos_raw})</small>`
                      : ''
                  }</td></tr>
                      <tr><th>Bytes Enviados</th><td>${
                        info.trafico_acumulado.bytes_enviados_formatted ||
                        info.trafico_acumulado.bytes_enviados ||
                        '-'
                      }${
                    info.trafico_acumulado.bytes_enviados_raw
                      ? ` <small class="text-muted">(raw: ${info.trafico_acumulado.bytes_enviados_raw})</small>`
                      : ''
                  }</td></tr>
                      <tr><th>Paquetes Recibidos</th><td>${
                        info.trafico_acumulado.paquetes_recibidos || '-'
                      }${
                    info.trafico_acumulado.paquetes_recibidos_raw
                      ? ` <small class="text-muted">(raw: ${info.trafico_acumulado.paquetes_recibidos_raw})</small>`
                      : ''
                  }</td></tr>
                      <tr><th>Paquetes Enviados</th><td>${
                        info.trafico_acumulado.paquetes_enviados || '-'
                      }${
                    info.trafico_acumulado.paquetes_enviados_raw
                      ? ` <small class="text-muted">(raw: ${info.trafico_acumulado.paquetes_enviados_raw})</small>`
                      : ''
                  }</td></tr>
                      <tr><th>Errores Recibidos</th><td>${
                        info.trafico_acumulado.errores_recibidos || '-'
                      }${
                    info.trafico_acumulado.errores_recibidos_raw
                      ? ` <small class="text-muted">(raw: ${info.trafico_acumulado.errores_recibidos_raw})</small>`
                      : ''
                  }</td></tr>
                      <tr><th>Errores Enviados</th><td>${
                        info.trafico_acumulado.errores_enviados || '-'
                      }${
                    info.trafico_acumulado.errores_enviados_raw
                      ? ` <small class="text-muted">(raw: ${info.trafico_acumulado.errores_enviados_raw})</small>`
                      : ''
                  }</td></tr>
                    </table>
                  </div>
                `;
                }

                // Tasas en tiempo real
                if (info.tasas_tiempo_real) {
                  formattedContent += `
                  <div class="col-12 mb-3">
                    <h6><i class="fas fa-tachometer-alt mr-2"></i>Tasas en Tiempo Real</h6>
                    <table class="table table-sm table-bordered">
                      <tr><th>RX Rate</th><td>${
                        info.tasas_tiempo_real.rx_rate_formatted ||
                        info.tasas_tiempo_real.rx_rate ||
                        '-'
                      }</td></tr>
                      <tr><th>TX Rate</th><td>${
                        info.tasas_tiempo_real.tx_rate_formatted ||
                        info.tasas_tiempo_real.tx_rate ||
                        '-'
                      }</td></tr>
                    </table>
                  </div>
                `;
                }

                // Tráfico adicional
                if (info.trafico_adicional) {
                  formattedContent += `
                  <div class="col-12 mb-3">
                    <h6><i class="fas fa-chart-bar mr-2"></i>Tráfico Adicional</h6>
                    <table class="table table-sm table-bordered">
                      <tr><th>Discards Recibidos</th><td>${
                        info.trafico_adicional.discards_recibidos || '-'
                      }</td></tr>
                      <tr><th>Discards Enviados</th><td>${
                        info.trafico_adicional.discards_enviados || '-'
                      }</td></tr>
                      <tr><th>Protocolos Desconocidos</th><td>${
                        info.trafico_adicional.protocolos_desconocidos || '-'
                      }</td></tr>
                      <tr><th>Último Cambio</th><td>${
                        info.trafico_adicional.ultimo_cambio_timestamp ||
                        info.trafico_adicional.ultimo_cambio ||
                        '-'
                      }</td></tr>
                    </table>
                  </div>
                `;
                }

                // MikroTik detallado
                if (info.mikrotik_detallado || info.mikrotik_especifico) {
                  const mikrotik = info.mikrotik_detallado || info.mikrotik_especifico;
                  formattedContent += `
                  <div class="col-12 mb-3">
                    <h6><i class="fas fa-server mr-2"></i>Información Detallada MikroTik</h6>
                    <table class="table table-sm table-bordered">
                      <tr><th>RX Rate</th><td>${
                        mikrotik.rx_rate_formatted ||
                        info.tasas_tiempo_real?.rx_rate_formatted ||
                        mikrotik.rx_rate ||
                        info.tasas_tiempo_real?.rx_rate ||
                        '-'
                      }${
                    info.mikrotik_especifico?.rx_rate_valor_raw
                      ? ` <small class="text-muted">(raw: ${info.mikrotik_especifico.rx_rate_valor_raw})</small>`
                      : ''
                  }</td></tr>
                      <tr><th>TX Rate</th><td>${
                        mikrotik.tx_rate_formatted ||
                        info.tasas_tiempo_real?.tx_rate_formatted ||
                        mikrotik.tx_rate ||
                        info.tasas_tiempo_real?.tx_rate ||
                        '-'
                      }${
                    info.mikrotik_especifico?.tx_rate_valor_raw
                      ? ` <small class="text-muted">(raw: ${info.mikrotik_especifico.tx_rate_valor_raw})</small>`
                      : ''
                  }</td></tr>
                      <tr><th>RX Packets/s</th><td>${
                        mikrotik.rx_packets || info.mikrotik_especifico?.rx_packets || '-'
                      }${
                    info.mikrotik_especifico?.rx_packets_raw
                      ? ` <small class="text-muted">(raw: ${info.mikrotik_especifico.rx_packets_raw})</small>`
                      : ''
                  }</td></tr>
                      <tr><th>TX Packets/s</th><td>${
                        mikrotik.tx_packets || info.mikrotik_especifico?.tx_packets || '-'
                      }${
                    info.mikrotik_especifico?.tx_packets_raw
                      ? ` <small class="text-muted">(raw: ${info.mikrotik_especifico.tx_packets_raw})</small>`
                      : ''
                  }</td></tr>
                      <tr><th>RX Drops</th><td>${
                        mikrotik.rx_drops || info.mikrotik_especifico?.rx_drops || '-'
                      }${
                    info.mikrotik_especifico?.rx_drops_raw
                      ? ` <small class="text-muted">(raw: ${info.mikrotik_especifico.rx_drops_raw})</small>`
                      : ''
                  }</td></tr>
                      <tr><th>TX Drops</th><td>${
                        mikrotik.tx_drops || info.mikrotik_especifico?.tx_drops || '-'
                      }${
                    info.mikrotik_especifico?.tx_drops_raw
                      ? ` <small class="text-muted">(raw: ${info.mikrotik_especifico.tx_drops_raw})</small>`
                      : ''
                  }</td></tr>
                    </table>
                  </div>
                `;
                }

                // Información del router
                if (info.router_sistema) {
                  formattedContent += `
                  <div class="col-12 mb-3">
                    <h6><i class="fas fa-network-wired mr-2"></i>Información del Router</h6>
                    <table class="table table-sm table-bordered">
                      <tr><th>Descripción</th><td>${
                        info.router_sistema.descripcion || '-'
                      }</td></tr>
                      <tr><th>Uptime</th><td>${
                        info.router_sistema.uptime_formatted || info.router_sistema.uptime || '-'
                      }</td></tr>
                      <tr><th>Nombre</th><td>${info.router_sistema.nombre || '-'}</td></tr>
                      <tr><th>Ubicación</th><td>${info.router_sistema.ubicacion || '-'}</td></tr>
                    </table>
                  </div>
                `;
                }

                // Información PPPoE
                if (info.pppoe_info) {
                  const pppoeInfo = info.pppoe_info;
                  formattedContent += `
                  <div class="col-12 mb-3">
                    <h6><i class="fas fa-info-circle mr-2"></i>Información PPPoE</h6>
                    <table class="table table-sm table-bordered">
                      <tr><th>Tipo de Interfaz</th><td>${
                        pppoeInfo.es_interfaz_pppoe
                          ? '<span class="badge badge-success">Interfaz PPPoE</span>'
                          : '<span class="badge badge-secondary">Interfaz Estándar</span>'
                      }</td></tr>
                      <tr><th>Nombre Interfaz</th><td><code>${
                        pppoeInfo.interface_name || '-'
                      }</code></td></tr>
                      <tr><th>Índice SNMP</th><td>${pppoeInfo.interface_index || '-'}</td></tr>
                      <tr><th>Nota</th><td><small class="text-muted">${
                        pppoeInfo.nota || '-'
                      }</small></td></tr>
                      ${
                        pppoeInfo.informacion_disponible
                          ? `<tr><th>Información Disponible por SNMP</th><td><ul class="mb-0 small">${Object.entries(
                              pppoeInfo.informacion_disponible
                            )
                              .map(
                                ([key, value]) =>
                                  `<li><strong>${key.replace(/_/g, ' ')}</strong>: ${value}</li>`
                              )
                              .join('')}</ul></td></tr>`
                          : ''
                      }
                      ${
                        pppoeInfo.informacion_no_disponible
                          ? `<tr><th>Información NO Disponible por SNMP</th><td><ul class="mb-0 small text-muted">${Object.entries(
                              pppoeInfo.informacion_no_disponible
                            )
                              .map(
                                ([key, value]) =>
                                  `<li><strong>${key.replace(/_/g, ' ')}</strong>: ${value}</li>`
                              )
                              .join('')}</ul></td></tr>`
                          : ''
                      }
                    </table>
                  </div>
                `;
                }

                formattedContent += '</div>';

                // JSON completo en un acordeón
                formattedContent += `
                <div class="mt-3">
                  <button class="btn btn-sm btn-secondary" type="button" data-toggle="collapse" data-target="#snmp-json-collapse">
                    <i class="fas fa-code mr-1"></i>Ver JSON Completo
                  </button>
                  <div class="collapse mt-2" id="snmp-json-collapse">
                    <pre style="white-space: pre-wrap; word-wrap: break-word; font-size: 11px; max-height: 400px; overflow-y: auto;" class="bg-light p-3 border rounded">${JSON.stringify(
                      response.info,
                      null,
                      2
                    )}</pre>
                  </div>
                </div>
              `;

                let modalContent = `
                <div class="modal fade" id="snmp-details-modal" tabindex="-1" role="dialog">
                  <div class="modal-dialog modal-xl" role="document">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title">
                          <i class="fas fa-network-wired mr-2"></i>
                          Información Completa SNMP - ${interfaceName}
                        </h5>
                        <button type="button" class="close" data-dismiss="modal">
                          <span>&times;</span>
                        </button>
                      </div>
                      <div class="modal-body" style="max-height: 75vh; overflow-y: auto;">
                        ${formattedContent}
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                      </div>
                    </div>
                  </div>
                </div>
              `;

                // Remover modal anterior si existe
                $('#snmp-details-modal').remove();

                // Agregar modal al body
                $('body').append(modalContent);

                // Mostrar modal
                $('#snmp-details-modal').modal('show');
              } else {
                // Si hay interfaces disponibles, mostrarlas
                let errorMessage =
                  'No se pudo obtener la información completa de SNMP: ' +
                  (response.message || 'Error desconocido');

                if (response.interfaces_disponibles && response.interfaces_disponibles.length > 0) {
                  let interfacesHtml =
                    '<div class="alert alert-info mt-3"><h6>Interfaces Disponibles en SNMP:</h6><ul class="mb-0">';

                  // Mostrar primero las interfaces PPPoE
                  if (response.interfaces_pppoe && response.interfaces_pppoe.length > 0) {
                    interfacesHtml += '<li><strong>Interfaces PPPoE:</strong><ul>';
                    response.interfaces_pppoe.forEach(iface => {
                      interfacesHtml += `<li>${iface.name} (índice: ${iface.index})</li>`;
                    });
                    interfacesHtml += '</ul></li>';
                  }

                  // Mostrar otras interfaces que coinciden
                  const matchingInterfaces = response.interfaces_disponibles.filter(
                    iface => iface.matches
                  );
                  if (matchingInterfaces.length > 0) {
                    interfacesHtml += '<li><strong>Interfaces que Coinciden:</strong><ul>';
                    matchingInterfaces.forEach(iface => {
                      interfacesHtml += `<li>${iface.name} (índice: ${iface.index})</li>`;
                    });
                    interfacesHtml += '</ul></li>';
                  }

                  interfacesHtml += '</ul></div>';

                  errorMessage += interfacesHtml;
                }

                // Crear modal de error con información
                const errorModal = `
                  <div class="modal fade" id="snmp-error-modal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                      <div class="modal-content">
                        <div class="modal-header bg-warning">
                          <h5 class="modal-title"><i class="fas fa-exclamation-triangle mr-2"></i>Error SNMP</h5>
                          <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                        </div>
                        <div class="modal-body">
                          <div class="alert alert-danger">${
                            response.message || 'Error desconocido'
                          }</div>
                          ${
                            response.interfaces_disponibles
                              ? `
                            <div class="mt-3">
                              <h6>Interfaz Buscada:</h6>
                              <code>${response.interface_buscada}</code>
                              ${
                                response.variantes_buscadas
                                  ? `
                                <small class="text-muted d-block mt-1">Variantes buscadas: ${response.variantes_buscadas.join(
                                  ', '
                                )}</small>
                              `
                                  : ''
                              }
                            </div>
                            <div class="mt-3">
                              <h6>Interfaces Disponibles en SNMP (${
                                response.total_interfaces
                              }):</h6>
                              ${
                                response.interfaces_pppoe && response.interfaces_pppoe.length > 0
                                  ? `
                                <div class="mb-2">
                                  <strong>Interfaces PPPoE (${response.total_pppoe}):</strong>
                                  <ul class="mb-0">
                                    ${response.interfaces_pppoe
                                      .map(
                                        iface => `
                                      <li><code>${iface.name}</code> (índice: ${iface.index})</li>
                                    `
                                      )
                                      .join('')}
                                  </ul>
                                </div>
                              `
                                  : ''
                              }
                              <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                  <thead>
                                    <tr>
                                      <th>Índice</th>
                                      <th>Nombre</th>
                                      <th>Tipo</th>
                                      <th>Coincide</th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                    ${response.interfaces_disponibles
                                      .slice(0, 20)
                                      .map(
                                        iface => `
                                      <tr class="${iface.matches ? 'table-warning' : ''}">
                                        <td>${iface.index}</td>
                                        <td><code>${iface.name}</code></td>
                                        <td>${
                                          iface.is_pppoe
                                            ? '<span class="badge badge-info">PPPoE</span>'
                                            : '-'
                                        }</td>
                                        <td>${
                                          iface.matches
                                            ? '<span class="badge badge-success">Sí</span>'
                                            : '<span class="badge badge-secondary">No</span>'
                                        }</td>
                                      </tr>
                                    `
                                      )
                                      .join('')}
                                  </tbody>
                                </table>
                              </div>
                              ${
                                response.total_interfaces > 20
                                  ? `<small class="text-muted">Mostrando 20 de ${response.total_interfaces} interfaces</small>`
                                  : ''
                              }
                            </div>
                          `
                              : ''
                          }
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        </div>
                      </div>
                    </div>
                  </div>
                `;

                $('#snmp-error-modal').remove();
                $('body').append(errorModal);
                $('#snmp-error-modal').modal('show');
              }

              $btn.prop('disabled', false).html(originalHtml);
            },
            error: function (xhr, status, error) {
              logError('❌ Error al cargar información SNMP:', {
                status: xhr.status,
                statusText: xhr.statusText,
                error: error,
                responseText: xhr.responseText,
                url: url,
                routerId: routerId,
                interfaceName: interfaceName,
              });

              let errorMessage = 'Error al cargar información completa de SNMP';
              if (xhr.status === 404) {
                errorMessage +=
                  ': Ruta no encontrada. Verifica que el servidor esté corriendo y que las rutas estén registradas.';
              } else if (xhr.status === 401 || xhr.status === 403) {
                errorMessage += ': No autorizado. Verifica que estés autenticado.';
              } else {
                errorMessage += ': ' + (xhr.responseJSON?.message || xhr.statusText || error);
              }

              window.showAlert(errorMessage, 'error');
              $btn.prop('disabled', false).html(originalHtml);
            },
          });
        });
    }, 100);

    return html;
  };

  PPPoeDetailDrawer.prototype.renderTrafficSection = function (conexion) {
    // Capturar 'this' al inicio para evitar problemas de inicialización
    var self = this;

    // RouterOS puede devolver campos con nombres específicos - revisar el objeto completo
    // Intentar múltiples variantes posibles
    // Usar 'var' en lugar de 'let' para evitar problemas de hoisting
    var txBytes = 0;
    var rxBytes = 0;
    var txRate = 0;
    var rxRate = 0;

    // Buscar todos los campos posibles que RouterOS podría devolver
    var allKeys = Object.keys(conexion);

    // Buscar campos de bytes transmitidos
    var key, lowerKey;
    for (key = 0; key < allKeys.length; key++) {
      lowerKey = allKeys[key].toLowerCase();
      if (lowerKey.includes('tx') && lowerKey.includes('byte') && !lowerKey.includes('rate')) {
        txBytes = parseInt(conexion[allKeys[key]]) || 0;
        break;
      }
    }

    // Buscar campos de bytes recibidos
    for (key = 0; key < allKeys.length; key++) {
      lowerKey = allKeys[key].toLowerCase();
      if (lowerKey.includes('rx') && lowerKey.includes('byte') && !lowerKey.includes('rate')) {
        rxBytes = parseInt(conexion[allKeys[key]]) || 0;
        break;
      }
    }

    // Buscar campos de velocidad transmitida
    for (key = 0; key < allKeys.length; key++) {
      lowerKey = allKeys[key].toLowerCase();
      if (
        lowerKey === 'tx/rate' ||
        lowerKey === 'tx-rate' ||
        (lowerKey.includes('tx') && lowerKey.includes('rate'))
      ) {
        txRate = parseInt(conexion[allKeys[key]]) || 0;
        break;
      }
    }

    // Buscar campos de velocidad recibida
    for (key = 0; key < allKeys.length; key++) {
      lowerKey = allKeys[key].toLowerCase();
      if (
        lowerKey === 'rx/rate' ||
        lowerKey === 'rx-rate' ||
        (lowerKey.includes('rx') && lowerKey.includes('rate'))
      ) {
        rxRate = parseInt(conexion[allKeys[key]]) || 0;
        break;
      }
    }

    logDebug('📊 Renderizando tráfico:', {
      txBytes,
      rxBytes,
      txRate,
      rxRate,
      allKeys: allKeys,
      conexion: conexion,
      campos_con_rate: allKeys.filter(k => k.toLowerCase().includes('rate')),
      valores_rate: allKeys
        .filter(k => k.toLowerCase().includes('rate'))
        .map(k => ({ key: k, value: conexion[k] })),
    });

    // Calcular todos los valores formateados ANTES de construir el HTML
    // Esto evita problemas con IIFE dentro de template strings
    // Usar 'self' en lugar de 'this' para evitar problemas de inicialización
    // Usar 'var' en lugar de 'const' para evitar problemas de hoisting
    // Verificar que los métodos existan antes de usarlos
    var txBytesData, rxBytesData, txSpeedData, rxSpeedData;

    if (self && typeof self.formatBytesSeparated === 'function') {
      txBytesData = self.formatBytesSeparated(txBytes);
      rxBytesData = self.formatBytesSeparated(rxBytes);
    } else {
      // Fallback si los métodos no están disponibles
      var formatBytesHelper = function (bytes) {
        if (!bytes || bytes === 0) return { value: '0', unit: 'B' };
        var k = 1024;
        var sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
        var i = Math.floor(Math.log(bytes) / Math.log(k));
        return {
          value: Math.round((bytes / Math.pow(k, i)) * 100) / 100,
          unit: sizes[i],
        };
      };
      txBytesData = formatBytesHelper(txBytes);
      rxBytesData = formatBytesHelper(rxBytes);
    }

    if (self && typeof self.formatMbpsSeparated === 'function') {
      txSpeedData = self.formatMbpsSeparated(txRate > 0 ? txRate : 0);
      rxSpeedData = self.formatMbpsSeparated(rxRate > 0 ? rxRate : 0);
    } else {
      // Fallback si los métodos no están disponibles
      var formatMbpsHelper = function (bytesPerSecond) {
        if (!bytesPerSecond || bytesPerSecond <= 0) {
          return { value: '0', unit: 'Kbps' };
        }
        var bitsPerSecond = bytesPerSecond * 8;
        var mbps = bitsPerSecond / 1000000;
        if (mbps >= 1000) {
          var gbps = mbps / 1000;
          return { value: Math.round(gbps * 100) / 100, unit: 'Gbps' };
        } else if (mbps >= 1) {
          return { value: Math.round(mbps * 100) / 100, unit: 'Mbps' };
        } else {
          var kbps = bitsPerSecond / 1000;
          return { value: Math.round(kbps * 100) / 100, unit: 'Kbps' };
        }
      };
      txSpeedData = formatMbpsHelper(txRate > 0 ? txRate : 0);
      rxSpeedData = formatMbpsHelper(rxRate > 0 ? rxRate : 0);
    }

    // Determinar clases CSS según si hay velocidad o no
    var txSpeedClass = txRate > 0 ? 'text-success' : 'text-muted';
    var rxSpeedClass = rxRate > 0 ? 'text-danger' : 'text-muted';

    // Mostrar velocidades siempre, incluso si son 0 (se actualizarán en tiempo real)
    var hasRates = txRate > 0 || rxRate > 0;
    var ratesAlert = !hasRates
      ? '<div class="alert alert-info alert-sm mb-0 mt-2 py-2" style="font-size: 0.75em;"><i class="fas fa-info-circle fa-xs mr-1"></i>Las velocidades se mostrarán cuando haya tráfico activo.</div>'
      : '';

    // Construir HTML usando concatenación de strings para evitar problemas con template strings
    var html = '';

    html += '<div class="card card-success card-outline mb-3">';
    html += '<div class="card-header">';
    html += '<h5 class="card-title mb-0">';
    html += '<i class="fas fa-chart-bar mr-2"></i>Tráfico en Tiempo Real';
    html += '</h5>';
    html += '</div>';
    html += '<div class="card-body">';
    html += '<div class="row">';

    // Columna Descarga (TX)
    html += '<div class="col-12 col-md-6 mb-2 mb-md-3">';
    html += '<div class="info-box shadow-sm mb-0" style="min-height: 110px;">';
    html +=
      '<span class="info-box-icon bg-success elevation-1" style="width: 70px; font-size: 1.1em; line-height: 70px; position: relative;">';
    html +=
      '<div id="pppoe-tx-bytes-icon" style="position: absolute; bottom: 4px; left: 0; right: 0; text-align: center; line-height: 1; color: rgba(255,255,255,0.95); font-weight: 600; font-size: 16px;">';
    html += '<div style="font-size: 18px;">' + txBytesData.value + '</div>';
    html += '<div style="font-size: 13px; margin-top: 2px;">' + txBytesData.unit + '</div>';
    html += '</div>';
    html += '</span>';
    html +=
      '<div class="info-box-content" style="text-align: center !important; width: 100%; padding-left: 0; display: flex; flex-direction: column; align-items: center; justify-content: flex-start;">';
    html +=
      '<span class="info-box-text" id="pppoe-tx-label" style="font-size: 0.75em; margin-bottom: 8px; display: block; text-align: center !important; width: 100%;">';
    html += '<strong style="text-align: center !important; display: block;">Descarga</strong>';
    html += '</span>';
    html +=
      '<div class="mb-2" id="pppoe-tx-rate-display" style="line-height: 1.3; min-height: 1.8em; text-align: center;">';
    html +=
      '<strong class="' +
      txSpeedClass +
      '" style="font-size: 1.6em; font-weight: 700; display: block; text-align: center;">' +
      txSpeedData.value +
      '</strong>';
    html +=
      '<span class="' +
      txSpeedClass +
      '" style="font-size: 0.9em; display: block; margin-top: 2px; text-align: center;">' +
      txSpeedData.unit +
      '</span>';
    html += '</div>';
    html += '</div>';
    html += '</div>';
    html += '</div>';

    // Columna Subida (RX)
    html += '<div class="col-12 col-md-6 mb-2 mb-md-3">';
    html += '<div class="info-box shadow-sm mb-0" style="min-height: 110px;">';
    html +=
      '<span class="info-box-icon bg-danger elevation-1" style="width: 70px; font-size: 1.1em; line-height: 70px; position: relative;">';
    html +=
      '<div id="pppoe-rx-bytes-icon" style="position: absolute; bottom: 4px; left: 0; right: 0; text-align: center; line-height: 1; color: rgba(255,255,255,0.95); font-weight: 600; font-size: 14px;">';
    html += '<div style="font-size: 15px;">' + rxBytesData.value + '</div>';
    html += '<div style="font-size: 11px; margin-top: 2px;">' + rxBytesData.unit + '</div>';
    html += '</div>';
    html += '</span>';
    html +=
      '<div class="info-box-content" style="text-align: center !important; width: 100%; padding-left: 0; display: flex; flex-direction: column; align-items: center; justify-content: flex-start;">';
    html +=
      '<span class="info-box-text" id="pppoe-rx-label" style="font-size: 0.75em; margin-bottom: 8px; display: block; text-align: center !important; width: 100%;">';
    html += '<strong style="text-align: center !important; display: block;">Subida</strong>';
    html += '</span>';
    html +=
      '<div class="mb-2" id="pppoe-rx-rate-display" style="line-height: 1.3; min-height: 1.8em; text-align: center;">';
    html +=
      '<strong class="' +
      rxSpeedClass +
      '" style="font-size: 1.6em; font-weight: 700; display: block; text-align: center;">' +
      rxSpeedData.value +
      '</strong>';
    html +=
      '<span class="' +
      rxSpeedClass +
      '" style="font-size: 0.9em; display: block; margin-top: 2px; text-align: center;">' +
      rxSpeedData.unit +
      '</span>';
    html += '</div>';
    html += '</div>';
    html += '</div>';
    html += '</div>';

    html += '</div>';
    html += ratesAlert;
    html += '</div>';
    html += '</div>';

    // Gráfico de tráfico
    html += '<div class="card card-primary card-outline">';
    html += '<div class="card-header py-2 py-md-3">';
    html += '<h6 class="card-title mb-0" style="font-size: 0.95em;">';
    html += '<i class="fas fa-chart-line fa-xs mr-1"></i>Gráfico de Velocidad';
    html += '</h6>';
    html += '</div>';
    html += '<div class="card-body p-2 p-md-3" style="position: relative; height: 250px;">';
    html += '<canvas id="pppoe-traffic-chart"></canvas>';
    html += '</div>';
    html += '</div>';

    return html;
  };

  PPPoeDetailDrawer.prototype.updateTraffic = function (conexion) {
    // Verificar que el drawer esté abierto antes de actualizar
    // Solo verificar isClosing, no isOpen (porque puede estar en proceso de apertura)
    if (this.isClosing) {
      logDebug('⏸️ Ignorando actualización de tráfico - drawer cerrándose');
      return;
    }

    const now = Date.now();

    // Guardar datos anteriores
    const previousData = this.data;
    this.lastUpdate = now;
    this.data = conexion;

    // Buscar campos dinámicamente
    const allKeys = Object.keys(conexion);
    let txBytes = 0;
    let rxBytes = 0;
    let txPackets = 0;
    let rxPackets = 0;
    let txRate = 0;
    let rxRate = 0;

    // Buscar campos de bytes transmitidos
    for (const key of allKeys) {
      const lowerKey = key.toLowerCase();
      if (lowerKey.includes('tx') && lowerKey.includes('byte') && !lowerKey.includes('rate')) {
        txBytes = parseInt(conexion[key]) || 0;
        break;
      }
    }

    // Buscar campos de bytes recibidos
    for (const key of allKeys) {
      const lowerKey = key.toLowerCase();
      if (lowerKey.includes('rx') && lowerKey.includes('byte') && !lowerKey.includes('rate')) {
        rxBytes = parseInt(conexion[key]) || 0;
        break;
      }
    }

    // Buscar campos de velocidad transmitida
    for (const key of allKeys) {
      const lowerKey = key.toLowerCase();
      if (
        lowerKey === 'tx/rate' ||
        lowerKey === 'tx-rate' ||
        (lowerKey.includes('tx') && lowerKey.includes('rate'))
      ) {
        txRate = parseInt(conexion[key]) || 0;
        break;
      }
    }

    // Buscar campos de velocidad recibida
    for (const key of allKeys) {
      const lowerKey = key.toLowerCase();
      if (
        lowerKey === 'rx/rate' ||
        lowerKey === 'rx-rate' ||
        (lowerKey.includes('rx') && lowerKey.includes('rate'))
      ) {
        rxRate = parseInt(conexion[key]) || 0;
        break;
      }
    }

    logDebug('📊 Actualizando tráfico:', {
      txBytes,
      rxBytes,
      txRate,
      rxRate,
    });

    // Actualizar elementos DOM - Datos dentro del icono
    const txBytesData = this.formatBytesSeparated(txBytes);
    $('#pppoe-tx-bytes-icon').html(
      `<div style="font-size: 18px;">${txBytesData.value}</div><div style="font-size: 13px; margin-top: 2px;">${txBytesData.unit}</div>`
    );

    const rxBytesData = this.formatBytesSeparated(rxBytes);
    $('#pppoe-rx-bytes-icon').html(
      `<div style="font-size: 18px;">${rxBytesData.value}</div><div style="font-size: 13px; margin-top: 2px;">${rxBytesData.unit}</div>`
    );
    $('#pppoe-uptime').html('<strong>' + this.formatUptime(conexion.uptime) + '</strong>');

    // Actualizar velocidad en los títulos usando tx/rate y rx/rate
    const $txLabel = $('#pppoe-tx-label');
    const $rxLabel = $('#pppoe-rx-label');

    // Actualizar velocidades siempre, mostrando "Calculando..." si es 0
    // Actualizar etiquetas (solo el título)
    $txLabel.html('<strong>Descarga</strong>');
    $rxLabel.html('<strong>Subida</strong>');

    // Actualizar velocidades en badges separados
    const $txRateDisplay = $('#pppoe-tx-rate-display');
    const $rxRateDisplay = $('#pppoe-rx-rate-display');

    // Siempre mostrar velocidad, incluso si es 0
    const txSpeed = this.formatMbpsSeparated(txRate);
    if (txRate > 0) {
      $txRateDisplay.html(
        `<strong class="text-success" style="font-size: 1.6em; font-weight: 700; display: block; text-align: center;">${txSpeed.value}</strong><span class="text-success" style="font-size: 0.9em; display: block; margin-top: 2px; text-align: center;">${txSpeed.unit}</span>`
      );
    } else {
      $txRateDisplay.html(
        `<strong class="text-muted" style="font-size: 1.6em; font-weight: 700; display: block; text-align: center;">${txSpeed.value}</strong><span class="text-muted" style="font-size: 0.9em; display: block; margin-top: 2px; text-align: center;">${txSpeed.unit}</span>`
      );
    }

    const rxSpeed = this.formatMbpsSeparated(rxRate);
    if (rxRate > 0) {
      $rxRateDisplay.html(
        `<strong class="text-danger" style="font-size: 1.6em; font-weight: 700; display: block; text-align: center;">${rxSpeed.value}</strong><span class="text-danger" style="font-size: 0.9em; display: block; margin-top: 2px; text-align: center;">${rxSpeed.unit}</span>`
      );
    } else {
      $rxRateDisplay.html(
        `<strong class="text-muted" style="font-size: 1.6em; font-weight: 700; display: block; text-align: center;">${rxSpeed.value}</strong><span class="text-muted" style="font-size: 0.9em; display: block; margin-top: 2px; text-align: center;">${rxSpeed.unit}</span>`
      );
    }

    logDebug('🔄 Velocidades actualizadas:', {
      txRate: txRate > 0 ? this.formatSpeed(txRate) : '0',
      rxRate: rxRate > 0 ? this.formatSpeed(rxRate) : '0',
      txRateRaw: txRate,
      rxRateRaw: rxRate,
    });

    // Actualizar gráfico de tráfico en tiempo real
    this.updateTrafficChart(txRate, rxRate);
  };

  PPPoeDetailDrawer.prototype.startRealTimeUpdates = function () {
    const self = this;

    // Limpiar intervalo anterior si existe
    if (this.updateInterval) {
      clearInterval(this.updateInterval);
    }

    // Verificar que tengamos los datos necesarios antes de iniciar
    if (!self.routerId || !self.sessionId) {
      logError('❌ No se puede iniciar actualizaciones: faltan routerId o sessionId', {
        routerId: self.routerId,
        sessionId: self.sessionId,
      });
      return;
    }

    // Actualizar cada 1 segundo
    logDebug('🔄 Iniciando intervalo de actualización...', {
      routerId: self.routerId,
      sessionId: self.sessionId,
      isOpen: self.isOpen,
      isClosing: self.isClosing,
    });

    // Ejecutar primera actualización inmediatamente (después de un pequeño delay)
    setTimeout(function () {
      if (!self.isClosing && self.isOpen && self.routerId && self.sessionId) {
        const url = `/red/routers/${self.routerId}/conexiones-pppoe/${encodeURIComponent(
          self.sessionId
        )}`;
        logDebug('🔄 Primera actualización inmediata...', url);

        const firstRequestStartTime = Date.now();
        const firstRequest = $.ajax({
          url: url,
          method: 'GET',
          timeout: 5000,
          success: function (response) {
            // Verificar que esta petición aún sea la activa
            if (self.activeAjaxRequest !== firstRequest) {
              return;
            }

            if (!self.isClosing && response.success && response.conexion) {
              self.updateTraffic(response.conexion);
              logDebug('✅ Primera actualización completada');
            }
            if (self.activeAjaxRequest === firstRequest) {
              self.activeAjaxRequest = null;
            }
          },
          error: function (xhr, status, error) {
            // Verificar que esta petición aún sea la activa
            if (self.activeAjaxRequest !== firstRequest) {
              return;
            }

            if (status !== 'abort' && xhr.statusText !== 'abort' && !self.isClosing) {
              logError('❌ Error en primera actualización:', {
                status: xhr.status,
                statusText: xhr.statusText,
                error: error,
              });
            }
            if (self.activeAjaxRequest === firstRequest) {
              self.activeAjaxRequest = null;
            }
          },
        });

        self.activeAjaxRequest = firstRequest;
        self.activeAjaxRequest._startTime = firstRequestStartTime;
      }
    }, 200);

    this.updateInterval = setInterval(function () {
      // Verificación PRIMERA y más importante: estado de cierre
      // Solo verificar isClosing, no isOpen (puede estar en proceso de apertura)
      if (self.isClosing) {
        logDebug('⏸️ Deteniendo actualizaciones - drawer cerrándose');
        if (self.updateInterval) {
          const intervalToClear = self.updateInterval;
          self.updateInterval = null;
          clearInterval(intervalToClear);
        }
        // Cancelar cualquier petición pendiente
        if (self.activeAjaxRequest) {
          try {
            self.activeAjaxRequest.abort();
          } catch (e) {
            // Ignorar errores al cancelar
          }
          self.activeAjaxRequest = null;
        }
        return;
      }

      // Verificar que isOpen esté en true
      if (!self.isOpen) {
        logDebug('⏸️ Drawer no está abierto aún, esperando...', {
          isOpen: self.isOpen,
          isClosing: self.isClosing,
        });
        return;
      }

      // Verificar que tengamos los datos necesarios
      if (!self.routerId || !self.sessionId) {
        logDebug('⏸️ Faltan datos necesarios para actualizar:', {
          routerId: self.routerId,
          sessionId: self.sessionId,
        });
        return;
      }

      // Si hay una petición activa, esperar a que termine antes de hacer una nueva
      // Esto evita que se ignoren respuestas válidas
      // Solo cancelar si la petición lleva más de 3 segundos (timeout es 5 segundos)
      if (self.activeAjaxRequest) {
        const requestAge = Date.now() - (self.activeAjaxRequest._startTime || Date.now());
        if (requestAge > 3000) {
          logDebug('⏳ Petición anterior tardando demasiado (>3s), cancelando...', {
            age: requestAge,
          });
          try {
            self.activeAjaxRequest.abort();
          } catch (e) {
            // Ignorar errores al cancelar
          }
          self.activeAjaxRequest = null;
          // Continuar para hacer una nueva petición
        } else {
          // Petición aún en curso y no ha tardado demasiado, esperar
          return; // Saltar este ciclo, la siguiente iteración intentará de nuevo
        }
      }

      const url = `/red/routers/${self.routerId}/conexiones-pppoe/${encodeURIComponent(
        self.sessionId
      )}`;
      logDebug('🔄 Actualizando tráfico...', url);

      // Guardar referencia a la petición AJAX ANTES de crearla
      const ajaxRequest = $.ajax({
        url: url,
        method: 'GET',
        timeout: 5000, // Timeout de 5 segundos
        success: function (response) {
          // Verificar que no se esté cerrando
          if (self.isClosing) {
            logDebug('⏸️ Ignorando respuesta - drawer cerrándose');
            if (self.activeAjaxRequest === ajaxRequest) {
              self.activeAjaxRequest = null;
            }
            return;
          }

          // Verificar que esta petición aún sea la activa
          if (self.activeAjaxRequest !== ajaxRequest) {
            logDebug('⏸️ Ignorando respuesta - petición ya no es la activa');
            return;
          }

          logDebug('✅ Respuesta recibida:', response);
          if (response.success && response.conexion) {
            // Resetear contador de errores en caso de éxito
            self.consecutiveErrors = 0;
            // Ocultar mensaje de error si existe
            $('#pppoe-drawer-body .connection-error-msg').remove();

            // Actualizar directamente si no se está cerrando
            if (!self.isClosing) {
              self.updateTraffic(response.conexion);
              logDebug('✅ Tráfico actualizado');
            } else {
              logDebug('⏸️ No se actualiza - drawer cerrándose durante procesamiento');
            }
          } else {
            logWarn('⚠️ Respuesta no exitosa:', response);
            self.consecutiveErrors++;
          }

          // Limpiar referencia después de procesar
          if (self.activeAjaxRequest === ajaxRequest) {
            self.activeAjaxRequest = null;
          }
        },
        error: function (xhr, status, error) {
          // Verificar que esta petición aún sea la activa
          if (self.activeAjaxRequest !== ajaxRequest) {
            return; // Ignorar si ya no es la petición activa
          }

          // Ignorar errores si se canceló la petición o el drawer se está cerrando
          if (status === 'abort' || xhr.statusText === 'abort' || self.isClosing) {
            // No loggear si es un abort normal
            if (self.activeAjaxRequest === ajaxRequest) {
              self.activeAjaxRequest = null;
            }
            return;
          }

          // Incrementar contador de errores consecutivos
          self.consecutiveErrors++;

          // Si hay demasiados errores consecutivos, pausar las actualizaciones
          if (self.consecutiveErrors >= self.maxConsecutiveErrors) {
            logWarn(
              `⏸️ Demasiados errores consecutivos (${self.consecutiveErrors}), pausando actualizaciones...`
            );
            if (self.updateInterval) {
              clearInterval(self.updateInterval);
              self.updateInterval = null;
            }
            // Mostrar mensaje al usuario
            const $body = $('#pppoe-drawer-body');
            const errorMsg = $body.find('.connection-error-msg');
            if (errorMsg.length === 0) {
              $body.prepend(`
                <div class="alert alert-warning alert-dismissible connection-error-msg m-3">
                  <button type="button" class="close" data-dismiss="alert">&times;</button>
                  <i class="fas fa-exclamation-triangle mr-2"></i>
                  <strong>Error de conexión:</strong> Se han detectado múltiples errores de conexión.
                  Las actualizaciones en tiempo real se han pausado. Intenta cerrar y reabrir el detalle.
                </div>
              `);
            }
            if (self.activeAjaxRequest === ajaxRequest) {
              self.activeAjaxRequest = null;
            }
            return;
          }

          // Mostrar error solo si no se está cerrando y no es un abort
          if (!self.isClosing) {
            logError('❌ Error al actualizar tráfico en tiempo real:', {
              status: xhr.status,
              statusText: xhr.statusText,
              error: error,
              responseText: xhr.responseText,
              consecutiveErrors: self.consecutiveErrors,
            });
          }

          // Limpiar referencia
          if (self.activeAjaxRequest === ajaxRequest) {
            self.activeAjaxRequest = null;
          }
        },
        complete: function () {
          // Limpiar referencia solo si esta petición es la activa
          if (self.activeAjaxRequest === ajaxRequest) {
            self.activeAjaxRequest = null;
          }
        },
      });

      // Guardar referencia DESPUÉS de crear la petición
      self.activeAjaxRequest = ajaxRequest;
      // Guardar tiempo de inicio para detectar peticiones que tardan demasiado
      self.activeAjaxRequest._startTime = Date.now();
    }, 1000); // Actualizar cada 1 segundo

    logDebug('✅ Actualización en tiempo real iniciada - intervalo:', this.updateInterval);
  };

  PPPoeDetailDrawer.prototype.initTrafficChart = function () {
    const self = this;

    // Verificar que Chart.js esté disponible (puede estar en window.Chart o Chart)
    const ChartLib =
      typeof Chart !== 'undefined'
        ? Chart
        : typeof window !== 'undefined' && window.Chart
        ? window.Chart
        : null;

    if (!ChartLib) {
      logWarn('⚠️ Chart.js no está disponible, el gráfico no se mostrará');
      // Intentar esperar un poco más por si Chart.js se carga después
      setTimeout(function () {
        if (typeof Chart !== 'undefined' || (typeof window !== 'undefined' && window.Chart)) {
          self.initTrafficChart();
        }
      }, 500);
      return;
    }

    // Destruir gráfico anterior si existe
    if (this.charts.traffic) {
      try {
        this.charts.traffic.destroy();
      } catch (e) {
        logWarn('Error al destruir gráfico anterior:', e);
      }
      this.charts.traffic = null;
    }

    // Resetear historial
    this.trafficHistory = {
      labels: [],
      txData: [],
      rxData: [],
      maxPoints: 60, // 60 puntos = 1 minuto de datos a 1 segundo por punto
    };

    // Función para inicializar el gráfico con reintentos
    const initChart = function (attempts) {
      attempts = attempts || 0;
      const canvas = document.getElementById('pppoe-traffic-chart');
      if (!canvas) {
        if (attempts < 10) {
          setTimeout(function () {
            initChart(attempts + 1);
          }, 100);
        } else {
          logWarn('⚠️ Canvas del gráfico no encontrado después de múltiples intentos');
        }
        return;
      }

      // Verificar que el canvas tenga dimensiones
      const rect = canvas.getBoundingClientRect();
      if (rect.width === 0 || rect.height === 0) {
        if (attempts < 10) {
          setTimeout(function () {
            initChart(attempts + 1);
          }, 100);
        } else {
          logWarn('⚠️ Canvas no tiene dimensiones válidas');
        }
        return;
      }

      const ctx = canvas.getContext('2d');

      self.charts.traffic = new ChartLib(ctx, {
        type: 'line',
        data: {
          labels: self.trafficHistory.labels,
          datasets: [
            {
              label: 'TX (Envío)',
              data: self.trafficHistory.txData,
              borderColor: 'rgb(54, 162, 235)',
              backgroundColor: 'rgba(54, 162, 235, 0.1)',
              tension: 0.4,
              fill: true,
            },
            {
              label: 'RX (Recepción)',
              data: self.trafficHistory.rxData,
              borderColor: 'rgb(75, 192, 192)',
              backgroundColor: 'rgba(75, 192, 192, 0.1)',
              tension: 0.4,
              fill: true,
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          aspectRatio: 2,
          interaction: {
            intersect: false,
            mode: 'index',
          },
          plugins: {
            legend: {
              display: true,
              position: 'top',
            },
            tooltip: {
              callbacks: {
                label: function (context) {
                  const value = context.parsed.y;
                  return (
                    context.dataset.label +
                    ': ' +
                    self.formatMbps(value) +
                    ' (' +
                    self.formatSpeed(value) +
                    ')'
                  );
                },
              },
            },
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                callback: function (value) {
                  return self.formatMbps(value);
                },
              },
              title: {
                display: true,
                text: 'Velocidad (B/s)',
              },
            },
            x: {
              title: {
                display: true,
                text: 'Tiempo',
              },
            },
          },
          animation: {
            duration: 0, // Sin animación para actualizaciones en tiempo real
          },
        },
      });

      logDebug('✅ Gráfico de tráfico inicializado');
    };

    // Iniciar con un pequeño delay para asegurar que el DOM esté listo
    setTimeout(function () {
      initChart(0);
    }, 200);

    // Iniciar con un pequeño delay para asegurar que el DOM esté listo
    setTimeout(function () {
      initChart();
    }, 200);
  };

  PPPoeDetailDrawer.prototype.updateTrafficChart = function (txRate, rxRate) {
    // Verificar que el drawer no se esté cerrando antes de actualizar el gráfico
    if (this.isClosing) {
      return;
    }

    if (!this.charts.traffic) {
      return;
    }

    const now = new Date();
    const timeLabel =
      now.getHours().toString().padStart(2, '0') +
      ':' +
      now.getMinutes().toString().padStart(2, '0') +
      ':' +
      now.getSeconds().toString().padStart(2, '0');

    // Agregar nuevos datos
    this.trafficHistory.labels.push(timeLabel);
    this.trafficHistory.txData.push(txRate || 0);
    this.trafficHistory.rxData.push(rxRate || 0);

    // Limitar a maxPoints
    if (this.trafficHistory.labels.length > this.trafficHistory.maxPoints) {
      this.trafficHistory.labels.shift();
      this.trafficHistory.txData.shift();
      this.trafficHistory.rxData.shift();
    }

    // Actualizar gráfico
    this.charts.traffic.data.labels = this.trafficHistory.labels;
    this.charts.traffic.data.datasets[0].data = this.trafficHistory.txData;
    this.charts.traffic.data.datasets[1].data = this.trafficHistory.rxData;

    // Actualizar escala Y automáticamente
    // Nota: Los valores están en bytes/segundo, pero el gráfico mostrará en Mbps
    const allValues = [...this.trafficHistory.txData, ...this.trafficHistory.rxData];
    const maxValue = Math.max(...allValues, 0);
    if (maxValue > 0) {
      // Mantener los valores en bytes/segundo para el gráfico
      // La conversión a Mbps se hace en el formateo de los ticks
      this.charts.traffic.options.scales.y.max = Math.ceil(maxValue * 1.2);
    } else {
      // Si no hay valores, establecer un máximo por defecto para que el gráfico se muestre
      this.charts.traffic.options.scales.y.max = 1000000; // 1 MB/s por defecto
    }

    try {
      this.charts.traffic.update('none'); // 'none' para actualización sin animación
    } catch (e) {
      logWarn('Error al actualizar gráfico:', e);
    }
  };

  PPPoeDetailDrawer.prototype.formatBytes = function (bytes) {
    if (!bytes || bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i];
  };

  PPPoeDetailDrawer.prototype.formatBytesSeparated = function (bytes) {
    if (!bytes || bytes === 0) {
      return { value: '0', unit: 'B' };
    }
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return {
      value: Math.round((bytes / Math.pow(k, i)) * 100) / 100,
      unit: sizes[i],
    };
  };

  PPPoeDetailDrawer.prototype.formatNumber = function (num) {
    if (!num || num === 0) return '0';
    return parseInt(num).toLocaleString('es-PE');
  };

  PPPoeDetailDrawer.prototype.formatSpeed = function (bytesPerSecond) {
    if (!bytesPerSecond || bytesPerSecond <= 0) return '0 B/s';
    const k = 1024;
    const sizes = ['B/s', 'KB/s', 'MB/s', 'GB/s'];
    const i = Math.floor(Math.log(bytesPerSecond) / Math.log(k));
    const speed = bytesPerSecond / Math.pow(k, i);
    return Math.round(speed * 100) / 100 + ' ' + sizes[i];
  };

  PPPoeDetailDrawer.prototype.formatMbps = function (bytesPerSecond) {
    if (!bytesPerSecond || bytesPerSecond <= 0) return '0 Mbps';
    // Convertir bytes/segundo a bits/segundo (multiplicar por 8)
    // Luego convertir a Mbps (dividir por 1,000,000)
    const bitsPerSecond = bytesPerSecond * 8;
    const mbps = bitsPerSecond / 1000000;

    if (mbps >= 1000) {
      // Si es mayor a 1000 Mbps, mostrar en Gbps
      const gbps = mbps / 1000;
      return Math.round(gbps * 100) / 100 + ' Gbps';
    } else if (mbps >= 1) {
      // Mostrar en Mbps con 2 decimales
      return Math.round(mbps * 100) / 100 + ' Mbps';
    } else {
      // Si es menor a 1 Mbps, mostrar en Kbps
      const kbps = bitsPerSecond / 1000;
      return Math.round(kbps * 100) / 100 + ' Kbps';
    }
  };

  PPPoeDetailDrawer.prototype.formatMbpsSeparated = function (bytesPerSecond) {
    if (!bytesPerSecond || bytesPerSecond <= 0) {
      return { value: '0', unit: 'Kbps' };
    }
    // Convertir bytes/segundo a bits/segundo (multiplicar por 8)
    const bitsPerSecond = bytesPerSecond * 8;
    const mbps = bitsPerSecond / 1000000;

    if (mbps >= 1000) {
      // Si es mayor a 1000 Mbps, mostrar en Gbps
      const gbps = mbps / 1000;
      return {
        value: Math.round(gbps * 100) / 100,
        unit: 'Gbps',
      };
    } else if (mbps >= 1) {
      // Mostrar en Mbps con 2 decimales
      return {
        value: Math.round(mbps * 100) / 100,
        unit: 'Mbps',
      };
    } else {
      // Si es menor a 1 Mbps, mostrar en Kbps
      const kbps = bitsPerSecond / 1000;
      return {
        value: Math.round(kbps * 100) / 100,
        unit: 'Kbps',
      };
    }
  };

  PPPoeDetailDrawer.prototype.formatUptime = function (uptime) {
    if (!uptime || uptime === '-' || typeof uptime !== 'string') {
      return uptime || '-';
    }

    try {
      // Parsear formato RouterOS: 1w2d15h26m4s
      const weekMatch = uptime.match(/(\d+)w/);
      const dayMatch = uptime.match(/(\d+)d/);
      const hourMatch = uptime.match(/(\d+)h/);
      const minuteMatch = uptime.match(/(\d+)m/);
      const secondMatch = uptime.match(/(\d+)s/);

      const weeks = weekMatch ? parseInt(weekMatch[1], 10) : 0;
      const days = dayMatch ? parseInt(dayMatch[1], 10) : 0;
      const hours = hourMatch ? parseInt(hourMatch[1], 10) : 0;
      const minutes = minuteMatch ? parseInt(minuteMatch[1], 10) : 0;
      const seconds = secondMatch ? parseInt(secondMatch[1], 10) : 0;

      // Convertir semanas a días
      const totalDays = weeks * 7 + days;

      // Construir string legible
      const parts = [];
      if (totalDays > 0) {
        parts.push(`${totalDays} ${totalDays === 1 ? 'día' : 'días'}`);
      }
      if (hours > 0) {
        parts.push(`${hours} ${hours === 1 ? 'hora' : 'horas'}`);
      }
      if (minutes > 0) {
        parts.push(`${minutes} ${minutes === 1 ? 'minuto' : 'minutos'}`);
      }
      if (seconds > 0 || parts.length === 0) {
        parts.push(`${seconds} ${seconds === 1 ? 'segundo' : 'segundos'}`);
      }

      return parts.join(', ');
    } catch (e) {
      logError('Error al formatear uptime:', e, 'uptime:', uptime);
      return uptime; // Devolver original si hay error
    }
  };

  // Inicializar cuando jQuery esté listo
  function initDrawer() {
    if (typeof $ !== 'undefined' && typeof $.ajax !== 'undefined') {
      $(document).ready(function () {
        drawerInstance = new PPPoeDetailDrawer();
        drawerInstance.init();
        window.pppoeDetailDrawer = drawerInstance;
        logDebug('✅ Drawer PPPoE inicializado con jQuery');
      });
    } else {
      // jQuery no está disponible aún, esperar un poco más
      setTimeout(initDrawer, 100);
    }
  }

  // Agregar event delegation para botones de abrir ONU
  // Se registra dentro del IIFE para asegurar que jQuery esté disponible
  function registerOnuClickHandler() {
    const $ =
      typeof jQuery !== 'undefined' ? jQuery : typeof window.$ !== 'undefined' ? window.$ : null;
    if (!$) {
      logWarn('⚠️ jQuery no disponible para registrar handler de clic ONU');
      return;
    }

    logDebug('🔧 Registrando event handler para .abrir-onu-btn...');

    // Remover handler anterior si existe para evitar duplicados
    $(document).off('click', '.abrir-onu-btn');

    // Registrar nuevo handler
    $(document).on('click', '.abrir-onu-btn', function (e) {
      e.preventDefault();
      e.stopPropagation();
      const $btn = $(this);
      const routerId = $btn.data('router-id');
      const ip = $btn.data('ip');

      logDebug('🖱️🖱️🖱️ BOTÓN ABRIR ONU CLICKEADO:', {
        routerId,
        ip,
        element: $btn[0],
      });

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

    logDebug('✅✅✅ Event handler para .abrir-onu-btn registrado correctamente');
  }

  // Registrar el handler cuando jQuery esté disponible
  // Usar $(document).ready para asegurar que jQuery esté completamente cargado
  if (typeof jQuery !== 'undefined' || typeof window.$ !== 'undefined') {
    const $ = typeof jQuery !== 'undefined' ? jQuery : window.$;
    $(document).ready(function () {
      logDebug('📋 Document ready - registrando handler ONU');
      registerOnuClickHandler();
    });
  } else {
    // Esperar a que jQuery esté disponible
    logDebug('⏳ Esperando jQuery para registrar handler ONU...');
    const checkJQuery = setInterval(function () {
      if (typeof jQuery !== 'undefined' || typeof window.$ !== 'undefined') {
        clearInterval(checkJQuery);
        const $ = typeof jQuery !== 'undefined' ? jQuery : window.$;
        $(document).ready(function () {
          logDebug('📋 Document ready (después de esperar) - registrando handler ONU');
          registerOnuClickHandler();
        });
      }
    }, 100);

    // Timeout después de 5 segundos
    setTimeout(function () {
      clearInterval(checkJQuery);
    }, 5000);
  }

  // Iniciar cuando el script se carga
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initDrawer);
  } else {
    // DOM ya está listo, esperar a jQuery
    setTimeout(initDrawer, 50);
  }
})(typeof jQuery !== 'undefined' ? jQuery : typeof window.$ !== 'undefined' ? window.$ : null);
