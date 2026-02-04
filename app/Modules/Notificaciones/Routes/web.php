<?php

use App\Modules\Notificaciones\Controllers\NotificacionController;
use App\Modules\Notificaciones\Controllers\PlantillaWhatsAppController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('notificaciones')->name('notificaciones.')->group(function () {
    // Plantillas
    Route::get('plantillas', [PlantillaWhatsAppController::class, 'index'])->name('plantillas.index');
    Route::get('plantillas/{plantillaWhatsApp}/edit', [PlantillaWhatsAppController::class, 'edit'])->name('plantillas.edit');
    Route::put('plantillas/{plantillaWhatsApp}', [PlantillaWhatsAppController::class, 'update'])->name('plantillas.update');

    // Envío de notificaciones
    Route::post('recibos/{recibo}/recordatorio', [NotificacionController::class, 'enviarRecordatorioPago'])->name('enviar-recordatorio');
    Route::post('enviar-recordatorio/{recibo}', [NotificacionController::class, 'enviarRecordatorioPago']);
});
