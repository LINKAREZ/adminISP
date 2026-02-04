<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;

// Ruta para favicon.ico (fallback para navegadores antiguos)
Route::get('/favicon.ico', function () {
    $faviconPath = public_path('favicon.svg');
    if (file_exists($faviconPath)) {
        return Response::file($faviconPath, ['Content-Type' => 'image/svg+xml']);
    }
    return abort(404);
})->name('favicon');

// Instalador (solo accesible cuando no está instalado)
Route::prefix('install')->name('installer.')->middleware('installer')->group(function () {
    Route::get('/', [\App\Http\Controllers\InstallerController::class, 'index'])->name('index');
    Route::post('/create-env', [\App\Http\Controllers\InstallerController::class, 'createEnv'])->name('create-env');
    Route::get('/database', [\App\Http\Controllers\InstallerController::class, 'database'])->name('database');
    Route::post('/database/test', [\App\Http\Controllers\InstallerController::class, 'testDatabase'])->name('test-database');
    Route::post('/database', [\App\Http\Controllers\InstallerController::class, 'saveDatabase'])->name('save-database');
    Route::post('/database/create', [\App\Http\Controllers\InstallerController::class, 'createDatabase'])->name('create-database');
    Route::post('/database/create-user', [\App\Http\Controllers\InstallerController::class, 'createDatabaseUser'])->name('create-database-user');
    Route::get('/migrate', [\App\Http\Controllers\InstallerController::class, 'migrate'])->name('migrate');
    Route::post('/migrate/run', [\App\Http\Controllers\InstallerController::class, 'runMigrations'])->name('run-migrations');
    Route::post('/migrate/seed', [\App\Http\Controllers\InstallerController::class, 'runSeeders'])->name('run-seeders');
    Route::get('/admin', [\App\Http\Controllers\InstallerController::class, 'admin'])->name('admin');
    Route::post('/admin', [\App\Http\Controllers\InstallerController::class, 'saveAdmin'])->name('save-admin');
    Route::get('/finish', [\App\Http\Controllers\InstallerController::class, 'finish'])->name('finish');
});

// Cargar rutas del módulo Auth
// IMPORTANTE: Las rutas cargadas con require() SÍ heredan el middleware 'web' automáticamente
// porque están siendo ejecutadas en el contexto de routes/web.php
require __DIR__ . '/../app/Modules/Auth/Routes/web.php';

// Rutas del Dashboard - Cargadas directamente aquí para evitar problemas de orden
Route::middleware('auth')->group(function () {
    Route::get('/', [\App\Modules\Dashboard\Controllers\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [\App\Modules\Dashboard\Controllers\DashboardController::class, 'index']);
});

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
