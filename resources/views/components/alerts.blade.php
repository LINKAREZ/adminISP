{{--
    ╔══════════════════════════════════════════════════════════════════════╗
    ║                    SISTEMA UNIFICADO DE ALERTAS                       ║
    ╠══════════════════════════════════════════════════════════════════════╣
    ║  TODO se muestra como TOASTS (notificaciones flotantes)               ║
    ╠══════════════════════════════════════════════════════════════════════╣
    ║  USO EN CONTROLLERS:                                                  ║
    ║  - return redirect()->with('success', 'Mensaje de éxito');            ║
    ║  - return redirect()->with('error', 'Mensaje de error');              ║
    ║  - return redirect()->with('warning', 'Mensaje de advertencia');      ║
    ║  - return redirect()->with('info', 'Mensaje informativo');            ║
    ╠══════════════════════════════════════════════════════════════════════╣
    ║  USO EN JAVASCRIPT:                                                   ║
    ║  - ToastManager.success('Mensaje de éxito');                          ║
    ║  - ToastManager.error('Mensaje de error');                            ║
    ║  - ToastManager.warning('Mensaje de advertencia');                    ║
    ║  - ToastManager.info('Mensaje informativo');                          ║
    ╚══════════════════════════════════════════════════════════════════════╝
--}}

{{-- ═══════════════════════════════════════════════════════════════════════ --}}
{{-- TOAST CONTAINER                                                        --}}
{{-- ═══════════════════════════════════════════════════════════════════════ --}}
<div id="toast-container" class="toast-container" role="status" aria-live="polite" aria-atomic="false"></div>

