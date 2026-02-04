<?php

namespace App\Modules\Notificaciones\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Notificaciones\Services\WhatsAppService;
use App\Modules\Comprobantes\Models\Recibo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NotificacionController extends Controller
{
    public function __construct(
        private WhatsAppService $whatsAppService
    ) {}

    /**
     * Enviar recordatorio de pago por WhatsApp
     */
    public function enviarRecordatorioPago(Request $request, Recibo $recibo)
    {
        $this->authorize('view', $recibo);
        try {
            $resultado = $this->whatsAppService->enviarRecordatorioPago($recibo);

            if ($resultado['success']) {
                // Retornar mensaje y teléfono para mostrar en modal
                return response()->json([
                    'success' => true,
                    'mensaje' => $resultado['mensaje'] ?? '',
                    'telefono' => $resultado['telefono'] ?? '',
                    'telefono_formateado' => $resultado['telefono_formateado'] ?? '',
                    'cliente' => $resultado['cliente'] ?? '',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $resultado['message'] ?? 'Error al generar el recordatorio',
            ], 400);
        } catch (\Exception $e) {
            Log::error('Error en NotificacionController::enviarRecordatorioPago', [
                'recibo_id' => $recibo->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la solicitud',
            ], 500);
        }
    }
}
