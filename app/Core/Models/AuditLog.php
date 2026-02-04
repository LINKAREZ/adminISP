<?php

namespace App\Core\Models;

use App\Core\Traits\BelongsToIsp;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo de Auditoría
 *
 * Registra todas las acciones críticas del sistema para trazabilidad
 * y cumplimiento de estándares de seguridad.
 */
class AuditLog extends Model
{
    use HasFactory, BelongsToIsp;

    protected $fillable = [
        'user_id',
        'action',
        'description',
        'model_type',
        'model_id',
        'model_label',
        'module',
        'old_values',
        'new_values',
        'metadata',
        'ip_address',
        'isp_id',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Obtener el nombre corto del modelo
     */
    public function getModelNameAttribute(): string
    {
        return class_basename($this->model_type);
    }

    /**
     * Obtener la descripción de la acción en español
     */
    public function getActionLabelAttribute(): string
    {
        $labels = [
            'created' => 'Creó',
            'updated' => 'Actualizó',
            'deleted' => 'Eliminó',
            'login' => 'Inició sesión',
            'logout' => 'Cerró sesión',
            'restored' => 'Restauró',
            'viewed' => 'Visualizó',
            'exported' => 'Exportó',
            'imported' => 'Importó',
        ];

        return $labels[$this->action] ?? ucfirst($this->action);
    }

    /**
     * Obtener el nombre del módulo en español
     */
    public function getModuleLabelAttribute(): string
    {
        $modules = [
            'clientes' => 'Clientes',
            'servicios' => 'Servicios',
            'comprobantes' => 'Comprobantes',
            'control_acceso' => 'Control de Acceso',
            'red' => 'Red',
            'sistema' => 'Sistema',
            'notificaciones' => 'Notificaciones',
        ];

        return $modules[$this->module] ?? ucfirst($this->module ?? 'Sistema');
    }

    /**
     * Obtener los campos que cambiaron (solo claves)
     */
    public function getChangedFieldsAttribute(): array
    {
        if ($this->action === 'updated' && $this->new_values) {
            return array_keys($this->new_values);
        }
        return [];
    }

    /**
     * Obtener resumen de cambios
     */
    public function getChangesSummaryAttribute(): string
    {
        $fields = $this->changed_fields;
        if (empty($fields)) {
            return '';
        }

        $count = count($fields);
        if ($count <= 3) {
            return implode(', ', $fields);
        }

        $remaining = $count - 3;
        return implode(', ', array_slice($fields, 0, 3)) . " (+{$remaining} más)";
    }

    /**
     * Relación con el usuario que realizó la acción
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\ControlAcceso\Models\User::class);
    }

    /**
     * Obtener el modelo relacionado
     */
    public function model()
    {
        return $this->morphTo();
    }
}
