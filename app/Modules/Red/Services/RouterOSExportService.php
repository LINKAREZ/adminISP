<?php

namespace App\Modules\Red\Services;

use App\Modules\Red\Models\Router;
use App\Modules\Servicios\Models\Servicio;
use Illuminate\Support\Facades\Log;

/**
 * Exporta datos del panel hacia el router MikroTik (PPPoE secrets).
 * Fase 1: Sincronizar servicios activos con usuario único al router.
 */
class RouterOSExportService
{
    public function __construct(
        private RouterOSPppoeService $pppoeService
    ) {}

    /**
     * Sincroniza los servicios activos del panel (con usuario PPPoE único) al router.
     * Crea secrets nuevos y actualiza existentes (password, perfil).
     *
     * @return array{created: int, updated: int, skipped: int, errors: array<int, string>}
     */
    public function syncServiciosToRouter(Router $router): array
    {
        $result = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => []];

        $servicios = Servicio::where('router_id', $router->id)
            ->where('estado', 'activo')
            ->where('tipo_pppoe', 'usuario_unico')
            ->whereNotNull('usuario_pppoe')
            ->where('usuario_pppoe', '!=', '')
            ->with('plan')
            ->get();

        $profiles = $this->pppoeService->getProfiles($router);
        $defaultProfile = $this->getDefaultProfileName($profiles);

        foreach ($servicios as $servicio) {
            $user = trim((string) $servicio->usuario_pppoe);
            $password = $servicio->password_pppoe ?? '';
            if ($password === '') {
                $result['skipped']++;
                $result['errors'][$servicio->id] = 'Servicio sin contraseña PPPoE';
                continue;
            }

            $profile = $servicio->plan && $servicio->plan->perfil_mikrotik
                ? $servicio->plan->perfil_mikrotik
                : $defaultProfile;
            if ($profile === '') {
                $profile = $defaultProfile;
            }

            $remoteAddress = $servicio->plan->remote_address ?? $servicio->ip_asignada ?? null;
            if ($remoteAddress === '') {
                $remoteAddress = null;
            }

            try {
                $secretId = $this->pppoeService->getSecretIdByName($router, $user);
                if ($secretId !== null) {
                    $this->pppoeService->updateSecret($router, $secretId, [
                        'password' => $password,
                        'profile' => $profile,
                        'disabled' => false,
                        'remote-address' => $remoteAddress,
                    ]);
                    $result['updated']++;
                } else {
                    $this->pppoeService->addSecret($router, $user, $password, $profile, $remoteAddress);
                    $result['created']++;
                }
            } catch (\Throwable $e) {
                Log::warning('Export PPPoE: error en servicio', [
                    'servicio_id' => $servicio->id,
                    'router_id' => $router->id,
                    'error' => $e->getMessage(),
                ]);
                $result['errors'][$servicio->id] = $e->getMessage();
            }
        }

        return $result;
    }

    private function getDefaultProfileName(array $profiles): string
    {
        if (empty($profiles)) {
            return 'default';
        }
        $first = $profiles[0];
        $name = $first['name'] ?? null;
        if (is_string($name) && $name !== '') {
            return trim($name);
        }
        return 'default';
    }
}
