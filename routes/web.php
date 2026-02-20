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

    // Super admin: cambiar ISP en sesión (para módulos tenant)
    Route::get('/session/switch-isp', function (\Illuminate\Http\Request $request) {
        $user = auth()->user();
        if (!$user || !method_exists($user, 'isSuperAdmin') || !$user->isSuperAdmin()) {
            return redirect()->back()->with('error', 'Acceso denegado.');
        }
        $ispId = (int) $request->query('isp_id', 0);
        if ($ispId < 1) {
            return redirect()->back()->with('error', 'ISP inválido.');
        }
        $centralConn = \App\Core\Services\TenantConnectionService::centralConnection();
        $qb = \App\Modules\Sistema\Models\Isp::on($centralConn)
            ->where('id', $ispId)
            ->whereNotNull('database_name')
            ->where('database_name', '!=', '');
        if (\Illuminate\Support\Facades\Schema::connection($centralConn)->hasColumn('isps', 'activo')) {
            $qb->where('activo', true);
        }
        $isp = $qb->first();
        if (!$isp) {
            return redirect()->back()->with('error', 'ISP no encontrado o sin base de datos.');
        }
        session(['current_isp_id' => $isp->id]);
        \App\Core\Services\TenantConnectionService::registerConnectionForIspId($isp->id);
        return redirect()->back()->with('success', "ISP cambiado a: {$isp->nombre}");
    })->name('session.switch-isp');

    // Módulo ControlAcceso - Las rutas están en app/Modules/ControlAcceso/Routes/web.php
    // Cargadas automáticamente por ModuleServiceProvider

    // Módulo Red - Las rutas están en app/Modules/Red/Routes/web.php
    // Cargadas automáticamente por ModuleServiceProvider

    // Módulo Servicios - Las rutas están en app/Modules/Servicios/Routes/web.php
    // Cargadas automáticamente por ModuleServiceProvider

    // Módulo Clientes - Rutas de importar-clientes registradas aquí para que route() esté disponible en todas las vistas (sidebar, tabs, etc.)
    Route::get('clientes/importar-clientes', [\App\Modules\Clientes\Controllers\ImportarClientesController::class, 'index'])->name('clientes.importar-clientes.index');
    Route::post('clientes/importar-clientes', [\App\Modules\Clientes\Controllers\ImportarClientesController::class, 'store'])->name('clientes.importar-clientes.store');
    Route::get('clientes/importar-clientes/plantilla', [\App\Modules\Clientes\Controllers\ImportarClientesController::class, 'plantilla'])->name('clientes.importar-clientes.plantilla');
    // Resto del módulo Clientes en app/Modules/Clientes/Routes/web.php vía ModuleServiceProvider

    // Módulo Comprobantes - Rutas en app/Modules/Comprobantes/Routes/web.php
    // Cargadas automáticamente por ModuleServiceProvider

    // Módulo Sistema - Las rutas están en app/Modules/Sistema/Routes/web.php
    // Cargadas automáticamente por ModuleServiceProvider

    // Rutas de Super Admin (solo para super administradores)
    Route::middleware('superadmin')->prefix('superadmin')->name('superadmin.')->group(function () {
        Route::get('/', [\App\Modules\Sistema\Controllers\SuperAdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/export', [\App\Modules\Sistema\Controllers\SuperAdminController::class, 'export'])->name('export');
        Route::get('/audit', [\App\Modules\Sistema\Controllers\SuperAdminAuditController::class, 'index'])->name('audit');
        Route::resource('plans', \App\Modules\Sistema\Controllers\SuperAdminPlanController::class);
        Route::get('/solicitudes', [\App\Modules\Sistema\Controllers\SuperAdminController::class, 'solicitudes'])->name('solicitudes.index');

        // ISPs (solo super admin)
        Route::resource('isps', \App\Modules\Sistema\Controllers\IspController::class)->parameters(['isps' => 'isp']);
        Route::post('isps/{isp}/create-database', [\App\Modules\Sistema\Controllers\IspController::class, 'createDatabase'])->name('isps.create-database');
        Route::patch('isps/{isp}/toggle', [\App\Modules\Sistema\Controllers\IspController::class, 'toggleStatus'])->name('isps.toggle');

        // Debug: comprobar que la app ve roles en la BD central (solo superadmin)
        Route::get('/debug-roles', function () {
            $conn = \App\Core\Services\TenantConnectionService::centralConnection();
            $count = \Illuminate\Support\Facades\DB::connection($conn)->table('roles')->count();
            $names = \App\Modules\ControlAcceso\Models\Role::on($conn)->orderBy('id')->pluck('name')->toArray();
            return response()->json([
                'connection' => $conn,
                'roles_count' => $count,
                'roles' => $names,
            ]);
        })->name('debug-roles');

    });
});
