<?php

namespace App\Modules\Sistema\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantRequest extends Model
{
    protected $connection = 'mysql';

    protected $table = 'tenant_requests';

    protected $fillable = [
        'nombre_isp',
        'email',
        'telefono',
        'mensaje',
        'status',
        'isp_id',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public function isp(): BelongsTo
    {
        return $this->belongsTo(Isp::class);
    }
}
