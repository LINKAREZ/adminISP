import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Verificación global de jQuery - Asegurar que esté disponible antes de que cualquier código lo use
const logDebug = (...args) => {
  if (window.logger && typeof window.logger.debug === 'function') {
    window.logger.debug(...args);
    return;
  }
  if (console && typeof console.debug === 'function') {
    console.debug(...args);
  }
};
logDebug('🔧 [bootstrap.js] Inicializando...');

// Suprimir errores de extensiones del navegador que interfieren con el código
// Estos errores son comunes con extensiones como ad blockers, password managers, etc.
if (typeof window !== 'undefined') {
  // Capturar errores de message channels (extensiones del navegador)
  const originalAddEventListener = window.addEventListener;
  window.addEventListener = function (type, listener, options) {
    // Si es un listener de 'message' que podría causar problemas con extensiones
    if (type === 'message') {
      const wrappedListener = function (event) {
        try {
          // Solo procesar si el origen es el mismo o es un mensaje interno
          if (event.source === window || event.origin === window.location.origin) {
            return listener.call(this, event);
          }
        } catch (error) {
          // Suprimir errores de extensiones del navegador
          if (error.message && error.message.includes('message channel')) {
            logDebug('Error de extensión del navegador suprimido:', error.message);
            return;
          }
          throw error;
        }
      };
      return originalAddEventListener.call(this, type, wrappedListener, options);
    }
    return originalAddEventListener.call(this, type, listener, options);
  };

  // Capturar errores no manejados relacionados con message channels
  window.addEventListener(
    'error',
    function (event) {
      if (event.error && event.error.message && event.error.message.includes('message channel')) {
        event.preventDefault();
        logDebug('Error de extensión del navegador suprimido:', event.error.message);
        return false;
      }
    },
    true
  );

  // Capturar promesas rechazadas relacionadas con message channels
  window.addEventListener('unhandledrejection', function (event) {
    if (
      event.reason &&
      event.reason.message &&
      (event.reason.message.includes('message channel') ||
        event.reason.message.includes('asynchronous response') ||
        event.reason.message.includes('listener indicated'))
    ) {
      event.preventDefault();
      logDebug('Error de extensión del navegador suprimido (promise):', event.reason.message);
      return false;
    }
  });

  // Capturar errores específicos de extensiones que retornan true pero no responden
  const originalErrorHandler = window.onerror;
  window.onerror = function (message, source, lineno, colno, error) {
    if (
      message &&
      typeof message === 'string' &&
      (message.includes('asynchronous response') ||
        message.includes('message channel closed') ||
        message.includes('listener indicated'))
    ) {
      logDebug('Error de extensión del navegador suprimido:', message);
      return true; // Suprimir el error
    }
    // Si hay un handler original, llamarlo
    if (originalErrorHandler) {
      return originalErrorHandler.call(this, message, source, lineno, colno, error);
    }
    return false;
  };
}
