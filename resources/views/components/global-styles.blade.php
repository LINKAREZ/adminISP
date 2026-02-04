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
        /* Colores Primarios - Índigo moderno */
        --primary: #4f46e5;
        --primary-light: #6366f1;
        --primary-dark: #4338ca;
        --primary-50: #eef2ff;
        --primary-100: #e0e7ff;

        /* Colores Secundarios */
        --secondary: #64748b;
        --secondary-light: #94a3b8;
        --secondary-dark: #475569;

        /* Estados */
        --success: #10b981;
        --success-light: #d1fae5;
        --danger: #ef4444;
        --danger-light: #fee2e2;
        --warning: #f59e0b;
        --warning-light: #fef3c7;
        --info: #3b82f6;
        --info-light: #dbeafe;

        /* Neutros */
        --gray-50: #f8fafc;
        --gray-100: #f1f5f9;
        --gray-200: #e2e8f0;
        --gray-300: #cbd5e1;
        --gray-400: #94a3b8;
        --gray-500: #64748b;
        --gray-600: #475569;
        --gray-700: #334155;
        --gray-800: #1e293b;
        --gray-900: #0f172a;

        /* Sombras modernas */
        --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
        --shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
        --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);

        /* Bordes */
        --radius-sm: 0.375rem;
        --radius: 0.5rem;
        --radius-md: 0.75rem;
        --radius-lg: 1rem;
        --radius-xl: 1.5rem;

        /* Transiciones */
        --transition-fast: 150ms cubic-bezier(0.4, 0, 0.2, 1);
        --transition: 200ms cubic-bezier(0.4, 0, 0.2, 1);
        --transition-slow: 300ms cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* === TIPOGRAFÍA MODERNA === */
    body {
        font-family: 'Source Sans Pro', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        font-size: 0.875rem;
        color: var(--gray-700);
        background: var(--gray-50);
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        /* Mobile-first: prevenir zoom en inputs */
        -webkit-text-size-adjust: 100%;
    }
    
    /* Mobile-first: Base font size más grande en móviles */
    @media (max-width: 767.98px) {
        body {
            font-size: 0.9375rem;
        }
    }

    h1, h2, h3, h4, h5, h6, .h1, .h2, .h3, .h4, .h5, .h6 {
        font-weight: 600;
        color: var(--gray-900);
        letter-spacing: -0.025em;
    }

    /* === CARDS MODERNAS === */
    .card {
        border-radius: var(--radius-lg);
        border: 1px solid var(--gray-200);
        box-shadow: var(--shadow);
        background: white;
        margin-bottom: 1.5rem;
        transition: box-shadow var(--transition), transform var(--transition);
    }

    .card:hover {
        box-shadow: var(--shadow-md);
    }
    
    /* Mobile-first: Cards más compactas en móviles */
    @media (max-width: 767.98px) {
        .card {
            margin-bottom: 1rem;
            border-radius: var(--radius);
        }
        
        .card:last-child {
            margin-bottom: 0;
        }
    }

    .card-header {
        background: linear-gradient(to right, var(--gray-50), white);
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
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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

    /* === TABLAS MODERNAS === */
    .table {
        margin-bottom: 0;
    }

    .table thead th {
        border-top: none;
        border-bottom: 2px solid var(--gray-200);
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--gray-500);
        font-weight: 600;
        padding: 0.875rem 1rem;
        background: var(--gray-50);
    }

    .table td {
        padding: 0.875rem 1rem;
        vertical-align: middle;
        border-top: 1px solid var(--gray-100);
        color: var(--gray-700);
    }

    .table-hover tbody tr {
        transition: background var(--transition-fast);
    }

    .table-hover tbody tr:hover {
        background-color: var(--primary-50);
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
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        border-color: var(--primary);
        color: white;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%);
        border-color: var(--primary-light);
    }

    .btn-secondary {
        background: var(--gray-600);
        border-color: var(--gray-600);
        color: white;
    }

    .btn-success {
        background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
        border-color: var(--success);
        color: white;
    }

    .btn-danger {
        background: linear-gradient(135deg, var(--danger) 0%, #dc2626 100%);
        border-color: var(--danger);
        color: white;
    }

    .btn-warning {
        background: linear-gradient(135deg, var(--warning) 0%, #d97706 100%);
        border-color: var(--warning);
        color: white;
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

    /* === SIDEBAR MODERNA === */
    .main-sidebar {
        box-shadow: var(--shadow-lg);
    }

    .sidebar-dark-primary {
        background: linear-gradient(180deg, var(--gray-900) 0%, var(--gray-800) 100%);
    }

    .sidebar-dark-primary .nav-sidebar > .nav-item > .nav-link.active {
        background: var(--primary);
        color: white;
        border-radius: var(--radius);
        margin: 0 0.5rem;
    }

    /* === NAVBAR MODERNA === */
    .main-header {
        border-bottom: 1px solid var(--gray-200);
        background: white;
        box-shadow: var(--shadow-sm);
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
        background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
        border-color: #a7f3d0;
    }
    .stat-card.bg-success-light .stat-value { color: var(--success); }

    .stat-card.bg-danger-light {
        background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
        border-color: #fecaca;
    }
    .stat-card.bg-danger-light .stat-value { color: var(--danger); }

    .stat-card.bg-warning-light {
        background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
        border-color: #fde68a;
    }
    .stat-card.bg-warning-light .stat-value { color: #b45309; }

    .stat-card.bg-info-light {
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        border-color: #bfdbfe;
    }
    .stat-card.bg-info-light .stat-value { color: var(--info); }

    /* === HEADER CLIENTE MODERNO === */
    .header-cliente {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
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

    @media (max-width: 767.98px) {
        .dashboard-stat-card {
            margin-bottom: 1rem;
        }
    }

    /* === ADMINLTE MOBILE === */
    @media (max-width: 767.98px) {
        .content-header-mobile {
            padding: 0.75rem 1rem;
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
        }

        .container-fluid-mobile {
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }
    }

    /* === SIDEBAR MOBILE === */
    @media (max-width: 767.98px) {
        .sidebar-nav-mobile {
            padding: 0.5rem 0;
        }

        .nav-item-mobile {
            margin-bottom: 0.25rem;
        }

        .nav-link-sidebar-mobile {
            min-height: 48px;
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

    /* === MOBILE-FIRST: TAMAÑOS TÁCTILES === */
    /* Botones con tamaño mínimo táctil (44x44px) en móviles */
    .btn-mobile-touch {
        min-height: 44px;
        min-width: 44px;
        padding: 0.625rem 1rem;
        font-size: 0.9375rem;
    }
    
    /* Inputs grandes en móviles para mejor usabilidad */
    @media (max-width: 767.98px) {
        .form-control,
        .form-select,
        select.form-control {
            min-height: 44px;
            font-size: 16px; /* Previene zoom en iOS */
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
    }

    /* === RESPONSIVE === */
    @media (max-width: 576px) {
        .btn { 
            padding: 0.625rem 1rem; 
            min-height: 44px;
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
            min-height: 44px;
        }
        .header-cliente { 
            padding: 1.25rem; 
        }
        .header-cliente .cliente-nombre { 
            font-size: 1.25rem; 
        }
        
        /* Input groups en móviles */
        .input-group .form-control {
            min-height: 44px;
        }
        
        .input-group-append .btn,
        .input-group-prepend .btn {
            min-height: 44px;
            min-width: 44px;
        }
        
        /* Cards más compactas en móviles */
        .card-title {
            font-size: 1.125rem;
        }
        
        /* Espaciado optimizado */
        .mb-3 {
            margin-bottom: 1rem !important;
        }
        .mt-3 {
            margin-top: 1rem !important;
        }
        
        /* Tablas ocultas en móviles */
        .table-responsive.d-none.d-md-block {
            display: none !important;
        }
        
        /* Cards móviles visibles */
        .d-md-none {
            display: block !important;
        }
        
        /* Mejoras de accesibilidad táctil */
        a, button, .btn, input[type="submit"], input[type="button"] {
            -webkit-tap-highlight-color: rgba(0, 0, 0, 0.1);
        }
        
        /* Scroll suave */
        html {
            scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
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
    
    /* === MOBILE-FIRST: BOTONES EN FOOTERS === */
    @media (max-width: 767.98px) {
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
        
        /* Botones en acciones de card */
        .card-tools .btn,
        .card-tools .x-btn,
        .card-tools a {
            min-height: 44px;
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
        
        /* Botones en d-flex */
        .d-flex.flex-wrap .btn,
        .d-flex.flex-wrap .x-btn {
            flex: 1 1 100%;
            margin-bottom: 0.5rem;
        }
        
        .d-flex.flex-wrap .btn:last-child,
        .d-flex.flex-wrap .x-btn:last-child {
            margin-bottom: 0;
        }
    }
    
    /* === MOBILE-FIRST: INPUT GROUPS === */
    @media (max-width: 767.98px) {
        .input-group {
            flex-wrap: wrap;
        }
        
        .input-group .form-control {
            flex: 1 1 100%;
            margin-bottom: 0.5rem;
            min-height: 44px;
            font-size: 16px; /* Previene zoom en iOS */
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
            min-height: 44px;
            min-width: 44px;
        }
        
        /* Input groups con múltiples botones */
        .input-group-append > *,
        .input-group-prepend > * {
            flex: 1;
        }
    }
    
    /* === MOBILE-FIRST: FORMULARIOS === */
    @media (max-width: 767.98px) {
        /* Formularios más espaciados */
        form .form-group {
            margin-bottom: 1.25rem;
        }
        
        /* Labels más grandes y legibles */
        form label {
            font-size: 0.9375rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
            display: block;
        }
        
        /* Textareas más grandes */
        textarea.form-control {
            min-height: 88px;
            font-size: 16px; /* Previene zoom en iOS */
        }
        
        /* Selects más grandes */
        select.form-control {
            min-height: 44px;
            font-size: 16px; /* Previene zoom en iOS */
            padding: 0.625rem 0.875rem;
        }
        
        /* Checkboxes y radios más grandes */
        .custom-control {
            padding-left: 2rem;
            min-height: 44px;
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
        
        /* Mejoras generales de usabilidad móvil */
        .container-fluid {
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }
        
        .row {
            margin-left: -0.5rem;
            margin-right: -0.5rem;
        }
        
        .row > * {
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }
        
        /* Cards en vista móvil sin márgenes extra */
        .d-md-none .card {
            margin-left: 0;
            margin-right: 0;
        }
        
        /* Mejor contraste en móviles */
        .text-muted {
            color: #6c757d !important;
        }
        
        /* Links táctiles en móviles (solo para botones y acciones) */
        a.btn,
        button,
        .btn,
        input[type="submit"],
        input[type="button"] {
            min-height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        /* Override para form-control-sm en móviles - convertir a tamaño táctil */
        .form-control-sm,
        .form-control.form-control-sm {
            min-height: 44px !important;
            font-size: 16px !important;
            padding: 0.625rem 0.875rem !important;
        }
        
        .btn-sm {
            min-height: 38px;
            padding: 0.5rem 0.75rem;
        }
        
        /* Asegurar que form-control-mobile tenga prioridad */
        .form-control-mobile {
            min-height: 44px !important;
            font-size: 16px !important;
            padding: 0.625rem 0.875rem !important;
        }
        
        /* Description lists (dl dt dd) optimizados para móviles */
        dl.row,
        .dl-mobile-optimized {
            margin: 0;
        }
        
        dl.row dt,
        .dl-mobile-optimized dt {
            padding: 0.5rem 0.75rem;
            margin-bottom: 0.25rem;
            font-weight: 500;
            font-size: 0.875rem;
            color: var(--gray-600);
            width: 100%;
        }
        
        dl.row dd,
        .dl-mobile-optimized dd {
            padding: 0.5rem 0.75rem;
            margin-bottom: 0.75rem;
            font-size: 0.9375rem;
            width: 100%;
        }
        
        /* En móviles, dt y dd se apilan verticalmente */
        @media (max-width: 575.98px) {
            dl.row dt.col-sm-4,
            dl.row dt.col-12.col-sm-4,
            .dl-mobile-optimized dt.col-sm-4,
            .dl-mobile-optimized dt.col-12.col-sm-4 {
                width: 100%;
                flex: 0 0 100%;
                max-width: 100%;
            }
            
            dl.row dd.col-sm-8,
            dl.row dd.col-12.col-sm-8,
            .dl-mobile-optimized dd.col-sm-8,
            .dl-mobile-optimized dd.col-12.col-sm-8 {
                width: 100%;
                flex: 0 0 100%;
                max-width: 100%;
            }
        }
        
        /* Formularios con offset en móviles */
        .col-md-8.offset-md-2,
        .col-lg-8.offset-lg-2,
        .col-md-6.offset-md-3 {
            max-width: 100%;
            margin-left: 0;
            margin-right: 0;
        }
        
        /* Columnas responsivas en móviles */
        .col-md-8,
        .col-lg-8 {
            width: 100%;
            flex: 0 0 100%;
            max-width: 100%;
        }
    }

    /* === COLORES ADICIONALES === */
    .bg-orange {
        background: linear-gradient(135deg, #f97316 0%, #ea580c 100%) !important;
        color: white !important;
    }

    .bg-purple {
        background: linear-gradient(135deg, #a855f7 0%, #9333ea 100%) !important;
        color: white !important;
    }

    .bg-indigo {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%) !important;
        color: white !important;
    }

    .bg-info {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%) !important;
    }

    .bg-success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
    }

    .bg-danger {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important;
    }

    .bg-warning {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
    }

    .bg-secondary {
        background: linear-gradient(135deg, #64748b 0%, #475569 100%) !important;
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
        color: var(--gray-300);
        margin-bottom: 1rem;
    }

    .empty-state h5 {
        color: var(--gray-600);
        margin-bottom: 0.5rem;
    }

    .empty-state p {
        color: var(--gray-500);
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
        box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.25);
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
