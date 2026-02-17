<?php

namespace App\Modules\Comprobantes\Services;

use App\Core\Exceptions\BusinessException;
use App\Core\Services\BaseService;
use App\Modules\Clientes\Models\Cliente;
use App\Modules\Comprobantes\Models\Comprobante;
use App\Modules\Comprobantes\Models\ComprobanteItem;
use App\Modules\Comprobantes\Models\Pago;
use App\Modules\Comprobantes\Models\Recibo;
use App\Modules\Comprobantes\Models\SerieComprobante;
use App\Modules\Servicios\Models\Servicio;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ComprobanteService extends BaseService
{
    /**
     * Crear comprobante desde un pago
     */
    public function crearDesdePago(Pago $pago, ?string $tipoComprobante = null): Comprobante
    {
        return $this->transactional(function () use ($pago, $tipoComprobante) {
            // Cargar relaciones necesarias
            $pago->loadMissing(['cliente', 'recibo.servicio.plan', 'recibo.servicio.ubicacion']);

            if (!$pago->cliente) {
                throw new BusinessException('El pago no tiene cliente asociado.');
            }

            // Verificar si ya existe un comprobante para este pago
            $comprobanteExistente = Comprobante::where('pago_id', $pago->id)->first();
            if ($comprobanteExistente) {
                return $comprobanteExistente;
            }

            // Determinar tipo de comprobante
            $tipo = $tipoComprobante ?? $this->determinarTipoComprobante($pago->cliente);

            // Obtener serie activa
            $serie = SerieComprobante::obtenerSerieActiva($tipo);
            if (!$serie) {
                throw new BusinessException("No hay serie activa para el tipo de comprobante: {$tipo}");
            }

            // Obtener siguiente número
            $numero = SerieComprobante::obtenerSiguienteNumero($tipo, $serie->serie);

            // Crear comprobante
            $comprobante = Comprobante::create([
                'pago_id' => $pago->id,
                'cliente_id' => $pago->cliente_id,
                'tipo' => $tipo,
                'serie' => $serie->serie,
                'numero' => $numero,
                'numero_completo' => $serie->formatearNumero($numero),
                'fecha_emision' => $pago->fecha_pago ?? now(),
                'monto' => $pago->monto,
                'moneda' => 'PEN',
                'subtotal' => $pago->monto, // ISP generalmente exonerado de IGV
                'igv' => 0,
                'exonerado_igv' => true,
                'forma_pago' => Comprobante::FORMA_CONTADO,
                'estado' => Comprobante::ESTADO_EMITIDO,
                'generado_por' => auth()->id(),
                'periodo_servicio' => $pago->recibo?->periodo,
                'fecha_inicio_servicio' => $this->calcularInicioServicio($pago->recibo),
                'fecha_fin_servicio' => $this->calcularFinServicio($pago->recibo),
            ]);

            // Guardar snapshot del cliente
            $comprobante->guardarSnapshotCliente();

            // Crear ítem del comprobante
            $this->crearItemDesdePago($comprobante, $pago);

            $this->logDebug('Comprobante creado desde pago', [
                'comprobante_id' => $comprobante->id,
                'pago_id' => $pago->id,
                'numero_completo' => $comprobante->numero_completo,
            ]);

            return $comprobante;
        }, 'Error al crear comprobante desde pago');
    }

    /**
     * Crear comprobante manual (sin pago asociado)
     */
    public function crearManual(array $datos): Comprobante
    {
        return $this->transactional(function () use ($datos) {
            $cliente = Cliente::findOrFail($datos['cliente_id']);
            $tipo = $datos['tipo'] ?? $this->determinarTipoComprobante($cliente);

            // Obtener serie
            $serieStr = $datos['serie'] ?? null;
            if ($serieStr) {
                $serie = SerieComprobante::where('tipo', $tipo)
                    ->where('serie', $serieStr)
                    ->firstOrFail();
            } else {
                $serie = SerieComprobante::obtenerSerieActiva($tipo);
                if (!$serie) {
                    throw new BusinessException("No hay serie activa para el tipo: {$tipo}");
                }
            }

            // Obtener siguiente número
            $numero = SerieComprobante::obtenerSiguienteNumero($tipo, $serie->serie);

            // Calcular montos
            $subtotal = $datos['subtotal'] ?? $datos['monto'];
            $igv = $datos['igv'] ?? 0;
            $monto = $datos['monto'] ?? ($subtotal + $igv);

            // Crear comprobante
            $comprobante = Comprobante::create([
                'cliente_id' => $cliente->id,
                'tipo' => $tipo,
                'serie' => $serie->serie,
                'numero' => $numero,
                'numero_completo' => $serie->formatearNumero($numero),
                'fecha_emision' => Carbon::parse($datos['fecha_emision'] ?? now()),
                'monto' => $monto,
                'moneda' => $datos['moneda'] ?? 'PEN',
                'subtotal' => $subtotal,
                'igv' => $igv,
                'descuento' => $datos['descuento'] ?? 0,
                'exonerado_igv' => $datos['exonerado_igv'] ?? true,
                'forma_pago' => $datos['forma_pago'] ?? Comprobante::FORMA_CONTADO,
                'fecha_vencimiento_pago' => isset($datos['fecha_vencimiento_pago'])
                    ? Carbon::parse($datos['fecha_vencimiento_pago'])
                    : null,
                'condiciones_pago' => $datos['condiciones_pago'] ?? null,
                'guia_remision' => $datos['guia_remision'] ?? null,
                'orden_compra' => $datos['orden_compra'] ?? null,
                'periodo_servicio' => $datos['periodo_servicio'] ?? null,
                'fecha_inicio_servicio' => isset($datos['fecha_inicio_servicio'])
                    ? Carbon::parse($datos['fecha_inicio_servicio'])
                    : null,
                'fecha_fin_servicio' => isset($datos['fecha_fin_servicio'])
                    ? Carbon::parse($datos['fecha_fin_servicio'])
                    : null,
                'estado' => Comprobante::ESTADO_EMITIDO,
                'generado_por' => auth()->id(),
                'notas' => $datos['notas'] ?? null,
            ]);

            // Guardar snapshot del cliente
            $comprobante->guardarSnapshotCliente();

            // Crear ítems si se proporcionan
            if (!empty($datos['items'])) {
                foreach ($datos['items'] as $index => $itemData) {
                    $this->crearItem($comprobante, $itemData, $index + 1);
                }
                // Recalcular totales
                $comprobante->calcularTotales();
            } else {
                // Crear ítem genérico
                $this->crearItem($comprobante, [
                    'descripcion' => $datos['descripcion'] ?? 'Servicio de Internet',
                    'cantidad' => 1,
                    'precio_unitario' => $monto,
                    'periodo' => $datos['periodo_servicio'] ?? null,
                ], 1);
            }

            $this->logDebug('Comprobante manual creado', [
                'comprobante_id' => $comprobante->id,
                'numero_completo' => $comprobante->numero_completo,
            ]);

            return $comprobante;
        }, 'Error al crear comprobante manual');
    }

    /**
     * Crear nota de crédito
     * @deprecated Este sistema solo maneja recibos internos. Las notas de crédito no están disponibles.
     */
    public function crearNotaCredito(Comprobante $comprobanteOriginal, string $motivo, string $tipoNota): Comprobante
    {
        throw new BusinessException('Las notas de crédito no están disponibles. Este sistema solo maneja recibos internos.');
    }

    /**
     * Anular comprobante
     */
    public function anular(Comprobante $comprobante, string $motivo): bool
    {
        if (!$comprobante->puedeAnularse()) {
            throw new BusinessException('Este comprobante no puede ser anulado.');
        }

        $resultado = $comprobante->anular($motivo, auth()->id());

        if ($resultado) {
            $this->logDebug('Comprobante anulado', [
                'comprobante_id' => $comprobante->id,
                'numero_completo' => $comprobante->numero_completo,
                'motivo' => $motivo,
            ]);
        }

        return $resultado;
    }

    /**
     * Determinar tipo de comprobante según cliente
     */
    private function determinarTipoComprobante(Cliente $cliente): string
    {
        // Solo se generan recibos (documentos internos)
        return Comprobante::TIPO_RECIBO;
    }

    /**
     * Crear ítem de comprobante desde un pago
     */
    private function crearItemDesdePago(Comprobante $comprobante, Pago $pago): ComprobanteItem
    {
        $recibo = $pago->recibo;
        $servicio = $recibo?->servicio;
        $plan = $servicio?->plan;

        $descripcion = 'Servicio de Internet';
        $descripcionDetalle = '';

        if ($plan) {
            $descripcion = "Servicio de Internet - Plan {$plan->nombre}";
            if ($plan->velocidad_bajada_mbps) {
                $descripcionDetalle .= "Velocidad: {$plan->velocidad_bajada_mbps} Mbps";
            }
        }

        if ($recibo && $recibo->codigo) {
            $descripcionDetalle .= ($descripcionDetalle ? ' | ' : '') . "Código Recibo: {$recibo->codigo}";
        }

        return ComprobanteItem::create([
            'comprobante_id' => $comprobante->id,
            'orden' => 1,
            'codigo_producto' => $servicio?->id ? "SERV-{$servicio->id}" : null,
            'codigo_sunat' => '84111502', // Código SUNAT para servicios de telecomunicaciones
            'unidad_medida' => ComprobanteItem::UNIDAD_SERVICIO,
            'descripcion' => $descripcion,
            'descripcion_detalle' => $descripcionDetalle ?: null,
            'cantidad' => 1,
            'precio_unitario' => $pago->monto,
            'valor_unitario' => $pago->monto, // Exonerado de IGV
            'subtotal' => $pago->monto,
            'igv' => 0,
            'total' => $pago->monto,
            'tipo_afectacion_igv' => ComprobanteItem::TIPO_EXONERADO,
            'porcentaje_igv' => 0,
            'servicio_id' => $servicio?->id,
            'recibo_id' => $recibo?->id,
            'periodo' => $recibo?->periodo,
        ]);
    }

    /**
     * Crear ítem de comprobante genérico
     */
    private function crearItem(Comprobante $comprobante, array $data, int $orden): ComprobanteItem
    {
        $tipoAfectacion = $data['tipo_afectacion_igv'] ?? ComprobanteItem::TIPO_EXONERADO;
        $porcentajeIgv = $tipoAfectacion === ComprobanteItem::TIPO_GRAVADO ? 18 : 0;

        return ComprobanteItem::create([
            'comprobante_id' => $comprobante->id,
            'orden' => $orden,
            'codigo_producto' => $data['codigo_producto'] ?? null,
            'codigo_sunat' => $data['codigo_sunat'] ?? '84111502',
            'unidad_medida' => $data['unidad_medida'] ?? ComprobanteItem::UNIDAD_SERVICIO,
            'descripcion' => $data['descripcion'],
            'descripcion_detalle' => $data['descripcion_detalle'] ?? null,
            'cantidad' => $data['cantidad'] ?? 1,
            'precio_unitario' => $data['precio_unitario'],
            'tipo_afectacion_igv' => $tipoAfectacion,
            'porcentaje_igv' => $porcentajeIgv,
            'servicio_id' => $data['servicio_id'] ?? null,
            'recibo_id' => $data['recibo_id'] ?? null,
            'periodo' => $data['periodo'] ?? null,
        ]);
    }

    /**
     * Calcular fecha inicio de servicio desde recibo.
     * Usa fecha_activacion_definitiva (inicio de cobro) o fecha_instalacion si es el primer mes.
     */
    private function calcularInicioServicio(?Recibo $recibo): ?Carbon
    {
        if (!$recibo || !$recibo->periodo) {
            return null;
        }

        try {
            $periodoMes = Carbon::createFromFormat('Y-m', $recibo->periodo);
            if ($recibo->relationLoaded('servicio') && $recibo->servicio) {
                $s = $recibo->servicio;
                $inicioCobro = $s->fecha_activacion_definitiva
                    ? $s->fecha_activacion_definitiva->copy()->startOfDay()
                    : $s->fecha_instalacion?->copy()->startOfDay();
                if ($inicioCobro && $inicioCobro->format('Y-m') === $periodoMes->format('Y-m')) {
                    return $inicioCobro;
                }
            }
            return $periodoMes->copy()->startOfMonth();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Calcular fecha fin de servicio desde recibo.
     * Si el servicio tiene fecha_corte en ese mes (prorrateo por suspensión), usa esa fecha.
     */
    private function calcularFinServicio(?Recibo $recibo): ?Carbon
    {
        if (!$recibo || !$recibo->periodo) {
            return null;
        }

        try {
            $periodoMes = Carbon::createFromFormat('Y-m', $recibo->periodo);
            if ($recibo->relationLoaded('servicio') && $recibo->servicio?->fecha_corte) {
                $fc = $recibo->servicio->fecha_corte;
                if ($fc->format('Y-m') === $periodoMes->format('Y-m')) {
                    return $fc->copy()->startOfDay();
                }
            }
            return $periodoMes->copy()->endOfMonth();
        } catch (\Exception $e) {
            return null;
        }
    }

    private function logDebug(string $message, array $context = []): void
    {
        if (config('app.debug')) {
            Log::debug($message, $context);
        }
    }
}
