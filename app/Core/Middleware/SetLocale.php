<?php

namespace App\Core\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware para establecer el idioma de la aplicación
 */
class SetLocale
{
    /**
     * Idiomas soportados
     */
    private array $supportedLocales = ['es', 'en'];

    /**
     * Manejar una solicitud entrante
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Prioridad: sesión > header > cookie > default
        $locale = $this->getLocale($request);

        App::setLocale($locale);

        return $next($request);
    }

    /**
     * Obtener el idioma a usar
     */
    private function getLocale(Request $request): string
    {
        // 1. Desde la sesión
        $locale = session('locale');
        if ($locale && in_array($locale, $this->supportedLocales)) {
            return $locale;
        }

        // 2. Desde el header Accept-Language
        $locale = $request->getPreferredLanguage($this->supportedLocales);
        if ($locale) {
            return $locale;
        }

        // 3. Desde cookie
        $locale = $request->cookie('locale');
        if ($locale && in_array($locale, $this->supportedLocales)) {
            return $locale;
        }

        // 4. Default (español)
        return 'es';
    }
}
