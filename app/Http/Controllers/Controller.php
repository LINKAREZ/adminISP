<?php

namespace App\Http\Controllers;

use App\Core\Traits\ApiResponses;
use App\Core\Traits\HandlesExceptions;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests, ApiResponses, HandlesExceptions;

    /**
     * Mensaje de éxito para operaciones CRUD
     */
    protected function successMessage(string $action, string $model = 'Registro'): string
    {
        return match ($action) {
            'create', 'store' => "{$model} creado correctamente.",
            'update' => "{$model} actualizado correctamente.",
            'delete', 'destroy' => "{$model} eliminado correctamente.",
            'restore' => "{$model} restaurado correctamente.",
            'activate' => "{$model} activado correctamente.",
            'deactivate' => "{$model} desactivado correctamente.",
            default => "Operación realizada correctamente.",
        };
    }

    /**
     * Redireccionar con mensaje de éxito
     */
    protected function redirectWithSuccess(string $route, string $message, array $params = [])
    {
        return redirect()->route($route, $params)->with('success', $message);
    }

    /**
     * Redireccionar con mensaje de error
     */
    protected function redirectWithError(string $route, string $message, array $params = [])
    {
        return redirect()->route($route, $params)->with('error', $message);
    }

    /**
     * Redireccionar atrás con mensaje de éxito
     */
    protected function backWithSuccess(string $message)
    {
        return back()->with('success', $message);
    }

    /**
     * Redireccionar atrás con mensaje de error
     */
    protected function backWithError(string $message)
    {
        return back()->with('error', $message);
    }
}
