<?php

namespace App\Core\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware para rastrear actividad de usuarios
 */
class TrackUserActivity
{
    /**
     * Manejar una solicitud entrante
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($user = $request->user()) {
            $this->updateLastActivity($user);
        }

        return $next($request);
    }

    /**
     * Actualizar última actividad del usuario
     */
    private function updateLastActivity($user): void
    {
        $cacheKey = "user_last_activity_{$user->id}";

        // Solo actualizar cada 5 minutos para evitar muchas escrituras
        if (!Cache::has($cacheKey)) {
            Cache::put($cacheKey, now(), 300);

            // Actualizar en la base de datos si el modelo tiene el campo
            if (method_exists($user, 'getTable')) {
                $user->timestamps = false;
                $user->update(['last_activity_at' => now()]);
                $user->timestamps = true;
            }
        }
    }

    /**
     * Verificar si un usuario está online
     */
    public static function isOnline($userId): bool
    {
        return Cache::has("user_last_activity_{$userId}");
    }

    /**
     * Obtener usuarios online
     */
    public static function getOnlineUserIds(): array
    {
        // Esta implementación requiere Redis para ser eficiente
        // Por ahora retorna array vacío
        return [];
    }
}
