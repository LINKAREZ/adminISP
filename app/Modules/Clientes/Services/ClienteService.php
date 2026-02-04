<?php

namespace App\Modules\Clientes\Services;

use App\Modules\Clientes\Models\Cliente;
use App\Core\Services\CacheService;

/**
 * Servicio para lógica de negocio relacionada con clientes
 * Extrae la lógica de negocio de los controladores
 */
class ClienteService
{
    public function __construct(
        private CacheService $cacheService
    ) {}

    /**
     * Calcular estadísticas de un cliente
     *
     * Calcula todas las estadísticas relevantes de un cliente incluyendo servicios,
     * recibos, promesas de pago y pagos. Los resultados se cachean para mejorar el rendimiento.
     *
     * Optimizado: Usa métodos de Eloquent en lugar de selectRaw cuando es posible
     * para mejor legibilidad y mantenibilidad, manteniendo el rendimiento.
     *
     * @param Cliente $cliente Cliente del cual calcular las estadísticas
     * @return array Array con las siguientes claves:
     *   - total_servicios: Número total de servicios
     *   - servicios_activos: Número de servicios activos
     *   - servicios_cortados: Número de servicios cortados
     *   - total_recibos: Número total de recibos
     *   - recibos_pendientes: Número de recibos pendientes
     *   - recibos_vencidos: Número de recibos vencidos
     *   - saldo_total: Suma total de saldos pendientes
     *   - promesas_pendientes: Número de promesas de pago pendientes
     *   - promesas_vencidas: Número de promesas de pago vencidas
     *   - total_pagos: Número total de pagos
     *   - pagos_mes_actual: Número de pagos del mes actual
     */
    public function calcularEstadisticas(Cliente $cliente): array
    {
        return $this->cacheService->obtenerEstadisticasCliente(
            $cliente->id,
            function () use ($cliente) {
                // Estadísticas de servicios - conteos directos en BD
                $serviciosStats = [
                    'total' => $cliente->servicios()->count(),
                    'activos' => $cliente->servicios()->where('estado', 'activo')->count(),
                    'cortados' => $cliente->servicios()->where('estado', 'cortado')->count(),
                ];

                // Estadísticas de recibos - usando selectRaw para cálculos complejos (más eficiente en BD)
                // NOTA: Esta consulta calcula múltiples métricas en una sola query para optimizar rendimiento.
                // Requiere índices compuestos en: (cliente_id, estado, saldo) y (cliente_id, estado, fecha_vencimiento)
                // para máximo rendimiento.
                $recibosStats = $cliente->recibos()
                    ->selectRaw('
                        COUNT(*) as total,
                        SUM(CASE WHEN saldo > 0 THEN 1 ELSE 0 END) as pendientes,
                        SUM(CASE WHEN (estado = "vencido" OR (estado = "pendiente" AND fecha_vencimiento < NOW())) AND saldo > 0 THEN 1 ELSE 0 END) as vencidas,
                        COALESCE(SUM(CASE WHEN estado IN ("pendiente", "vencido") THEN saldo ELSE 0 END), 0) as saldo_total
                    ')
                    ->first();

                // Estadísticas de promesas - conteos directos en BD
                $now = now();
                $promesasStats = [
                    'pendientes' => $cliente->promesasPago()->where('estado', 'pendiente')->count(),
                    'vencidas' => $cliente->promesasPago()
                        ->where(function ($query) use ($now) {
                            $query->where('estado', 'vencida')
                                ->orWhere(function ($q) use ($now) {
                                    $q->where('estado', 'pendiente')
                                        ->where('fecha_compromiso', '<', $now);
                                });
                        })
                        ->count(),
                ];

                // Estadísticas de pagos - usando whereYear/whereMonth para mejor legibilidad
                $anioActual = (int)date('Y');
                $mesActual = (int)date('m');
                $pagosStats = [
                    'total' => $cliente->pagos()->count(),
                    'mes_actual' => $cliente->pagos()
                        ->whereYear('fecha_pago', $anioActual)
                        ->whereMonth('fecha_pago', $mesActual)
                        ->count(),
                ];

                return [
                    'total_servicios' => $serviciosStats['total'],
                    'servicios_activos' => $serviciosStats['activos'],
                    'servicios_cortados' => $serviciosStats['cortados'],
                    'total_recibos' => (int)($recibosStats->total ?? 0),
                    'recibos_pendientes' => (int)($recibosStats->pendientes ?? 0),
                    'recibos_vencidos' => (int)($recibosStats->vencidas ?? 0),
                    'saldo_total' => (float)($recibosStats->saldo_total ?? 0),
                    'promesas_pendientes' => $promesasStats['pendientes'],
                    'promesas_vencidas' => $promesasStats['vencidas'],
                    'total_pagos' => $pagosStats['total'],
                    'pagos_mes_actual' => $pagosStats['mes_actual'],
                ];
            }
        );
    }

    /**
     * Invalidar caché de estadísticas del cliente
     */
    public function invalidarEstadisticas(Cliente $cliente): void
    {
        $this->cacheService->invalidarEstadisticasCliente($cliente->id);
    }

    /**
     * Verificar si un cliente puede ser eliminado
     *
     * Verifica si un cliente cumple con las condiciones necesarias para ser eliminado.
     * Un cliente NO puede ser eliminado si:
     * - Tiene servicios activos
     * - Tiene recibos pendientes (con saldo > 0)
     *
     * @param Cliente $cliente Cliente a verificar
     * @return array Array con las siguientes claves:
     *   - puede_eliminar: bool Indica si el cliente puede ser eliminado
     *   - razones: array Lista de razones por las cuales no puede ser eliminado (vacío si puede eliminarse)
     */
    public function puedeEliminar(Cliente $cliente): array
    {
        $puedeEliminar = true;
        $razones = [];

        if ($cliente->servicios()->where('estado', 'activo')->exists()) {
            $puedeEliminar = false;
            $razones[] = 'tiene servicios activos';
        }

        if ($cliente->recibos()->where('saldo', '>', 0)->exists()) {
            $puedeEliminar = false;
            $razones[] = 'tiene recibos pendientes';
        }

        return [
            'puede_eliminar' => $puedeEliminar,
            'razones' => $razones,
        ];
    }

    /**
     * Procesar datos de DNI/RUC para crear cliente
     *
     * Procesa y normaliza los datos obtenidos de las APIs de DNI/RUC (APISPERU)
     * y los mapea a los campos correctos del modelo Cliente.
     *
     * Extrae la lógica de mapeo de campos del controlador para mantener
     * separación de responsabilidades.
     *
     * @param array $validated Array de datos validados del formulario
     * @param \Illuminate\Http\Request $request Request con datos adicionales (DNI/RUC)
     * @return array Array de datos procesados listos para crear el cliente
     */
    public function procesarDatosCliente(array $validated, $request): array
    {
        // Construir nombre completo a partir de campos individuales (si existen)
        // NOTA: Los campos individuales NO se guardan en BD, solo se usan para construir 'nombre'
        $partesNombre = [];
        if ($request->filled('dni_nombres')) {
            $partesNombre[] = $request->dni_nombres;
        }
        if ($request->filled('dni_apellido_paterno')) {
            $partesNombre[] = $request->dni_apellido_paterno;
        }
        if ($request->filled('dni_apellido_materno')) {
            $partesNombre[] = $request->dni_apellido_materno;
        }

        // Si hay campos individuales, construir nombre completo
        if (!empty($partesNombre)) {
            $validated['nombre'] = trim(implode(' ', array_filter($partesNombre)));
        }

        // Campos de información de RUC
        if ($request->filled('ruc_nombre_comercial')) {
            $validated['nombre_comercial'] = $request->ruc_nombre_comercial;
        }
        if ($request->filled('ruc_estado')) {
            $validated['estado_ruc'] = $request->ruc_estado;
        }
        if ($request->filled('ruc_condicion')) {
            $validated['condicion_ruc'] = $request->ruc_condicion;
        }
        if ($request->filled('ruc_ubigeo')) {
            $validated['ubigeo'] = $request->ruc_ubigeo;
        }
        if ($request->filled('ruc_capital')) {
            $validated['capital'] = is_numeric($request->ruc_capital) ? $request->ruc_capital : null;
        }

        // Campos de dirección de la API
        if ($request->filled('direccion_api')) {
            $validated['direccion_api'] = $request->direccion_api;
        }
        if ($request->filled('departamento_api')) {
            $validated['departamento_api'] = $request->departamento_api;
        }
        if ($request->filled('provincia_api')) {
            $validated['provincia_api'] = $request->provincia_api;
        }
        if ($request->filled('distrito_api')) {
            $validated['distrito_api'] = $request->distrito_api;
        }
        if ($request->filled('fuente_info')) {
            $validated['fuente_info'] = $request->fuente_info;
        }

        return $validated;
    }
}
