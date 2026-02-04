<?php

namespace App\Modules\Dashboard\Repositories;

use App\Core\Contracts\Repositories\DashboardRepositoryInterface;
use App\Modules\Clientes\Models\Cliente;
use App\Modules\Servicios\Models\Servicio;
use App\Modules\Comprobantes\Models\Recibo;
use App\Modules\Comprobantes\Models\Pago;
use App\Modules\Red\Models\Router;
use App\Modules\Red\Models\Nodo;
use App\Modules\Servicios\Models\Plan;
use Illuminate\Support\Facades\Cache;

class DashboardRepository implements DashboardRepositoryInterface
{
    public function getEstadisticas(): array
    {
        $ispId = session('current_isp_id') ?? 'global';
        $cacheKey = 'dashboard_stats_' . $ispId;

        return Cache::remember($cacheKey, now()->addMinutes(10), function () {
            return [
                'clientes' => $this->getEstadisticasClientes(),
                'servicios' => $this->getEstadisticasServicios(),
                'comprobantes' => $this->getEstadisticasComprobantes(),
                'red' => $this->getEstadisticasRed(),
                'ingresosMensuales' => $this->getIngresosMensuales(),
            ];
        });
    }

    public function getEstadisticasClientes(): array
    {
        $now = now();
        $inicioMes = $now->copy()->startOfMonth();

        // Clientes al día (sin recibos vencidos ni pendientes con saldo)
        $clientesAlDia = Cliente::whereDoesntHave('recibos', function ($query) use ($now) {
            $query->where(function ($q) use ($now) {
                $q->where('estado', 'vencido')
                  ->where('saldo', '>', 0);
            })->orWhere(function ($q) use ($now) {
                $q->where('estado', 'pendiente')
                  ->where('saldo', '>', 0)
                  ->where('fecha_vencimiento', '<', $now);
            });
        })->count();

        return [
            'total' => Cliente::count(),
            'nuevosMes' => Cliente::where('created_at', '>=', $inicioMes)->count(),
            'alDia' => $clientesAlDia,
        ];
    }

