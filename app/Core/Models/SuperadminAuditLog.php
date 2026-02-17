<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuperadminAuditLog extends Model
{
    protected $connection = 'mysql';

    protected $table = 'superadmin_audit_logs';

    protected $fillable = [
        'user_id',
        'action',
        'description',
        'model_type',
        'model_id',
        'ip_address',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getModelNameAttribute(): string
    {
        return $this->model_type ? class_basename($this->model_type) : '';
    }

    public function getActionLabelAttribute(): string
    {
        $labels = [
            'created' => 'Creó',
            'updated' => 'Actualizó',
            'deleted' => 'Eliminó',
            'exported' => 'Exportó',
            'toggled' => 'Cambió estado',
            'create_admin' => 'Creó admin',
            'create_database' => 'Creó BD tenant',
        ];
        return $labels[$this->action] ?? ucfirst($this->action ?? '');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\ControlAcceso\Models\User::class);
    }
}
