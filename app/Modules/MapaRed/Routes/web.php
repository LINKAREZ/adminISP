<?php

use App\Modules\MapaRed\Http\Controllers\MapaRedController;
use App\Modules\MapaRed\Http\Controllers\TileProxyController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('mapa-red')->name('mapa-red.')->group(function () {
    Route::get('/', [MapaRedController::class, 'index'])->name('index');
    Route::get('tile/{z}/{x}/{y}', TileProxyController::class)->name('tile')->where(['z' => '[0-9]+', 'x' => '[0-9]+', 'y' => '[0-9]+']);
});
