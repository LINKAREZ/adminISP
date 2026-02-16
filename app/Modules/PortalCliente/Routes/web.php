<?php

use App\Modules\PortalCliente\Controllers\PortalDashboardController;
use App\Modules\PortalCliente\Controllers\PortalLoginController;
use App\Modules\PortalCliente\Controllers\PortalRecibosController;
use App\Modules\PortalCliente\Controllers\PortalReportarPagoController;
use App\Modules\PortalCliente\Controllers\PortalTicketController;
use Illuminate\Support\Facades\Route;

// Portal del cliente: contexto ISP (único por despliegue o APP_PORTAL_ISP_ID)
Route::middleware(['web', 'portal.isp'])->prefix('portal')->name('portal.')->group(function () {
    // Login (invitado)
    Route::middleware('portal.guest')->group(function () {
        Route::get('login', [PortalLoginController::class, 'create'])->name('login');
        Route::post('login', [PortalLoginController::class, 'store'])->name('login.store');
    });

    // Rutas autenticadas (portal.cliente)
    Route::middleware('portal.cliente')->group(function () {
        Route::get('/', [PortalDashboardController::class, 'index'])->name('dashboard');
        Route::get('recibos', [PortalRecibosController::class, 'index'])->name('recibos');
        Route::get('reportar-pago', [PortalReportarPagoController::class, 'create'])->name('reportar-pago');
        Route::post('reportar-pago', [PortalReportarPagoController::class, 'store'])->name('reportar-pago.store');
        Route::post('logout', function () {
            request()->session()->forget('portal_cliente_id');
            return redirect()->route('portal.login');
        })->name('logout');

        Route::prefix('tickets')->name('tickets.')->group(function () {
            Route::get('/', [PortalTicketController::class, 'index'])->name('index');
            Route::get('create', [PortalTicketController::class, 'create'])->name('create');
            Route::post('/', [PortalTicketController::class, 'store'])->name('store');
            Route::get('{ticket}', [PortalTicketController::class, 'show'])->name('show');
            Route::post('{ticket}/responder', [PortalTicketController::class, 'responder'])->name('responder');
        });
    });
});
