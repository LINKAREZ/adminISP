<?php

use App\Modules\Auditoria\Controllers\AuditoriaController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'permission:auditoria.read'])->prefix('auditoria')->name('auditoria.')->group(function () {
    Route::get('/', [AuditoriaController::class, 'index'])->name('index');
    Route::get('/{auditLog}', [AuditoriaController::class, 'show'])->name('show');
});
