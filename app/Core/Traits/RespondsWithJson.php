<?php

namespace App\Core\Traits;

use Illuminate\Http\JsonResponse;

/**
 * Trait para estandarizar respuestas JSON en controladores
 * Elimina duplicación de código para respuestas AJAX
 */
trait RespondsWithJson
{
    /**
     * Retornar respuesta JSON exitosa
     */
    protected function jsonSuccess(string $message, array $data = [], int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            ...$data
        ], $status);
    }

    /**
     * Retornar respuesta JSON de error
     */
    protected function jsonError(string $message, array $errors = [], int $status = 400): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }

    /**
     * Retornar respuesta JSON de validación
     */
    protected function jsonValidation(array $errors, string $message = 'Error de validación'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], 422);
    }

    /**
     * Retornar respuesta JSON o vista según el tipo de petición
     */
    protected function respondOrRedirect(
        $request,
        string $route,
        string $message,
        string $type = 'success',
        array $with = []
    ) {
        if ($request->wantsJson() || $request->ajax()) {
            return $type === 'success'
                ? $this->jsonSuccess($message)
                : $this->jsonError($message);
        }

        // Extraer parámetros de ruta si existen
        $routeParams = [];
        if (isset($with['cliente']) && is_numeric($with['cliente'])) {
            $routeParams['cliente'] = $with['cliente'];
            unset($with['cliente']);
        }
        if (isset($with['servicio']) && is_object($with['servicio'])) {
            $routeParams['servicio'] = $with['servicio'];
            unset($with['servicio']);
        }

        $redirect = redirect()->route($route, $routeParams)->with($type, $message);

        foreach ($with as $key => $value) {
            $redirect->with($key, $value);
        }

        return $redirect;
    }
}

