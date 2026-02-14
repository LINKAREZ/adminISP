<?php

use App\Modules\CorteFacturacion\Controllers\CorteFacturacionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('corte-facturacion')->name('corte-facturacion.')->group(function () {
    Route::get('/', [CorteFacturacionController::class, 'index'])->name('index');
    Route::post('/ejecutar-facturacion', [CorteFacturacionController::class, 'ejecutarFacturacion'])->name('ejecutar-facturacion');
    Route::post('/ejecutar-corte', [CorteFacturacionController::class, 'ejecutarCorte'])->name('ejecutar-corte');
});
