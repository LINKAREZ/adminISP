/**
 * Inicialización estándar de DataTables para AdminLTE3
 * Configuración común para todas las tablas del proyecto
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

window.initDataTable = function (tableId, options = {}) {
  const $ = window.jQuery || window.$;

  if (typeof $ === 'undefined') {
    logWarn('jQuery no está disponible');
    return null;
  }

  // Verificar que DataTable esté disponible (múltiples formas)
  const hasDataTable =
    typeof window.DataTable !== 'undefined' ||
    (typeof $.fn !== 'undefined' && typeof $.fn.DataTable !== 'undefined') ||
    (typeof $.fn !== 'undefined' && typeof $.fn.dataTable !== 'undefined');

  if (!hasDataTable) {
    logWarn('DataTable no está disponible');
    return null;
  }

  const $table = $(`#${tableId}`);
  if (!$table.length) {
    logWarn(`Tabla con ID "${tableId}" no encontrada`);
    return null;
  }

  // Verificar que la tabla tenga contenido (no esté vacía)
  // Verificar también que tenga thead y tbody antes de continuar
  const $theadCheck = $table.find('thead');
  const $tbodyCheck = $table.find('tbody');

  if (
    !$table.html() ||
    $table.html().trim() === '' ||
    $theadCheck.length === 0 ||
    $tbodyCheck.length === 0
  ) {
    logWarn(
      `Tabla ${tableId} no está lista aún (contenido: ${!!$table.html()}, thead: ${
        $theadCheck.length
      }, tbody: ${$tbodyCheck.length}), esperando...`
    );
    // Reintentar después de un delay más largo
    const retryCount = options._retryCount || 0;
    if (retryCount < 10) {
      // Máximo 10 reintentos (2 segundos total)
      options._retryCount = retryCount + 1;
      setTimeout(function () {
        initDataTable(tableId, options);
      }, 200);
    } else {
      logError(`Tabla ${tableId} no se pudo inicializar después de múltiples reintentos`);
    }
    return null;
  }

  // Limpiar el contador de reintentos si existe
  delete options._retryCount;

  // Verificar si ya está inicializada - Si destroy está en las opciones, destruir primero
  if (
    typeof $.fn.DataTable !== 'undefined' &&
    typeof $.fn.DataTable.isDataTable === 'function' &&
    $.fn.DataTable.isDataTable(`#${tableId}`)
  ) {
    if (options.destroy === true) {
      logDebug(
        `DataTable ya inicializado para ${tableId}, destruyendo antes de reinicializar...`
      );
      $table.DataTable().destroy();
      $table.empty(); // Limpiar contenido
    } else {
      logDebug(`DataTable ya inicializado para ${tableId}`);
      return $table.DataTable();
    }
  }

  // Configuración por defecto
  const defaultOptions = {
    language: {
      search: 'Buscar:',
      lengthMenu: 'Mostrar _MENU_ registros',
      info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
      infoEmpty: 'Mostrando 0 a 0 de 0 registros',
      infoFiltered: '(filtrado de _MAX_ registros totales)',
      zeroRecords: 'No se encontraron registros',
      emptyTable: 'No hay datos disponibles en la tabla',
      paginate: {
        first: 'Primero',
        last: 'Último',
        next: 'Siguiente',
        previous: 'Anterior',
      },
      processing: 'Procesando...',
      loadingRecords: 'Cargando...',
    },
    pageLength: 25,
    lengthMenu: [
      [10, 25, 50, 100, -1],
      [10, 25, 50, 100, 'Todos'],
    ],
    responsive: true,
    order: [[0, 'asc']],
    dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
    processing: true,
    autoWidth: false,
    deferRender: false, // Renderizar inmediatamente el HTML
    columnDefs: [
      {
        targets: -1, // Última columna (acciones)
        orderable: false,
        searchable: false,
      },
    ],
  };

  // Fusionar opciones personalizadas (deep merge para columnDefs)
  const finalOptions = Object.assign({}, defaultOptions, options);

  // Si hay columnDefs personalizados, combinarlos con los por defecto
  if (options.columnDefs && defaultOptions.columnDefs) {
    finalOptions.columnDefs = [...defaultOptions.columnDefs, ...options.columnDefs];
  }

  // Asegurar que DataTables lea el HTML del DOM, no datos estructurados
  // Eliminar cualquier configuración que pueda hacer que DataTables espere datos estructurados
  if (!finalOptions.ajax && !finalOptions.data) {
    delete finalOptions.ajax;
    delete finalOptions.data;

    // CRÍTICO: Eliminar columns si está definido, para que lea del DOM HTML directamente
    if (finalOptions.columns !== undefined) {
      delete finalOptions.columns;
    }

    // Agregar configuración para leer correctamente el HTML
    finalOptions.retrieve = true; // Permitir reinicialización

    // Asegurar que no se use serverSide (que requiere datos estructurados)
    if (finalOptions.serverSide !== undefined && finalOptions.serverSide === true) {
      logWarn(
        `Warning: serverSide está habilitado pero no hay ajax configurado para ${tableId}`
      );
      delete finalOptions.serverSide;
    }
  }

  // Verificar que la tabla tenga estructura válida antes de inicializar
  // Usar find() para buscar en todos los niveles (puede estar dentro de div.table-responsive)
  const $thead = $table.find('thead');
  const $tbody = $table.find('tbody');

  if ($thead.length === 0 || $tbody.length === 0) {
    logWarn(
      `Tabla ${tableId} no tiene thead o tbody válido. Thead: ${$thead.length}, Tbody: ${$tbody.length}`
    );
    logWarn(
      `Contenido de la tabla:`,
      $table[0] ? $table[0].innerHTML.substring(0, 200) : 'tabla no encontrada'
    );
    return null;
  }

  // Contar columnas en el header
  const columnCount = $thead.children('tr').first().children('th, td').length;

  if (columnCount === 0) {
    logWarn(`Tabla ${tableId} no tiene columnas definidas en el header`);
    return null;
  }

  // Verificar que todas las filas tengan el mismo número de celdas (excepto filas con colspan)
  let rowError = false;
  $tbody.find('tr').each(function () {
    const $row = $(this);
    const cellCount = $row.find('td').length;
    const hasColspan = $row.find('td[colspan]').length > 0;

    // Si no tiene colspan, debe tener el mismo número de celdas que columnas
    if (!hasColspan && cellCount !== columnCount) {
      logError(
        `Error en tabla ${tableId}: Fila tiene ${cellCount} celdas pero se esperan ${columnCount} columnas`
      );
      rowError = true;
      return false; // Salir del loop
    }
  });

  if (rowError) {
    logError(
      `No se puede inicializar DataTable para ${tableId} debido a estructura de filas incorrecta`
    );
    return null;
  }

  // Si solo hay una fila con colspan (estado vacío), no inicializar DataTable
  const $rows = $tbody.find('tr');
  if ($rows.length === 1) {
    const $onlyRow = $rows.first();
    const hasColspan = $onlyRow.find('td[colspan]').length > 0;
    const cellCount = $onlyRow.find('td').length;
    if (hasColspan && cellCount === 1) {
      logDebug(
        `Tabla ${tableId} tiene solo fila de estado vacío, omitiendo DataTable`
      );
      return null;
    }
  }

  // Inicializar DataTable
  try {
    const dataTable = $table.DataTable(finalOptions);
    logDebug(`✅ DataTable inicializado para ${tableId} con ${columnCount} columnas`);
    return dataTable;
  } catch (error) {
    logError(`Error al inicializar DataTable para ${tableId}:`, error);
    logError('Detalles del error:', error.message, error.stack);
    return null;
  }
};

/**
 * Inicializar DataTables cuando el DOM esté listo y jQuery esté disponible
 */
