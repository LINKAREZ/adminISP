<?php

use App\Modules\Tenant\Controllers\TenantStatusController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('tenant')->name('tenant.')->group(function () {
    Route::get('/suspended', [TenantStatusController::class, 'suspended'])->name('suspended');
    Route::get('/pending', [TenantStatusController::class, 'pending'])->name('pending');
    Route::get('/cancelled', [TenantStatusController::class, 'cancelled'])->name('cancelled');
});
