<?php

use App\Modules\Comprobantes\Controllers\ReciboController;
use App\Modules\Comprobantes\Controllers\PagoController;
use App\Modules\Comprobantes\Controllers\PromesaPagoController;
use App\Modules\Comprobantes\Controllers\ComprobanteController;
use App\Modules\Comprobantes\Controllers\CategoriaGastoController;
use App\Modules\Comprobantes\Controllers\DashboardFinanzasController;
use App\Modules\Comprobantes\Controllers\GastoController;
use App\Modules\Comprobantes\Controllers\ImportarPagosController;
use App\Modules\Comprobantes\Controllers\ReporteController;
use Illuminate\Support\Facades\Route;

// Rutas API movidas a routes/api.php

// Rutas de recibos (directas, no anidadas - solo delete)
Route::middleware(['web', 'auth', 'permission:comprobantes.recibos.delete|comprobantes.delete'])
    ->delete('recibos/{recibo}', [ReciboController::class, 'destroy'])
    ->name('recibos.destroy');

// Rutas de pagos (directas, no anidadas - solo delete)
Route::middleware(['web', 'auth', 'permission:comprobantes.pagos.delete|comprobantes.delete'])
    ->delete('pagos/{pago}', [PagoController::class, 'destroy'])
    ->name('pagos.destroy');

// Rutas de comprobantes (fiscales)
// IMPORTANTE: rutas concretas (importar-pagos, series) ANTES de comprobantes/{comprobante} para evitar que {comprobante} capture "importar-pagos"
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('comprobantes', [ComprobanteController::class, 'index'])
        ->middleware('permission:comprobantes.comprobantes.read|comprobantes.read')
        ->name('comprobantes.index');
    Route::get('comprobantes/create', [ComprobanteController::class, 'create'])
        ->middleware('permission:comprobantes.comprobantes.create|comprobantes.create')
        ->name('comprobantes.create');
    Route::get('comprobantes/importar-pagos', [ImportarPagosController::class, 'index'])
        ->middleware('permission:comprobantes.importar-pagos.create|comprobantes.create')
        ->name('comprobantes.importar-pagos.index');
    Route::post('comprobantes/importar-pagos', [ImportarPagosController::class, 'store'])
        ->middleware('permission:comprobantes.importar-pagos.create|comprobantes.create')
        ->name('comprobantes.importar-pagos.store');
    Route::get('comprobantes/importar-pagos/plantilla', [ImportarPagosController::class, 'plantilla'])
        ->middleware('permission:comprobantes.importar-pagos.read|comprobantes.read')
        ->name('comprobantes.importar-pagos.plantilla');
    Route::get('comprobantes/series', [ComprobanteController::class, 'series'])
        ->middleware('permission:comprobantes.comprobantes.read|comprobantes.read')
        ->name('comprobantes.series');
    Route::get('comprobantes-series', [ComprobanteController::class, 'series'])
        ->middleware('permission:comprobantes.comprobantes.read|comprobantes.read');
    Route::post('comprobantes', [ComprobanteController::class, 'store'])
        ->middleware('permission:comprobantes.comprobantes.create|comprobantes.create')
        ->name('comprobantes.store');
    Route::get('comprobantes/{comprobante}', [ComprobanteController::class, 'show'])
        ->middleware('permission:comprobantes.comprobantes.read|comprobantes.read')
        ->name('comprobantes.show');
    Route::get('comprobantes/{comprobante}/edit', [ComprobanteController::class, 'edit'])
        ->middleware('permission:comprobantes.comprobantes.update|comprobantes.update')
        ->name('comprobantes.edit');
    Route::put('comprobantes/{comprobante}', [ComprobanteController::class, 'update'])
        ->middleware('permission:comprobantes.comprobantes.update|comprobantes.update')
        ->name('comprobantes.update');
    Route::delete('comprobantes/{comprobante}', [ComprobanteController::class, 'destroy'])
        ->middleware('permission:comprobantes.comprobantes.delete|comprobantes.delete')
        ->name('comprobantes.destroy');
    Route::post('comprobantes/{comprobante}/anular', [ComprobanteController::class, 'anular'])
        ->middleware('permission:comprobantes.comprobantes.anular|comprobantes.delete')
        ->name('comprobantes.anular');
    Route::get('comprobantes/{comprobante}/ver', [ComprobanteController::class, 'ver'])
        ->middleware('permission:comprobantes.comprobantes.read|comprobantes.read')
        ->name('comprobantes.ver');
    Route::get('comprobantes/{comprobante}/descargar', [ComprobanteController::class, 'descargarRecibo'])
        ->middleware('permission:comprobantes.comprobantes.read|comprobantes.read')
        ->name('comprobantes.descargar');

    Route::get('pagos/{pago}/comprobante', [ComprobanteController::class, 'generar'])
        ->middleware('permission:comprobantes.comprobantes.read|comprobantes.read')
        ->name('pagos.comprobante');
    Route::get('pagos/{pago}/comprobante/descargar', [ComprobanteController::class, 'descargar'])
        ->middleware('permission:comprobantes.comprobantes.read|comprobantes.read')
        ->name('pagos.comprobante.descargar');

    Route::post('comprobantes/masivos', [ComprobanteController::class, 'generarMasivos'])
        ->middleware('permission:comprobantes.comprobantes.create|comprobantes.create')
        ->name('comprobantes.generar-masivos');
    Route::post('comprobantes/masivos/eliminar', [ComprobanteController::class, 'eliminarMasivos'])
        ->middleware('permission:comprobantes.comprobantes.delete|comprobantes.delete')
        ->name('comprobantes.eliminar-masivos');
});

