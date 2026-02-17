<?php

use App\Modules\Almacen\Controllers\ArticuloController;
use App\Modules\Almacen\Controllers\AlmacenController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('almacen')->name('almacen.')->group(function () {
    Route::resource('articulos', ArticuloController::class);
    Route::get('almacenes', [AlmacenController::class, 'index'])->name('almacenes.index');
    Route::get('almacenes/{almacen}/stock', [AlmacenController::class, 'stock'])->name('almacenes.stock');
    Route::get('movimientos', [AlmacenController::class, 'movimientos'])->name('movimientos.index');
    Route::get('entregas', [AlmacenController::class, 'entregarForm'])->name('entregas.create');
    Route::post('entregas', [AlmacenController::class, 'entregarStore'])->name('entregas.store');
});
