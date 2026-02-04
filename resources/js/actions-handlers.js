/**
 * Manejadores globales de acciones CRUD
 * Se registran inmediatamente cuando se carga el script
 */

(function() {
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

    // Mapeo de rutas por módulo
    const routes = {
        'clientes': {
            edit: (id) => `/clientes/${id}/edit`,
            view: (id) => `/clientes/${id}`,
            delete: (id) => `/clientes/${id}`,
            confirmDelete: '¿Estás seguro de eliminar este cliente? Esta acción no se puede deshacer.'
        },
        'users': {
            edit: (id) => `/users/${id}/edit`,
            view: (id) => `/users/${id}`,
            delete: (id) => `/users/${id}`,
            confirmDelete: '¿Está seguro de eliminar este usuario?'
        },
        'roles': {
            edit: (id) => `/roles/${id}/edit`,
            view: (id) => `/roles/${id}`,
            delete: (id) => `/roles/${id}`,
            confirmDelete: '¿Está seguro de eliminar este rol?'
        },
        'permissions': {
            edit: (id) => `/permissions/${id}/edit`,
            view: (id) => `/permissions/${id}`,
            delete: (id) => `/permissions/${id}`,
            confirmDelete: '¿Está seguro de eliminar este permiso?'
        },
        'planes': {
            edit: (id) => `/servicios/planes/${id}/edit`,
            view: (id) => `/servicios/planes/${id}`,
            delete: (id) => `/servicios/planes/${id}`,
            confirmDelete: '¿Está seguro de eliminar este plan?'
        },
        'servicios': {
            edit: (id) => `/servicios/${id}/edit`,
            view: (id) => `/servicios/${id}`,
            delete: (id) => `/servicios/${id}`,
            confirmDelete: '¿Está seguro de eliminar este servicio?'
        },
        'nodos': {
            edit: (id) => `/red/nodos/${id}/edit`,
            view: (id) => `/red/nodos/${id}`,
            delete: (id) => `/red/nodos/${id}`,
            confirmDelete: '¿Está seguro de eliminar este nodo?'
        },
        'routers': {
            edit: (id) => `/red/routers/${id}/edit`,
            view: (id) => `/red/routers/${id}`,
            delete: (id) => `/red/routers/${id}`,
            confirmDelete: '¿Está seguro de eliminar este router?'
        },
        'medios-pago': {
            edit: (id) => `/sistema/medios-pago/${id}/edit`,
            view: (id) => `/sistema/medios-pago/${id}`,
            delete: (id) => `/sistema/medios-pago/${id}`,
            confirmDelete: '¿Está seguro de eliminar este medio de pago?'
        }
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

    // Función para eliminar
    function handleDelete(module, id) {
        const routeConfig = routes[module];
        if (!routeConfig || !id) {
            logError('Módulo no encontrado o ID inválido:', module, id);
            return;
        }

        if (!confirm(routeConfig.confirmDelete)) {
            return;
        }

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = routeConfig.delete(id);

        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        form.appendChild(csrfInput);

        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);

        document.body.appendChild(form);
        form.submit();
    }

    // Registrar listeners globales
    window.addEventListener('action-edit', function(e) {
        const module = getCurrentModule();
        const id = e.detail?.id;

        logDebug('Evento action-edit recibido:', { module, id, detail: e.detail });

        if (!module || !id) {
            logError('Módulo o ID no válido:', { module, id });
            return;
        }

        const routeConfig = routes[module];
        if (routeConfig && routeConfig.edit) {
            window.location.href = routeConfig.edit(id);
        } else {
            logError('Ruta de edición no encontrada para módulo:', module);
        }
    });

    window.addEventListener('action-view', function(e) {
        const module = getCurrentModule();
        const id = e.detail?.id;

        logDebug('Evento action-view recibido:', { module, id, detail: e.detail });

        if (!module || !id) {
            logError('Módulo o ID no válido:', { module, id });
            return;
        }

        const routeConfig = routes[module];
        if (routeConfig && routeConfig.view) {
            window.location.href = routeConfig.view(id);
        } else {
            logError('Ruta de visualización no encontrada para módulo:', module);
        }
    });

    window.addEventListener('action-delete', function(e) {
        const module = getCurrentModule();
        const id = e.detail?.id;

        logDebug('Evento action-delete recibido:', { module, id, detail: e.detail });

        if (!module || !id) {
            logError('Módulo o ID no válido:', { module, id });
            return;
        }

        handleDelete(module, id);
    });

    logDebug('✅ Manejadores globales de acciones CRUD registrados');
})();
