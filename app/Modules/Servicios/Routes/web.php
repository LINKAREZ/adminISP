<?php

use App\Modules\Servicios\Controllers\ServicioController;
use App\Modules\Servicios\Controllers\ServicioMainController;
use App\Modules\Servicios\Controllers\PlanController;
use App\Modules\Servicios\Controllers\OnuController;
use Illuminate\Support\Facades\Route;

// Servicios - Ruta principal (DEBE ir ANTES del resource para evitar conflicto)
Route::middleware(['web', 'auth'])->get('servicios', [ServicioMainController::class, 'index'])->name('servicios.home');

// Tipos de servicio: Internet, IPTV, CATV
Route::middleware(['web', 'auth'])->prefix('servicios')->name('servicios.')->group(function () {
    Route::get('internet', [ServicioMainController::class, 'internet'])->name('internet.index');
    Route::get('iptv', [ServicioMainController::class, 'iptv'])->name('iptv.index');
    Route::get('catv', [ServicioMainController::class, 'catv'])->name('catv.index');
});

// Planes como hijo de Internet: /servicios/internet/planes
// IMPORTANTE: Rutas concretas ANTES del resource para que no sean capturadas por planes/{plan}
Route::middleware(['web', 'auth'])->prefix('servicios/internet')->name('servicios.')->group(function () {
    Route::get('planes/interfaces-dhcp', [PlanController::class, 'getInterfacesDhcp'])->name('planes.interfaces-dhcp');
    Route::get('planes/servidores-dhcp', [PlanController::class, 'getServidoresDhcp'])->name('planes.servidores-dhcp');
    Route::get('planes/detalle-servidor-dhcp', [PlanController::class, 'getDetalleServidorDhcp'])->name('planes.detalle-servidor-dhcp');
    Route::post('planes/importar-dhcp', [PlanController::class, 'importarDhcp'])->name('planes.importar-dhcp');
    Route::post('planes/perfiles/importar', [PlanController::class, 'importarPerfiles'])->name('planes.importar-perfiles');
    Route::post('planes/perfiles/guardar', [PlanController::class, 'guardarPerfilesImportados'])->name('planes.guardar-perfiles-importados');
    Route::post('planes/importar-perfiles', [PlanController::class, 'importarPerfiles']);
    Route::post('planes/guardar-perfiles-importados', [PlanController::class, 'guardarPerfilesImportados']);
    Route::resource('planes', PlanController::class);
});

// Redirección desde rutas antiguas /servicios/planes* hacia /servicios/internet/planes*
Route::middleware(['web', 'auth'])->get('servicios/planes', function () {
    return redirect()->route('servicios.planes.index', request()->query(), 301);
});
Route::middleware(['web', 'auth'])->get('servicios/planes/create', function () {
    return redirect()->route('servicios.planes.create', request()->query(), 301);
});
Route::middleware(['web', 'auth'])->get('servicios/planes/{plane}', function ($plane) {
    return redirect()->route('servicios.planes.show', $plane, 301);
});
Route::middleware(['web', 'auth'])->get('servicios/planes/{plane}/edit', function ($plane) {
    return redirect()->route('servicios.planes.edit', $plane, 301);
});

// Servicios PPPoE - Excluir 'create' porque se maneja de forma anidada en clientes/{cliente}/servicios/create
Route::middleware(['web', 'auth'])->group(function () {
    Route::resource('servicios', ServicioController::class)
        ->parameters(['servicios' => 'servicio'])
        ->except(['create', 'index']); // Excluir index también para evitar conflicto
    Route::post('servicios/{servicio}/estado', [ServicioController::class, 'cambiarEstado'])->name('servicios.cambiar-estado');
    Route::post('servicios/{servicio}/abrir-interfaz-onu', [ServicioController::class, 'abrirInterfazOnu'])->name('servicios.abrir-interfaz-onu');
    Route::get('servicios/{servicio}/ip-pppoe', [ServicioController::class, 'getIpPppoe'])->name('servicios.ip-pppoe');
    Route::get('servicios/{servicio}/obtener-ip-dhcp', [ServicioController::class, 'obtenerIpDhcp'])->name('servicios.obtener-ip-dhcp');
    Route::post('servicios/{servicio}/make-static-dhcp', [ServicioController::class, 'makeStaticDhcp'])->name('servicios.make-static-dhcp');
    Route::post('servicios/{servicio}/aplicar-simple-queue', [ServicioController::class, 'aplicarSimpleQueue'])->name('servicios.aplicar-simple-queue');
    Route::post('servicios/{servicio}/quitar-simple-queue', [ServicioController::class, 'quitarSimpleQueue'])->name('servicios.quitar-simple-queue');
    Route::get('servicios/{servicio}/abrir-onu', [ServicioController::class, 'abrirOnuRedirect'])->name('servicios.abrir-onu');
    Route::get('servicios/provisionales', [ServicioController::class, 'provisionales'])->name('servicios.provisionales');
    // Ruta específica para index de servicios PPPoE
    Route::redirect('servicios/pppoe', '/servicios/internet', 301)->name('servicios.index');
    Route::redirect('servicios-pppoe', '/servicios/internet', 301);
    Route::post('servicios/{servicio}/cambiar-estado', [ServicioController::class, 'cambiarEstado']);
    Route::get('servicios/{servicio}/migrar-router', [ServicioController::class, 'migrarRouterForm'])->name('servicios.migrar-router');
    Route::post('servicios/{servicio}/migrar-router', [ServicioController::class, 'migrarRouterStore'])->name('servicios.migrar-router.store');
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
