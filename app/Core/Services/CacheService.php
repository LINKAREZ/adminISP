<?php

namespace App\Core\Services;

use Illuminate\Support\Facades\Cache;

/**
 * Servicio centralizado para manejo de caché
 *
 * Facilita la invalidación y gestión de caché en toda la aplicación.
 * Proporciona métodos reutilizables para operaciones comunes de caché
 * con claves consistentes y TTLs apropiados.
 *
 * @package App\Core\Services
 */
class CacheService
{
    /**
     * Invalidar caché de estadísticas de un cliente
     *
     * Elimina la caché de estadísticas de un cliente específico.
     * Se debe llamar cuando se modifique información que afecte las estadísticas
     * (servicios, recibos, pagos, etc.).
     *
     * @param int $clienteId ID del cliente
     * @return void
     */
    public function invalidarEstadisticasCliente(int $clienteId): void
    {
        Cache::forget("cliente.{$clienteId}.estadisticas");
    }

    /**
     * Obtener estadísticas de un cliente con caché
     *
     * Obtiene las estadísticas de un cliente desde caché o las calcula
     * si no están en caché. Las estadísticas se almacenan por 5 minutos
     * por defecto.
     *
     * @param int $clienteId ID del cliente
     * @param callable $callback Función que calcula las estadísticas si no están en caché
     * @param int $minutes Minutos de vida útil del caché (default: 5)
     * @return array Estadísticas del cliente
     */
    public function obtenerEstadisticasCliente(int $clienteId, callable $callback, int $minutes = 5): array
    {
        return Cache::remember(
            "cliente.{$clienteId}.estadisticas",
            now()->addMinutes($minutes),
            $callback
        );
    }

    /**
     * Invalidar múltiples cachés de clientes
     *
     * Invalida las estadísticas de múltiples clientes en una sola operación.
     * Útil cuando se realizan cambios masivos que afectan a varios clientes.
     *
     * @param array $clienteIds Array de IDs de clientes
     * @return void
     */
    public function invalidarEstadisticasClientes(array $clienteIds): void
    {
        foreach ($clienteIds as $clienteId) {
            $this->invalidarEstadisticasCliente($clienteId);
        }
    }

    /**
     * Invalidar caché de dashboard
     *
     * Elimina la caché de estadísticas del dashboard.
     * Se debe llamar cuando se modifique información que afecte
     * las métricas mostradas en el dashboard.
     *
     * @return void
     */
    public function invalidarDashboard(): void
    {
        Cache::forget('dashboard.estadisticas');
    }

    /**
     * Invalidar caché de usuarios
     *
     * Invalidación granular: Solo invalida caché relacionado con usuarios
     * evitando afectar otras partes del sistema.
     *
     * Invalida:
     * - Páginas de usuarios paginadas (hasta 10 páginas con diferentes tamaños)
     * - Lista de roles activos
     *
     * @return void
     */
    public static function invalidateUsersCache(): void
    {
        // Invalidar páginas de usuarios (hasta 10 páginas con diferentes perPage)
        for ($i = 1; $i <= 10; $i++) {
            foreach ([10, 15, 20, 25, 50] as $perPage) {
                Cache::forget("users.paginated.{$perPage}.{$i}");
            }
        }

        // Invalidar roles activos (pueden cambiar al modificar usuarios)
        Cache::forget('roles.active');
    }

    /**
     * Invalidar caché de roles
     *
     * Invalida todas las cachés relacionadas con roles:
     * - Lista de roles activos
     * - Permisos agrupados por módulo
     * - Páginas de roles paginadas
     *
     * @return void
     */
    public static function invalidateRolesCache(): void
    {
        Cache::forget('roles.active');
        Cache::forget('permissions.grouped.by.module');
        // Invalidar páginas de roles
        for ($i = 1; $i <= 10; $i++) {
            Cache::forget("roles.paginated.15.{$i}");
        }
    }

    /**
     * Invalidar caché de permisos
     *
     * Invalida todas las cachés relacionadas con permisos:
     * - Lista de módulos únicos
     * - Permisos agrupados por módulo
     * - Páginas de permisos paginadas (con diferentes filtros)
     * - Colecciones completas de permisos
     *
     * Nota: La invalidación de cachés con filtros es aproximada,
     * ya que se invalidan los filtros más comunes. Para una invalidación
     * completa, se recomienda usar tags de caché si están disponibles.
     *
     * @return void
     */
    public static function invalidatePermissionsCache(): void
    {
        Cache::forget('permissions.modules');
        Cache::forget('permissions.grouped.by.module');

        // Invalidar todas las claves de caché de permisos usando tags si están disponibles
        // Si no, invalidar manualmente las claves más comunes
        $patterns = [
            'permissions.paginated.*',
            'permissions.all.*',
        ];

        // Invalidar páginas de permisos (hasta 10 páginas con diferentes perPage)
        for ($i = 1; $i <= 10; $i++) {
            foreach ([10, 15, 20, 25, 50, 100] as $perPage) {
                // Intentar invalidar con diferentes hashes de filtros comunes
                $commonFilters = [
                    md5(serialize([])),
                    md5(serialize(['module' => ''])),
                ];
                foreach ($commonFilters as $hash) {
                    Cache::forget("permissions.paginated.{$hash}.{$perPage}.{$i}");
                }
            }
        }

        // Invalidar colecciones completas (sin paginación)
        $commonFilterHashes = [
            md5(serialize([])),
            md5(serialize(['module' => ''])),
        ];
        foreach ($commonFilterHashes as $hash) {
            Cache::forget("permissions.all.{$hash}");
        }
    }

    /**
     * Invalidar caché de servicios por router
     *
     * Elimina la caché de servicios asociados a un router específico.
     * Se debe llamar cuando se modifiquen servicios de un router.
     *
     * @param int $routerId ID del router
     * @return void
     */
    public function invalidarServiciosRouter(int $routerId): void
    {
        Cache::forget("router.{$routerId}.servicios");
    }

    /**
     * Invalidar caché de planes por router
     *
     * Elimina la caché de planes asociados a un router específico.
     * Se debe llamar cuando se modifiquen planes de un router.
     *
     * @param int $routerId ID del router
     * @return void
     */
    public function invalidarPlanesRouter(int $routerId): void
    {
        Cache::forget("router.{$routerId}.planes");
    }
}
