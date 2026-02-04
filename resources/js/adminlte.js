/**
 * Punto de entrada principal para AdminLTE 3
 * Solo usa jQuery/Bootstrap - Sin Alpine.js
 *
 * ORDEN DE CARGA:
 * 1. Dependencias base (axios, bootstrap.js)
 * 2. AdminLTE y jQuery (adminlte-init.js)
 * 3. DataTables (requiere jQuery)
 * 4. Librerías globales (Chart.js, DataTables)
 * 5. AdminLTE Actions (requiere jQuery)
 */

// Logger helper (debe cargarse temprano)
import './logger';

const logDebug = (...args) => {
  if (window.logger && typeof window.logger.debug === 'function') {
    window.logger.debug(...args);
    return;
  }
  if (console && typeof console.debug === 'function') {
    console.debug(...args);
  }
};

// 1. Configuración base (axios)
logDebug('🔧 [adminlte.js] Iniciando carga de módulos...');
import './bootstrap';
logDebug('✅ [adminlte.js] bootstrap.js cargado');
logDebug('✅ [adminlte.js] logger.js cargado');

// 2. AdminLTE y dependencias (jQuery, Bootstrap, AdminLTE)
logDebug('🔧 [adminlte.js] Cargando adminlte-init.js (jQuery, Bootstrap, AdminLTE)...');
import './adminlte-init';
logDebug('✅ [adminlte.js] adminlte-init.js cargado');

// 3. Librerías globales (debe cargarse ANTES de datatables-init)
import Chart from 'chart.js/auto';
import DataTable from 'datatables.net-bs4';

// Exponer inmediatamente en window
window.Chart = Chart;
window.DataTable = DataTable;

// Asegurar que DataTable también esté disponible globalmente
if (typeof global !== 'undefined') {
  global.DataTable = DataTable;
}

// Disparar evento cuando DataTable esté listo
if (typeof document !== 'undefined') {
  setTimeout(() => {
    const event = new CustomEvent('datatable:ready', {
      bubbles: true,
      cancelable: false,
    });
    document.dispatchEvent(event);
  }, 0);
}

// 4. DataTables init (requiere jQuery y DataTable)
logDebug('🔧 [adminlte.js] Cargando datatables-init.js...');
import './datatables-init';
logDebug('✅ [adminlte.js] datatables-init.js cargado');

// 5. AdminLTE Actions (requiere jQuery, se ejecuta en $(document).ready)
logDebug('🔧 [adminlte.js] Cargando adminlte-actions.js...');
import './adminlte-actions';
logDebug('✅ [adminlte.js] adminlte-actions.js cargado');

// 6. Gestión de conexiones PPPoE (requiere jQuery)
import './pppoe-connections';

// 7. Tabs del router (requiere jQuery)
import './router-tabs';

// 8. Gestión de reglas del router (requiere jQuery)
import './router-reglas';

// 9. Drawer PPPoE (requiere jQuery)
import './pppoe-detail-drawer';
