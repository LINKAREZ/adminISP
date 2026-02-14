<?php

use App\Modules\Onboarding\Controllers\LandingController;
use App\Modules\Onboarding\Controllers\PreciosController;
use App\Modules\Onboarding\Controllers\SolicitudController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('/landing', [LandingController::class, 'index'])->name('landing');
    Route::get('/precios', [PreciosController::class, 'index'])->name('precios');
    Route::get('/solicitar-cuenta', [SolicitudController::class, 'form'])->name('solicitud.form');
    Route::post('/solicitar-cuenta', [SolicitudController::class, 'store'])->middleware('throttle:5,1')->name('solicitud.store');
});
