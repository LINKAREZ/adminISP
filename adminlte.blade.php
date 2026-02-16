<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, user-scalable=yes">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>Admin ISP</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" href="{{ asset('favicon.svg') }}">

    {{-- Tipografía unificada: Inter (tendencias 2024-2025) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    {{-- CARGAR CSS AdminLTE --}}
    @vite(['resources/css/adminlte.css'])

    {{-- Font Awesome CDN (cargar DESPUÉS del CSS compilado para que tenga prioridad) --}}
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@7.1.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />

    @stack('styles')
</head>
@php
    $user = auth()->user();
    $isSuperAdmin = $user && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin();
@endphp
<body class="hold-transition sidebar-mini layout-fixed {{ $isSuperAdmin ? 'superadmin-panel' : '' }}">
<div class="wrapper">

    <!-- Preloader -->
    <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="{{ asset('favicon.svg') }}" alt="Admin ISP Logo" height="60" width="60">
    </div>

    <!-- Navbar -->
    @include('layouts.partials.adminlte-navbar')

    <!-- Main Sidebar Container -->
    @if($isSuperAdmin)
        @include('layouts.partials.adminlte-sidebar-superadmin')
    @else
        @include('layouts.partials.adminlte-sidebar')
    @endif

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper @if(View::hasSection('hide-content-header')) content-no-header @endif">
        @if(!View::hasSection('hide-content-header'))
        <!-- Content Header (Page header) -->
        <div class="content-header content-header-mobile">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-12 col-sm-6">
                        <h1 class="m-0 page-title-mobile">@yield('page-title', 'Dashboard')</h1>
                    </div><!-- /.col -->
                    <div class="col-12 col-sm-6">
                        <ol class="breadcrumb float-sm-right breadcrumb-mobile">
                            @yield('breadcrumb')
                        </ol>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->
        @endif
        <!-- Main content -->
        <section class="content content-mobile">
            <div class="container-fluid container-fluid-mobile">
                @yield('content')
            </div><!-- /.container-fluid -->
        </section>
        <!-- /.content -->
        
    </div>
    <!-- /.content-wrapper -->

    <!-- Footer -->
    @include('layouts.partials.adminlte-footer')

    <!-- Control Sidebar -->
    <aside class="control-sidebar control-sidebar-dark">
        <!-- Control sidebar content goes here -->
    </aside>
    <!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->

<!-- Drawers y Modales globales - Fuera del wrapper para z-index correcto -->
@stack('drawers')

<!-- Sistema Unificado de Alertas (Flash Messages + Toasts) -->
@include('components.alerts')

{{-- CARGAR JS AdminLTE (debe estar ANTES de @stack('scripts') para que jQuery esté disponible) --}}
@vite(['resources/js/adminlte.js'])

@stack('scripts')
</body>
</html>
