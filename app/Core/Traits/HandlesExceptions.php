<?php

namespace App\Core\Traits;

use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * Trait para manejo consistente de excepciones en controladores
 */
trait HandlesExceptions
{
    /**
     * Ejecutar acción con manejo de excepciones
     *
     * @param callable $action Acción a ejecutar
     * @param string $errorMessage Mensaje de error por defecto
     * @param string|null $redirectTo Ruta de redirección en caso de error
     * @return mixed
     */
    protected function handleAction(callable $action, string $errorMessage = 'Ha ocurrido un error', ?string $redirectTo = null)
    {
        try {
            return $action();
        } catch (ValidationException $e) {
            throw $e; // Dejar que Laravel maneje las validaciones
        } catch (ModelNotFoundException $e) {
            $message = 'El registro solicitado no existe.';
            Log::warning($message, ['exception' => $e->getMessage()]);

            return $this->handleError($message, $redirectTo);
        } catch (QueryException $e) {
            $message = $this->parseQueryException($e);
            if (config('app.debug')) {
                Log::error('Error de base de datos: ' . $e->getMessage(), [
                    'sql' => $e->getSql() ?? 'N/A',
                    'bindings' => $e->getBindings() ?? [],
                ]);
            } else {
                Log::error('Error de base de datos: ' . $e->getMessage());
            }

            return $this->handleError($message, $redirectTo);
        } catch (Exception $e) {
            if (config('app.debug')) {
                Log::error($errorMessage . ': ' . $e->getMessage(), [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]);
            } else {
                Log::error($errorMessage . ': ' . $e->getMessage());
            }

            return $this->handleError($errorMessage, $redirectTo);
        }
    }

    /**
     * Ejecutar acción con manejo de excepciones y respuesta JSON
     */
    protected function handleApiAction(callable $action, string $errorMessage = 'Ha ocurrido un error')
    {
        try {
            return $action();
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors(),
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'El registro solicitado no existe.',
            ], 404);
        } catch (QueryException $e) {
            $message = $this->parseQueryException($e);
            if (config('app.debug')) {
                Log::error('Error de base de datos: ' . $e->getMessage(), [
                    'sql' => $e->getSql() ?? 'N/A',
                    'bindings' => $e->getBindings() ?? [],
                ]);
            } else {
                Log::error('Error de base de datos: ' . $e->getMessage());
            }

            return response()->json([
                'success' => false,
                'message' => $message,
            ], 500);
        } catch (Exception $e) {
            if (config('app.debug')) {
                Log::error($errorMessage . ': ' . $e->getMessage(), ['exception' => $e]);
            } else {
                Log::error($errorMessage . ': ' . $e->getMessage());
            }

            return response()->json([
                'success' => false,
                'message' => $errorMessage,
            ], 500);
        }
    }

    /**
     * Manejar error con redirección o excepción
     */
    private function handleError(string $message, ?string $redirectTo = null)
    {
        if ($redirectTo) {
            return redirect()->route($redirectTo)->with('error', $message);
        }

        return back()->with('error', $message);
    }

    /**
     * Parsear excepciones de base de datos a mensajes amigables
     */
    private function parseQueryException(QueryException $e): string
    {
        $errorCode = $e->errorInfo[1] ?? null;

        return match ($errorCode) {
            1062 => 'Ya existe un registro con los mismos datos únicos.',
            1451, 1452 => 'No se puede realizar la operación porque existen registros relacionados.',
            1048 => 'Faltan campos requeridos.',
            1054 => 'Error en la estructura de datos.',
            1146 => 'Error interno: tabla no encontrada.',
            default => 'Error al procesar la operación en la base de datos.',
        };
    }

    /**
     * Ejecutar transacción con manejo de excepciones
     */
    protected function handleTransaction(callable $action, string $errorMessage = 'Error al procesar la transacción')
    {
        try {
            return DB::transaction($action);
        } catch (Exception $e) {
            Log::error($errorMessage . ': ' . $e->getMessage());
            throw $e;
        }
    }
}
