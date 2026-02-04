<?php

namespace App\Modules\ControlAcceso\Models;

use App\Core\Traits\Auditable;
use App\Core\Traits\BelongsToIsp;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory, Auditable, BelongsToIsp;

    protected $fillable = [
        'name',
        'description',
        'is_active',
        'isp_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relación uno a muchos con usuarios (un rol puede tener muchos usuarios)
     */
    public function users(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Relación muchos a muchos con permisos
     */
    public function permissions(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    /**
     * Scope para roles activos
     */
    public function scopeActive(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_active', true);
    }
}
