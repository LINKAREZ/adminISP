<?php

namespace App\Modules\Servicios\Services;

use App\Modules\Servicios\Models\Plan;
use App\Modules\Red\Models\Router;
use Illuminate\Support\Facades\Log;

class PlanService
{

    public function procesarPerfilesRouterOS(Router $router, array $perfiles): array
    {
        $planesExistentes = Plan::where('router_id', $router->id)
            ->get([
                'id',
                'perfil_mikrotik',
                'precio_mensual',
                'tipo_conexion',
                'velocidad_bajada_mbps',
                'velocidad_subida_mbps',
                'local_address',
                'remote_address',
                'dns',
                'rate_limit',
            ])
            ->keyBy('perfil_mikrotik');

        $perfilesExcluidos = ['default', 'default-encryption'];

        $perfilesProcesados = [];
        foreach ($perfiles as $perfil) {
            $nombrePerfil = $perfil['name'] ?? '';

            if (in_array(strtolower($nombrePerfil), array_map('strtolower', $perfilesExcluidos))) {
                continue;
            }

            $velocidades = $this->extraerVelocidadesRateLimit($perfil['rate-limit'] ?? '');
            $dns = $perfil['dns-server'] ?? $perfil['dns'] ?? '';
            $planExistente = $planesExistentes[$nombrePerfil] ?? null;

            $perfilesProcesados[] = [
                'name' => $nombrePerfil,
                'local-address' => $planExistente?->local_address ?? ($perfil['local-address'] ?? ''),
                'remote-address' => $planExistente?->remote_address ?? ($perfil['remote-address'] ?? ''),
                'dns' => $planExistente?->dns ?? $dns,
                'rate-limit' => $planExistente?->rate_limit ?? ($perfil['rate-limit'] ?? ''),
                'velocidad_bajada_mbps' => $planExistente?->velocidad_bajada_mbps ?? $velocidades['bajada'],
                'velocidad_subida_mbps' => $planExistente?->velocidad_subida_mbps ?? $velocidades['subida'],
                'tipo_conexion' => $planExistente?->tipo_conexion ?? 'pppoe',
                'precio_mensual' => $planExistente?->precio_mensual ?? null,
                'exists' => (bool) $planExistente,
            ];
        }

        return $perfilesProcesados;
    }

    public function extraerVelocidadesRateLimit(string $rateLimit): array
    {
        $velocidadBajada = 0;
        $velocidadSubida = 0;

        if (empty($rateLimit)) {
            return ['bajada' => $velocidadBajada, 'subida' => $velocidadSubida];
        }

        if (preg_match('/(\d+)([KMGT]?)\/(\d+)([KMGT]?)/i', $rateLimit, $matches)) {
            $bajada = (int)$matches[1];
            $subida = (int)$matches[3];
            $multiplierBajada = $matches[2] ?? '';
            $multiplierSubida = $matches[4] ?? '';

            if (empty($multiplierBajada) && $bajada > 1000) {
                $velocidadBajada = (int)($bajada / 1000000);
            } else {
                $velocidadBajada = $this->convertToMbps($bajada, $multiplierBajada);
            }

            if (empty($multiplierSubida) && $subida > 1000) {
                $velocidadSubida = (int)($subida / 1000000);
            } else {
                $velocidadSubida = $this->convertToMbps($subida, $multiplierSubida);
            }
        }

        return [
            'bajada' => $velocidadBajada,
            'subida' => $velocidadSubida,
        ];
    }

    private function convertToMbps(int $value, string $multiplier): int
    {
        $multipliers = [
            'K' => 0.001,
            'M' => 1,
            'G' => 1000,
            'T' => 1000000,
        ];

        $multiplierValue = $multipliers[strtoupper($multiplier)] ?? 1;
        return (int)($value * $multiplierValue);
    }

    public function guardarPerfilesImportados(int $routerId, array $perfiles): array
    {
        $guardados = 0;
        $actualizados = 0;
        $errores = 0;

        foreach ($perfiles as $perfilData) {
            try {
                $planExistente = Plan::where('router_id', $routerId)
                    ->where('perfil_mikrotik', $perfilData['name'])
                    ->first();

                $datosPlan = [
                    'router_id' => $routerId,
                    'nombre' => $perfilData['nombre'] ?? $perfilData['name'],
                    'perfil_mikrotik' => $perfilData['name'],
                    'local_address' => !empty($perfilData['local-address']) ? $perfilData['local-address'] : null,
                    'remote_address' => !empty($perfilData['remote-address']) ? $perfilData['remote-address'] : null,
                    'dns' => !empty($perfilData['dns']) ? $perfilData['dns'] : null,
                    'rate_limit' => !empty($perfilData['rate-limit']) ? $perfilData['rate-limit'] : null,
                    'velocidad_bajada_mbps' => $perfilData['velocidad_bajada_mbps'] ?? 0,
                    'velocidad_subida_mbps' => $perfilData['velocidad_subida_mbps'] ?? 0,
                    'precio_mensual' => $perfilData['precio_mensual'],
                    'tipo_conexion' => $perfilData['tipo_conexion'],
                    'estado' => $perfilData['estado'] ?? true,
                ];

                if (!$planExistente) {
                    Plan::create($datosPlan);
                    $guardados++;
                } else {
                    $planExistente->update($datosPlan);
                    $actualizados++;
                }
            } catch (\Exception $e) {
                Log::error('Error al guardar plan importado', [
                    'router_id' => $routerId,
                    'perfil' => $perfilData['name'] ?? 'unknown',
                    'error' => $e->getMessage()
                ]);
                $errores++;
            }
        }

        return [
            'guardados' => $guardados,
            'actualizados' => $actualizados,
            'errores' => $errores,
            'total' => count($perfiles),
        ];
    }
}