function initDataTablesAuto() {
  // Verificar que jQuery esté disponible - verificación más robusta
  const jQueryAvailable = (typeof jQuery !== 'undefined' && jQuery !== null) ||
                         (typeof window.jQuery !== 'undefined' && window.jQuery !== null) ||
                         (typeof window.$ !== 'undefined' && window.$ !== null);

  if (!jQueryAvailable) {
    logWarn('⏳ [datatables-init] jQuery no está disponible aún para DataTables, esperando...');
    setTimeout(initDataTablesAuto, 100);
    return;
  }

  const $ = window.jQuery || window.$ || jQuery;

  // Verificar que jQuery esté realmente disponible y sea una función
  if (typeof $ === 'undefined' || $ === null || typeof $ !== 'function') {
    logError('❌ [datatables-init] jQuery no está disponible o no es una función en initDataTablesAuto');
    setTimeout(initDataTablesAuto, 100);
    return;
  }

  logDebug('✅ [datatables-init] jQuery disponible en initDataTablesAuto, buscando tablas...');

  // Auto-inicializar tablas con atributo data-datatable="true"
  $('table[data-datatable="true"]').each(function () {
      const tableId = $(this).attr('id');
      if (tableId) {
        // Verificar que la tabla tenga contenido y estructura válida primero
        const $currentTable = $(this);
        const $theadCheck = $currentTable.find('thead');
        const $tbodyCheck = $currentTable.find('tbody');

      if (
        !$currentTable.html() ||
        $currentTable.html().trim() === '' ||
        $theadCheck.length === 0 ||
        $tbodyCheck.length === 0
      ) {
        logWarn(`Tabla ${tableId} no está lista aún, esperando...`);
        // Reintentar después de un delay
        const retryCount = (options._retryCount || 0) + 1;
        if (retryCount < 10) {
          options._retryCount = retryCount;
          setTimeout(function () {
            const $retryTable = $('#' + tableId);
            if ($retryTable.length) {
              const $retryThead = $retryTable.find('thead');
              const $retryTbody = $retryTable.find('tbody');
              if (
                $retryTable.html() &&
                $retryTable.html().trim() !== '' &&
                $retryThead.length > 0 &&
                $retryTbody.length > 0
              ) {
                initDataTable(tableId, options);
              }
            }
          }, 200);
        }
        return; // Continuar con la siguiente tabla
      }

      // Verificar que la tabla tenga al menos un thead y tbody
      // Usar find() para buscar en todos los niveles, no solo hijos directos
      const hasThead = $currentTable.find('thead').length > 0;
      const hasTbody = $currentTable.find('tbody').length > 0;

      if (hasThead && hasTbody) {
        // Obtener opciones desde data-options (puede ser JSON string o objeto)
        let options = {};
        const dataOptions = $(this).data('options');
        if (dataOptions) {
          if (typeof dataOptions === 'string') {
            try {
              options = JSON.parse(dataOptions);
            } catch (e) {
              logWarn(`Error parseando opciones JSON para ${tableId}:`, e);
            }
          } else {
            options = dataOptions;
          }
        }
        initDataTable(tableId, options);
      } else {
        logWarn(`Tabla ${tableId} no tiene thead o tbody, omitiendo DataTables`);
      }
    } else {
      logWarn('Tabla con data-datatable="true" no tiene ID, agregando ID temporal');
      const tempId = 'datatable-' + Math.random().toString(36).substr(2, 9);
      $(this).attr('id', tempId);
      let options = {};
      const dataOptions = $(this).data('options');
      if (dataOptions) {
        if (typeof dataOptions === 'string') {
          try {
            options = JSON.parse(dataOptions);
          } catch (e) {
            logWarn(`Error parseando opciones JSON para ${tempId}:`, e);
          }
        } else {
          options = dataOptions;
        }
      }
      initDataTable(tempId, options);
    }
  });
}

