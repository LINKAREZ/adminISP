<?php

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withCommands([
        \App\Console\Commands\IspCreateDatabase::class,
        \App\Console\Commands\IspMigrateTenant::class,
        \App\Console\Commands\IspMigrateToMultiTenant::class,
        \App\Console\Commands\InstallReset::class,
    ])
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: function () {
            // Cargar rutas API centralizadas
            require __DIR__ . '/../routes/api.php';
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Detrás de nginx/proxy: usar X-Forwarded-Proto para HTTPS y evitar 419 en login
        $middleware->trustProxies(at: '*');
        $middleware->redirectGuestsTo('/login');
        // Comentado para evitar bucles - se maneja manualmente en el controlador
        // $middleware->redirectUsersTo('/dashboard');
        $middleware->web(append: [
            \App\Http\Middleware\RedirectIfNotInstalled::class,
            \App\Core\Middleware\SetIspContext::class,
        ]);
        $middleware->alias([
            'permission' => \App\Core\Middleware\CheckPermission::class,
            'superadmin' => \App\Core\Middleware\EnsureSuperAdmin::class,
            'installer' => \App\Http\Middleware\EnsureNotInstalled::class,
            'portal.cliente' => \App\Http\Middleware\EnsurePortalCliente::class,
            'portal.guest' => \App\Http\Middleware\RedirectIfPortalCliente::class,
            'portal.isp' => \App\Http\Middleware\SetPortalIspContext::class,
            'tenant.aviso' => \App\Http\Middleware\SetTenantFromQueryForAviso::class,
        ]);
    })
    ->withProviders([
        \App\Providers\AppServiceProvider::class,
        \App\Providers\AuthServiceProvider::class,
        \App\Core\Providers\ViewServiceProvider::class,
        \App\Modules\Installer\ModuleServiceProvider::class,
        \App\Modules\ControlAcceso\ModuleServiceProvider::class,
        \App\Modules\Clientes\ModuleServiceProvider::class,
        \App\Modules\Servicios\ModuleServiceProvider::class,
        \App\Modules\Comprobantes\ModuleServiceProvider::class,
        \App\Modules\Red\ModuleServiceProvider::class,
        \App\Modules\Sistema\ModuleServiceProvider::class,
        \App\Modules\Dashboard\ModuleServiceProvider::class,
        \App\Modules\Auth\ModuleServiceProvider::class,
        \App\Modules\Notificaciones\ModuleServiceProvider::class,
        \App\Modules\Auditoria\ModuleServiceProvider::class,
        \App\Modules\Instalaciones\ModuleServiceProvider::class,
        \App\Modules\Infraestructura\ModuleServiceProvider::class,
        \App\Modules\Almacen\ModuleServiceProvider::class,
        \App\Modules\MapaRed\ModuleServiceProvider::class,
        \App\Modules\CorteFacturacion\ModuleServiceProvider::class,
        \App\Modules\Onboarding\ModuleServiceProvider::class,
        \App\Modules\Tenant\ModuleServiceProvider::class,
        \App\Modules\PortalCliente\ModuleServiceProvider::class,
    ])
    ->withExceptions(function (Exceptions $exceptions) {
        // Redirigir cuando falten tablas FTTH (OLT/ODF/splitters) en el tenant
        $exceptions->render(function (QueryException $exception, \Illuminate\Http\Request $request) {
            if ($request->expectsJson() || $request->ajax()) {
                return null;
            }
            $msg = $exception->getMessage();
            $tablasFtth = ['olts', 'odfs', 'olt_puertos_pon', 'odf_puertos', 'enlace_olt_odf', 'recorrido_hilo_origen', 'splitters', 'splitter_salidas'];
            $esTablaFaltante = str_contains($msg, 'Base table or view not found')
                && str_contains($msg, "doesn't exist")
                && collect($tablasFtth)->contains(fn ($t) => str_contains($msg, $t));
            if (!$esTablaFaltante) {
                return null;
            }
            $ruta = $request->route();
            $nombreRuta = $ruta ? $ruta->getName() : null;
            if ($nombreRuta && str_starts_with($nombreRuta, 'infraestructura.')) {
                $ispId = request()->user()?->isp_id;
                return redirect()->route('infraestructura.mapa.index')
                    ->with('warning', 'Las tablas FTTH (OLT/ODF) no existen en este ISP. Vaya a Infraestructura → Detalle PON y pulse «Crear tablas FTTH ahora», o ejecute en el servidor: php artisan isp:migrate-tenant' . ($ispId ? ' --isp=' . $ispId : ''));
            }
            return null;
        });

        // En rutas superadmin: mostrar error con mensaje para diagnosticar 500
        $exceptions->render(function (\Throwable $exception, \Illuminate\Http\Request $request) {
            if ($request->is('superadmin*') && !$request->expectsJson() && !$request->ajax()) {
                Log::error('Excepción en superadmin', [
                    'message' => $exception->getMessage(),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                    'trace' => $exception->getTraceAsString(),
                ]);
                return response()->view('superadmin.error-dashboard-raw', [
                    'message' => $exception->getMessage(),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                ], 500);
            }
            return null;
        });

        // Manejo de excepciones para respuestas AJAX/JSON
        $exceptions->render(function (\Throwable $exception, \Illuminate\Http\Request $request) {
            if ($request->expectsJson() || $request->ajax()) {
                // Obtener código de estado HTTP de la excepción si está disponible
                $status = 500;
                if ($exception instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                    $status = $exception->getStatusCode();
                } elseif ($exception instanceof \Illuminate\Http\Exceptions\HttpResponseException) {
                    $status = $exception->getResponse()->getStatusCode();
                }
                $message = $exception->getMessage();

                // En producción, no exponer mensajes de error detallados
                if (!config('app.debug') && $status >= 500) {
                    $message = 'Ocurrió un error interno. Por favor, intenta nuevamente.';
                }

                // Log del error para debugging
                Log::error('Excepción no manejada', [
                    'message' => $exception->getMessage(),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                    'trace' => config('app.debug') ? $exception->getTraceAsString() : null,
                    'request_url' => $request->fullUrl(),
                    'request_method' => $request->method(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'errors' => config('app.debug') ? [
                        'exception' => get_class($exception),
                        'file' => $exception->getFile(),
                        'line' => $exception->getLine(),
                    ] : [],
                ], $status);
            }
        });
    })->create();