// Dashboard Finanzas y Gastos
Route::middleware(['web', 'auth'])->prefix('finanzas')->name('comprobantes.')->group(function () {
    Route::get('dashboard', [DashboardFinanzasController::class, 'index'])->middleware('permission:comprobantes.dashboard-finanzas.read|comprobantes.read')->name('dashboard-finanzas');
    Route::resource('gastos', GastoController::class)->names('gastos')->parameters(['gastos' => 'gasto']);
    Route::resource('categorias-gasto', CategoriaGastoController::class)->names('categorias-gasto')->parameters(['categorias-gasto' => 'categoriaGasto']);
});

// Rutas de reportes (módulo Comprobantes)
Route::middleware(['web', 'auth', 'permission:comprobantes.reportes.read|comprobantes.read'])
    ->prefix('reportes')
    ->name('comprobantes.reportes.')
    ->group(function () {
        Route::get('cuadre-caja', [ReporteController::class, 'cuadreCaja'])->name('cuadre-caja');
        Route::get('detalle-medio-pago', [ReporteController::class, 'detalleMedioPago'])->name('detalle-medio-pago');
        Route::get('ingresos', [ReporteController::class, 'ingresos'])->name('ingresos');
        Route::get('ingresos/exportar', [ReporteController::class, 'ingresosExportar'])->middleware('permission:comprobantes.reportes.export|comprobantes.reportes.read|comprobantes.read')->name('ingresos.exportar');
    });

// Rutas anidadas de recibos, pagos y promesas bajo clientes
Route::middleware(['web', 'auth'])
    ->prefix('clientes/{cliente}')
    ->name('clientes.')
    ->group(function () {
        // API: Obtener recibos del cliente (AJAX para formularios)
        Route::get('recibos/por-servicio', [ReciboController::class, 'getRecibosByServicioId'])
            ->middleware('permission:comprobantes.recibos.read|comprobantes.read')
            ->name('recibos-cliente');

        Route::get('recibos/create', [ReciboController::class, 'create'])
            ->middleware('permission:comprobantes.recibos.create|comprobantes.create')
            ->name('recibos.create');
        Route::get('recibos/{recibo}', [ReciboController::class, 'show'])
            ->middleware('permission:comprobantes.recibos.read|comprobantes.read')
            ->name('recibos.show');
        Route::get('recibos/{recibo}/edit', [ReciboController::class, 'edit'])
            ->middleware('permission:comprobantes.recibos.update|comprobantes.update')
            ->name('recibos.edit');
        Route::post('recibos', [ReciboController::class, 'store'])
            ->middleware('permission:comprobantes.recibos.create|comprobantes.create')
            ->name('recibos.store');
        Route::put('recibos/{recibo}', [ReciboController::class, 'update'])
            ->middleware('permission:comprobantes.recibos.update|comprobantes.update')
            ->name('recibos.update');

        // Pagos
        Route::get('pagos/create', [PagoController::class, 'create'])
            ->middleware('permission:comprobantes.pagos.create|comprobantes.create')
            ->name('pagos.create');
        Route::get('pagos/{pago}', [PagoController::class, 'show'])
            ->middleware('permission:comprobantes.pagos.read|comprobantes.read')
            ->name('pagos.show');
        Route::get('pagos/{pago}/edit', [PagoController::class, 'edit'])
            ->middleware('permission:comprobantes.pagos.update|comprobantes.update')
            ->name('pagos.edit');
        Route::post('pagos', [PagoController::class, 'store'])
            ->middleware('permission:comprobantes.pagos.create|comprobantes.create')
            ->name('pagos.store');
        Route::put('pagos/{pago}', [PagoController::class, 'update'])
            ->middleware('permission:comprobantes.pagos.update|comprobantes.update')
            ->name('pagos.update');
        Route::get('pagos/{pago}/captura', [PagoController::class, 'mostrarCaptura'])
            ->middleware('permission:comprobantes.pagos.read|comprobantes.read')
            ->name('pagos.captura');

        // Promesas de Pago (anidadas en recibos)
        Route::prefix('recibos/{recibo}')->name('promesas-pago.')->group(function () {
            Route::get('promesas-pago/create', [PromesaPagoController::class, 'create'])
                ->middleware('permission:comprobantes.recibos.create|comprobantes.create')
                ->name('create');
            Route::get('promesas-pago/{promesa}/edit', [PromesaPagoController::class, 'edit'])
                ->middleware('permission:comprobantes.recibos.update|comprobantes.update')
                ->name('edit');
            Route::post('promesas-pago', [PromesaPagoController::class, 'store'])
                ->middleware('permission:comprobantes.recibos.create|comprobantes.create')
                ->name('store');
            Route::put('promesas-pago/{promesa}', [PromesaPagoController::class, 'update'])
                ->middleware('permission:comprobantes.recibos.update|comprobantes.update')
                ->name('update');
            Route::post('promesas-pago/{promesa}/cumplir', [PromesaPagoController::class, 'cumplir'])
                ->middleware('permission:comprobantes.recibos.update|comprobantes.update')
                ->name('cumplir');
            Route::post('promesas-pago/{promesa}/cancelar', [PromesaPagoController::class, 'cancelar'])
                ->middleware('permission:comprobantes.recibos.update|comprobantes.update')
                ->name('cancelar');
            Route::delete('promesas-pago/{promesa}', [PromesaPagoController::class, 'destroy'])
                ->middleware('permission:comprobantes.recibos.delete|comprobantes.delete')
                ->name('destroy');
        });
    });
