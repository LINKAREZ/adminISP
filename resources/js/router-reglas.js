/**
 * Gestión de reglas del router usando jQuery/Bootstrap
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
    logDebug('⏳ [router-reglas] Esperando jQuery...', {
      'jQuery': typeof jQuery,
      'window.$': typeof window.$,
      'window.jQuery': typeof window.jQuery
    });

    if (typeof jQuery !== 'undefined' && typeof window.$ !== 'undefined') {
      const $ = window.jQuery || window.$;
      logDebug('✅ [router-reglas] jQuery disponible, ejecutando callback');
      callback($);
    } else if (typeof window.jQuery !== 'undefined') {
      window.$ = window.jQuery;
      logDebug('✅ [router-reglas] jQuery disponible (window.jQuery), ejecutando callback');
      callback(window.jQuery);
    } else {
      logDebug('⏳ [router-reglas] jQuery no disponible aún, reintentando en 50ms...');
      setTimeout(() => waitForJQuery(callback), 50);
    }
  }

  logDebug('🔧 [router-reglas] Inicializando, esperando jQuery...');
  waitForJQuery(function ($) {
    logDebug('✅ [router-reglas] jQuery recibido en callback, continuando...');

  let reglasData = {
    routerId: null,
    reglas: [],
    cargando: false,
    error: null,
    mostrarFormulario: false,
    creando: false,
    errorCrear: null,
    exportando: null,
    formulario: {
      nombre: '',
      tipo: 'firewall',
      configuracion: {
        source_address_list: '',
        chain: 'forward',
        comment: '',
        disabled: false,
        list: '',
        address: '',
      },
      activo: true,
      notas: '',
    },
  };

  function initReglas(routerId, reglasIniciales, conexionExitosa) {
    reglasData.routerId = routerId;
    reglasData.reglas = reglasIniciales || [];
    reglasData.conexionExitosa = conexionExitosa;

    // Renderizar reglas iniciales
    if (reglasData.reglas.length > 0) {
      renderReglas();
    }

    // Event listeners
    $(document).on('click', '#toggle-formulario-regla', function () {
      toggleFormulario();
    });

    $(document).on('click', '#cancelar-formulario-regla', function () {
      cancelarFormulario();
    });

    $(document).on('submit', '#form-crear-regla', function (e) {
      e.preventDefault();
      crearRegla();
    });

    $(document).on('change', '#regla-tipo', function () {
      const tipo = $(this).val();
      $('.config-tipo').hide();
      $(`.config-${tipo}`).show();
    });

    $(document).on('click', '.btn-cargar-reglas', function () {
      cargarReglas();
    });

    $(document).on('click', '.btn-exportar-regla', function () {
      const reglaId = $(this).data('regla-id');
      exportarRegla(reglaId);
    });

    $(document).on('click', '.btn-eliminar-regla', function () {
      const reglaId = $(this).data('regla-id');
      eliminarRegla(reglaId);
    });

    // Inicializar visibilidad del formulario
    if (reglasData.mostrarFormulario) {
      $('#formulario-regla-container').show();
    }

    // Configurar tipo inicial
    $('#regla-tipo').trigger('change');

    logDebug('✅ Gestión de reglas inicializada', {
      routerId: reglasData.routerId,
      reglasIniciales: reglasData.reglas.length,
    });
  }

  function toggleFormulario() {
    reglasData.mostrarFormulario = !reglasData.mostrarFormulario;
    if (reglasData.mostrarFormulario) {
      $('#formulario-regla-container').slideDown();
      $('#toggle-formulario-regla').html(
        '<i class="fas fa-times mr-1"></i> Cancelar'
      );
    } else {
      $('#formulario-regla-container').slideUp();
      $('#toggle-formulario-regla').html(
        '<i class="fas fa-plus mr-1"></i> Agregar Regla'
      );
      cancelarFormulario();
    }
  }

  function cancelarFormulario() {
    reglasData.formulario = {
      nombre: '',
      tipo: 'firewall',
      configuracion: {
        source_address_list: '',
        chain: 'forward',
        comment: '',
        disabled: false,
        list: '',
        address: '',
      },
      activo: true,
      notas: '',
    };
    $('#form-crear-regla')[0].reset();
    $('#regla-tipo').trigger('change');
    reglasData.errorCrear = null;
    $('#error-crear-regla').hide();
  }

  async function crearRegla() {
    reglasData.creando = true;
    reglasData.errorCrear = null;
    $('#error-crear-regla').hide();
    $('#btn-crear-regla').prop('disabled', true).html(
      '<i class="fas fa-spinner fa-spin mr-1"></i> Creando...'
    );

    const formData = {
      nombre: $('#regla-nombre').val(),
      tipo: $('#regla-tipo').val(),
      configuracion: {
        source_address_list: $('#config-source-address-list').val() || '',
        chain: $('#config-chain').val() || 'forward',
        comment: $('#config-comment').val() || '',
        disabled: $('#config-disabled').prop('checked') || false,
        list: $('#config-list').val() || '',
        address: $('#config-address').val() || '',
      },
      activo: $('#regla-activo').prop('checked'),
      notas: $('#regla-notas').val() || '',
    };

    try {
      const response = await fetch(`/red/routers/${reglasData.routerId}/reglas`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
        },
        body: JSON.stringify(formData),
      });

      const data = await response.json();
      if (data.success) {
        await cargarReglas();
        toggleFormulario();
        mostrarToast('Regla creada correctamente', 'success');
      } else {
        reglasData.errorCrear = data.message || 'Error al crear regla';
        $('#error-crear-regla').text(reglasData.errorCrear).show();
      }
    } catch (e) {
      reglasData.errorCrear = 'Error al conectar con el servidor: ' + e.message;
      $('#error-crear-regla').text(reglasData.errorCrear).show();
      logError('Error al crear regla:', e);
    } finally {
      reglasData.creando = false;
      $('#btn-crear-regla').prop('disabled', false).html(
        '<i class="fas fa-save mr-1"></i> Crear Regla'
      );
    }
  }

  async function cargarReglas() {
    logDebug('🔄 Iniciando carga de reglas...');
    reglasData.cargando = true;
    reglasData.error = null;
    $('#reglas-loading').show();
    $('#reglas-error').hide();
    $('#reglas-content').hide();

    try {
      const response = await fetch(`/red/routers/${reglasData.routerId}/reglas`);
      const data = await response.json();
      logDebug('📦 Respuesta de cargarReglas:', data);

      if (data.success && data.reglas) {
        reglasData.reglas = data.reglas;
        logDebug('📋 Reglas actualizadas:', reglasData.reglas.map(r => ({id: r.id, nombre: r.nombre, exportado: r.exportado})));
        renderReglas();
        logDebug('✅ Render completado');
      } else {
        reglasData.error = data.message || 'Error al cargar reglas';
        $('#reglas-error').text(reglasData.error).show();
      }
    } catch (e) {
      reglasData.error = 'Error al conectar con el servidor';
      $('#reglas-error').text(reglasData.error).show();
      logError('❌ Error al cargar reglas:', e);
    } finally {
      reglasData.cargando = false;
      $('#reglas-loading').hide();
      if (!reglasData.error) {
        $('#reglas-content').show();
      }
    }
  }

  function renderReglas() {
    const $tableBody = $('#reglas-table-body');
    const $mobileList = $('#reglas-mobile-list');
    const $emptyMessage = $('#reglas-empty');

    $tableBody.empty();
    $mobileList.empty();

    if (reglasData.reglas.length === 0) {
      $emptyMessage.show();
      $tableBody.html(
        '<tr><td colspan="5" class="text-center py-5"><i class="fas fa-inbox fa-2x text-muted mb-2"></i><p class="small text-muted mb-0">No hay reglas registradas</p></td></tr>'
      );
      return;
    }

    $emptyMessage.hide();

    reglasData.reglas.forEach(function (regla) {
      // Vista desktop (tabla)
      const row = `
        <tr>
          <td>
            <div class="font-weight-bold">${regla.nombre || '-'}</div>
            ${regla.notas ? `<div class="small text-muted mt-1">${regla.notas}</div>` : ''}
          </td>
          <td><span class="small">${regla.tipo || '-'}</span></td>
          <td>
            <span class="badge badge-${regla.activo ? 'success' : 'secondary'}">
              ${regla.activo ? 'Activo' : 'Inactivo'}
            </span>
          </td>
          <td>
            <span class="badge badge-${regla.exportado ? 'info' : 'warning'}">
              ${regla.exportado ? 'Exportado' : 'Pendiente'}
            </span>
          </td>
          <td class="text-right">
            ${!regla.exportado ? `
            <button
              type="button"
              class="btn btn-sm btn-primary btn-exportar-regla"
              data-regla-id="${regla.id}"
              ${reglasData.exportando === regla.id ? 'disabled' : ''}
            >
              ${reglasData.exportando === regla.id
                ? '<i class="fas fa-spinner fa-spin"></i>'
                : '<i class="fas fa-upload"></i>'}
            </button>
            ` : ''}
            <button
              type="button"
              class="btn btn-sm btn-danger btn-eliminar-regla ${!regla.exportado ? 'ml-1' : ''}"
              data-regla-id="${regla.id}"
            >
              <i class="fas fa-trash"></i>
            </button>
          </td>
        </tr>
      `;
      $tableBody.append(row);

      // Vista móvil (cards)
      const card = `
        <div class="card mb-2">
          <div class="card-body p-2">
            <div class="font-weight-bold small">${regla.nombre || '-'}</div>
            <div class="small text-muted mt-1">Tipo: ${regla.tipo || '-'}</div>
            ${regla.notas ? `<div class="small text-muted mt-1">${regla.notas}</div>` : ''}
            <div class="mt-2">
              <span class="badge badge-${regla.activo ? 'success' : 'secondary'} mr-1">
                ${regla.activo ? 'Activo' : 'Inactivo'}
              </span>
              <span class="badge badge-${regla.exportado ? 'info' : 'warning'}">
                ${regla.exportado ? 'Exportado' : 'Pendiente'}
              </span>
            </div>
            <div class="mt-2">
              ${!regla.exportado ? `
              <button
                type="button"
                class="btn btn-sm btn-primary btn-exportar-regla"
                data-regla-id="${regla.id}"
                ${reglasData.exportando === regla.id ? 'disabled' : ''}
              >
                ${reglasData.exportando === regla.id
                  ? '<i class="fas fa-spinner fa-spin mr-1"></i> Exportando...'
                  : '<i class="fas fa-upload mr-1"></i> Exportar'}
              </button>
              ` : ''}
              <button
                type="button"
                class="btn btn-sm btn-danger btn-eliminar-regla ${!regla.exportado ? 'ml-1' : ''}"
                data-regla-id="${regla.id}"
              >
                <i class="fas fa-trash mr-1"></i> Eliminar
              </button>
            </div>
          </div>
        </div>
      `;
      $mobileList.append(card);
    });
  }

  async function exportarRegla(reglaId) {
    if (!confirm('¿Está seguro de exportar esta regla a MikroTik?')) {
      return;
    }

    logDebug('📤 Iniciando exportación de regla:', reglaId);

    // Guardar el estado anterior del botón
    const $btn = $(`.btn-exportar-regla[data-regla-id="${reglaId}"]`);
    const originalHtml = $btn.html();

    // Mostrar estado de carga
    reglasData.exportando = reglaId;
    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

    try {
      logDebug('📡 Enviando petición al servidor...');
      const response = await fetch(
        `/red/routers/${reglasData.routerId}/reglas/${reglaId}/exportar`,
        {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
          },
        }
      );

      logDebug('📥 Respuesta recibida, status:', response.status);
      const data = await response.json();
      logDebug('📦 Datos de respuesta:', data);

      // Limpiar estado de exportando inmediatamente
      reglasData.exportando = null;
      logDebug('🔄 Estado exportando limpiado');

      if (data.success) {
        logDebug('✅ Exportación exitosa, recargando reglas...');
        // Recargar reglas para obtener el estado actualizado
        await cargarReglas();
        logDebug('✅ Reglas recargadas:', reglasData.reglas);
        mostrarToast('Regla exportada correctamente', 'success');
      } else {
        logDebug('❌ Error en exportación:', data.message);
        // Restaurar botón en caso de error
        $btn.prop('disabled', false).html(originalHtml);
        renderReglas(); // Actualizar UI
        mostrarToast(data.message || 'Error al exportar regla', 'error');
      }
    } catch (e) {
      logError('❌ Excepción al exportar regla:', e);
      // Limpiar estado y restaurar botón en caso de error
      reglasData.exportando = null;
      $btn.prop('disabled', false).html(originalHtml);
      renderReglas(); // Actualizar UI
      mostrarToast('Error al conectar con el servidor', 'error');
    }
  }

  async function eliminarRegla(reglaId) {
    if (!confirm('¿Está seguro de eliminar esta regla?')) {
      return;
    }

    try {
      const response = await fetch(
        `/red/routers/${reglasData.routerId}/reglas/${reglaId}`,
        {
          method: 'DELETE',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            Accept: 'application/json',
          },
        }
      );

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
      }

      const data = await response.json();
      if (data.success) {
        await cargarReglas();
        mostrarToast('Regla eliminada correctamente', 'success');
      } else {
        mostrarToast(data.message || 'Error al eliminar regla', 'error');
      }
    } catch (e) {
      mostrarToast('Error al conectar con el servidor', 'error');
      logError('Error al eliminar regla:', e);
    }
  }

  function mostrarToast(mensaje, tipo) {
    // Usar toast de AdminLTE si está disponible
    if (typeof toastr !== 'undefined') {
      toastr[tipo === 'error' ? 'error' : 'success'](mensaje);
    } else {
      window.showAlert(mensaje, 'warning');
    }
  }

    // Exponer funciones globalmente
    window.initRouterReglas = initReglas;
  }); // Fin de waitForJQuery
})();
