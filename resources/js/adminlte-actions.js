/**
 * Manejador de acciones CRUD usando AdminLTE/Bootstrap
 * Funciona con dropdowns nativos de Bootstrap
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
    logDebug('⏳ [adminlte-actions] Esperando jQuery...', {
      'jQuery': typeof jQuery,
      'window.$': typeof window.$,
      'window.jQuery': typeof window.jQuery
    });

    if (typeof jQuery !== 'undefined' && typeof window.$ !== 'undefined') {
      const $ = window.jQuery || window.$;
      logDebug('✅ [adminlte-actions] jQuery disponible, ejecutando callback');
      callback($);
    } else if (typeof window.jQuery !== 'undefined') {
      window.$ = window.jQuery;
      logDebug('✅ [adminlte-actions] jQuery disponible (window.jQuery), ejecutando callback');
      callback(window.jQuery);
    } else {
      logDebug('⏳ [adminlte-actions] jQuery no disponible aún, reintentando en 50ms...');
      setTimeout(() => waitForJQuery(callback), 50);
    }
  }

  // Esperar a jQuery antes de ejecutar el código
  logDebug('🔧 [adminlte-actions] Inicializando, esperando jQuery...');
  waitForJQuery(function ($) {
    logDebug('✅ [adminlte-actions] jQuery recibido en callback, continuando...');

  // Mapeo de rutas por módulo
  const routes = {
    clientes: {
      edit: id => `/clientes/${id}/edit`,
      view: id => `/clientes/${id}`,
      delete: id => `/clientes/${id}`,
      confirmDelete: '¿Estás seguro de eliminar este cliente? Esta acción no se puede deshacer.',
    },
    users: {
      edit: id => `/users/${id}/edit`,
      view: id => `/users/${id}`,
      delete: id => `/users/${id}`,
      confirmDelete: '¿Está seguro de eliminar este usuario?',
    },
    roles: {
      edit: id => `/roles/${id}/edit`,
      view: id => `/roles/${id}`,
      delete: id => `/roles/${id}`,
      confirmDelete: '¿Está seguro de eliminar este rol?',
    },
    permissions: {
      edit: id => `/permissions/${id}/edit`,
      view: id => `/permissions/${id}`,
      delete: id => `/permissions/${id}`,
      confirmDelete: '¿Está seguro de eliminar este permiso?',
    },
    planes: {
      edit: id => `/servicios/planes/${id}/edit`,
      view: id => `/servicios/planes/${id}`,
      delete: id => `/servicios/planes/${id}`,
      confirmDelete: '¿Está seguro de eliminar este plan?',
    },
    servicios: {
      edit: id => `/servicios/${id}/edit`,
      view: id => `/servicios/${id}`,
      delete: id => `/servicios/${id}`,
      confirmDelete: '¿Está seguro de eliminar este servicio?',
    },
    nodos: {
      edit: id => `/red/nodos/${id}/edit`,
      view: id => `/red/nodos/${id}`,
      delete: id => `/red/nodos/${id}`,
      confirmDelete: '¿Está seguro de eliminar este nodo?',
    },
    routers: {
      edit: id => `/red/routers/${id}/edit`,
      view: id => `/red/routers/${id}`,
      delete: id => `/red/routers/${id}`,
      confirmDelete: '¿Está seguro de eliminar este router?',
    },
    'medios-pago': {
      edit: id => `/sistema/medios-pago/${id}/edit`,
      view: id => `/sistema/medios-pago/${id}`,
      delete: id => `/sistema/medios-pago/${id}`,
      confirmDelete: '¿Está seguro de eliminar este medio de pago?',
    },
  };

  // Función para obtener el módulo desde la URL actual
  function getCurrentModule() {
    const path = window.location.pathname;
    if (path.includes('/clientes')) return 'clientes';
    if (path.includes('/users')) return 'users';
    if (path.includes('/roles')) return 'roles';
    if (path.includes('/permissions')) return 'permissions';
    if (path.includes('/servicios/planes')) return 'planes';
    if (path.includes('/servicios')) return 'servicios';
    if (path.includes('/red/nodos')) return 'nodos';
    if (path.includes('/red/routers')) return 'routers';
    if (path.includes('/medios-pago')) return 'medios-pago';
    return null;
  }

   // Función para manejar acción de editar
   function handleEdit(e) {
     e.preventDefault();
     e.stopPropagation();
     e.stopImmediatePropagation();

     const $ = window.jQuery || window.$;
     const $link = $(this);

     // Asegurar que tenemos el elemento correcto
    if (!$link.hasClass('action-edit')) {
      logError('Elemento incorrecto:', $link);
       return;
     }

    const id = $link.data('id') || $link.attr('data-id');
    // Usar attr en lugar de data para evitar problemas con data-attributes
    const routeEdit = $link.attr('data-route-edit') || $link.data('route-edit');
    const module = getCurrentModule();

    logDebug('🔍 Acción Editar:', {
      id,
      routeEdit,
      module,
      element: $link[0],
      hasClass: $link.hasClass('action-edit'),
      html: $link[0].outerHTML
    });

    let url;
    if (routeEdit && routeEdit !== '' && routeEdit !== 'undefined' && routeEdit !== null) {
      url = routeEdit;
    } else if (module && routes[module]) {
      url = routes[module].edit(id);
    } else {
      logError('❌ No se pudo determinar la ruta de edición', { routeEdit, module, id });
      window.showAlert('Error: No se pudo determinar la ruta de edición', 'error');
      return;
    }

    logDebug('✅ Navegando a (Editar):', url);
    window.location.href = url;
  }

   // Función para manejar acción de ver
   function handleView(e) {
     e.preventDefault();
     e.stopPropagation();
     e.stopImmediatePropagation();

     const $ = window.jQuery || window.$;

     // Obtener el elemento que disparó el evento (puede ser el <a> o un hijo como <i>)
     let target = $(e.target);
     if (!target.hasClass('action-view')) {
       target = target.closest('.action-view');
     }

    if (!target.length) {
      logError('❌ No se encontró el elemento .action-view');
       return;
     }

    const id = target.data('id') || target.attr('data-id');
    // Usar attr en lugar de data para evitar problemas con data-attributes
    const routeView = target.attr('data-route-view') || target.data('route-view');
    const module = getCurrentModule();

    logDebug('🔍 Acción Ver:', {
      id,
      routeView,
      module,
      element: target[0],
      hasClass: target.hasClass('action-view'),
      html: target[0].outerHTML.substring(0, 200)
    });

    let url;
    if (routeView && routeView !== '' && routeView !== 'undefined' && routeView !== null) {
      url = routeView;
    } else if (module && routes[module]) {
      url = routes[module].view(id);
    } else {
      logError('❌ No se pudo determinar la ruta de visualización', { routeView, module, id });
      window.showAlert('Error: No se pudo determinar la ruta de visualización', 'error');
      return;
    }

    logDebug('✅ Navegando a (Ver):', url);
    window.location.href = url;
  }

   // Función para manejar acción de eliminar
   function handleDelete(e) {
     e.preventDefault();
     const $ = window.jQuery || window.$;
     const $link = $(this);
    const id = $link.data('id') || $link.attr('data-id');
    // Usar attr en lugar de data para evitar problemas con data-attributes
    const routeDelete = $link.attr('data-route-delete') || $link.data('route-delete');
    const confirmMessage = $link.attr('data-confirm-message') || $link.data('confirm-message');
    const module = getCurrentModule();

    logDebug('Acción Eliminar:', { id, routeDelete, module });

    let url, message;
    if (routeDelete && routeDelete !== '') {
      url = routeDelete;
      message = confirmMessage || '¿Está seguro de eliminar este elemento?';
    } else if (module && routes[module]) {
      url = routes[module].delete(id);
      message = routes[module].confirmDelete;
    } else {
      logError('No se pudo determinar la ruta de eliminación');
      window.showAlert('Error: No se pudo determinar la ruta de eliminación', 'error');
      return;
    }

    if (!confirm(message)) {
      return;
    }

    // Crear formulario para DELETE
    const form = $('<form>', {
      method: 'POST',
      action: url,
    });

    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    if (!csrfToken) {
      logError('CSRF token no encontrado');
      window.showAlert('Error: No se encontró el token CSRF', 'error');
      return;
    }

    form.append(
      $('<input>', {
        type: 'hidden',
        name: '_token',
        value: csrfToken,
      })
    );

    form.append(
      $('<input>', {
        type: 'hidden',
        name: '_method',
        value: 'DELETE',
      })
    );

    $('body').append(form);
    form.submit();
  }

   /**
    * Inicializar cuando el DOM esté listo y jQuery esté disponible
    * Usa event delegation para funcionar con contenido dinámico
    */
   function initActions() {
     // Verificar que jQuery esté disponible
     if (typeof jQuery === 'undefined' && typeof window.$ === 'undefined') {
       // Escuchar evento cuando jQuery esté disponible
       if (window.jQuery) {
         window.$ = window.jQuery;
       } else {
         // Esperar evento de AdminLTE listo
         document.addEventListener('adminlte:ready', initActions, { once: true });
         return;
       }
     }

     // Usar jQuery global
     const $ = window.jQuery || window.$;

     // Delegar eventos a los elementos de acción (funciona con contenido dinámico)
     // Usar delegation para que funcione con contenido cargado dinámicamente
     // IMPORTANTE: Usar stopPropagation para evitar que un click en "Ver" active "Editar"
     $(document).off('click', '.action-edit').on('click', '.action-edit', function(e) {
       e.preventDefault();
       e.stopPropagation();
       e.stopImmediatePropagation();
       handleEdit.call(this, e);
     });

     $(document).off('click', '.action-view').on('click', '.action-view', function(e) {
       e.preventDefault();
       e.stopPropagation();
       e.stopImmediatePropagation();
       handleView.call(this, e);
     });

     $(document).off('click', '.action-delete').on('click', '.action-delete', function(e) {
       e.preventDefault();
       e.stopPropagation();
       e.stopImmediatePropagation();
       handleDelete.call(this, e);
     });

     logDebug('✅ Manejadores de acciones AdminLTE registrados');
   }

   // Inicializar cuando jQuery y AdminLTE estén listos
   if (typeof jQuery !== 'undefined' || typeof window.$ !== 'undefined') {
     // jQuery ya está disponible, esperar a que AdminLTE esté listo
     const $ = window.jQuery || window.$;

     if (document.readyState === 'loading') {
       document.addEventListener('adminlte:ready', initActions, { once: true });
       if (typeof $ !== 'undefined') {
         $(document).ready(initActions);
       }
     } else {
       // DOM ya está listo
       if (typeof $ !== 'undefined') {
         $(document).ready(initActions);
       } else {
         document.addEventListener('adminlte:ready', initActions, { once: true });
       }
     }
  } else {
    // Esperar a que AdminLTE esté listo
    document.addEventListener('adminlte:ready', initActions, { once: true });
  }
  }); // Fin de waitForJQuery
})();
