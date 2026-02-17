<?php

use App\Modules\Instalaciones\Controllers\ComisionController;
use App\Modules\Instalaciones\Controllers\OrdenInstalacionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('instalaciones')->name('instalaciones.')->group(function () {
    Route::get('/', [OrdenInstalacionController::class, 'index'])->name('index');
    Route::get('altas', [OrdenInstalacionController::class, 'seguimientoAltas'])->name('altas');
    Route::get('comisiones', [ComisionController::class, 'index'])->name('comisiones.index');
    Route::post('comisiones/registrar', [ComisionController::class, 'registrar'])->name('comisiones.registrar');
    Route::post('comisiones/{comision}/pagar', [ComisionController::class, 'pagar'])->name('comisiones.pagar');
    // Wizard: nueva orden en 3 pasos
    Route::get('nueva', [OrdenInstalacionController::class, 'paso1'])->name('nueva');
    Route::post('crear-paso-1', [OrdenInstalacionController::class, 'storePaso1'])->name('crear-paso-1');
    Route::get('{orden}/paso-2', [OrdenInstalacionController::class, 'paso2'])->name('paso-2');
    Route::post('{orden}/paso-2', [OrdenInstalacionController::class, 'storePaso2'])->name('guardar-paso-2');
    Route::get('{orden}/paso-3', [OrdenInstalacionController::class, 'paso3'])->name('paso-3');
    Route::post('{orden}/paso-3', [OrdenInstalacionController::class, 'storePaso3'])->name('guardar-paso-3');
    Route::get('{orden}/paso-4', [OrdenInstalacionController::class, 'paso4'])->name('paso-4');
    Route::post('{orden}/paso-4', [OrdenInstalacionController::class, 'storePaso4'])->name('guardar-paso-4');
    // Tomar orden (técnico se asigna)
    Route::post('{orden}/tomar', [OrdenInstalacionController::class, 'tomar'])->name('tomar');
    // CRUD clásico (mantener por compatibilidad)
    Route::get('create', [OrdenInstalacionController::class, 'create'])->name('create');
    Route::post('/', [OrdenInstalacionController::class, 'store'])->name('store');
    Route::get('{orden}/completar', [OrdenInstalacionController::class, 'completarForm'])->name('completar-form');
    Route::post('{orden}/completar', [OrdenInstalacionController::class, 'completar'])->name('completar');
    Route::get('{orden}', [OrdenInstalacionController::class, 'show'])->name('show');
    Route::get('{orden}/edit', [OrdenInstalacionController::class, 'edit'])->name('edit');
    Route::put('{orden}', [OrdenInstalacionController::class, 'update'])->name('update');
    Route::delete('{orden}', [OrdenInstalacionController::class, 'destroy'])->name('destroy');
});