// Esperar a que jQuery y DataTable estén disponibles y AdminLTE esté listo
function waitForDependenciesAndInit() {
  // Verificar que jQuery esté disponible - verificación más robusta
  const jQueryAvailable = (typeof jQuery !== 'undefined' && jQuery !== null) ||
                         (typeof window.jQuery !== 'undefined' && window.jQuery !== null) ||
                         (typeof window.$ !== 'undefined' && window.$ !== null);

  if (!jQueryAvailable) {
    logWarn('⏳ [datatables-init] jQuery no disponible aún, reintentando en 100ms...');
    setTimeout(waitForDependenciesAndInit, 100);
    return;
  }

  const $ = window.jQuery || window.$ || jQuery;

  // Verificar que jQuery sea una función
  if (typeof $ !== 'function') {
    logWarn('⏳ [datatables-init] jQuery no es una función aún, reintentando en 100ms...');
    setTimeout(waitForDependenciesAndInit, 100);
    return;
  }

  // Verificar que DataTable también esté disponible
  // Verificar múltiples formas: window.DataTable, $.fn.DataTable, $.fn.dataTable
  const hasDataTable =
    typeof window.DataTable !== 'undefined' ||
    (typeof $.fn !== 'undefined' && typeof $.fn.DataTable !== 'undefined') ||
    (typeof $.fn !== 'undefined' && typeof $.fn.dataTable !== 'undefined');

  if (!hasDataTable) {
    // DataTable aún no está disponible, esperar un poco más
    setTimeout(waitForDependenciesAndInit, 100);
    return;
  }

  // jQuery y DataTable están disponibles, inicializar
  if (document.readyState === 'loading') {
    $(document).ready(initDataTablesAuto);
  } else {
    initDataTablesAuto();
  }
}

