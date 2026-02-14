<?php

namespace App\Modules\Dashboard\Services;

use App\Core\Contracts\Repositories\DashboardRepositoryInterface;

class DashboardService
{
    public function __construct(
        private DashboardRepositoryInterface $repository
    ) {}

    public function getEstadisticas(): array
    {
        $estadisticas = $this->repository->getEstadisticas();

        return [
            'totalClientes' => $estadisticas['clientes']['total'],
            'clientesNuevosMes' => $estadisticas['clientes']['nuevosMes'],
            'clientesAlDia' => $estadisticas['clientes']['alDia'] ?? 0,
            'totalServicios' => $estadisticas['servicios']['total'],
            'serviciosActivos' => $estadisticas['servicios']['activos'],
            'serviciosCortados' => $estadisticas['servicios']['cortados'],
            'serviciosNuevosMes' => $estadisticas['servicios']['nuevosMes'],
            'serviciosActivosConRecibosVencidos' => $estadisticas['comprobantes']['serviciosVencidos']['activosConRecibosVencidos'] ?? 0,
            'totalRecibos' => $estadisticas['comprobantes']['recibos']['total'],
            'recibosPendientes' => $estadisticas['comprobantes']['recibos']['pendientes'],
            'recibosVencidos' => $estadisticas['comprobantes']['recibos']['vencidas'],
            'saldoTotalPendiente' => $estadisticas['comprobantes']['recibos']['saldoTotalPendiente'] ?? 0,
            'montoTotalVencido' => $estadisticas['comprobantes']['recibos']['montoTotalVencido'] ?? 0,
            'totalPagos' => $estadisticas['comprobantes']['pagos']['total'],
            'pagosMes' => $estadisticas['comprobantes']['pagos']['mes'] ?? 0,
            'pagosHoy' => $estadisticas['comprobantes']['pagos']['hoy'] ?? 0,
            'pagosCountHoy' => $estadisticas['comprobantes']['pagos']['countHoy'] ?? 0,
            'pagosCountMes' => $estadisticas['comprobantes']['pagos']['countMes'],
            'pagosDuplicados' => $estadisticas['comprobantes']['pagos']['duplicados'] ?? ['total' => 0, 'pagos' => collect(), 'pagosJson' => []],
            'totalRouters' => $estadisticas['red']['routers']['total'],
            'routersActivos' => $estadisticas['red']['routers']['activos'],
            'totalNodos' => $estadisticas['red']['nodos']['total'],
            'totalPlanes' => $estadisticas['red']['planes']['total'],
            'serviciosRecientes' => $estadisticas['servicios']['recientes'],
            'recibosVencidosRecientes' => $estadisticas['comprobantes']['recibos']['vencidasRecientes'],
            'pagosRecientes' => $estadisticas['comprobantes']['pagos']['recientes'],
            'serviciosPorEstado' => $estadisticas['servicios']['porEstado'],
            'recibosPorEstado' => [
                'pendientes' => $estadisticas['comprobantes']['recibos']['pendientes'],
                'vencidas' => $estadisticas['comprobantes']['recibos']['vencidas'],
                'pagadas' => $estadisticas['comprobantes']['recibos']['pagadas'],
            ],
            'ingresosMensuales' => $estadisticas['ingresosMensuales'],
        ];
    }

    /**
     * Checklist de primeros pasos para ISPs nuevos.
     * Solo incluye ítems pendientes para mostrar en el dashboard.
     */
    public function getChecklistPrimerosPasos(array $estadisticas): array
    {
        $totalRouters = $estadisticas['totalRouters'] ?? 0;
        $totalPlanes = $estadisticas['totalPlanes'] ?? 0;
        $totalClientes = $estadisticas['totalClientes'] ?? 0;

        $items = [
            [
                'label' => 'Configurar router',
                'done' => $totalRouters > 0,
                'route' => 'red.routers.create',
                'params' => [],
            ],
            [
                'label' => 'Crear plan de internet',
                'done' => $totalPlanes > 0,
                'route' => 'servicios.planes.index',
                'params' => [],
            ],
            [
                'label' => 'Registrar primer cliente',
                'done' => $totalClientes > 0,
                'route' => 'clientes.index',
                'params' => [],
            ],
        ];

        $pendientes = array_filter($items, fn ($i) => !$i['done']);
        return [
            'items' => $items,
            'tienePendientes' => count($pendientes) > 0,
        ];
    }
}
