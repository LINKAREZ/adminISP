/**
 * Logger helper para desarrollo y producción
 * En producción, los logs solo se muestran si hay errores críticos
 */

const isDevelopment =
  window.location.hostname === 'localhost' ||
  window.location.hostname === '127.0.0.1' ||
  window.location.hostname.includes('localhost');

const originalConsole = {
  log: console.log,
  debug: console.debug,
  warn: console.warn,
  error: console.error,
  group: console.group,
  groupEnd: console.groupEnd,
};

/**
 * Logger principal
 */
const logger = {
  /**
   * Log de información (solo en desarrollo)
   */
  info: (...args) => {
    if (isDevelopment) {
      originalConsole.log('[INFO]', ...args);
    }
  },

  /**
   * Log de advertencias (siempre visible)
   */
  warn: (...args) => {
    originalConsole.warn('[WARN]', ...args);
  },

  /**
   * Log de errores (siempre visible)
   */
  error: (...args) => {
    originalConsole.error('[ERROR]', ...args);

    // En producción, podrías enviar esto a un servicio de logging
    if (!isDevelopment) {
      // Aquí podrías enviar el error a tu backend para logging
      // fetch('/api/log-error', { method: 'POST', body: JSON.stringify({ error: args }) });
    }
  },

  /**
   * Log de debug (solo en desarrollo)
   */
  debug: (...args) => {
    if (isDevelopment) {
      originalConsole.debug('[DEBUG]', ...args);
    }
  },

  /**
   * Log de grupo (solo en desarrollo)
   */
  group: label => {
    if (isDevelopment) {
      originalConsole.group(label);
    }
  },

  /**
   * Cerrar grupo (solo en desarrollo)
   */
  groupEnd: () => {
    if (isDevelopment) {
      originalConsole.groupEnd();
    }
  },
};

// Exportar para uso global
window.logger = logger;

// También exportar como módulo ES6 si se usa import
if (typeof module !== 'undefined' && module.exports) {
  module.exports = logger;
}

// Estandarizar console.log/debug usando el logger global
if (!isDevelopment) {
  console.log = () => {};
  console.debug = () => {};
} else {
  console.log = (...args) => logger.debug(...args);
  console.debug = (...args) => logger.debug(...args);
}

// Unificar warn/error para mantener formato consistente
console.warn = (...args) => logger.warn(...args);
console.error = (...args) => logger.error(...args);

// Helpers globales para notificaciones y alerts
const showToast = (type, message, options = {}) => {
  if (window.ToastManager && typeof window.ToastManager[type] === 'function') {
    window.ToastManager[type](message, options.title, options);
    return true;
  }
  if (window.appState && typeof window.appState.showToast === 'function') {
    window.appState.showToast(type, message);
    return true;
  }
  return false;
};

window.notify = {
  success: (message, options = {}) => showToast('success', message, options),
  error: (message, options = {}) => showToast('error', message, options),
  warn: (message, options = {}) => showToast('warning', message, options),
  info: (message, options = {}) => showToast('info', message, options),
};

window.showAlert = (message, type = 'info', options = {}) => {
  if (!showToast(type, message, options)) {
    alert(message);
  }
};