// Función para esperar a que jQuery esté disponible
function waitForJQueryAndInit() {
  // Verificar que jQuery esté disponible - verificación más robusta
  const jQueryAvailable = (typeof jQuery !== 'undefined' && jQuery !== null) ||
                         (typeof window.jQuery !== 'undefined' && window.jQuery !== null) ||
                         (typeof window.$ !== 'undefined' && window.$ !== null);

  logDebug('⏳ [datatables-init] Esperando jQuery...', {
    'jQuery': typeof jQuery,
    'window.$': typeof window.$,
    'window.jQuery': typeof window.jQuery,
    'jQueryAvailable': jQueryAvailable
  });

  if (!jQueryAvailable) {
    logDebug('⏳ [datatables-init] jQuery no disponible aún, reintentando en 100ms...');
    setTimeout(waitForJQueryAndInit, 100);
    return;
  }

  const $ = window.jQuery || window.$ || jQuery;

  // Verificar que jQuery sea una función
  if (typeof $ !== 'function') {
    logDebug('⏳ [datatables-init] jQuery no es una función aún, reintentando en 100ms...');
    setTimeout(waitForJQueryAndInit, 100);
    return;
  }

  logDebug('✅ [datatables-init] jQuery disponible, configurando $(document).ready...');

  // Ahora que jQuery está disponible, usar $(document).ready
  $(document).ready(function () {
    logDebug('✅ [datatables-init] $(document).ready ejecutado');
    // Esperar más tiempo para asegurar que el DOM esté completamente renderizado
    setTimeout(function () {
      const hasDataTable =
        typeof window.DataTable !== 'undefined' ||
        (typeof $.fn !== 'undefined' && typeof $.fn.DataTable !== 'undefined') ||
        (typeof $.fn !== 'undefined' && typeof $.fn.dataTable !== 'undefined');

      if (hasDataTable) {
        // Esperar un poco más para que las tablas se rendericen completamente
        setTimeout(function () {
          initDataTablesAuto();
        }, 100);
      } else {
        // Reintentar si DataTable aún no está disponible
        setTimeout(initDataTablesAuto, 500);
      }
    }, 500);
  });
}

// Iniciar la espera de jQuery
waitForJQueryAndInit();

// Escuchar evento de DataTable listo
document.addEventListener('datatable:ready', function () {
  setTimeout(waitForDependenciesAndInit, 100);
});

// También intentar inmediatamente si las dependencias ya están disponibles
waitForDependenciesAndInit();
