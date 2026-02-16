<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Instalador - Admin ISP</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Mobile first */
        :root { --primary: #007bff; --success: #28a745; --danger: #dc3545; --touch: 44px; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html { -webkit-text-size-adjust: 100%; }
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: #f4f6f9; min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 0.75rem; font-size: 1rem; }
        .installer-container { width: 100%; max-width: 100%; }
        .installer-card { background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,.08); padding: 1rem; margin-bottom: 0.75rem; overflow: hidden; }
        .installer-header { text-align: center; margin-bottom: 1rem; }
        .installer-header h1 { font-size: 1.35rem; color: #333; margin-bottom: 0.2rem; }
        .installer-header p { color: #666; font-size: 0.875rem; }
        .installer-logo { width: 56px; height: 56px; margin: 0 auto 0.75rem; display: block; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 0.35rem; color: #333; font-size: 0.9rem; }
        .form-control { width: 100%; padding: 0.65rem 0.75rem; border: 1px solid #ced4da; border-radius: 6px; font-size: 16px; min-height: var(--touch); }
        .form-control:focus { border-color: var(--primary); outline: none; box-shadow: 0 0 0 3px rgba(0,123,255,.15); }
        .form-control-sm { padding: 0.5rem 0.65rem; font-size: 16px; min-height: 40px; }
        .btn { display: inline-block; padding: 0.65rem 1rem; font-size: 1rem; font-weight: 600; text-align: center; border: none; border-radius: 6px; cursor: pointer; text-decoration: none; transition: background .2s; min-height: var(--touch); line-height: 1.3; }
        .btn-primary { background: var(--primary); color: #fff; }
        .btn-primary:hover { background: #0056b3; color: #fff; }
        .btn-success { background: var(--success); color: #fff; }
        .btn-success:hover { background: #218838; color: #fff; }
        .btn-block { width: 100%; display: block; }
        .alert { padding: 0.75rem 1rem; border-radius: 6px; margin-bottom: 1rem; font-size: 0.9rem; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        .alert-warning { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
        .text-danger { color: var(--danger); font-size: 0.85rem; margin-top: 0.25rem; }
        .requirement-item { display: flex; align-items: center; padding: 0.6rem 0; border-bottom: 1px solid #eee; min-height: var(--touch); }
        .requirement-item:last-child { border-bottom: none; }
        .requirement-icon { width: 24px; margin-right: 0.75rem; font-size: 1.1rem; flex-shrink: 0; }
        .requirement-ok { color: var(--success); }
        .requirement-fail { color: var(--danger); }
        .text-muted { color: #6c757d; font-size: 0.85rem; }
        .steps { display: flex; justify-content: flex-start; gap: 0.35rem; margin-bottom: 1.25rem; flex-wrap: nowrap; overflow-x: auto; -webkit-overflow-scrolling: touch; padding-bottom: 0.25rem; }
        .step { padding: 0.4rem 0.6rem; border-radius: 20px; font-size: 0.8rem; background: #e9ecef; color: #6c757d; white-space: nowrap; flex-shrink: 0; }
        .step.active { background: var(--primary); color: #fff; }
        .step.done { background: var(--success); color: #fff; }
        .btn-outline-primary { background: #fff; color: var(--primary); border: 1px solid var(--primary); }
        .btn-outline-primary:hover { background: rgba(0,123,255,.08); color: var(--primary); }
        .btn-outline-secondary { background: #fff; color: #6c757d; border: 1px solid #ced4da; }
        .btn-outline-secondary:hover { background: #f8f9fa; color: #495057; }
        .installer-section { margin-bottom: 1.25rem; }
        .installer-section-title { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: #6c757d; margin-bottom: 0.6rem; padding-bottom: 0.3rem; border-bottom: 1px solid #e9ecef; }
        .installer-section-box { background: #f8f9fa; border-radius: 8px; padding: 1rem; border: 1px solid #e9ecef; }
        .installer-flow { display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap; font-size: 0.75rem; color: #6c757d; margin-bottom: 1rem; }
        .installer-flow span { padding: 0.25rem 0.4rem; background: #e9ecef; border-radius: 4px; }
        .installer-flow .arrow { color: #adb5bd; font-size: 0.7rem; }
        .result-box { padding: 0.65rem 0.9rem; border-radius: 6px; font-size: 0.9rem; border-left: 4px solid; word-break: break-word; }
        .result-box.success { background: #d4edda; border-left-color: var(--success); color: #155724; }
        .result-box.danger { background: #f8d7da; border-left-color: var(--danger); color: #721c24; }
        .result-box.info { background: #d1ecf1; border-left-color: #17a2b8; color: #0c5460; }
        .result-box.warning { background: #fff3cd; border-left-color: #ffc107; color: #856404; }
        .form-row-2 { display: grid; grid-template-columns: 1fr; gap: 0.75rem; }
        .installer-actions { display: flex; flex-direction: column; gap: 0.5rem; }
        .installer-actions .btn { width: 100%; }
        .password-wrap { display: flex; flex-direction: column; gap: 0.35rem; width: 100%; }
        .password-wrap .form-control { flex: 1; min-width: 0; }
        .password-wrap .btn-password-toggle { width: 100%; }
        .row-of-fields { display: flex; flex-direction: column; gap: 0.5rem; }
        .row-of-fields .form-control { flex: 1; min-width: 0; }
        .output-log { background: #1e1e1e; color: #d4d4d4; padding: 1rem; border-radius: 6px; font-family: monospace; font-size: 0.8rem; max-height: 200px; overflow-y: auto; white-space: pre-wrap; margin-top: 1rem; word-break: break-all; }
        .spinner { display: inline-block; width: 1rem; height: 1rem; border: 2px solid #fff; border-radius: 50%; border-top-color: transparent; animation: spin 0.8s linear infinite; vertical-align: middle; margin-right: 0.5rem; }
        @keyframes spin { to { transform: rotate(360deg); } }
        /* 480px+ */
        @media (min-width: 480px) {
            body { padding: 1rem; }
            .installer-container { max-width: 520px; }
            .installer-card { padding: 1.5rem; margin-bottom: 1rem; }
            .installer-header h1 { font-size: 1.5rem; }
            .installer-logo { width: 64px; height: 64px; }
            .form-row-2 { grid-template-columns: 1fr 90px; }
            .installer-section-box { padding: 1.25rem; }
            .steps { justify-content: center; flex-wrap: wrap; overflow-x: visible; }
            .step { font-size: 0.85rem; padding: 0.35rem 0.75rem; }
        }
        /* 600px+ */
        @media (min-width: 600px) {
            .installer-card { padding: 2rem; }
            .form-row-2 { grid-template-columns: 1fr 100px; }
            .installer-actions { flex-direction: row; flex-wrap: wrap; }
            .installer-actions .btn { width: auto; }
            .password-wrap { flex-direction: row; align-items: center; max-width: 100%; }
            .password-wrap .btn-password-toggle { width: auto; }
            .row-of-fields { flex-direction: row; flex-wrap: wrap; align-items: flex-start; }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="installer-container">
        <div class="installer-header">
            <img src="{{ asset('favicon.svg') }}" alt="Admin ISP" class="installer-logo">
            <h1>Instalador Admin ISP</h1>
            <p>Configura tu sistema paso a paso</p>
        </div>
        @yield('content')
    </div>
    <script>
    document.querySelectorAll('.btn-password-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = this.getAttribute('data-target');
            var input = document.getElementById(id);
            if (!input) return;
            if (input.type === 'password') { input.type = 'text'; this.textContent = 'Ocultar'; }
            else { input.type = 'password'; this.textContent = 'Ver'; }
        });
    });
    </script>
    @stack('scripts')
</body>
</html>
