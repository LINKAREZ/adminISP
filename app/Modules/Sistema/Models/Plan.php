<?php

namespace App\Modules\Sistema\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $connection = 'mysql';

    protected $fillable = [
        'name',
        'slug',
        'max_clientes',
        'max_usuarios',
        'max_storage_mb',
        'price_monthly',
        'price_yearly',
        'currency',
        'interval',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price_monthly' => 'decimal:2',
        'price_yearly' => 'decimal:2',
    ];

    public function isps(): HasMany
    {
        return $this->hasMany(Isp::class);
    }
}
