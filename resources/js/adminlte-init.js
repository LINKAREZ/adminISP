/**
 * Inicialización de AdminLTE 3 y dependencias
 *
 * DEPENDENCIAS (orden crítico):
 * 1. jQuery (requerido por Bootstrap y AdminLTE)
 * 2. Bootstrap (requerido por AdminLTE)
 * 3. AdminLTE (extiende Bootstrap)
 *
 * Este archivo NO debe inicializar Alpine.js
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

// 1. jQuery debe cargarse PRIMERO (requerido por Bootstrap y AdminLTE)
logDebug('🔧 [adminlte-init] Importando jQuery...');
import $ from 'jquery';

// Asignar directamente a window
window.$ = $;
window.jQuery = $;

logDebug('✅ [adminlte-init] jQuery cargado y asignado:', {
  'window.jQuery': typeof window.jQuery,
  'window.$': typeof window.$,
  'jQuery versión': $.fn?.jquery || 'N/A'
});

// 2. Bootstrap (requiere jQuery)
import 'bootstrap/dist/js/bootstrap.bundle.min.js';

// 3. AdminLTE (requiere jQuery y Bootstrap)
import 'admin-lte/dist/js/adminlte.min.js';

// Inicializar AdminLTE cuando el DOM esté listo
logDebug('🔧 [adminlte-init] Configurando $(document).ready...');
$(document).ready(function () {
  // AdminLTE se inicializa automáticamente
  // Los elementos con data-widget se inicializan automáticamente
  logDebug('✅ [adminlte-init] AdminLTE 3 inicializado');

  // Dispatcher de evento cuando AdminLTE está listo
  const event = new CustomEvent('adminlte:ready', {
    bubbles: true,
    cancelable: false,
  });
  document.dispatchEvent(event);
  logDebug('📢 [adminlte-init] Evento adminlte:ready disparado');
});
