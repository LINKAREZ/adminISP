<?php

use App\Modules\Sistema\Controllers\SistemaController;
use App\Modules\Sistema\Controllers\MedioPagoController;
use App\Modules\Sistema\Controllers\OnuModeloController;
use App\Modules\Sistema\Controllers\ApiController;
use App\Modules\Sistema\Controllers\OnuMarcaController;
use App\Modules\Sistema\Controllers\IspController;
use App\Modules\Sistema\Controllers\SuperAdminController;
use App\Modules\Notificaciones\Controllers\PlantillaWhatsAppController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('sistema')->name('sistema.')->group(function () {
    // Ruta principal del módulo Sistema
    Route::get('/', [SistemaController::class, 'index'])->name('index');

    Route::resource('medios-pago', MedioPagoController::class)->parameters(['medios-pago' => 'mediosPago']);
    Route::post('apis/init', [ApiController::class, 'initDefaults'])->name('apis.init');
    Route::resource('apis', ApiController::class)->parameters(['apis' => 'api'])->only(['index', 'edit', 'update']);

    // Plantillas de WhatsApp (usar route('sistema.plantillas-whatsapp.index') para enlaces)
    Route::get('plantillas/whatsapp', [PlantillaWhatsAppController::class, 'index'])->name('plantillas-whatsapp.index');
    Route::get('plantillas/whatsapp/{plantillaWhatsApp}/edit', [PlantillaWhatsAppController::class, 'edit'])->name('plantillas-whatsapp.edit');
    Route::put('plantillas/whatsapp/{plantillaWhatsApp}', [PlantillaWhatsAppController::class, 'update'])->name('plantillas-whatsapp.update');

    // Rutas de Equipo con sub-pestañas
    Route::prefix('equipo')->name('equipo.')->group(function () {
        // Marcas
        Route::resource('marcas', OnuMarcaController::class)->parameters(['marcas' => 'marca']);

        // Modelos (mantener compatibilidad con la ruta anterior)
        Route::get('modelos/create', [OnuModeloController::class, 'create'])->name('modelos.create');
        Route::post('modelos', [OnuModeloController::class, 'store'])->name('modelos.store');
        Route::get('modelos', [OnuModeloController::class, 'index'])->name('modelos.index');
        Route::get('modelos/{modelo}', [OnuModeloController::class, 'show'])->name('modelos.show');
        Route::get('modelos/{modelo}/edit', [OnuModeloController::class, 'edit'])->name('modelos.edit');
        Route::put('modelos/{modelo}', [OnuModeloController::class, 'update'])->name('modelos.update');
    });

    // Mantener compatibilidad con rutas antiguas
    Route::resource('modelos-onu', OnuModeloController::class)->parameters(['modelos-onu' => 'modelo']);
});