    public function getEstadisticasServicios(): array
    {
        $now = now();
        $inicioMes = $now->copy()->startOfMonth();

        return [
            'total' => Servicio::count(),
            'activos' => Servicio::where('estado', 'activo')->count(),
            'cortados' => Servicio::where('estado', 'cortado')->count(),
            'nuevosMes' => Servicio::where('created_at', '>=', $inicioMes)->count(),
            'recientes' => Servicio::with(['ubicacion.cliente', 'plan', 'router'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
            'porEstado' => [
                'activos' => Servicio::where('estado', 'activo')->count(),
                'cortados' => Servicio::where('estado', 'cortado')->count(),
            ],
        ];
    }

    public function getEstadisticasComprobantes(): array
    {
        $now = now();
        $inicioMes = $now->copy()->startOfMonth();
        $inicioHoy = $now->copy()->startOfDay();

        return [
            'recibos' => [
                'total' => Recibo::count(),
                'pendientes' => Recibo::where('estado', 'pendiente')->where('saldo', '>', 0)->count(),
                'vencidas' => Recibo::where('estado', 'vencido')->where('saldo', '>', 0)->count(),
                'pagadas' => Recibo::where('estado', 'pagado')->count(),
                'saldoTotalPendiente' => Recibo::where('estado', 'pendiente')->where('saldo', '>', 0)->sum('saldo'),
                'montoTotalVencido' => Recibo::where('estado', 'vencido')->where('saldo', '>', 0)->sum('saldo'),
                'vencidasRecientes' => Recibo::with(['servicio.ubicacion.cliente', 'servicio.plan'])
                    ->where('estado', 'vencido')
                    ->where('saldo', '>', 0)
                    ->orderBy('fecha_vencimiento', 'asc')
                    ->limit(10)
                    ->get(),
            ],
            'pagos' => [
                'total' => Pago::count(),
                'mes' => Pago::where('fecha_pago', '>=', $inicioMes)->sum('monto'),
                'hoy' => Pago::where('fecha_pago', '>=', $inicioHoy)->sum('monto'),
                'countHoy' => Pago::where('fecha_pago', '>=', $inicioHoy)->count(),
                'countMes' => Pago::where('fecha_pago', '>=', $inicioMes)->count(),
                'recientes' => Pago::with(['cliente', 'recibo.servicio.ubicacion.cliente', 'medioPago'])
                    ->orderBy('fecha_pago', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->get(),
                'duplicados' => $this->getPagosConNumeroOperacionDuplicado(),
            ],
            'serviciosVencidos' => [
                'activosConRecibosVencidos' => Servicio::where('estado', 'activo')
                    ->whereHas('recibos', function ($q) {
                        $q->where('estado', 'vencido')
                            ->where('saldo', '>', 0);
                    })
                    ->count(),
            ],
        ];
    }

    public function getEstadisticasRed(): array
    {
        return [
            'routers' => [
                'total' => Router::count(),
                'activos' => Router::where('estado', true)->count(),
            ],
            'nodos' => [
                'total' => Nodo::count(),
            ],
            'planes' => [
                'total' => Plan::count(),
            ],
        ];
    }

    public function getIngresosMensuales(int $meses = 6): array
    {
        $now = now();
        $ingresosMensuales = [];

        for ($i = ($meses - 1); $i >= 0; $i--) {
            $mes = $now->copy()->subMonths($i);
            $inicioMesCalculo = $mes->copy()->startOfMonth();
            $finMesCalculo = $mes->copy()->endOfMonth();

            $ingresosMensuales[] = [
                'mes' => $mes->format('M Y'),
                'monto' => Pago::whereBetween('fecha_pago', [$inicioMesCalculo, $finMesCalculo])
                    ->sum('monto'),
                'cantidad' => Pago::whereBetween('fecha_pago', [$inicioMesCalculo, $finMesCalculo])
                    ->count(),
            ];
        }

        return $ingresosMensuales;
    }

    /**
     * Obtener pagos con números de operación duplicados
     * Retorna una colección agrupada por número de operación con los pagos duplicados
     */
    public function getPagosConNumeroOperacionDuplicado(): array
    {
        // Obtener números de operación que aparecen más de una vez
        $numerosDuplicados = Pago::select('numero_operacion')
            ->whereNotNull('numero_operacion')
            ->where('numero_operacion', '!=', '')
            ->groupBy('numero_operacion')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('numero_operacion');

        if ($numerosDuplicados->isEmpty()) {
            return [
                'total' => 0,
                'pagos' => collect(),
                'pagosJson' => [],
            ];
        }

        // Obtener todos los pagos con esos números de operación
        $pagosDuplicados = Pago::with(['cliente', 'recibo.cliente', 'servicio.ubicacion.cliente', 'medioPago', 'comprobante'])
            ->whereIn('numero_operacion', $numerosDuplicados)
            ->orderBy('numero_operacion')
            ->orderBy('fecha_pago', 'desc')
            ->get();

        // Agrupar por número de operación y preparar datos para JSON
        $pagosAgrupados = $pagosDuplicados->groupBy('numero_operacion');

        // Preparar datos serializables para JavaScript
        $pagosSerializados = [];
        foreach ($pagosAgrupados as $numeroOperacion => $pagos) {
            $pagosSerializados[$numeroOperacion] = $pagos->map(function ($pago) {
                // Obtener cliente_id de diferentes fuentes
                $clienteIdFinal = $pago->cliente_id;
                if (!$clienteIdFinal && $pago->recibo && $pago->recibo->cliente) {
                    $clienteIdFinal = $pago->recibo->cliente->id;
                }
                if (!$clienteIdFinal && $pago->servicio && $pago->servicio->ubicacion && $pago->servicio->ubicacion->cliente) {
                    $clienteIdFinal = $pago->servicio->ubicacion->cliente->id;
                }

                return [
                    'id' => $pago->id,
                    'cliente_id' => $clienteIdFinal,
                    'cliente' => $pago->cliente ? ['id' => $pago->cliente->id, 'nombre' => $pago->cliente->nombre] : null,
                    'recibo' => $pago->recibo ? [
                        'id' => $pago->recibo->id,
                        'cliente' => $pago->recibo->cliente ? ['id' => $pago->recibo->cliente->id, 'nombre' => $pago->recibo->cliente->nombre] : null
                    ] : null,
                    'servicio' => $pago->servicio ? [
                        'id' => $pago->servicio->id,
                        'ubicacion' => $pago->servicio->ubicacion ? [
                            'cliente' => $pago->servicio->ubicacion->cliente ? [
                                'id' => $pago->servicio->ubicacion->cliente->id,
                                'nombre' => $pago->servicio->ubicacion->cliente->nombre
                            ] : null
                        ] : null
                    ] : null,
                    'fecha_pago' => $pago->fecha_pago ? $pago->fecha_pago->toDateString() : null,
                    'monto' => $pago->monto,
                    'medio_pago' => $pago->medio_pago,
                    'medioPago' => $pago->medioPago ? [
                        'id' => $pago->medioPago->id,
                        'nombre' => $pago->medioPago->nombre,
                        'nombreCompleto' => $pago->medioPago->nombreCompleto,
                        'tipo' => $pago->medioPago->tipo
                    ] : null,
                    'comprobante' => $pago->comprobante ? [
                        'id' => $pago->comprobante->id,
                        'numero' => $pago->comprobante->numero ?? null,
                    ] : null,
                    'captura' => $pago->captura ?? null,
                ];
            })->toArray();
        }

        return [
            'total' => $pagosAgrupados->count(),
            'pagos' => $pagosAgrupados,
            'pagosJson' => $pagosSerializados,
        ];
    }
}
