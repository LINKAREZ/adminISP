<?php

use App\Modules\ControlAcceso\Controllers\UserController;
use App\Modules\ControlAcceso\Controllers\RoleController;
use App\Modules\ControlAcceso\Controllers\PermissionController;
use App\Modules\ControlAcceso\Controllers\ProfileController;
use App\Modules\ControlAcceso\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

// Aplicar middleware 'auth' a todas las rutas de este módulo
Route::middleware(['web', 'auth'])->group(function () {
    // Perfil de usuario
    Route::get('profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::get('profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('profile/password', [ProfileController::class, 'password'])->name('profile.password');
    Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    // Configuración
    Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');

    // Usuarios
    Route::resource('users', UserController::class)->middleware('throttle:60,1');

    // Roles
    Route::resource('roles', RoleController::class)->middleware('throttle:60,1');

    // Permisos
    Route::resource('permissions', PermissionController::class)
        ->only(['index', 'create', 'store', 'show'])
        ->middleware('throttle:60,1');

    // Rutas para gestionar recursos de permisos
    Route::get('permissions/resources/show', [PermissionController::class, 'showResource'])->name('permissions.resource.show');
    Route::get('permissions/resources/edit', [PermissionController::class, 'editResource'])->name('permissions.resource.edit');
    Route::put('permissions/resources/update', [PermissionController::class, 'updateResource'])->name('permissions.resource.update');
    Route::delete('permissions/resources/destroy', [PermissionController::class, 'destroyResource'])->name('permissions.resource.destroy');
    Route::get('permissions/resource/show', [PermissionController::class, 'showResource']);
    Route::get('permissions/resource/edit', [PermissionController::class, 'editResource']);
    Route::put('permissions/resource/update', [PermissionController::class, 'updateResource']);
    Route::delete('permissions/resource/destroy', [PermissionController::class, 'destroyResource']);
});
