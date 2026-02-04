<?php

use App\Modules\Red\Controllers\RouterController;
use App\Modules\Red\Controllers\NodoController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('red')->name('red.')->group(function () {
    Route::resource('nodos', NodoController::class);
    Route::resource('routers', RouterController::class);

    // Conexiones PPPoE
    Route::get('routers/{router}/conexiones-pppoe', [RouterController::class, 'conexionesPppoe'])->name('routers.conexiones-pppoe');
    Route::get('routers/{router}/conexiones-pppoe/{sessionId}', [RouterController::class, 'detalleConexionPppoe'])->name('routers.detalle-conexion-pppoe');
    Route::post('routers/{router}/desconectar-pppoe', [RouterController::class, 'desconectarPppoe'])->name('routers.desconectar-pppoe');
    Route::post('routers/{router}/crear-nat-onu', [RouterController::class, 'crearNatOnu'])->name('routers.crear-nat-onu');
    Route::post('routers/{router}/eliminar-nat-onu', [RouterController::class, 'eliminarNatOnu'])->name('routers.eliminar-nat-onu');

    // Reglas de Firewall (Bloqueo)
    Route::get('routers/{router}/reglas-bloqueo', [RouterController::class, 'getReglasBloqueo'])->name('routers.reglas-bloqueo');
    Route::get('routers/{router}/address-lists', [RouterController::class, 'getAddressLists'])->name('routers.address-lists');
    Route::get('routers/{router}/address-list-items', [RouterController::class, 'getAddressListItems'])->name('routers.address-list-items');
    Route::post('routers/{router}/address-list-items', [RouterController::class, 'addAddressListItem'])->name('routers.add-address-list-item');
    Route::post('routers/{router}/crear-regla-bloqueo', [RouterController::class, 'crearReglaBloqueo'])->name('routers.crear-regla-bloqueo');

    // Reglas almacenadas en BD
    Route::get('routers/{router}/reglas', [RouterController::class, 'getReglas'])->name('routers.reglas');
    Route::post('routers/{router}/reglas', [RouterController::class, 'storeRegla'])->name('routers.reglas.store');
    Route::put('routers/{router}/reglas/{regla}', [RouterController::class, 'updateRegla'])->name('routers.reglas.update');
    Route::delete('routers/{router}/reglas/{regla}', [RouterController::class, 'destroyRegla'])->name('routers.reglas.destroy');
    Route::post('routers/{router}/reglas/{regla}/exportar', [RouterController::class, 'exportarRegla'])->name('routers.reglas.exportar');

    // Prueba SNMP
    Route::get('routers/{router}/test-snmp', [RouterController::class, 'testSnmp'])->name('routers.test-snmp');
    Route::get('routers/{router}/snmp-interface/{interfaceName}', [RouterController::class, 'getSnmpInterfaceInfo'])->name('routers.snmp-interface');
});
