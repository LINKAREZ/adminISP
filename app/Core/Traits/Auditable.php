<?php

namespace App\Core\Traits;

use App\Core\Models\AuditLog;

/**
 * Trait para modelos auditables
 *
 * Registra automáticamente cambios (create, update, delete) en la tabla de auditoría.
 */
trait Auditable
{
    /**
     * Boot del trait - registra eventos del modelo
     */
    protected static function bootAuditable(): void
    {
        static::created(function ($model) {
            if (app()->runningInConsole() && !app()->runningUnitTests()) {
                return;
            }
            static::logAudit('created', $model, null, $model->getAttributes());
        });

        static::updated(function ($model) {
            if (app()->runningInConsole() && !app()->runningUnitTests()) {
                return;
            }
            // Solo registrar si hubo cambios reales
            $changes = $model->getChanges();
            unset($changes['updated_at']); // Ignorar updated_at
            if (!empty($changes)) {
                static::logAudit('updated', $model, $model->getOriginal(), $changes);
            }
        });

        static::deleted(function ($model) {
            if (app()->runningInConsole() && !app()->runningUnitTests()) {
                return;
            }
            static::logAudit('deleted', $model, $model->getAttributes(), null);
        });
    }

    /**
     * Registrar acción en auditoría
     */
    protected static function logAudit(string $action, $model, ?array $oldValues = null, ?array $newValues = null): void
    {
        try {
            // Obtener etiqueta del modelo (nombre identificador)
            $modelLabel = static::getModelLabel($model);

            // Obtener módulo del modelo
            $module = static::getModelModule($model);

            // Generar descripción legible
            $description = static::generateDescription($action, $model, $newValues);

            // Filtrar valores sensibles
            $filteredOldValues = static::filterSensitiveData($oldValues);
            $filteredNewValues = static::filterSensitiveData($newValues);

            // Metadata enriquecida
            $metadata = static::buildMetadata($model, $action);

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => $action,
                'description' => $description,
                'model_type' => get_class($model),
                'model_id' => $model->id ?? null,
                'model_label' => $modelLabel,
                'module' => $module,
                'old_values' => $filteredOldValues,
                'new_values' => $filteredNewValues,
                'ip_address' => request()->ip() ?? null,
                'user_agent' => request()->userAgent() ?? null,
                'metadata' => $metadata,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al registrar auditoría: ' . $e->getMessage(), [
                'model' => get_class($model),
                'action' => $action,
                'error' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Obtener etiqueta identificadora del modelo
     */
    protected static function getModelLabel($model): ?string
    {
        // Intentar obtener un campo identificador común
        $identifierFields = ['nombre', 'name', 'titulo', 'title', 'codigo', 'numero', 'email', 'documento'];

        foreach ($identifierFields as $field) {
            if (isset($model->$field) && !empty($model->$field)) {
                return (string) $model->$field;
            }
        }

        // Si el modelo tiene un método getAuditLabel, usarlo
        if (method_exists($model, 'getAuditLabel')) {
            return $model->getAuditLabel();
        }

        return null;
    }

    /**
     * Obtener el módulo al que pertenece el modelo
     */
    protected static function getModelModule($model): ?string
    {
        $class = get_class($model);

        // Mapeo de namespaces a módulos
        $moduleMap = [
            'Clientes' => 'clientes',
            'Servicios' => 'servicios',
            'Comprobantes' => 'comprobantes',
            'ControlAcceso' => 'control_acceso',
            'Red' => 'red',
            'Sistema' => 'sistema',
            'Notificaciones' => 'notificaciones',
        ];

        foreach ($moduleMap as $namespace => $module) {
            if (str_contains($class, "\\{$namespace}\\")) {
                return $module;
            }
        }

        return 'sistema';
    }

    /**
     * Generar descripción legible de la acción
     */
    protected static function generateDescription(string $action, $model, ?array $newValues): string
    {
        $modelName = class_basename($model);
        $modelLabel = static::getModelLabel($model);

        // Nombres legibles de modelos
        $modelNames = [
            'Cliente' => 'cliente',
            'Servicio' => 'servicio',
            'Plan' => 'plan',
            'Recibo' => 'recibo',
            'Pago' => 'pago',
            'User' => 'usuario',
            'Role' => 'rol',
            'Permission' => 'permiso',
            'Router' => 'router',
            'Nodo' => 'nodo',
            'Comprobante' => 'comprobante',
            'MedioPago' => 'medio de pago',
            'Ubicacion' => 'ubicación',
            'Onu' => 'ONU',
            'OnuModelo' => 'modelo de ONU',
            'PromesaPago' => 'promesa de pago',
            'PlantillaWhatsApp' => 'plantilla de WhatsApp',
            'ApiConfig' => 'configuración de API',
        ];

        $readableName = $modelNames[$modelName] ?? strtolower($modelName);
        $label = $modelLabel ? " \"{$modelLabel}\"" : '';

        switch ($action) {
            case 'created':
                return "Creó {$readableName}{$label}";
            case 'updated':
                $changedFields = $newValues ? array_keys($newValues) : [];
                unset($changedFields[array_search('updated_at', $changedFields)]);
                $fieldsText = count($changedFields) > 0
                    ? ' (' . implode(', ', array_slice($changedFields, 0, 3)) . (count($changedFields) > 3 ? '...' : '') . ')'
                    : '';
                return "Actualizó {$readableName}{$label}{$fieldsText}";
            case 'deleted':
                return "Eliminó {$readableName}{$label}";
            default:
                return ucfirst($action) . " {$readableName}{$label}";
        }
    }

    /**
     * Filtrar datos sensibles
     */
    protected static function filterSensitiveData(?array $data): ?array
    {
        if (!$data) {
            return null;
        }

        $sensitiveFields = [
            'password',
            'password_confirmation',
            'secret',
            'token',
            'api_key',
            'api_secret',
            'private_key',
            'remember_token',
            'two_factor_secret',
            'two_factor_recovery_codes',
        ];

        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '[OCULTO]';
            }
        }

        return $data;
    }