{{-- ═══════════════════════════════════════════════════════════════════════ --}}
{{-- ESTILOS CSS                                                            --}}
{{-- ═══════════════════════════════════════════════════════════════════════ --}}
<style>
    .toast-container {
        position: fixed;
        top: 1rem;
        right: 1rem;
        z-index: 9999;
        pointer-events: none;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        max-width: 400px;
    }

    .toast-item {
        pointer-events: auto;
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        padding: 1rem 1.25rem;
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        border-left: 4px solid;
        transform: translateX(120%);
        opacity: 0;
        transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    }

    .toast-item.show {
        transform: translateX(0);
        opacity: 1;
    }

    .toast-item.hiding {
        transform: translateX(120%);
        opacity: 0;
        transition: all 0.3s ease-in;
    }

    .toast-item.toast-success { border-left-color: #28a745; }
    .toast-item.toast-error { border-left-color: #dc3545; }
    .toast-item.toast-warning { border-left-color: #ffc107; }
    .toast-item.toast-info { border-left-color: #17a2b8; }

    .toast-icon {
        flex-shrink: 0;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .toast-icon i {
        font-size: 0.875rem;
        color: white;
    }

    .toast-success .toast-icon { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); }
    .toast-error .toast-icon { background: linear-gradient(135deg, #dc3545 0%, #e83e8c 100%); }
    .toast-warning .toast-icon { background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%); }
    .toast-warning .toast-icon i { color: #212529; }
    .toast-info .toast-icon { background: linear-gradient(135deg, #17a2b8 0%, #6610f2 100%); }

    .toast-content {
        flex: 1;
        min-width: 0;
        padding-top: 2px;
    }

    .toast-message {
        margin: 0;
        font-size: 0.9375rem;
        color: #374151;
        line-height: 1.5;
        font-weight: 500;
    }

    .toast-close {
        flex-shrink: 0;
        width: 24px;
        height: 24px;
        border: none;
        background: #f3f4f6;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #9ca3af;
        transition: all 0.2s;
        padding: 0;
        margin-top: -2px;
    }

    .toast-close:hover {
        background: #e5e7eb;
        color: #374151;
        transform: scale(1.1);
    }

    /* Progress bar for auto-close */
    .toast-progress {
        position: absolute;
        bottom: 0;
        left: 0;
        height: 3px;
        background: rgba(0, 0, 0, 0.1);
        border-radius: 0 0 0 12px;
        animation: progressShrink linear forwards;
    }

    .toast-success .toast-progress { background: #28a745; }
    .toast-error .toast-progress { background: #dc3545; }
    .toast-warning .toast-progress { background: #ffc107; }
    .toast-info .toast-progress { background: #17a2b8; }

    @keyframes progressShrink {
        from { width: 100%; }
        to { width: 0%; }
    }

    /* Responsive */
    @media (max-width: 480px) {
        .toast-container {
            left: 0.5rem;
            right: 0.5rem;
            max-width: none;
        }

        .toast-item {
            padding: 0.875rem 1rem;
        }
    }
</style>

{{-- ═══════════════════════════════════════════════════════════════════════ --}}
{{-- JAVASCRIPT                                                             --}}
{{-- ═══════════════════════════════════════════════════════════════════════ --}}
<script>
(function() {
    'use strict';

    // =========================================
    // Toast Manager
    // =========================================
    window.ToastManager = {
        container: null,
        defaultDuration: {
            'success': 3000,
            'error': 5000,
            'warning': 4000,
            'info': 3500
        },

        init: function() {
            this.container = document.getElementById('toast-container');
            return this;
        },

        _getIcon: function(type) {
            var icons = {
                'success': 'fa-check',
                'error': 'fa-times',
                'warning': 'fa-exclamation',
                'info': 'fa-info'
            };
            return icons[type] || 'fa-info';
        },

        add: function(message, type, duration) {
            if (!this.container) this.init();
            type = type || 'info';
            duration = duration || this.defaultDuration[type] || 3500;

            var id = 'toast-' + Date.now();
            var toast = document.createElement('div');
            toast.id = id;
            toast.className = 'toast-item toast-' + type;
            toast.style.position = 'relative';
            toast.innerHTML = [
                '<div class="toast-icon"><i class="fas ' + this._getIcon(type) + '"></i></div>',
                '<div class="toast-content"><p class="toast-message">' + this._escape(message) + '</p></div>',
                '<button type="button" class="toast-close" onclick="ToastManager.remove(\'' + id + '\')">',
                '<i class="fas fa-times"></i></button>',
                '<div class="toast-progress" style="animation-duration: ' + duration + 'ms;"></div>'
            ].join('');

            this.container.appendChild(toast);

            // Trigger show animation
            requestAnimationFrame(function() {
                requestAnimationFrame(function() {
                    toast.classList.add('show');
                });
            });

            // Auto remove
            var self = this;
            setTimeout(function() { self.remove(id); }, duration);

            return id;
        },

        remove: function(id) {
            var toast = document.getElementById(id);
            if (toast && !toast.classList.contains('hiding')) {
                toast.classList.add('hiding');
                toast.classList.remove('show');
                setTimeout(function() {
                    if (toast.parentNode) {
                        toast.parentNode.removeChild(toast);
                    }
                }, 300);
            }
        },

        _escape: function(text) {
            var div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        },

        success: function(message, duration) { return this.add(message, 'success', duration); },
        error: function(message, duration) { return this.add(message, 'error', duration); },
        warning: function(message, duration) { return this.add(message, 'warning', duration); },
        info: function(message, duration) { return this.add(message, 'info', duration); }
    };

    // Initialize ToastManager when DOM is ready
    function initToastManager() {
        ToastManager.init();

        // Show flash messages from session as toasts
        @if(session('success'))
            ToastManager.success(@json(session('success')));
        @endif

        @if(session('error'))
            ToastManager.error(@json(session('error')));
        @endif

        @if(session('warning'))
            ToastManager.warning(@json(session('warning')));
        @endif

        @if(session('info'))
            ToastManager.info(@json(session('info')));
        @endif
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initToastManager);
    } else {
        initToastManager();
    }
})();
</script>
