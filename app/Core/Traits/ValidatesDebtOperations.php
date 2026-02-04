<?php

namespace App\Core\Traits;

trait ValidatesDebtOperations
{
    /**
     * Validar si un recibo puede recibir pagos
     */
    protected function validateDebtForPayment($recibo, $cliente = null): ?\Illuminate\Http\JsonResponse
    {
        if ($recibo->estaPagada() || $recibo->estado === \App\Modules\Comprobantes\Models\Recibo::ESTADO_PAGADO) {
            $message = 'No se puede registrar un pago para un recibo que ya está pagado.';

            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $message
                ], 422);
            }

            return redirect()
                ->route('clientes.show', $cliente ?? $recibo->cliente_id)
                ->with('error', $message)
                ->with('active_tab', 'pagos');
        }

        return null;
    }

    /**
     * Validar si un recibo puede tener promesas de pago
     */
    protected function validateDebtForPromise($recibo, $cliente = null): ?\Illuminate\Http\JsonResponse
    {
        if ($recibo->pagos()->exists()) {
            $message = 'No se puede crear una promesa de pago para un recibo que ya tiene pagos registrados.';

            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $message
                ], 422);
            }

            return redirect()
                ->route('clientes.show', $cliente ?? $recibo->cliente_id)
                ->with('error', $message)
                ->with('active_tab', 'pagos');
        }

        return null;
    }

    /**
     * Validar si un recibo puede ser editado
     */
    protected function validateDebtForEdit($recibo, $cliente = null): ?\Illuminate\Http\JsonResponse
    {
        if ($recibo->estaPagada() || $recibo->estado === \App\Modules\Comprobantes\Models\Recibo::ESTADO_PAGADO) {
            $message = 'No se puede editar un recibo que ya está pagado.';

            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $message
                ], 422);
            }

            return redirect()
                ->route('clientes.show', $cliente ?? $recibo->cliente_id)
                ->with('error', $message)
                ->with('active_tab', 'pagos');
        }

        return null;
    }

    /**
     * Validar si un cliente tiene promesas activas (para pagos generales)
     */
    protected function validateClientHasActivePromises($cliente): ?\Illuminate\Http\JsonResponse
    {
        if ($cliente->tienePromesasActivas()) {
            $message = 'No se puede registrar un pago general cuando hay promesas de pago activas. Debe pagar las promesas desde la tabla de Recibos.';

            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $message
                ], 422);
            }

            return redirect()
                ->route('clientes.show', $cliente)
                ->with('error', $message)
                ->with('active_tab', 'pagos');
        }

        return null;
    }
}