    /**
     * Construir metadata enriquecida
     */
    protected static function buildMetadata($model, string $action): array
    {
        $request = request();

        $metadata = [
            'url' => $request->fullUrl() ?? null,
            'method' => $request->method() ?? null,
            'route' => $request->route() ? $request->route()->getName() : null,
            'referer' => $request->header('referer'),
            'session_id' => session()->getId(),
        ];

        // Agregar información adicional según el tipo de modelo
        if (method_exists($model, 'getAuditMetadata')) {
            $metadata = array_merge($metadata, $model->getAuditMetadata());
        }

        // Agregar relaciones importantes
        $relatedData = static::getRelatedData($model);
        if (!empty($relatedData)) {
            $metadata['related'] = $relatedData;
        }

        return $metadata;
    }

    /**
     * Obtener datos relacionados importantes
     */
    protected static function getRelatedData($model): array
    {
        $related = [];

        // Intentar obtener cliente relacionado
        if (method_exists($model, 'cliente') && $model->cliente) {
            $related['cliente'] = [
                'id' => $model->cliente->id,
                'nombre' => $model->cliente->nombre ?? null,
            ];
        }

        // Intentar obtener servicio relacionado
        if (method_exists($model, 'servicio') && $model->servicio) {
            $related['servicio'] = [
                'id' => $model->servicio->id,
                'usuario_pppoe' => $model->servicio->usuario_pppoe ?? null,
            ];
        }

        // Intentar obtener recibo relacionado
        if (method_exists($model, 'recibo') && $model->recibo) {
            $related['recibo'] = [
                'id' => $model->recibo->id,
                'codigo' => $model->recibo->codigo ?? null,
            ];
        }

        return $related;
    }
}
