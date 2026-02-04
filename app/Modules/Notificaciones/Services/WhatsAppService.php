<?php

namespace App\Modules\Notificaciones\Services;

use App\Modules\Notificaciones\Models\PlantillaWhatsApp;
use App\Modules\Comprobantes\Models\Recibo;
use App\Modules\Clientes\Models\Cliente;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class WhatsAppService
{
    /**
     * Enviar recordatorio de pago por WhatsApp
     */
    public function enviarRecordatorioPago(Recibo $recibo): array
    {
        try {
            $cliente = $recibo->cliente ?? $recibo->servicio?->ubicacion?->cliente;

            if (!$cliente) {
                return [
                    'success' => false,
                    'message' => 'No se encontró el cliente asociado al recibo',
                ];
            }

            $telefono = $this->obtenerTelefono($cliente);

            if (!$telefono) {
                return [
                    'success' => false,
                    'message' => 'El cliente no tiene un número de teléfono válido',
                ];
            }

            // Obtener plantilla
            $plantilla = PlantillaWhatsApp::porTipo('recordatorio_pago');

            // Si no existe plantilla, crear una por defecto
            if (!$plantilla) {
                $plantilla = $this->crearPlantillaPorDefecto();
            }

            // Preparar variables
            $variables = $this->prepararVariables($recibo, $cliente);

            // Procesar mensaje
            $mensaje = $plantilla->procesarMensaje($variables);

            // Asegurar que el mensaje tenga "Admin ISP" en lugar de "Panel ISP"
            $mensaje = str_replace('Panel ISP', 'Admin ISP', $mensaje);

            // Retornar mensaje y teléfono para envío manual
            // (aún no se envía vía API)
            return [
                'success' => true,
                'mensaje' => $mensaje,
                'telefono' => $telefono,
                'telefono_formateado' => $this->formatearTelefonoParaMostrar($telefono),
                'cliente' => $cliente->nombre,
            ];

        } catch (\Exception $e) {
            Log::error('Error al enviar recordatorio de pago por WhatsApp', [
                'recibo_id' => $recibo->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Error al enviar el mensaje: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Obtener teléfono del cliente en formato válido para WhatsApp
     * WhatsApp requiere: código de país + número (sin espacios, sin +, solo números)
     * Ejemplo: 51912345678 (51 = Perú, 912345678 = número)
     */
    private function obtenerTelefono(Cliente $cliente): ?string
    {
        $telefono = $cliente->telefonos;

        if (!$telefono) {
            return null;
        }

        // Limpiar teléfono: solo números
        $telefono = preg_replace('/[^0-9]/', '', $telefono);

        // Remover ceros iniciales si los hay
        $telefono = ltrim($telefono, '0');

        // Validar que tenga al menos 9 dígitos (número peruano mínimo)
        if (strlen($telefono) < 9) {
            return null;
        }

        // Si ya tiene código de país (51), usarlo tal cual
        if (str_starts_with($telefono, '51')) {
            // Validar que tenga al menos 11 dígitos (51 + 9 dígitos)
            if (strlen($telefono) >= 11) {
                return $telefono;
            }
        }

        // Si empieza con 9 (formato peruano), agregar código de país 51
        if (str_starts_with($telefono, '9')) {
            return '51' . $telefono;
        }

        // Si tiene 9 dígitos y no empieza con 9, asumir que es número peruano y agregar 51
        if (strlen($telefono) == 9) {
            return '51' . $telefono;
        }

        // Si tiene más de 9 dígitos pero no empieza con 51, intentar agregar 51
        if (strlen($telefono) > 9 && !str_starts_with($telefono, '51')) {
            // Podría ser un número con código de área, agregar 51 al inicio
            return '51' . $telefono;
        }

        // Si no cumple ninguna condición, retornar null
        return null;
    }

    /**
     * Preparar variables para la plantilla
     */
    private function prepararVariables(Recibo $recibo, Cliente $cliente): array
    {
        $servicio = $recibo->servicio;
        $plan = $servicio?->plan;

        return [
            'cliente' => $cliente->nombre,
            'documento' => $cliente->documento ?? '',
            'monto' => number_format($recibo->saldo ?? $recibo->monto, 2),
            'codigo_recibo' => $recibo->codigo ?? '',
            'fecha_vencimiento' => $recibo->fecha_vencimiento
                ? $recibo->fecha_vencimiento->format('d/m/Y')
                : '',
            'dias_vencido' => $recibo->fecha_vencimiento && $recibo->fecha_vencimiento->isPast()
                ? $recibo->fecha_vencimiento->diffInDays(now())
                : 0,
            'plan' => $plan?->nombre ?? '',
            'empresa' => config('app.name', 'Admin ISP'),
        ];
    }

    /**
     * Enviar mensaje por WhatsApp
     *
     * NOTA: Esta implementación es un ejemplo básico.
     * Debes configurar tu API de WhatsApp (Twilio, WhatsApp Business API, etc.)
     */
    private function enviarMensaje(string $telefono, string $mensaje): array
    {
        // Obtener configuración de WhatsApp
        $apiUrl = config('services.whatsapp.api_url');
        $apiToken = config('services.whatsapp.api_token');
        $phoneNumberId = config('services.whatsapp.phone_number_id');

        // Si no hay configuración, usar método alternativo (enlace directo)
        if (!$apiUrl || !$apiToken) {
            return $this->enviarMensajeEnlaceDirecto($telefono, $mensaje);
        }

        try {
            // Ejemplo con WhatsApp Business API
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiToken,
                'Content-Type' => 'application/json',
            ])->post("{$apiUrl}/v1/messages", [
                'messaging_product' => 'whatsapp',
                'to' => $telefono,
                'type' => 'text',
                'text' => [
                    'body' => $mensaje,
                ],
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Mensaje enviado correctamente',
                    'data' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'message' => 'Error al enviar mensaje: ' . $response->body(),
            ];

        } catch (\Exception $e) {
            Log::error('Error en API de WhatsApp', [
                'error' => $e->getMessage(),
            ]);

            // Fallback a enlace directo
            return $this->enviarMensajeEnlaceDirecto($telefono, $mensaje);
        }
    }

    /**
     * Enviar mensaje usando enlace directo de WhatsApp Web
     * Este método abre WhatsApp Web/App con el mensaje prellenado
     *
     * IMPORTANTE: WhatsApp requiere el número SIN el signo + en la URL
     * Formato: https://wa.me/51912345678?text=mensaje
     */
    private function enviarMensajeEnlaceDirecto(string $telefono, string $mensaje): array
    {
        // Asegurar que el teléfono no tenga el signo +
        $telefono = str_replace('+', '', $telefono);

        // Validar que el teléfono solo tenga números
        if (!preg_match('/^[0-9]+$/', $telefono)) {
            return [
                'success' => false,
                'message' => 'El número de teléfono no es válido. Debe contener solo números.',
            ];
        }

        // Validar longitud mínima (código de país + número)
        if (strlen($telefono) < 10) {
            return [
                'success' => false,
                'message' => 'El número de teléfono es muy corto. Debe incluir código de país.',
            ];
        }

        // Codificar mensaje para URL
        $mensajeCodificado = urlencode($mensaje);

        // Crear enlace de WhatsApp (sin + en el número)
        $whatsappUrl = "https://wa.me/{$telefono}?text={$mensajeCodificado}";

        return [
            'success' => true,
            'message' => 'Enlace de WhatsApp generado',
            'url' => $whatsappUrl,
            'method' => 'direct_link',
        ];
    }

    /**
     * Crear plantilla por defecto si no existe
     */
    private function crearPlantillaPorDefecto(): PlantillaWhatsApp
    {
        $plantilla = PlantillaWhatsApp::updateOrCreate(
            ['tipo' => 'recordatorio_pago'],
            [
                'nombre' => 'Recordatorio de Pago',
                'mensaje' => "Hola {cliente},\n\nTe recordamos que tienes un recibo pendiente de pago:\n\n📋 *Código de Recibo:* {codigo_recibo}\n💰 *Monto a pagar:* S/ {monto}\n📅 *Fecha de vencimiento:* {fecha_vencimiento}\n\nPor favor, realiza el pago para evitar la suspensión del servicio.\n\nGracias por tu atención.\n\n*Admin ISP*",
                'activo' => true,
            ]
        );

        // Si la plantilla tiene "Panel ISP", actualizarla a "Admin ISP"
        if (str_contains($plantilla->mensaje, 'Panel ISP')) {
            $plantilla->mensaje = str_replace('Panel ISP', 'Admin ISP', $plantilla->mensaje);
            $plantilla->save();
        }

        return $plantilla;
    }

    /**
     * Formatear teléfono para mostrar (agregar + si no lo tiene)
     */
    private function formatearTelefonoParaMostrar(string $telefono): string
    {
        // Si no tiene +, agregarlo
        if (!str_starts_with($telefono, '+')) {
            return '+' . $telefono;
        }
        return $telefono;
    }

    private function logDebug(string $message, array $context = []): void
    {
        if (config('app.debug')) {
            Log::debug($message, $context);
        }
    }
}
