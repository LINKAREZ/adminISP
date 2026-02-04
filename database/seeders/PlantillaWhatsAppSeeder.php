<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Notificaciones\Models\PlantillaWhatsApp;

class PlantillaWhatsAppSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plantilla = PlantillaWhatsApp::updateOrCreate(
            ['tipo' => 'recordatorio_pago'],
            [
                'nombre' => 'Recordatorio de Pago',
                'mensaje' => "Hola {cliente},\n\nTe recordamos que tienes un recibo pendiente de pago:\n\n📋 *Código de Recibo:* {codigo_recibo}\n💰 *Monto a pagar:* S/ {monto}\n📅 *Fecha de vencimiento:* {fecha_vencimiento}\n\nPor favor, realiza el pago para evitar la suspensión del servicio.\n\nGracias por tu atención.\n\n*Admin ISP*",
                'activo' => true,
            ]
        );

        // Si la plantilla ya existía y tenía "Panel ISP", actualizarla
        if (str_contains($plantilla->mensaje, 'Panel ISP')) {
            $plantilla->mensaje = str_replace('Panel ISP', 'Admin ISP', $plantilla->mensaje);
            $plantilla->save();
        }
    }
}
