<?php

namespace App\Modules\Comprobantes\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Comprobantes\Requests\StorePromesaPagoRequest;
use App\Modules\Comprobantes\Requests\UpdatePromesaPagoRequest;
use App\Modules\Comprobantes\Models\PromesaPago;
use App\Modules\Comprobantes\Services\PromesaPagoService;
use App\Modules\Clientes\Models\Cliente;
use App\Modules\Comprobantes\Models\Recibo;
use App\Core\Traits\RespondsWithJson;
use App\Core\Traits\ValidatesDebtOperations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PromesaPagoController extends Controller
{
    use RespondsWithJson, ValidatesDebtOperations;

    public function __construct(
        private PromesaPagoService $promesaPagoService
    ) {}

    public function create(Cliente $cliente, Recibo $recibo)
    {
        // Verificar si ya existe una promesa de pago activa para este recibo
        $promesaActiva = $recibo->promesaPagoActiva();

        // Si hay promesa activa, redirigir con mensaje de error
        if ($promesaActiva) {
            $estadoTexto = $promesaActiva->estado === PromesaPago::ESTADO_PENDIENTE
                ? 'pendiente'
                : 'vencida';
            $fechaCompromiso = $promesaActiva->fecha_compromiso->format('d/m/Y');
            $montoComprometido = number_format($promesaActiva->monto_comprometido, 2);

            $mensaje = "Ya existe una promesa de pago {$estadoTexto} para este recibo. " .
                "Fecha de compromiso: {$fechaCompromiso}. " .
                "Monto comprometido: S/ {$montoComprometido}. " .
                "Solo se puede crear una promesa de pago por recibo.";

            $this->logDebug('Promesa activa existente, redirigiendo', [
                'recibo_id' => $recibo->id,
                'promesa_id' => $promesaActiva->id,
                'cliente_id' => $cliente->id
            ]);

            // Siempre redirigir con mensaje de error
            return redirect()
                ->route('clientes.show', $cliente)
                ->with('error', $mensaje)
                ->with('active_tab', 'pagos');
        }

        $promesa = null;
        return view('clientes.promesas-pago.create', compact('cliente', 'recibo', 'promesa'));
    }

    public function edit(Cliente $cliente, Recibo $recibo, PromesaPago $promesa)
    {
        return view('clientes.promesas-pago.edit', compact('cliente', 'recibo', 'promesa'));
    }

    public function store(StorePromesaPagoRequest $request, Cliente $cliente, Recibo $recibo)
    {
        $validacion = $this->validateDebtForPromise($recibo, $cliente);
        if ($validacion) {
            return $validacion;
        }

        try {
            $validated = $request->validated();
            $validated['cliente_id'] = $cliente->id;
            $validated['servicio_id'] = $recibo->servicio_id;
            $validated['creado_por'] = auth()->id();
            $validated['estado'] = PromesaPago::ESTADO_PENDIENTE;

            // Remover hora_compromiso si la columna no existe en la BD
            if (!\Illuminate\Support\Facades\Schema::hasColumn('promesas_pago', 'hora_compromiso')) {
                unset($validated['hora_compromiso']);
            }

            $promesa = PromesaPago::create($validated);

            // Activar servicio automáticamente si está cortado
            $this->promesaPagoService->procesarPromesaCreada($promesa);

            $this->logDebug('Promesa de pago creada correctamente', [
                'promesa_id' => $promesa->id,
                'recibo_id' => $recibo->id,
                'cliente_id' => $cliente->id,
                'fecha_compromiso' => $promesa->fecha_compromiso->format('Y-m-d'),
                'monto_comprometido' => $promesa->monto_comprometido
            ]);

            return redirect()
                ->route('clientes.show', $cliente)
                ->with('success', 'Promesa de pago creada correctamente.')
                ->with('active_tab', 'pagos');
        } catch (\Exception $e) {
            Log::error('Error al crear promesa de pago', [
                'recibo_id' => $recibo->id,
                'cliente_id' => $cliente->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()
                ->withInput()
                ->with('error', 'Error al crear la promesa de pago: ' . $e->getMessage());
        }
    }

    public function update(UpdatePromesaPagoRequest $request, Cliente $cliente, Recibo $recibo, PromesaPago $promesa)
    {
        try {
            $validated = $request->validated();
            $promesa->update($validated);

            $this->logDebug('Promesa de pago actualizada correctamente', [
                'promesa_id' => $promesa->id,
                'recibo_id' => $recibo->id,
                'cliente_id' => $cliente->id,
                'cambios' => $promesa->getChanges()
            ]);

            return redirect()
                ->route('clientes.show', $cliente)
                ->with('success', 'Promesa de pago actualizada correctamente.')
                ->with('active_tab', 'pagos');
        } catch (\Exception $e) {
            Log::error('Error al actualizar promesa de pago', [
                'promesa_id' => $promesa->id,
                'recibo_id' => $recibo->id,
                'cliente_id' => $cliente->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()
                ->withInput()
                ->with('error', 'Error al actualizar la promesa de pago: ' . $e->getMessage());
        }
    }

    public function cumplir(Cliente $cliente, Recibo $recibo, PromesaPago $promesa)
    {
        try {
            $promesa->marcarComoCumplida();

            $this->logDebug('Promesa de pago marcada como cumplida', [
                'promesa_id' => $promesa->id,
                'recibo_id' => $recibo->id,
                'cliente_id' => $cliente->id,
                'fecha_compromiso' => $promesa->fecha_compromiso->format('Y-m-d'),
                'cumplida_at' => $promesa->cumplida_at?->format('Y-m-d H:i:s')
            ]);

            return redirect()
                ->route('clientes.show', $cliente)
                ->with('success', 'Promesa de pago marcada como cumplida.')
                ->with('active_tab', 'pagos');
        } catch (\Exception $e) {
            Log::error('Error al marcar promesa como cumplida', [
                'promesa_id' => $promesa->id,
                'recibo_id' => $recibo->id,
                'cliente_id' => $cliente->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()
                ->with('error', 'Error al marcar la promesa como cumplida: ' . $e->getMessage());
        }
    }

    public function cancelar(Cliente $cliente, Recibo $recibo, PromesaPago $promesa)
    {
        try {
            $promesa->update(['estado' => PromesaPago::ESTADO_CANCELADA]);

            $this->logDebug('Promesa de pago cancelada', [
                'promesa_id' => $promesa->id,
                'recibo_id' => $recibo->id,
                'cliente_id' => $cliente->id
            ]);

            return redirect()
                ->route('clientes.show', $cliente)
                ->with('success', 'Promesa de pago cancelada correctamente.')
                ->with('active_tab', 'pagos');
        } catch (\Exception $e) {
            Log::error('Error al cancelar promesa de pago', [
                'promesa_id' => $promesa->id,
                'recibo_id' => $recibo->id,
                'cliente_id' => $cliente->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()
                ->with('error', 'Error al cancelar la promesa de pago: ' . $e->getMessage());
        }
    }

    public function destroy(Cliente $cliente, Recibo $recibo, PromesaPago $promesa)
    {
        try {
            // Cargar relaciones necesarias antes de eliminar
            $servicioId = $promesa->servicio_id;
            
            $promesaData = [
                'id' => $promesa->id,
                'recibo_id' => $promesa->recibo_id,
                'servicio_id' => $servicioId,
                'fecha_compromiso' => $promesa->fecha_compromiso->format('Y-m-d'),
            ];

            // Procesar eliminación: cortar servicio si tiene recibos vencidos
            $this->promesaPagoService->procesarPromesaEliminada($promesa);

            $promesa->delete();

            $this->logDebug('Promesa de pago eliminada', [
                'promesa_id' => $promesaData['id'],
                'recibo_id' => $promesaData['recibo_id'],
                'cliente_id' => $cliente->id
            ]);

            return redirect()
                ->route('clientes.show', $cliente)
                ->with('success', 'Promesa de pago eliminada correctamente.')
                ->with('active_tab', 'pagos');
        } catch (\Exception $e) {
            Log::error('Error al eliminar promesa de pago', [
                'promesa_id' => $promesa->id ?? null,
                'recibo_id' => $recibo->id,
                'cliente_id' => $cliente->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()
                ->with('error', 'Error al eliminar la promesa de pago: ' . $e->getMessage());
        }
    }

    private function logDebug(string $message, array $context = []): void
    {
        if (config('app.debug')) {
            Log::debug($message, $context);
        }
    }
}
