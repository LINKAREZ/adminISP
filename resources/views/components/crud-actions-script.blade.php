@props(['baseRoute', 'entityName', 'confirmMessage'])

@push('scripts')
<script>
    (function() {
        'use strict';

        const logWarn = (...args) => {
            if (window.logger && typeof window.logger.warn === 'function') {
                window.logger.warn(...args);
                return;
            }
            if (console && typeof console.warn === 'function') {
                console.warn(...args);
            }
        };

        // Esperar a que el DOM esté listo
        function initCrudActions() {
            try {
                // Event listener para editar
                window.addEventListener('action-edit', function(e) {
                    try {
                        const routeEdit = e.detail?.routeEdit;
                        const id = e.detail?.id;

                        if (routeEdit) {
                            window.location.href = routeEdit;
                        } else if (id && '{{ $baseRoute ?? "" }}') {
                            window.location.href = `{{ $baseRoute ?? "" }}/${id}/edit`;
                        }
                    } catch (error) {
                        logWarn('Error en action-edit:', error);
                    }
                }, { passive: true });

                // Event listener para ver
                window.addEventListener('action-view', function(e) {
                    try {
                        const routeView = e.detail?.routeView;
                        const id = e.detail?.id;

                        if (routeView) {
                            window.location.href = routeView;
                        } else if (id && '{{ $baseRoute ?? "" }}') {
                            window.location.href = `{{ $baseRoute ?? "" }}/${id}`;
                        }
                    } catch (error) {
                        logWarn('Error en action-view:', error);
                    }
                }, { passive: true });

                // Event listener para eliminar
                window.addEventListener('action-delete', function(e) {
                    try {
                        const routeDelete = e.detail?.routeDelete;
                        const id = e.detail?.id;
                        const confirmMessage = e.detail?.confirmMessage || '{{ $confirmMessage ?? "¿Está seguro de eliminar este elemento?" }}';

                        let deleteRoute = routeDelete;
                        if (!deleteRoute && id && '{{ $baseRoute ?? "" }}') {
                            deleteRoute = `{{ $baseRoute ?? "" }}/${id}`;
                        }

                        if (deleteRoute && confirm(confirmMessage)) {
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = deleteRoute;

                            const csrfInput = document.createElement('input');
                            csrfInput.type = 'hidden';
                            csrfInput.name = '_token';
                            csrfInput.value = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
                            form.appendChild(csrfInput);

                            const methodInput = document.createElement('input');
                            methodInput.type = 'hidden';
                            methodInput.name = '_method';
                            methodInput.value = 'DELETE';
                            form.appendChild(methodInput);

                            document.body.appendChild(form);
                            form.submit();
                        }
                    } catch (error) {
                        logWarn('Error en action-delete:', error);
                    }
                }, { passive: true });
            } catch (error) {
                logWarn('Error al inicializar CRUD actions:', error);
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initCrudActions, { once: true });
        } else {
            initCrudActions();
        }
    })();
</script>
@endpush
