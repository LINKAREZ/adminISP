<?php

use App\Modules\Installer\Controllers\InstallerController;
use Illuminate\Support\Facades\Route;

// Instalador (solo accesible cuando no está instalado)
Route::prefix('install')->name('installer.')->middleware('installer')->group(function () {
    Route::get('/', [InstallerController::class, 'index'])->name('index');
    Route::post('/create-env', [InstallerController::class, 'createEnv'])->name('create-env');
    Route::get('/database', [InstallerController::class, 'database'])->name('database');
    Route::post('/database', [InstallerController::class, 'saveDatabase'])->name('save-database');
    Route::post('/database/test', [InstallerController::class, 'testDatabase'])->name('test-database');
    Route::post('/create-database', [InstallerController::class, 'createDatabase'])->name('create-database');
    Route::post('/create-database-user', [InstallerController::class, 'createDatabaseUser'])->name('create-database-user');
    Route::get('/migrate', [InstallerController::class, 'migrate'])->name('migrate');
    Route::post('/migrate/run', [InstallerController::class, 'runMigrations'])->name('run-migrations');
    Route::post('/migrate/seed', [InstallerController::class, 'runSeeders'])->name('run-seeders');
    Route::get('/admin', [InstallerController::class, 'admin'])->name('admin');
    Route::post('/admin', [InstallerController::class, 'saveAdmin'])->name('save-admin');
    Route::get('/finish', [InstallerController::class, 'finish'])->name('finish');
});
