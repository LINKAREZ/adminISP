<?php

namespace App\Console\Commands;

use App\Core\Services\TenantConnectionService;
use App\Modules\Clientes\Models\Cliente;
use App\Modules\Clientes\Models\ClienteCredencial;
use App\Modules\Sistema\Models\Isp;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CrearCredencialPortalCliente extends Command
{
    protected $signature = 'portal:crear-credencial
                            {cliente_id : ID del cliente}
                            {password : Contraseña para el portal}
                            {--isp= : ID del ISP (opcional)}';
    protected $description = 'Crea o actualiza credencial de portal para un cliente (documento + contraseña)';

    public function handle(): int
    {
        $clienteId = (int) $this->argument('cliente_id');
        $password = $this->argument('password');
        $ispId = $this->option('isp') ? (int) $this->option('isp') : null;

        if ($ispId) {
            $isp = Isp::on(TenantConnectionService::CENTRAL_CONNECTION)->find($ispId);
            if (!$isp || !$isp->database_name) {
                $this->error('ISP no encontrado o sin BD tenant.');
                return self::FAILURE;
            }
            TenantConnectionService::setCurrentIspId($isp->id);
        }

        $cliente = Cliente::find($clienteId);
        if (!$cliente) {
            $this->error('Cliente no encontrado.');
            return self::FAILURE;
        }

        $documento = $cliente->documento ?? (string) $clienteId;
        $credencial = ClienteCredencial::updateOrCreate(
            ['cliente_id' => $cliente->id],
            [
                'documento' => $documento,
                'password' => Hash::make($password),
            ]
        );
        $this->info("Credencial creada/actualizada para cliente #{$cliente->id} ({$cliente->nombre}). Documento de acceso: {$documento}");
        return self::SUCCESS;
    }
}
