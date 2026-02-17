<?php

namespace App\Modules\Clientes\Models;

use App\Core\Traits\BelongsToIsp;
use App\Core\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ClienteCredencial extends Model
{
    use BelongsToIsp, UsesTenantConnection;

    protected $table = 'cliente_credenciales';

    protected $fillable = ['cliente_id', 'email', 'documento', 'password', 'token', 'token_expira_at'];

    protected $hidden = ['password', 'token'];

    protected $casts = [
        'token_expira_at' => 'datetime',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public static function generarToken(): string
    {
        return Str::random(64);
    }

    public function crearTokenAcceso(int $minutos = 60): string
    {
        $token = self::generarToken();
        $this->update([
            'token' => $token,
            'token_expira_at' => now()->addMinutes($minutos),
        ]);
        return $token;
    }

    public function tokenValido(): bool
    {
        return $this->token && $this->token_expira_at && $this->token_expira_at->isFuture();
    }
}
