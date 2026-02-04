<?php

/**
 * Rutas API Centralizadas
 *
 * Todas las rutas API del sistema están centralizadas aquí
 * para mejor organización y mantenimiento.
 *
 * Prefijo: /api
 * Middleware: web, auth, throttle:120,1
 */

use App\Modules\Clientes\Controllers\ClienteController;
use App\Modules\Comprobantes\Controllers\PagoController;
use App\Modules\Comprobantes\Controllers\ReciboController;
use App\Modules\Servicios\Controllers\OnuController;
use App\Modules\Servicios\Controllers\ServicioController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'throttle:120,1'])->prefix('api')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | API - Clientes
    |--------------------------------------------------------------------------
    */
    Route::get('clientes/{cliente}/servicios/credenciales', [ClienteController::class, 'obtenerCredencialesServicios'])
        ->middleware('throttle:30,1')
        ->name('api.clientes.servicios.credenciales');

    Route::get('clientes/{cliente}/siguiente-usuario-pppoe', [ClienteController::class, 'getSiguienteUsuarioPppoe'])
        ->middleware('throttle:60,1')
        ->name('api.clientes.siguiente-usuario-pppoe');

    /*
    |--------------------------------------------------------------------------
    | API - Servicios
    |--------------------------------------------------------------------------
    */
    Route::get('routers-by-nodo', [ServicioController::class, 'getRoutersByNodo'])
        ->middleware('throttle:60,1')
        ->name('api.routers-by-nodo');

    Route::get('planes-by-router', [ServicioController::class, 'getPlanesByRouter'])
        ->middleware('throttle:60,1')
        ->name('api.planes-by-router');

    Route::get('ip-pools-by-router', [ServicioController::class, 'getIpPoolsByRouter'])
        ->middleware('throttle:60,1')
        ->name('api.ip-pools-by-router');

    Route::get('ip-libres', [ServicioController::class, 'getIpLibres'])
        ->middleware('throttle:60,1')
        ->name('api.ip-libres');

    Route::get('sugerir-ip-libre', [ServicioController::class, 'getSugerirIpLibre'])
        ->middleware('throttle:60,1')
        ->name('api.sugerir-ip-libre');

    Route::get('buscar-equipo-existente', [ServicioController::class, 'buscarEquipoExistente'])
        ->middleware('throttle:30,1')
        ->name('api.buscar-equipo-existente');

    Route::get('servicios/{servicio}/onu', [ServicioController::class, 'getOnuByServicio'])
        ->middleware('throttle:60,1')
        ->name('api.servicios.onu');

    Route::get('servicios/{id}/datos', [ServicioController::class, 'getServicioById'])
        ->middleware('throttle:60,1')
        ->name('api.servicios.datos');

    Route::get('servicios/{servicio}/recibos', [ReciboController::class, 'getRecibosByServicio'])
        ->middleware('throttle:60,1')
        ->name('api.servicios.recibos');

    /*
    |--------------------------------------------------------------------------
    | API - ONUs
    |--------------------------------------------------------------------------
    */
    Route::post('onus', [OnuController::class, 'storeWithoutService'])
        ->middleware('throttle:30,1')
        ->name('api.onus.store');

    Route::get('onus/buscar-por-mac', [OnuController::class, 'buscarPorMac'])
        ->middleware('throttle:30,1')
        ->name('api.onus.buscar-por-mac');

    Route::get('onus/{onu}', [OnuController::class, 'show'])
        ->where('onu', '[0-9]+')
        ->middleware('throttle:60,1')
        ->name('api.onus.show');

    Route::put('onus/{onu}', [OnuController::class, 'updateApi'])
        ->where('onu', '[0-9]+')
        ->middleware('throttle:30,1')
        ->name('api.onus.update');

    /*
    |--------------------------------------------------------------------------
    | API - Pagos
    |--------------------------------------------------------------------------
    */
    Route::get('pagos/verificar-duplicado', [PagoController::class, 'verificarDuplicado'])
        ->middleware('throttle:30,1')
        ->name('api.pagos.verificar-duplicado');

    Route::post('pagos/verificar-numero-operacion', [PagoController::class, 'verificarNumeroOperacion'])
        ->middleware('throttle:30,1')
        ->name('api.pagos.verificar-numero-operacion');
});
