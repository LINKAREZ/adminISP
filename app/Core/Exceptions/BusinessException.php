<?php

namespace App\Core\Exceptions;

use Exception;

/**
 * Excepción para errores de lógica de negocio
 */
class BusinessException extends Exception
{
    protected array $context = [];

    public function __construct(string $message = '', int $code = 422, ?Exception $previous = null, array $context = [])
    {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    /**
     * Crear excepción con contexto
     */
    public static function withContext(string $message, array $context = []): self
    {
        return new self($message, 422, null, $context);
    }

    /**
     * Obtener contexto
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Renderizar la excepción para respuesta HTTP
     */
    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $this->getMessage(),
                'context' => $this->context,
            ], $this->getCode());
        }

        return back()->with('error', $this->getMessage())->withInput();
    }
}
