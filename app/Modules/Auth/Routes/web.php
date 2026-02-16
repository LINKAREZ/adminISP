<?php

use App\Modules\Auth\Controllers\AuthenticatedSessionController;
use App\Modules\Auth\Controllers\McpLoginController;
use Illuminate\Support\Facades\Route;

// Aplicar middleware 'web' explícitamente para asegurar sesión y CSRF
Route::middleware('web')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])
        ->middleware('guest')
        ->name('login');

    Route::get('/mcp-login', McpLoginController::class)
        ->middleware('guest')
        ->name('mcp-login');

    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:5,1');

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
        ->middleware('auth')
        ->name('logout');
});
