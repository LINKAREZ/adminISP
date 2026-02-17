<?php

use App\Modules\Infraestructura\Controllers\CajaNapController;
use App\Modules\Infraestructura\Controllers\DetallePonController;
use App\Modules\Infraestructura\Controllers\OdfController;
use App\Modules\Infraestructura\Controllers\OltController;
use App\Modules\Infraestructura\Controllers\EditorInfraestructuraController;
use App\Modules\Infraestructura\Controllers\HiloController;
use App\Modules\Infraestructura\Controllers\MapaController;
use App\Modules\Infraestructura\Controllers\MufaController;
use App\Modules\Infraestructura\Controllers\PosteController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('infraestructura')->name('infraestructura.')->group(function () {
    Route::get('/', fn () => redirect()->route('infraestructura.mapa.index'));

    Route::resource('postes', PosteController::class);
    Route::resource('cajas-nap', CajaNapController::class)->parameters(['cajas-nap' => 'cajaNap']);
    Route::resource('mufas', MufaController::class)->parameters(['mufas' => 'mufa']);
    Route::get('mapa', [MapaController::class, 'index'])->name('mapa.index');

    // OLTs y puertos PON (trazabilidad FTTH)
    Route::resource('olts', OltController::class)->parameters(['olts' => 'olt']);
    Route::resource('odfs', OdfController::class)->parameters(['odfs' => 'odf']);
    Route::post('odfs/{odf}/puertos', [OdfController::class, 'storePuerto'])->name('odfs.puertos.store');
    Route::post('odfs/{odf}/puertos-bloque', [OdfController::class, 'storePuertosBloque'])->name('odfs.puertos.store-bloque');
    Route::delete('odfs/{odf}/puertos/{puerto}', [OdfController::class, 'destroyPuerto'])->name('odfs.puertos.destroy');
    Route::post('olts/{olt}/puertos-pon', [OltController::class, 'storePuertoPon'])->name('olts.puertos-pon.store');
    Route::delete('olts/{olt}/puertos-pon/{puertoPon}', [OltController::class, 'destroyPuertoPon'])->name('olts.puertos-pon.destroy');
    Route::post('olts/{olt}/puertos-pon/{puertoPon}/enlace', [OltController::class, 'storeEnlace'])->name('olts.puertos-pon.enlace.store');
    Route::put('olts/{olt}/puertos-pon/{puertoPon}/enlace', [OltController::class, 'updateEnlace'])->name('olts.puertos-pon.enlace.update');
    Route::delete('olts/{olt}/puertos-pon/{puertoPon}/enlace', [OltController::class, 'destroyEnlace'])->name('olts.puertos-pon.enlace.destroy');

    // Detalle PON: trazabilidad OLT → ODF → cable → splitter → NAP → abonado
    Route::get('detalle-pon', [DetallePonController::class, 'index'])->name('detalle-pon.index');
    Route::post('detalle-pon/migrar-ftth', [DetallePonController::class, 'migrarFtth'])->name('detalle-pon.migrar-ftth');
    Route::get('detalle-pon/{oltPuertoPon}', [DetallePonController::class, 'show'])->name('detalle-pon.show');

    // Editor: redirige al mapa (un solo mapa con todo)
    Route::get('editor', fn () => redirect()->route('infraestructura.mapa.index'))->name('editor.index');
    Route::get('editor/data', [EditorInfraestructuraController::class, 'data'])->name('editor.data');
    Route::post('editor/posicion', [EditorInfraestructuraController::class, 'updatePosicion'])->name('editor.posicion');
    Route::post('editor/postes', [EditorInfraestructuraController::class, 'storePoste'])->name('editor.postes.store');
    Route::post('editor/cajas-nap', [EditorInfraestructuraController::class, 'storeCajaNap'])->name('editor.cajas-nap.store');
    Route::post('editor/mufas', [EditorInfraestructuraController::class, 'storeMufa'])->name('editor.mufas.store');
    Route::post('editor/cables/recorrido', [EditorInfraestructuraController::class, 'storeCablesRecorrido'])->name('editor.cables.recorrido');
    Route::put('editor/recorridos/{recorrido}', [EditorInfraestructuraController::class, 'updateRecorrido'])->name('editor.recorridos.update');
    Route::put('editor/recorridos/{recorrido}/puntos', [EditorInfraestructuraController::class, 'updateRecorridoPuntos'])->name('editor.recorridos.puntos.update');
    Route::delete('editor/recorridos/{recorrido}', [EditorInfraestructuraController::class, 'destroyRecorrido'])->name('editor.recorridos.destroy');

    // Hilos: anidados en caja NAP (crear/editar/eliminar desde la caja)
    Route::get('cajas-nap/{cajaNap}/hilos', [HiloController::class, 'index'])->name('cajas-nap.hilos.index');
    Route::post('cajas-nap/{cajaNap}/hilos', [HiloController::class, 'store'])->name('cajas-nap.hilos.store');
    Route::put('cajas-nap/{cajaNap}/hilos/{hilo}', [HiloController::class, 'update'])->name('cajas-nap.hilos.update');
    Route::match(['DELETE'], 'cajas-nap/{cajaNap}/hilos/{hilo}', [HiloController::class, 'destroy'])->name('cajas-nap.hilos.destroy');
});
