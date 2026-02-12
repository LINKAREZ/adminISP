@push('styles')
{{-- Google Fonts - Inter (Tendencia 2024-2025) --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
    /* ============================================
       SISTEMA DE DISEÑO MODERNO 2024-2025
       Admin ISP - Tendencias UI/UX
       ============================================ */

    :root {
        /* ===== PALETA CORPORATIVA UNIFICADA (Admin + Super Admin) =====
           Inspirada en IBM Carbon / estándares WCAG AA. Misma base para ambos panels. */

        --color-primary: #0f62fe;
        --color-surface: #ffffff;
        --color-background: #f4f6f8;
        --color-muted: #525f7f;
        --color-border: #d1d9e6;

        /* Mobile-first: breakpoints */
        --bp-sm: 576px;
        --bp-md: 768px;
        --bp-lg: 992px;
        --bp-xl: 1200px;

        --touch-min: 44px;

        --safe-top: env(safe-area-inset-top, 0);
        --safe-right: env(safe-area-inset-right, 0);
        --safe-bottom: env(safe-area-inset-bottom, 0);
        --safe-left: env(safe-area-inset-left, 0);

        /* Primario: azul corporativo (Carbon Blue 60), contraste ≥4.5:1 con blanco */
        --primary: #0f62fe;
        --primary-light: #4589ff;
        --primary-dark: #0043ce;
        --primary-50: #edf5ff;
        --primary-100: #d0e2ff;

        /* Secundario: gris neutro alineado con escala */
        --secondary: #525f7f;
        --secondary-light: #6b7a9e;
        --secondary-dark: #3d475c;

        /* Estados semánticos (contraste WCAG AA) */
        --success: #0d9488;
        --success-light: #ccfbf1;
        --danger: #dc2626;
        --danger-light: #fee2e2;
        --warning: #d97706;
        --warning-light: #fef3c7;
        --info: #0284c7;
        --info-light: #e0f2fe;

        /* Neutros: escala única slate para armonía */
        --gray-50: #f4f6f8;
        --gray-100: #e8ecf1;
        --gray-200: #d1d9e6;
        --gray-300: #a8b4c4;
        --gray-400: #7b8a9e;
        --gray-500: #525f7f;
        --gray-600: #3d475c;
        --gray-700: #2d3548;
        --gray-800: #1e2433;
        --gray-900: #12161f;

        /* Sombras modernas */
        --shadow-xs: 0 1px 1px 0 rgb(0 0 0 / 0.04);
        --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
        --shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
        --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);

        /* Radios estandarizados (8px, 12px, 16px) */
        --radius-sm: 8px;
        --radius: 12px;
        --radius-md: 12px;
        --radius-lg: 16px;
        --radius-xl: 1.5rem;

        /* Transiciones */
        --transition-fast: 150ms cubic-bezier(0.4, 0, 0.2, 1);
        --transition: 200ms cubic-bezier(0.4, 0, 0.2, 1);
        --transition-slow: 300ms cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* === MOBILE-FIRST: BASE === */
    html {
        /* Evitar overflow horizontal en móvil */
        overflow-x: hidden;
        -webkit-overflow-scrolling: touch;
    }

    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        font-size: 0.9375rem;
        font-weight: 400;
        color: var(--gray-700);
        background: var(--gray-50);
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        -webkit-text-size-adjust: 100%;
        /* Safe area: padding inferior para home indicator (iOS/Android) */
        padding-left: var(--safe-left);
        padding-right: var(--safe-right);
    }

    /* Mobile-first: base = móvil (1rem); desde 768px reducir si se desea */
    @media (min-width: 768px) {
        body {
            font-size: 0.9375rem;
        }
    }

    /* Jerarquía tipográfica definitiva */
    .content-header h1, .page-title-mobile {
        font-size: 1.5rem;
        font-weight: 600;
    }
    h2, .section-title {
        font-size: 1.125rem;
        font-weight: 600;
    }
    .card-header .card-title, .card-title {
        font-size: 1rem;
        font-weight: 600;
    }
    .text-muted {
        font-size: 0.8125rem;
        font-weight: 400;
        color: var(--gray-600) !important;
    }
    h1, h2, h3, h4, h5, h6, .h1, .h2, .h3, .h4, .h5, .h6 {
        font-weight: 600;
        color: var(--gray-900);
        letter-spacing: -0.025em;
    }

    /* === CARDS MODERNAS (mobile-first: base = móvil) === */
    .card {
        background: white;
        border: 1px solid var(--gray-200);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        margin-bottom: 1rem;
        transition: box-shadow 0.2s ease;
    }

    .card:last-child {
        margin-bottom: 0;
    }

    .card:hover {
        box-shadow: var(--shadow-md);
    }

    @media (min-width: 768px) {
        .card {
            margin-bottom: 1.5rem;
            border-radius: 12px;
        }
        .card:last-child {
            margin-bottom: 0;
        }
    }

    .card-header {
        background: var(--gray-50);
        border-bottom: 1px solid var(--gray-200);
        padding: 1rem 1.25rem;
        border-radius: var(--radius-lg) var(--radius-lg) 0 0;
    }

    .card-header .card-title {
        font-size: 0.9375rem;
        font-weight: 600;
        margin: 0;
        color: var(--gray-800);
    }

    .card-body {
        padding: 1.25rem;
    }

    /* Evitar que los selects se corten en formularios de pago */
    .card-body form .form-group {
        overflow: visible !important;
        margin-bottom: 1.5rem;
    }

    .card-body form select {
        overflow: visible !important;
    }

    /* === SELECTORES DE FECHA (DÍA, MES, AÑO) === */
    .fecha-selector {
        height: 50px;
        font-size: 1.1rem;
        border: 2px solid var(--gray-300);
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .fecha-selector:hover {
        border-color: var(--primary);
        box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.1);
    }

    .fecha-selector:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
        outline: none;
    }

    /* Resaltar opciones de fecha actual en negrita */
    .fecha-selector option.fecha-hoy {
        font-weight: 900 !important;
        background-color: rgba(102, 126, 234, 0.1) !important;
        color: var(--primary-dark) !important;
    }

    /* Estilo adicional para el selector cuando tiene opción "hoy" seleccionada */
    .fecha-selector:has(option.fecha-hoy:checked) {
        border-color: var(--primary);
        box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.2);
    }

    /* === DISPLAY DE FECHA MEJORADO === */
    .fecha-display-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        background: var(--primary);
        color: white;
        font-weight: 600;
        font-size: 1rem;
        border-radius: var(--radius);
        box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
        transition: all 0.3s ease;
        min-width: 280px;
        justify-content: center;
    }

    .fecha-display-badge:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }

    #fecha_display_text {
        text-transform: capitalize;
    }

    /* === AVISO DE NÚMERO DE OPERACIÓN DUPLICADO === */
    .aviso-duplicado {
        border-left: 4px solid #ffc107;
        background-color: #fff3cd;
        border-radius: var(--radius);
        padding: 0.75rem 1rem;
        animation: slideDown 0.3s ease-out;
    }

    .aviso-duplicado i {
        color: #856404;
    }

    .aviso-duplicado .mensaje-duplicado {
        color: #856404;
        font-weight: 500;
    }

    /* Animación shake para input con error */
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
        20%, 40%, 60%, 80% { transform: translateX(5px); }
    }

    /* Animación slideDown para el aviso */
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Asegurar que los campos de medio de pago se muestren correctamente */
    .campos-medio-pago {
        overflow: visible !important;
        margin-top: 1rem;
        padding: 1rem;
        background: var(--gray-50);
        border-radius: var(--radius);
        border: 1px solid var(--gray-200);
        transition: opacity 0.3s ease-out;
    }

    /* Los estilos inline tienen prioridad, así que no usar !important aquí */
    .campos-medio-pago.show {
        display: block;
        opacity: 1;
        visibility: visible;
    }

    .card-footer {
        background: var(--gray-50);
        border-top: 1px solid var(--gray-200);
        padding: 1rem 1.25rem;
        border-radius: 0 0 var(--radius-lg) var(--radius-lg);
    }

    .card-outline.card-primary {
        border-top: 3px solid var(--primary);
    }

    .card-outline.card-info {
        border-top: 3px solid var(--info);
    }

    /* === INFO BOXES MODERNAS === */
    .info-box {
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow);
        border: 1px solid var(--gray-200);
        background: white;
        min-height: auto;
        padding: 1rem;
        display: flex;
        align-items: center;
        transition: all var(--transition);
    }

    .info-box:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
    }

    .info-box .info-box-icon {
        border-radius: var(--radius-md);
        width: 56px;
        height: 56px;
        font-size: 1.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .info-box-content {
        padding: 0 1rem;
    }

    .info-box-text {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--gray-500);
        font-weight: 500;
    }

    .info-box-number {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--gray-900);
        line-height: 1.2;
    }

    /* === TABS MODERNAS === */
    .nav-tabs {
        border-bottom: 1px solid var(--gray-200);
        gap: 0.25rem;
    }

    .nav-tabs .nav-link {
        border: none;
        border-bottom: 2px solid transparent;
        color: var(--gray-500);
        padding: 0.875rem 1.25rem;
        font-weight: 500;
        font-size: 0.875rem;
        background: transparent;
        border-radius: var(--radius) var(--radius) 0 0;
        transition: all var(--transition);
        margin-bottom: -1px;
    }

    .nav-tabs .nav-link:hover {
        color: var(--primary);
        background: var(--primary-50);
        border-bottom-color: var(--primary-light);
    }

    .nav-tabs .nav-link.active {
        color: var(--primary);
        border-bottom-color: var(--primary);
        background: white;
        font-weight: 600;
    }

    .nav-tabs .nav-link i {
        margin-right: 0.5rem;
        opacity: 0.7;
    }

    .nav-tabs .nav-link.active i {
        opacity: 1;
    }

    /* Corregir color de pestañas en card-primary */
    .card-primary.card-tabs .nav-tabs .nav-link {
        color: var(--gray-500) !important;
    }

    .card-primary.card-tabs .nav-tabs .nav-link:hover {
        color: var(--primary) !important;
    }

    .card-primary.card-tabs .nav-tabs .nav-link.active {
        color: var(--primary) !important;
    }

    /* === TABLAS MODERNAS (aireadas, estilo Stripe/Notion) === */
    .table {
        margin-bottom: 0;
    }

    .table thead th {
        border-top: none;
        border-bottom: 1px solid var(--gray-200);
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--gray-500);
        font-weight: 600;
        padding: 14px 16px;
        min-height: 48px;
        background: var(--gray-50);
    }

    .table td {
        padding: 14px 16px;
        min-height: 48px;
        vertical-align: middle;
        border-top: 1px solid var(--gray-100);
        color: var(--gray-700);
    }

    .table-hover tbody tr {
        transition: background var(--transition-fast);
    }

    .table-hover tbody tr:hover {
        background-color: var(--gray-50);
    }

    .table-striped tbody tr:nth-of-type(odd) {
        background-color: var(--gray-50);
    }

    /* === BOTONES MODERNOS === */
    .btn {
        border-radius: var(--radius);
        font-weight: 500;
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
        border: 1px solid transparent;
        transition: all var(--transition);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .btn:hover {
        transform: translateY(-1px);
        box-shadow: var(--shadow-md);
    }

    .btn:active {
        transform: translateY(0);
    }

    /* Mobile-first: feedback táctil (base = móvil) */
    .btn:active {
        opacity: 0.9;
    }
    .dropdown-item:active {
        background: var(--primary-100);
    }

    .btn-sm {
        padding: 0.375rem 0.75rem;
        font-size: 0.8125rem;
        border-radius: var(--radius-sm);
    }

    .btn-lg {
        padding: 0.75rem 1.5rem;
        font-size: 1rem;
        border-radius: var(--radius-md);
    }

    .btn-primary {
        background-color: var(--primary);
        border-color: var(--primary);
        color: white;
    }
    .btn-primary:hover {
        filter: brightness(0.92);
        border-color: var(--primary-dark);
    }
    .btn-primary:active {
        filter: brightness(0.88);
    }
    .btn-primary:focus {
        outline: 2px solid var(--primary);
        outline-offset: 2px;
    }

    .btn-secondary {
        background-color: var(--gray-600);
        border-color: var(--gray-600);
        color: white;
    }
    .btn-secondary:hover { filter: brightness(0.92); }
    .btn-secondary:active { filter: brightness(0.88); }
    .btn-secondary:focus {
        outline: 2px solid var(--gray-600);
        outline-offset: 2px;
    }

    .btn-success {
        background-color: var(--success);
        border-color: var(--success);
        color: white;
    }
    .btn-success:hover { filter: brightness(0.92); }
    .btn-success:active { filter: brightness(0.88); }
    .btn-success:focus {
        outline: 2px solid var(--success);
        outline-offset: 2px;
    }

    .btn-danger {
        background-color: var(--danger);
        border-color: var(--danger);
        color: white;
    }
    .btn-danger:hover { filter: brightness(0.92); }
    .btn-danger:active { filter: brightness(0.88); }
    .btn-danger:focus {
        outline: 2px solid var(--danger);
        outline-offset: 2px;
    }

    .btn-warning {
        background-color: var(--warning);
        border-color: var(--warning);
        color: white;
    }
    .btn-warning:hover { filter: brightness(0.92); }
    .btn-warning:active { filter: brightness(0.88); }
    .btn-warning:focus {
        outline: 2px solid var(--warning);
        outline-offset: 2px;
    }

    .btn-outline-primary {
        color: var(--primary);
        border-color: var(--primary);
        background: transparent;
    }

    .btn-outline-primary:hover {
        background: var(--primary);
        color: white;
    }

    .btn-outline-secondary {
        color: var(--gray-600);
        border-color: var(--gray-300);
        background: transparent;
    }

    .btn-outline-secondary:hover {
        background: var(--gray-100);
        color: var(--gray-700);
        border-color: var(--gray-400);
    }

    .btn-light {
        background: white;
        border-color: var(--gray-300);
        color: var(--gray-700);
    }

    .btn-light:hover {
        background: var(--gray-100);
        border-color: var(--gray-400);
    }

    /* === BADGES MODERNAS === */
    .badge {
        border-radius: var(--radius);
        padding: 0.35em 0.65em;
        font-weight: 500;
        font-size: 0.75rem;
        letter-spacing: 0.02em;
    }

    .badge-success {
        background: var(--success-light);
        color: #065f46;
    }
    .badge-danger {
        background: var(--danger-light);
        color: #991b1b;
    }
    .badge-warning {
        background: var(--warning-light);
        color: #92400e;
    }
    .badge-info {
        background: var(--info-light);
        color: #1e40af;
    }
    .badge-secondary {
        background: var(--gray-200);
        color: var(--gray-700);
    }
    .badge-primary {
        background: var(--primary-100);
        color: var(--primary-dark);
    }

    .badge-sm {
        padding: 0.2em 0.45em;
        font-size: 0.65rem;
    }

    .badge-lg {
        padding: 0.5em 0.9em;
        font-size: 0.9rem;
    }

    /* === FORMULARIOS MODERNOS === */
    .form-control {
        border-radius: var(--radius);
        border: 1px solid var(--gray-300);
        padding: 0.625rem 0.875rem;
        font-size: 0.875rem;
        line-height: 1.5;
        transition: all var(--transition);
        background: white;
    }

    .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px var(--primary-100);
        outline: none;
    }

    .form-control-sm {
        padding: 0.5rem 0.75rem;
        font-size: 0.8125rem;
    }

    select.form-control {
        appearance: none;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 0.75rem center;
        background-size: 16px;
        line-height: 1.5;
        min-height: 42px;
        padding-top: 0.5rem;
        padding-bottom: 0.5rem;
        padding-right: 2.5rem;
    }

    /* === DATATABLES LENGTH SELECT === */
    .dt-length select,
    .dataTables_length select,
    select.custom-select-sm {
        min-width: 70px !important;
        width: 70px !important;
        padding: 0.375rem 2rem 0.375rem 0.75rem !important;
        text-indent: 5px !important;
        background-position: right 0.5rem center !important;
    }

    label {
        font-weight: 500;
        font-size: 0.8125rem;
        color: var(--gray-700);
        margin-bottom: 0.375rem;
    }

    /* === CODE MODERNO === */
    code {
        background: var(--gray-100);
        padding: 0.2rem 0.5rem;
        border-radius: var(--radius-sm);
        font-size: 0.8125rem;
        color: var(--primary-dark);
        font-weight: 500;
        font-family: 'SF Mono', Monaco, 'Cascadia Code', monospace;
    }

    /* === ALERTAS MODERNAS === */
    .alert {
        border-radius: var(--radius-md);
        border: 1px solid;
        padding: 1rem 1.25rem;
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
    }

    .alert-info {
        background: var(--info-light);
        border-color: #93c5fd;
        color: #1e40af;
    }

    .alert-warning {
        background: var(--warning-light);
        border-color: #fcd34d;
        color: #92400e;
    }

    .alert-danger {
        background: var(--danger-light);
        border-color: #fca5a5;
        color: #991b1b;
    }

    .alert-success {
        background: var(--success-light);
        border-color: #6ee7b7;
        color: #065f46;
    }

    /* === SIDEBAR MODERNA (SaaS 2026) === */
    .main-sidebar {
        box-shadow: var(--shadow-lg);
    }

    .sidebar-dark-primary {
        background: var(--gray-800);
    }

    .sidebar-dark-primary .nav-sidebar > .nav-item > .nav-link {
        padding: 0.625rem 0.75rem;
        transition: all var(--transition);
    }
    .sidebar-dark-primary .nav-sidebar .nav-icon {
        opacity: 0.7;
    }
    .sidebar-dark-primary .nav-sidebar .nav-link:hover .nav-icon {
        opacity: 1;
    }
    .sidebar-dark-primary .nav-sidebar > .nav-item > .nav-link.active {
        background: rgba(255, 255, 255, 0.08);
        color: white;
        border-radius: var(--radius);
        margin: 0 0.5rem;
    }
    .sidebar-dark-primary .nav-sidebar > .nav-item > .nav-link.active .nav-icon {
        opacity: 1;
    }

    /* Superadmin sidebar - mismo estilo moderno */
    .sidebar-dark-warning {
        background: var(--gray-800);
    }
    .sidebar-dark-warning .nav-sidebar > .nav-item > .nav-link {
        padding: 0.625rem 0.75rem;
        transition: all var(--transition);
    }
    .sidebar-dark-warning .nav-sidebar .nav-icon {
        opacity: 0.7;
    }
    .sidebar-dark-warning .nav-sidebar .nav-link:hover .nav-icon {
        opacity: 1;
    }
    .sidebar-dark-warning .nav-sidebar > .nav-item > .nav-link.active {
        background: rgba(255, 255, 255, 0.08);
        color: white;
        border-radius: var(--radius);
        margin: 0 0.5rem;
    }

    /* === NAVBAR MODERNA === */
    .main-header {
        border-bottom: 1px solid var(--gray-200);
        background: white;
        box-shadow: var(--shadow-sm);
        padding-top: 0.5rem;
        padding-bottom: 0.5rem;
    }

    /* === BREADCRUMB === */
    .breadcrumb {
        background: transparent;
        padding: 0;
        margin: 0;
        font-size: 0.8125rem;
    }

    .breadcrumb-item a {
        color: var(--gray-500);
        transition: color var(--transition-fast);
    }

    .breadcrumb-item a:hover {
        color: var(--primary);
    }

    .breadcrumb-item.active {
        color: var(--gray-700);
        font-weight: 500;
    }

    /* === CONTENT HEADER === */
    .content-header h1 {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--gray-900);
        letter-spacing: -0.025em;
    }

    /* === STAT CARDS MODERNAS === */
    .stat-card {
        text-align: center;
        padding: 1.5rem 1rem;
        border-radius: var(--radius-lg);
        background: white;
        border: 1px solid var(--gray-200);
        box-shadow: var(--shadow);
        transition: all var(--transition);
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-lg);
    }

    .stat-card .stat-value {
        font-size: 2rem;
        font-weight: 700;
        line-height: 1.2;
        margin-bottom: 0.5rem;
    }

    .stat-card .stat-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        color: var(--gray-500);
        letter-spacing: 0.05em;
        font-weight: 500;
    }

    .stat-card.bg-success-light {
        background: var(--success-light);
        border-color: #a7f3d0;
    }
    .stat-card.bg-success-light .stat-value { color: var(--success); }

    .stat-card.bg-danger-light {
        background: var(--danger-light);
        border-color: #fecaca;
    }
    .stat-card.bg-danger-light .stat-value { color: var(--danger); }

    .stat-card.bg-warning-light {
        background: var(--warning-light);
        border-color: #fde68a;
    }
    .stat-card.bg-warning-light .stat-value { color: #b45309; }

    .stat-card.bg-info-light {
        background: var(--info-light);
        border-color: #bfdbfe;
    }
    .stat-card.bg-info-light .stat-value { color: var(--info); }

    /* === HEADER CLIENTE MODERNO === */
    .header-cliente {
        background: var(--primary);
        color: white;
        border-radius: var(--radius-lg);
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: var(--shadow-lg);
        position: relative;
        overflow: hidden;
    }

    .header-cliente::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        pointer-events: none;
    }

    .header-cliente .cliente-nombre {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        position: relative;
    }

    .header-cliente .cliente-doc {
        font-size: 0.9rem;
        opacity: 0.9;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.75rem;
        position: relative;
    }

    .header-cliente .badge {
        background: rgba(255,255,255,0.2);
        color: white;
        backdrop-filter: blur(10px);
    }

    .header-cliente code {
        background: rgba(255,255,255,0.2);
        color: white;
        border: none;
    }

    .header-cliente .btn-light {
        background: white;
        color: var(--primary);
        border: none;
    }

    .header-cliente .btn-light:hover {
        background: var(--gray-100);
    }

    .header-cliente .btn-outline-light {
        background: transparent;
        border-color: rgba(255,255,255,0.5);
        color: white;
    }

    .header-cliente .btn-outline-light:hover {
        background: rgba(255,255,255,0.1);
        border-color: white;
    }

    /* === SERVICIO CARD === */
    .servicio-card {
        border-left: 4px solid var(--info);
        transition: all var(--transition);
    }

    .servicio-card:hover {
        border-left-color: var(--primary);
        transform: translateX(4px);
        box-shadow: var(--shadow-lg);
    }

    /* === RECIBO ITEM === */
    .recibo-item {
        transition: all var(--transition);
        border-radius: var(--radius-md);
    }

    .recibo-item:hover {
        background: var(--gray-50);
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    .recibo-item.border-left-danger {
        border-left: 4px solid var(--danger) !important;
    }

    /* === FILTROS MODERNOS === */
    #filtros-recibo .btn {
        border-radius: var(--radius);
    }

    #filtros-recibo .btn.active {
        font-weight: 600;
        box-shadow: var(--shadow);
    }

    /* === DASHBOARD === */
    .dashboard-stat-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        border-radius: 0.5rem;
    }

    .dashboard-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
    }

    /* Mobile-first: stats en móvil */
    .dashboard-stat-card {
        margin-bottom: 1rem;
    }
    @media (min-width: 768px) {
        .dashboard-stat-card {
            margin-bottom: 1rem;
        }
    }

    /* === ADMINLTE (mobile-first: base = móvil) === */
    .content-header-mobile {
        padding: 0.75rem 1rem;
        padding-left: calc(1rem + var(--safe-left));
        padding-right: calc(1rem + var(--safe-right));
    }
    .page-title-mobile {
        font-size: 1.25rem;
        margin-bottom: 0.5rem;
    }
    .breadcrumb-mobile {
        font-size: 0.8125rem;
        padding: 0.5rem 0;
        margin-bottom: 0;
    }
    .breadcrumb-mobile li {
        margin-right: 0.25rem;
    }
    .content-mobile {
        padding: 0.75rem 0.5rem;
        padding-bottom: calc(1rem + var(--safe-bottom));
    }
    .container-fluid-mobile {
        padding-left: max(0.75rem, var(--safe-left));
        padding-right: max(0.75rem, var(--safe-right));
    }
    .content-wrapper {
        padding-bottom: var(--safe-bottom);
    }
    .wrapper {
        max-width: 100vw;
        overflow-x: hidden;
    }
    .sidebar-nav-mobile {
        padding: 0.5rem 0;
    }
    .nav-item-mobile {
        margin-bottom: 0.25rem;
    }
    .nav-link-sidebar-mobile {
        min-height: var(--touch-min);
        padding: 0.75rem 1rem;
        display: flex;
        align-items: center;
        font-size: 0.9375rem;
        -webkit-tap-highlight-color: transparent;
    }
    .nav-link-sidebar-mobile .nav-icon {
        width: 24px;
        font-size: 1.125rem;
        margin-right: 0.75rem;
    }
    .nav-link-sidebar-mobile p {
        margin: 0;
        font-size: 0.9375rem;
    }
    .nav-link-sidebar-mobile::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        z-index: 0;
    }

    @media (min-width: 768px) {
        .content-header-mobile {
            padding: 1rem 1.25rem;
            padding-left: calc(1.25rem + var(--safe-left));
            padding-right: calc(1.25rem + var(--safe-right));
        }
        .page-title-mobile {
            font-size: 1.5rem;
            margin-bottom: 0;
        }
        .breadcrumb-mobile {
            font-size: 0.875rem;
        }
        .content-mobile {
            padding: 1rem;
            padding-bottom: 1rem;
        }
        .container-fluid-mobile {
            padding-left: max(1rem, var(--safe-left));
            padding-right: max(1rem, var(--safe-right));
        }
        .content-wrapper {
            padding-bottom: 0;
        }
        .wrapper {
            max-width: none;
        }
    }

    /* === UTILIDADES === */
    .font-mono, .font-monospace {
        font-family: 'SF Mono', Monaco, 'Cascadia Code', monospace;
    }

    /* === PRELOADER === */
    .preloader {
        background: white !important;
    }

    /* === ELIMINAR SOMBRAS DE ADMINLTE === */
    .elevation-1, .elevation-2, .elevation-3, .elevation-4 {
        box-shadow: none !important;
    }

    /* === INFO COMPACT === */
    .info-compact {
        font-size: 0.9rem;
    }

    .info-compact .info-label {
        font-size: 0.8125rem;
        color: var(--gray-500);
        font-weight: 500;
    }

    .info-compact td {
        padding: 0.625rem 0;
        vertical-align: middle;
    }

    .info-compact tr:not(:last-child) {
        border-bottom: 1px solid var(--gray-100);
    }

    /* === TAB CONTENT === */
    #cliente-tab-content {
        display: block !important;
    }

    #cliente-tab-content .tab-pane {
        display: none !important;
    }

    #cliente-tab-content .tab-pane.show.active {
        display: block !important;
    }

    /* === MOBILE-FIRST: TAMAÑOS TÁCTILES (base = touch 44px) === */
    .btn-mobile-touch {
        min-height: var(--touch-min);
        min-width: var(--touch-min);
        padding: 0.625rem 1rem;
        font-size: 0.9375rem;
    }

    .form-control,
    .form-select,
    select.form-control {
        min-height: var(--touch-min);
        font-size: 16px; /* Previene zoom en iOS en móvil */
        padding: 0.625rem 0.875rem;
    }
    .form-group {
        margin-bottom: 1.25rem;
    }
    .form-group label {
        font-size: 0.9375rem;
        font-weight: 500;
        margin-bottom: 0.5rem;
    }
    @media (min-width: 768px) {
        .form-control,
        .form-select,
        select.form-control {
            font-size: 0.9375rem;
        }
    }

    /* === MOBILE-FIRST: base = móvil pequeño (≤576), luego min-width para progresión === */
    .btn {
        padding: 0.625rem 1rem;
        min-height: var(--touch-min);
        font-size: 0.9375rem;
    }
    .btn-sm {
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        min-height: 38px;
    }
    .btn-lg {
        padding: 0.75rem 1.25rem;
        min-height: 48px;
        font-size: 1rem;
    }
    .card-header {
        padding: 0.875rem 1rem;
    }
    .card-body {
        padding: 1rem;
    }
    .card-footer {
        padding: 0.875rem 1rem;
    }
    .info-box {
        padding: 0.875rem;
    }
    .info-box .info-box-icon {
        width: 48px;
        height: 48px;
        font-size: 1.25rem;
    }
    .info-box-number {
        font-size: 1.25rem;
    }
    .stat-card {
        padding: 1.25rem 0.875rem;
    }
    .stat-card .stat-value {
        font-size: 1.5rem;
    }
    .nav-tabs .nav-link {
        padding: 0.75rem 1rem;
        font-size: 0.875rem;
        min-height: var(--touch-min);
    }
    .header-cliente {
        padding: 1.25rem;
    }
    .header-cliente .cliente-nombre {
        font-size: 1.25rem;
    }
    .card-title {
        font-size: 1.125rem;
    }
    .mb-3 {
        margin-bottom: 1rem !important;
    }
    .mt-3 {
        margin-top: 1rem !important;
    }
    .table-responsive.d-none.d-md-block {
        display: none !important;
    }
    .d-md-none {
        display: block !important;
    }
    a, button, .btn, input[type="submit"], input[type="button"] {
        -webkit-tap-highlight-color: rgba(0, 0, 0, 0.1);
    }
    html {
        scroll-behavior: smooth;
        -webkit-overflow-scrolling: touch;
    }
    @media (min-width: 576px) {
        .btn {
            padding: 0.5rem 0.75rem;
            min-height: auto;
            font-size: 0.875rem;
        }
        .btn-sm {
            min-height: auto;
        }
        .btn-lg {
            min-height: auto;
        }
        .card-header {
            padding: 1rem 1.25rem;
        }
        .card-body {
            padding: 1.25rem;
        }
        .card-footer {
            padding: 1rem 1.25rem;
        }
        .info-box {
            padding: 1rem;
        }
        .info-box .info-box-icon {
            width: 60px;
            height: 60px;
            font-size: 1.5rem;
        }
        .info-box-number {
            font-size: 1.5rem;
        }
        .stat-card {
            padding: 1.5rem 1rem;
        }
        .stat-card .stat-value {
            font-size: 1.75rem;
        }
        .nav-tabs .nav-link {
            min-height: auto;
        }
        .header-cliente {
            padding: 1.5rem;
        }
        .header-cliente .cliente-nombre {
            font-size: 1.5rem;
        }
        .card-title {
            font-size: 1rem;
        }
        .mb-3 {
            margin-bottom: 1rem !important;
        }
        .mt-3 {
            margin-top: 1rem !important;
        }
    }
    
    /* Desktop: ocultar vista móvil */
    @media (min-width: 768px) {
        .d-md-none {
            display: none !important;
        }
        
        .d-none.d-md-block {
            display: block !important;
        }
    }

    /* === ANIMACIONES SUTILES === */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .card, .info-box, .stat-card {
        animation: fadeIn 0.3s ease-out;
    }

    /* === BTN GROUP === */
    .btn-group .btn {
        border-radius: 0;
    }

    .btn-group .btn:first-child {
        border-radius: var(--radius-sm) 0 0 var(--radius-sm);
    }

    .btn-group .btn:last-child {
        border-radius: 0 var(--radius-sm) var(--radius-sm) 0;
    }

    .btn-group-sm .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
    
    /* === MOBILE-FIRST: BOTONES EN FOOTERS (base = móvil) === */
    .card-footer,
    [x-slot="footer"] {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    .card-footer .btn,
    .card-footer a.btn,
    .card-footer button.btn,
    [x-slot="footer"] .btn,
    [x-slot="footer"] a.btn,
    [x-slot="footer"] button.btn,
    [x-slot="footer"] > * {
        width: 100% !important;
        margin: 0 !important;
    }
    .card-footer .float-right,
    [x-slot="footer"] .float-right {
        float: none !important;
        order: 2;
    }
    .card-tools .btn,
    .card-tools .x-btn,
    .card-tools a {
        min-height: var(--touch-min);
        margin-bottom: 0.5rem;
    }
    .card-tools {
        flex-direction: column;
        width: 100%;
        margin-top: 0.75rem;
    }
    .card-tools > * {
        width: 100%;
    }
    .d-flex.flex-wrap .btn,
    .d-flex.flex-wrap .x-btn {
        flex: 1 1 100%;
        margin-bottom: 0.5rem;
    }
    .d-flex.flex-wrap .btn:last-child,
    .d-flex.flex-wrap .x-btn:last-child {
        margin-bottom: 0;
    }
    @media (min-width: 768px) {
        .card-footer,
        [x-slot="footer"] {
            flex-direction: row;
            flex-wrap: wrap;
        }
        .card-footer .btn,
        .card-footer a.btn,
        .card-footer button.btn,
        [x-slot="footer"] .btn,
        [x-slot="footer"] a.btn,
        [x-slot="footer"] button.btn,
        [x-slot="footer"] > * {
            width: auto !important;
        }
        .card-footer .float-right,
        [x-slot="footer"] .float-right {
            float: right !important;
            order: 0;
            margin-left: auto;
        }
        .card-tools .btn,
        .card-tools .x-btn,
        .card-tools a {
            min-height: auto;
            margin-bottom: 0;
        }
        .card-tools {
            flex-direction: row;
            width: auto;
            margin-top: 0;
        }
        .card-tools > * {
            width: auto;
        }
        .d-flex.flex-wrap .btn,
        .d-flex.flex-wrap .x-btn {
            flex: 0 1 auto;
            margin-bottom: 0;
        }
    }

    /* === MOBILE-FIRST: INPUT GROUPS (base = móvil) === */
    .input-group {
        flex-wrap: wrap;
    }
    .input-group .form-control {
        flex: 1 1 100%;
        margin-bottom: 0.5rem;
        min-height: var(--touch-min);
        font-size: 16px;
    }
    .input-group-append,
    .input-group-prepend {
        flex: 1 1 auto;
        width: 100%;
        display: flex;
    }
    .input-group-append .btn,
    .input-group-prepend .btn,
    .input-group-append a,
    .input-group-prepend a {
        flex: 1;
        min-height: var(--touch-min);
        min-width: var(--touch-min);
    }
    .input-group-append > *,
    .input-group-prepend > * {
        flex: 1;
    }
    @media (min-width: 768px) {
        .input-group {
            flex-wrap: nowrap;
        }
        .input-group .form-control {
            flex: 1 1 auto;
            margin-bottom: 0;
            font-size: 0.9375rem;
        }
        .input-group-append,
        .input-group-prepend {
            width: auto;
            flex: 0 0 auto;
        }
        .input-group-append .btn,
        .input-group-prepend .btn,
        .input-group-append a,
        .input-group-prepend a {
            min-height: auto;
            min-width: auto;
        }
        .input-group-append > *,
        .input-group-prepend > * {
            flex: none;
        }
    }
    
    /* === MOBILE-FIRST: FORMULARIOS (base = móvil) === */
    form .form-group {
        margin-bottom: 1.25rem;
    }
    form label {
        font-size: 0.9375rem;
        font-weight: 500;
        margin-bottom: 0.5rem;
        display: block;
    }
    textarea.form-control {
        min-height: 88px;
        font-size: 16px;
    }
    select.form-control {
        min-height: var(--touch-min);
        font-size: 16px;
        padding: 0.625rem 0.875rem;
    }
    .custom-control {
        padding-left: 2rem;
        min-height: var(--touch-min);
        display: flex;
        align-items: center;
    }
    .custom-control-input {
        width: 20px;
        height: 20px;
    }
    .custom-control-label {
        font-size: 0.9375rem;
        padding-left: 0.5rem;
        cursor: pointer;
    }
    .container-fluid {
        padding-left: max(0.75rem, var(--safe-left));
        padding-right: max(0.75rem, var(--safe-right));
    }
    .row {
        margin-left: -0.5rem;
        margin-right: -0.5rem;
    }
    .row > * {
        padding-left: 0.5rem;
        padding-right: 0.5rem;
    }
    .d-md-none .card {
        margin-left: 0;
        margin-right: 0;
    }
    a.btn,
    button,
    .btn,
    input[type="submit"],
    input[type="button"] {
        min-height: var(--touch-min);
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .form-control-sm,
    .form-control.form-control-sm {
        min-height: var(--touch-min) !important;
        font-size: 16px !important;
        padding: 0.625rem 0.875rem !important;
    }
    .btn-sm {
        min-height: 38px;
        padding: 0.5rem 0.75rem;
    }
    .form-control-mobile {
        min-height: var(--touch-min) !important;
        font-size: 16px !important;
        padding: 0.625rem 0.875rem !important;
    }
    dl.row,
    .dl-mobile-optimized {
        margin: 0;
    }
    dl.row dt,
    .dl-mobile-optimized dt,
    dl.row dt.col-sm-4,
    dl.row dt.col-12.col-sm-4,
    .dl-mobile-optimized dt.col-sm-4,
    .dl-mobile-optimized dt.col-12.col-sm-4 {
        padding: 0.5rem 0.75rem;
        margin-bottom: 0.25rem;
        font-weight: 500;
        font-size: 0.875rem;
        color: var(--gray-600);
        width: 100%;
        flex: 0 0 100%;
        max-width: 100%;
    }
    dl.row dd,
    .dl-mobile-optimized dd,
    dl.row dd.col-sm-8,
    dl.row dd.col-12.col-sm-8,
    .dl-mobile-optimized dd.col-sm-8,
    .dl-mobile-optimized dd.col-12.col-sm-8 {
        padding: 0.5rem 0.75rem;
        margin-bottom: 0.75rem;
        font-size: 0.9375rem;
        width: 100%;
        flex: 0 0 100%;
        max-width: 100%;
    }
    .col-md-8.offset-md-2,
    .col-lg-8.offset-lg-2,
    .col-md-6.offset-md-3 {
        max-width: 100%;
        margin-left: 0;
        margin-right: 0;
    }
    .col-md-8,
    .col-lg-8 {
        width: 100%;
        flex: 0 0 100%;
        max-width: 100%;
    }
    @media (min-width: 576px) {
        dl.row dt.col-sm-4,
        dl.row dt.col-12.col-sm-4,
        .dl-mobile-optimized dt.col-sm-4,
        .dl-mobile-optimized dt.col-12.col-sm-4 {
            width: 33.333333%;
            flex: 0 0 33.333333%;
            max-width: 33.333333%;
        }
        dl.row dd.col-sm-8,
        dl.row dd.col-12.col-sm-8,
        .dl-mobile-optimized dd.col-sm-8,
        .dl-mobile-optimized dd.col-12.col-sm-8 {
            width: 66.666667%;
            flex: 0 0 66.666667%;
            max-width: 66.666667%;
        }
    }
    @media (min-width: 768px) {
        form .form-group {
            margin-bottom: 1rem;
        }
        form label {
            font-size: 0.875rem;
        }
        textarea.form-control {
            min-height: auto;
            font-size: 0.9375rem;
        }
        select.form-control {
            min-height: auto;
            font-size: 0.9375rem;
        }
        .custom-control {
            min-height: auto;
        }
        .container-fluid {
            padding-left: 15px;
            padding-right: 15px;
        }
        .row {
            margin-left: -15px;
            margin-right: -15px;
        }
        .row > * {
            padding-left: 15px;
            padding-right: 15px;
        }
        a.btn,
        button,
        .btn,
        input[type="submit"],
        input[type="button"] {
            min-height: auto;
        }
        .form-control-sm,
        .form-control.form-control-sm {
            min-height: auto !important;
            font-size: 0.8125rem !important;
            padding: 0.25rem 0.5rem !important;
        }
        .btn-sm {
            min-height: auto;
        }
        .form-control-mobile {
            min-height: auto !important;
            font-size: 0.9375rem !important;
        }
        .col-md-8.offset-md-2,
        .col-lg-8.offset-lg-2,
        .col-md-6.offset-md-3 {
            max-width: 66.666667%;
            margin-left: 16.666667%;
        }
        .col-md-8,
        .col-lg-8 {
            width: 66.666667%;
            flex: 0 0 66.666667%;
            max-width: 66.666667%;
        }
    }

    /* === COLORES ADICIONALES (sólidos) === */
    .bg-orange {
        background-color: #ea580c !important;
        color: white !important;
    }

    .bg-purple {
        background-color: #9333ea !important;
        color: white !important;
    }

    .bg-indigo {
        background-color: var(--primary) !important;
        color: white !important;
    }

    .bg-info {
        background-color: var(--info) !important;
    }

    .bg-success {
        background-color: var(--success) !important;
    }

    .bg-danger {
        background-color: var(--danger) !important;
    }

    .bg-warning {
        background-color: var(--warning) !important;
    }

    .bg-secondary {
        background-color: var(--secondary) !important;
    }

    /* === LINKS === */
    a {
        color: var(--primary);
        transition: color var(--transition-fast);
    }

    a:hover {
        color: var(--primary-dark);
        text-decoration: none;
    }

    /* === EMPTY STATE === */
    .empty-state {
        text-align: center;
        padding: 3rem 1.5rem;
    }

    .empty-state i {
        font-size: 4rem;
        color: var(--gray-500);
        margin-bottom: 1rem;
    }

    .empty-state h5 {
        color: var(--gray-600);
        margin-bottom: 0.5rem;
    }

    .empty-state p {
        color: var(--gray-600);
        margin-bottom: 1.5rem;
    }

    /* === DROPDOWN === */
    .dropdown-menu {
        border-radius: var(--radius);
        border: 1px solid var(--gray-200);
        box-shadow: var(--shadow-lg);
        padding: 0.5rem;
    }

    /* Evitar que los dropdowns de acciones queden recortados o superpuestos */
    .dropdown-actions-fix,
    .actions-menu-dropdown,
    .dropdown-menu.show {
        z-index: 9999 !important;
    }

    /* Dropdown dentro de .table-responsive: no recortar (mismo comportamiento que fuera de tabla) */
    .table-responsive .btn-group,
    .table-responsive .dropdown {
        position: static;
    }
    .table-responsive .btn-group .dropdown-menu,
    .table-responsive .dropdown .dropdown-menu {
        position: absolute;
    }

    /* Scroll vertical en dropdowns de acciones cuando hay muchas opciones */
    .dropdown-actions-fix,
    .actions-menu-dropdown,
    .dropdown-menu-scroll {
        max-height: 70vh;
        overflow-y: auto;
    }

    .dropdown-item {
        border-radius: var(--radius-sm);
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
        transition: all var(--transition-fast);
    }

    .dropdown-item:hover {
        background: var(--primary-50);
        color: var(--primary);
    }

    /* === MODAL === */
    .modal-content {
        border-radius: var(--radius-lg);
        border: none;
        box-shadow: var(--shadow-xl);
        transition: box-shadow var(--transition), transform var(--transition);
    }

    .modal-header {
        border-bottom: 1px solid var(--gray-200);
        padding: 1.25rem 1.5rem;
    }

    .modal-body {
        padding: 1.5rem;
    }

    .modal-footer {
        border-top: 1px solid var(--gray-200);
        padding: 1rem 1.5rem;
    }

    /* Mobile-first: modales full-screen en base (móvil) */
    .modal-dialog {
        margin: 0;
        max-width: 100%;
        min-height: 100vh;
        min-height: 100dvh;
    }
    .modal-dialog .modal-content {
        min-height: 100vh;
        min-height: 100dvh;
        border-radius: 0;
        border: none;
    }
    .modal-dialog .modal-header {
        padding: 1rem 1rem 1rem calc(1rem + var(--safe-left));
        padding-right: calc(1rem + var(--safe-right));
    }
    .modal-dialog .modal-body {
        padding: 1rem;
        padding-left: calc(1rem + var(--safe-left));
        padding-right: calc(1rem + var(--safe-right));
        padding-bottom: calc(1rem + var(--safe-bottom));
        -webkit-overflow-scrolling: touch;
        overflow-y: auto;
    }
    .modal-dialog .modal-footer {
        padding: 1rem;
        padding-left: calc(1rem + var(--safe-left));
        padding-right: calc(1rem + var(--safe-right));
        padding-bottom: calc(1rem + var(--safe-bottom));
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    .modal-dialog .modal-footer .btn {
        flex: 1 1 auto;
        min-height: var(--touch-min);
    }
    @media (min-width: 768px) {
        .modal-dialog {
            margin: 1.75rem auto;
            max-width: 500px;
            min-height: 0;
        }
        .modal-dialog .modal-content {
            min-height: 0;
            border-radius: var(--radius-lg);
            border: 1px solid var(--gray-200);
        }
        .modal-dialog .modal-header {
            padding: 1rem 1.5rem;
        }
        .modal-dialog .modal-body {
            padding: 1.5rem;
            padding-bottom: 1.5rem;
        }
        .modal-dialog .modal-footer {
            padding: 1rem 1.5rem;
            flex-wrap: nowrap;
        }
        .modal-dialog .modal-footer .btn {
            flex: none;
            min-height: auto;
        }
    }

    /* === PAGINATION === */
    .pagination {
        gap: 0.25rem;
    }

    .page-link {
        border-radius: var(--radius) !important;
        border: 1px solid var(--gray-200);
        color: var(--gray-600);
        padding: 0.5rem 0.875rem;
        font-size: 0.875rem;
        transition: all var(--transition-fast);
    }

    .page-link:hover {
        background: var(--primary-50);
        border-color: var(--primary);
        color: var(--primary);
    }

    .page-item.active .page-link {
        background: var(--primary);
        border-color: var(--primary);
        color: white;
    }

    /* Mobile-first: paginación táctil (base = touch 44px) */
    .pagination {
        flex-wrap: wrap;
        gap: 0.5rem;
        justify-content: center;
    }
    .page-link {
        min-width: var(--touch-min);
        min-height: var(--touch-min);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.625rem;
        font-size: 0.9375rem;
    }
    .page-item:not(.active) .page-link:active {
        background: var(--gray-100);
    }
    @media (min-width: 768px) {
        .pagination {
            flex-wrap: nowrap;
            gap: 0.25rem;
        }
        .page-link {
            min-width: auto;
            min-height: auto;
            padding: 0.5rem 0.875rem;
            font-size: 0.875rem;
        }
    }

    /* === INPUT GROUP === */
    .input-group .form-control {
        border-radius: var(--radius) 0 0 var(--radius);
    }

    .input-group .btn {
        border-radius: 0 var(--radius) var(--radius) 0;
    }

    .input-group-text {
        background: var(--gray-100);
        border: 1px solid var(--gray-300);
        color: var(--gray-500);
    }

    /* === SCROLLBAR === */
    ::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    ::-webkit-scrollbar-track {
        background: var(--gray-100);
    }

    ::-webkit-scrollbar-thumb {
        background: var(--gray-300);
        border-radius: 4px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: var(--gray-400);
    }

    /* === OPACITY === */
    .opacity-50 {
        opacity: 0.5;
    }

    .opacity-75 {
        opacity: 0.75;
    }

    /* === QUICK ACTIONS === */
    .quick-actions .btn {
        flex: 1;
    }

    /* === CUSTOM FILE INPUT === */
    .custom-file {
        position: relative;
        display: inline-block;
        width: 100%;
        height: calc(1.5em + 0.75rem + 2px);
        margin-bottom: 0;
    }

    .custom-file-input {
        position: relative;
        z-index: 2;
        width: 100%;
        height: calc(1.5em + 0.75rem + 2px);
        margin: 0;
        opacity: 0;
    }

    .custom-file-input:focus ~ .custom-file-label {
        border-color: var(--primary);
        box-shadow: 0 0 0 0.2rem rgba(15, 98, 254, 0.25);
    }

    .custom-file-input:disabled ~ .custom-file-label,
    .custom-file-input[disabled] ~ .custom-file-label {
        background-color: var(--gray-200);
        opacity: 0.65;
    }

    .custom-file-label {
        position: absolute;
        top: 0;
        right: 0;
        left: 0;
        z-index: 1;
        height: calc(1.5em + 0.75rem + 2px);
        padding: 0.375rem 0.75rem;
        font-weight: 400;
        line-height: 1.5;
        color: var(--gray-700);
        background-color: #fff;
        border: 1px solid var(--gray-300);
        border-radius: var(--radius);
        cursor: pointer;
    }

    .custom-file-label::after {
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        z-index: 3;
        display: block;
        height: calc(1.5em + 0.75rem);
        padding: 0.375rem 0.75rem;
        line-height: 1.5;
        color: var(--gray-700);
        content: attr(data-browse);
        background-color: var(--gray-100);
        border-left: inherit;
        border-radius: 0 var(--radius) var(--radius) 0;
    }
</style>
@endpush
