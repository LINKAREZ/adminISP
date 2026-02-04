{{-- Estilos específicos para la vista de cliente --}}
{{-- Los estilos globales (global-styles.blade.php) cubren la mayoría de los casos --}}
@push('styles')
<style>
    /* === Tabs de cliente === */
    #cliente-tab-content {
        display: block !important;
    }

    #cliente-tab-content .tab-pane {
        display: none !important;
    }

    #cliente-tab-content .tab-pane.show.active {
        display: block !important;
    }

    /* === Servicios === */
    #content-servicios .servicio-card {
        border-left-width: 4px;
    }

    #content-servicios dl dt {
        font-weight: 600;
        color: var(--gray-500);
        font-size: 0.8125rem;
    }

    #content-servicios dl dd {
        margin-bottom: 0.5rem;
        color: var(--gray-700);
    }

    /* === Btn-group - Uniformidad completa de bordes y colores === */
    .btn-group:not(.actions-menu) {
        display: inline-flex;
        align-items: stretch; /* Asegurar que todos los botones tengan la misma altura */
        vertical-align: middle;
    }

    .btn-group:not(.actions-menu) > .btn,
    .btn-group:not(.actions-menu) > a.btn,
    .btn-group:not(.actions-menu) form .btn {
        position: relative;
        flex: 0 1 auto;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        vertical-align: middle;
        height: auto; /* Permitir que el height se ajuste automáticamente */
    }

    /* Asegurar altura uniforme para todos los botones en btn-group */
    .btn-group:not(.actions-menu) > .btn,
    .btn-group:not(.actions-menu) > a.btn,
    .btn-group:not(.actions-menu) form .btn,
    .btn-group:not(.actions-menu) > form[style*="display: contents"] .btn {
        min-height: 31.5px; /* Altura estándar para btn-sm */
        line-height: 1.5;
    }

    /* Para botones btn-xs dentro de btn-group */
    .btn-group:not(.actions-menu) .btn-xs {
        min-height: 24px;
        padding: 0.1rem 0.3rem;
        font-size: 0.75rem;
    }

    /* Reset todos los border-radius primero */
    /* Aplica tanto a botones directos como a botones dentro de formularios */
    .btn-group:not(.actions-menu) > .btn:not(:first-child):not(:last-child),
    .btn-group:not(.actions-menu) form .btn:not(:first-child):not(:last-child) {
        border-radius: 0 !important;
    }

    .btn-group:not(.actions-menu) > .btn:first-child,
    .btn-group:not(.actions-menu) > form:first-child .btn,
    .btn-group:not(.actions-menu) > a:first-child {
        border-top-left-radius: var(--radius-sm, 0.25rem) !important;
        border-bottom-left-radius: var(--radius-sm, 0.25rem) !important;
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }

    .btn-group:not(.actions-menu) > .btn:last-child,
    .btn-group:not(.actions-menu) > form:last-child .btn,
    .btn-group:not(.actions-menu) > a:last-child {
        border-top-right-radius: var(--radius-sm, 0.25rem) !important;
        border-bottom-right-radius: var(--radius-sm, 0.25rem) !important;
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    .btn-group:not(.actions-menu) > .btn:not(:first-child),
    .btn-group:not(.actions-menu) > form:not(:first-child) .btn,
    .btn-group:not(.actions-menu) > a:not(:first-child) {
        margin-left: -1px;
    }

    /* Forzar colores de borde para TODOS los botones outline en btn-group */
    /* Incluye botones directamente en btn-group Y botones dentro de formularios con display:contents */
    /* Usar selectores más específicos para mayor prioridad */
    .btn-group > .btn.btn-outline-secondary,
    .btn-group form[style*="display: contents"] .btn.btn-outline-secondary,
    .btn-group form .btn.btn-outline-secondary {
        border: 1px solid var(--gray-400, #94a3b8) !important;
        border-color: var(--gray-400, #94a3b8) !important;
    }

    .btn-group > .btn.btn-outline-warning,
    .btn-group form[style*="display: contents"] .btn.btn-outline-warning,
    .btn-group form .btn.btn-outline-warning {
        border: 1px solid #f59e0b !important;
        border-color: #f59e0b !important;
        color: #d97706 !important;
    }

    .btn-group > .btn.btn-outline-warning:hover,
    .btn-group form[style*="display: contents"] .btn.btn-outline-warning:hover,
    .btn-group form .btn.btn-outline-warning:hover {
        background-color: #fef3c7 !important;
        border-color: #f59e0b !important;
    }

    .btn-group > .btn.btn-outline-danger,
    .btn-group form[style*="display: contents"] .btn.btn-outline-danger,
    .btn-group form .btn.btn-outline-danger,
    #content-pagos .btn-group form .btn.btn-outline-danger,
    #content-servicios .btn-group form .btn.btn-outline-danger,
    #content-datos .btn-group form .btn.btn-outline-danger {
        border: 1px solid #ef4444 !important;
        border-color: #ef4444 !important;
        color: #dc2626 !important;
    }

    .btn-group > .btn.btn-outline-danger:hover,
    .btn-group form[style*="display: contents"] .btn.btn-outline-danger:hover,
    .btn-group form .btn.btn-outline-danger:hover,
    #content-pagos .btn-group form .btn.btn-outline-danger:hover,
    #content-servicios .btn-group form .btn.btn-outline-danger:hover,
    #content-datos .btn-group form .btn.btn-outline-danger:hover {
        background-color: #fee2e2 !important;
        border-color: #ef4444 !important;
    }

    .btn-group > .btn.btn-outline-success,
    .btn-group form[style*="display: contents"] .btn.btn-outline-success,
    .btn-group form .btn.btn-outline-success {
        border: 1px solid #10b981 !important;
        border-color: #10b981 !important;
        color: #059669 !important;
    }

    .btn-group > .btn.btn-outline-success:hover,
    .btn-group form[style*="display: contents"] .btn.btn-outline-success:hover,
    .btn-group form .btn.btn-outline-success:hover {
        background-color: #d1fae5 !important;
        border-color: #10b981 !important;
    }

    .btn-group > .btn.btn-outline-info,
    .btn-group form[style*="display: contents"] .btn.btn-outline-info,
    .btn-group form .btn.btn-outline-info {
        border: 1px solid #3b82f6 !important;
        border-color: #3b82f6 !important;
        color: #2563eb !important;
    }

    .btn-group > .btn.btn-outline-info:hover,
    .btn-group form[style*="display: contents"] .btn.btn-outline-info:hover,
    .btn-group form .btn.btn-outline-info:hover {
        background-color: #dbeafe !important;
        border-color: #3b82f6 !important;
    }

    /* Asegurar margin-left para botones dentro de formularios */
    .btn-group:not(.actions-menu) form .btn:not(:first-child),
    .btn-group:not(.actions-menu) form[style*="display: contents"] .btn:not(:first-child) {
        margin-left: -1px !important;
    }

    /* Asegurar que formularios dentro de btn-group no rompan el layout */
    .btn-group:not(.actions-menu) form {
        display: contents;
    }

    /* Asegurar padding uniforme para todos los botones en btn-group */
    .btn-group:not(.actions-menu) > .btn,
    .btn-group:not(.actions-menu) > a.btn,
    .btn-group:not(.actions-menu) form .btn {
        padding-top: 0.25rem;
        padding-bottom: 0.25rem;
    }

    /* Asegurar que botones con diferentes clases tengan la misma altura */
    .btn-group:not(.actions-menu) .btn-success,
    .btn-group:not(.actions-menu) .btn-outline-secondary,
    .btn-group:not(.actions-menu) .btn-outline-danger,
    .btn-group:not(.actions-menu) .btn-outline-warning,
    .btn-group:not(.actions-menu) .btn-outline-success,
    .btn-group:not(.actions-menu) .btn-outline-info {
        box-sizing: border-box;
        height: 100%;
    }

    /* Asegurar alineación vertical perfecta */
    .btn-group:not(.actions-menu) {
        align-items: center;
    }

    /* === Botones de acciones más pequeños para clientes === */
    #cliente-container .actions-menu {
        flex-shrink: 0 !important;
        width: auto !important;
        max-width: fit-content !important;
        display: inline-flex !important;
        flex: 0 0 auto !important;
    }

    #cliente-container .actions-menu-btn {
        width: 26px !important;
        height: 26px !important;
        min-width: 26px !important;
        max-width: 26px !important;
        flex-shrink: 0 !important;
        flex: 0 0 26px !important;
        box-sizing: border-box !important;
    }

    #cliente-container .actions-menu-btn i {
        font-size: 0.625rem !important;
        line-height: 1 !important;
    }

    @media (min-width: 768px) {
        #cliente-container .actions-menu-btn {
            width: 24px !important;
            height: 24px !important;
            min-width: 24px !important;
            max-width: 24px !important;
            flex: 0 0 24px !important;
        }

        #cliente-container .actions-menu-btn i {
            font-size: 0.5625rem !important;
        }
    }

    @media (min-width: 1024px) {
        #cliente-container .actions-menu-btn {
            width: 22px !important;
            height: 22px !important;
            min-width: 22px !important;
            max-width: 22px !important;
            flex: 0 0 22px !important;
        }

        #cliente-container .actions-menu-btn i {
            font-size: 0.5rem !important;
        }
    }

    /* === Responsive === */
    @media (max-width: 576px) {
        .btn-group-mobile {
            flex-direction: column;
            gap: 0.5rem;
        }
        .btn-group-mobile .btn {
            width: 100%;
        }
    }
</style>
@endpush
