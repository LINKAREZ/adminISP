<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;

// Debug sesión (solo en local/debug; eliminar en producción)
if (config('app.debug')) {
    Route::get('/session-debug', function () {
        return response()->json([
            'session_id' => session()->getId(),
            'auth_check' => auth()->check(),
            'auth_id' => auth()->id(),
            'session_keys' => array_keys(session()->all()),
        ]);
    })->middleware('web');
}

// Ruta para favicon.ico (fallback para navegadores antiguos)
Route::get('/favicon.ico', function () {
    $faviconPath = public_path('favicon.svg');
    if (file_exists($faviconPath)) {
        return Response::file($faviconPath, ['Content-Type' => 'image/svg+xml']);
    }
    return abort(404);
})->name('favicon');

// Instalador (módulo Installer) - solo accesible cuando no está instalado
require __DIR__ . '/../app/Modules/Installer/Routes/web.php';

// Cargar rutas del módulo Auth (heredan middleware 'web' por ejecutarse en este contexto)
require __DIR__ . '/../app/Modules/Auth/Routes/web.php';

// Resto de módulos: rutas cargadas desde cada ModuleServiceProvider (loadRoutesFrom)

// Rutas protegidas
Route::middleware('auth')->group(function () {

    // Módulo ControlAcceso - Las rutas están en app/Modules/ControlAcceso/Routes/web.php
    // Cargadas automáticamente por ModuleServiceProvider

    // Módulo Red - Las rutas están en app/Modules/Red/Routes/web.php
    // Cargadas automáticamente por ModuleServiceProvider

    // Módulo Servicios - Las rutas están en app/Modules/Servicios/Routes/web.php
    // Cargadas automáticamente por ModuleServiceProvider

    // Módulo Clientes - Las rutas están en app/Modules/Clientes/Routes/web.php
    // Cargadas automáticamente por ModuleServiceProvider
    // Rutas anidadas de clientes están en app/Modules/Clientes/Routes/web.php

    // Módulo Comprobantes - Rutas en app/Modules/Comprobantes/Routes/web.php
    // Cargadas automáticamente por ModuleServiceProvider

    // Módulo Sistema - Las rutas están en app/Modules/Sistema/Routes/web.php
    // Cargadas automáticamente por ModuleServiceProvider

    // Rutas de Super Admin (solo para super administradores)
    Route::middleware('superadmin')->prefix('superadmin')->name('superadmin.')->group(function () {
        Route::get('/', [\App\Modules\Sistema\Controllers\SuperAdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/create-admin-user', [\App\Modules\Sistema\Controllers\SuperAdminController::class, 'createAdminUser'])->name('create-admin-user');
        Route::post('/create-admin-user', [\App\Modules\Sistema\Controllers\SuperAdminController::class, 'storeAdminUser'])
            ->middleware('throttle:10,1')
            ->name('store-admin-user');
        Route::get('/export', [\App\Modules\Sistema\Controllers\SuperAdminController::class, 'export'])->name('export');

        // ISPs (solo super admin)
        Route::resource('isps', \App\Modules\Sistema\Controllers\IspController::class)->parameters(['isps' => 'isp']);
        Route::patch('isps/{isp}/toggle', [\App\Modules\Sistema\Controllers\IspController::class, 'toggleStatus'])->name('isps.toggle');
    });
});
