<?php

use App\Modules\Clientes\Controllers\ClienteController;
use App\Modules\Clientes\Controllers\UbicacionController;
use Illuminate\Support\Facades\Route;

// Fotos de ubicación (protegidas por auth; evita acceso público a /storage/...)
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('ubicaciones/{ubicacion}/foto/{num}', [UbicacionController::class, 'showFoto'])
        ->name('ubicaciones.foto')
        ->where('num', '[123]');
});

// IMPORTANTE: Estas rutas deben ir ANTES del resource para evitar conflictos de route model binding
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('clientes/consultas/dni', [ClienteController::class, 'consultarDni'])->name('clientes.consultar-dni');
    Route::get('clientes/consultas/ruc', [ClienteController::class, 'consultarRuc'])->name('clientes.consultar-ruc');
    Route::get('clientes/consultar-dni', [ClienteController::class, 'consultarDni']);
    Route::get('clientes/consultar-ruc', [ClienteController::class, 'consultarRuc']);
    Route::get('clientes/pppoe/importar', [ClienteController::class, 'importarPppoeForm'])->name('clientes.pppoe.importar');
    Route::post('clientes/pppoe/importar', [ClienteController::class, 'importarPppoe'])->name('clientes.pppoe.importar.store');
    // API movida a routes/api.php: api.clientes.servicios.credenciales
    Route::post('clientes/servicios/vencidos/cortar', [ClienteController::class, 'cortarServiciosVencidos'])->name('clientes.cortar-servicios-vencidos');
    Route::post('clientes/cortar-servicios-vencidos', [ClienteController::class, 'cortarServiciosVencidos']);
    Route::get('clientes/{cliente}/crear-usuario-pppoe', [ClienteController::class, 'crearUsuarioPppoeForm'])->name('clientes.crear-usuario-pppoe');
    Route::post('clientes/{cliente}/crear-usuario-pppoe', [ClienteController::class, 'storeCrearUsuarioPppoe'])->name('clientes.crear-usuario-pppoe.store');
    Route::resource('clientes', ClienteController::class);
});

// Rutas anidadas de clientes - SOLO ubicaciones
Route::middleware(['web', 'auth'])->prefix('clientes/{cliente}')->name('clientes.')->group(function () {
    // Ubicaciones
    Route::get('ubicaciones/create', [UbicacionController::class, 'create'])->name('ubicaciones.create');
    Route::get('ubicaciones/{ubicacion}/edit', [UbicacionController::class, 'edit'])->name('ubicaciones.edit');
    Route::post('ubicaciones', [UbicacionController::class, 'store'])->name('ubicaciones.store');
    Route::put('ubicaciones/{ubicacion}', [UbicacionController::class, 'update'])->name('ubicaciones.update');
    Route::delete('ubicaciones/{ubicacion}', [UbicacionController::class, 'destroy'])->name('ubicaciones.destroy');
});
