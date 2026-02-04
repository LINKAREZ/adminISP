<?php

use App\Modules\Servicios\Controllers\ServicioController;
use App\Modules\Servicios\Controllers\ServicioMainController;
use App\Modules\Servicios\Controllers\PlanController;
use App\Modules\Servicios\Controllers\OnuController;
use Illuminate\Support\Facades\Route;

// Servicios - Ruta principal (DEBE ir ANTES del resource para evitar conflicto)
Route::middleware(['web', 'auth'])->get('servicios', [ServicioMainController::class, 'index'])->name('servicios.home');

// Servicios - Rutas con prefijo
Route::middleware(['web', 'auth'])->prefix('servicios')->name('servicios.')->group(function () {
    // Servicios - Planes
    Route::resource('planes', PlanController::class);
    Route::post('planes/perfiles/importar', [PlanController::class, 'importarPerfiles'])->name('planes.importar-perfiles');
    Route::post('planes/perfiles/guardar', [PlanController::class, 'guardarPerfilesImportados'])->name('planes.guardar-perfiles-importados');
    Route::post('planes/importar-perfiles', [PlanController::class, 'importarPerfiles']);
    Route::post('planes/guardar-perfiles-importados', [PlanController::class, 'guardarPerfilesImportados']);
});

// Servicios PPPoE - Excluir 'create' porque se maneja de forma anidada en clientes/{cliente}/servicios/create
Route::middleware(['web', 'auth'])->group(function () {
    Route::resource('servicios', ServicioController::class)
        ->parameters(['servicios' => 'servicio'])
        ->except(['create', 'index']); // Excluir index también para evitar conflicto
    Route::post('servicios/{servicio}/estado', [ServicioController::class, 'cambiarEstado'])->name('servicios.cambiar-estado');
    Route::post('servicios/{servicio}/abrir-interfaz-onu', [ServicioController::class, 'abrirInterfazOnu'])->name('servicios.abrir-interfaz-onu');
    Route::get('servicios/{servicio}/ip-pppoe', [ServicioController::class, 'getIpPppoe'])->name('servicios.ip-pppoe');
    Route::get('servicios/{servicio}/abrir-onu', [ServicioController::class, 'abrirOnuRedirect'])->name('servicios.abrir-onu');
    Route::get('servicios/provisionales', [ServicioController::class, 'provisionales'])->name('servicios.provisionales');
    // Ruta específica para index de servicios PPPoE
    Route::get('servicios/pppoe', [ServicioController::class, 'index'])->name('servicios.index');
    Route::get('servicios-pppoe', [ServicioController::class, 'index']);
    Route::post('servicios/{servicio}/cambiar-estado', [ServicioController::class, 'cambiarEstado']);
});

// Rutas API movidas a routes/api.php

// Rutas de servicios (ONU)
Route::middleware(['web', 'auth'])->prefix('servicios/{servicio}')->name('servicios.')->group(function () {
    Route::get('onu/create', [OnuController::class, 'create'])->name('onu.create');
    Route::post('onu', [OnuController::class, 'store'])->name('onu.store');
    Route::put('onu/{onu}', [OnuController::class, 'update'])->name('onu.update');
    Route::delete('onu/{onu}', [OnuController::class, 'destroy'])->name('onu.destroy');
});

// Rutas anidadas de servicios bajo clientes
Route::middleware(['web', 'auth'])
    ->prefix('clientes/{cliente}')
    ->name('clientes.')
    ->group(function () {
        Route::get('servicios/create', [ServicioController::class, 'create'])->name('servicios.create');
        Route::post('servicios', [ServicioController::class, 'store'])->name('servicios.store');
        Route::get('servicios/{servicio}', [ServicioController::class, 'show'])->name('servicios.show');
        Route::get('servicios/{servicio}/edit', [ServicioController::class, 'edit'])->name('servicios.edit');
        Route::put('servicios/{servicio}', [ServicioController::class, 'update'])->name('servicios.update');
        Route::delete('servicios/{servicio}', [ServicioController::class, 'destroy'])->name('servicios.destroy');
    });
