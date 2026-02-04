<?php

namespace App\Core\Exceptions;

use Exception;

/**
 * Excepción para acciones no autorizadas
 */
class UnauthorizedException extends Exception
{
    protected ?string $permission;
    protected ?string $action;

    public function __construct(string $message = 'No tiene permiso para realizar esta acción', ?string $permission = null, ?string $action = null)
    {
        parent::__construct($message, 403);
        $this->permission = $permission;
        $this->action = $action;
    }

    /**
     * Crear para un permiso específico
     */
    public static function forPermission(string $permission): self
    {
        return new self("No tiene el permiso requerido: {$permission}", $permission);
    }

    /**
     * Crear para una acción específica
     */
    public static function forAction(string $action): self
    {
        return new self("No está autorizado para: {$action}", null, $action);
    }

    /**
     * Renderizar la excepción
     */
    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $this->getMessage(),
                'permission' => $this->permission,
                'action' => $this->action,
            ], 403);
        }

        return back()->with('error', $this->getMessage());
    }
}
