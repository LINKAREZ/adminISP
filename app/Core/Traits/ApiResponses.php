<?php

namespace App\Core\Traits;

use Illuminate\Http\JsonResponse;

/**
 * Trait para estandarizar respuestas API
 */
trait ApiResponses
{
    /**
     * Respuesta exitosa
     */
    protected function successResponse($data = null, string $message = 'Operación exitosa', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Respuesta de error
     */
    protected function errorResponse(string $message = 'Ha ocurrido un error', int $code = 400, $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    /**
     * Respuesta de creación exitosa
     */
    protected function createdResponse($data = null, string $message = 'Registro creado correctamente'): JsonResponse
    {
        return $this->successResponse($data, $message, 201);
    }

    /**
     * Respuesta de actualización exitosa
     */
    protected function updatedResponse($data = null, string $message = 'Registro actualizado correctamente'): JsonResponse
    {
        return $this->successResponse($data, $message, 200);
    }

    /**
     * Respuesta de eliminación exitosa
     */
    protected function deletedResponse(string $message = 'Registro eliminado correctamente'): JsonResponse
    {
        return $this->successResponse(null, $message, 200);
    }

    /**
     * Respuesta de no encontrado
     */
    protected function notFoundResponse(string $message = 'Registro no encontrado'): JsonResponse
    {
        return $this->errorResponse($message, 404);
    }

    /**
     * Respuesta de no autorizado
     */
    protected function unauthorizedResponse(string $message = 'No autorizado'): JsonResponse
    {
        return $this->errorResponse($message, 401);
    }

    /**
     * Respuesta de prohibido
     */
    protected function forbiddenResponse(string $message = 'Acceso denegado'): JsonResponse
    {
        return $this->errorResponse($message, 403);
    }

    /**
     * Respuesta de validación fallida
     */
    protected function validationErrorResponse($errors, string $message = 'Error de validación'): JsonResponse
    {
        return $this->errorResponse($message, 422, $errors);
    }

    /**
     * Respuesta de error del servidor
     */
    protected function serverErrorResponse(string $message = 'Error interno del servidor'): JsonResponse
    {
        return $this->errorResponse($message, 500);
    }

    /**
     * Respuesta paginada
     */
    protected function paginatedResponse($paginator, string $message = 'Datos obtenidos correctamente'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
            'links' => [
                'first' => $paginator->url(1),
                'last' => $paginator->url($paginator->lastPage()),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
            ],
        ], 200);
    }
}
