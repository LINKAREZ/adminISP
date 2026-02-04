<?php

namespace App\Modules\ControlAcceso\Models;

use App\Core\Traits\Auditable;
use App\Core\Traits\BelongsToIsp;
use App\Core\Traits\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory, Auditable, Searchable, BelongsToIsp;

    protected $connection = 'mysql';

    protected $fillable = [
        'name',
        'display_name',
        'module',
        'description',
        'is_hidden',
        'isp_id',
    ];

    protected $casts = [
        'is_hidden' => 'boolean',
    ];

    /**
     * Relación muchos a muchos con roles
     */
    public function roles(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * Scope para filtrar por módulo
     */
    public function scopeByModule(\Illuminate\Database\Eloquent\Builder $query, string $module): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('module', $module);
    }

    /**
     * Scope para excluir permisos ocultos
     */
    public function scopeVisible(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_hidden', false);
    }

    /**
     * Scope para incluir solo permisos ocultos
     */
    public function scopeHidden(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_hidden', true);
    }
}
