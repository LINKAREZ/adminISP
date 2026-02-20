<?php

namespace App\Console\Commands;

use App\Core\Services\TenantConnectionService;
use App\Core\Services\TenantDatabaseService;
use App\Modules\Sistema\Models\Isp;
use App\Modules\Sistema\Models\Licencia;
use Illuminate\Console\Command;

class CreateWanIspCommand extends Command
{
    protected $signature = 'isp:create-wan
                            {--nombre=WAN : Nombre del ISP}
                            {--licencia=plan-100 : Slug de la licencia (ej. plan-100 = 100 clientes)}
                            {--force : Crear aunque ya exista un ISP con ese nombre}';

    protected $description = 'Crea el ISP WAN con plan 100 clientes (para pruebas o instalación inicial).';

    public function handle(): int
    {
        $nombre = $this->option('nombre');
        $licenciaSlug = $this->option('licencia');
        $force = $this->option('force');

        $query = Isp::withoutGlobalScope(\App\Core\Scopes\IspScope::class)->where('nombre', $nombre);
        if (!$force && $query->exists()) {
            $this->warn("Ya existe un ISP con nombre \"{$nombre}\". Usa --force para no comprobar.");

            return self::FAILURE;
        }

        $licencia = Licencia::on(TenantConnectionService::centralConnection())->where('slug', $licenciaSlug)->first();
        if (!$licencia && \Illuminate\Support\Facades\Schema::connection(TenantConnectionService::centralConnection())->hasTable('licencias')) {
            $this->warn("Licencia con slug \"{$licenciaSlug}\" no encontrada. Ejecutando LicenciasSeeder (o PlansSeeder si existe)...");
            if (class_exists(\Database\Seeders\LicenciasSeeder::class)) {
                $this->call('db:seed', ['--class' => 'Database\\Seeders\\LicenciasSeeder']);
            } elseif (class_exists(\Database\Seeders\PlansSeeder::class)) {
                $this->call('db:seed', ['--class' => 'Database\\Seeders\\PlansSeeder']);
            }
            $licencia = Licencia::on(TenantConnectionService::centralConnection())->where('slug', $licenciaSlug)->first();
        }

        $isp = Isp::withoutGlobalScope(\App\Core\Scopes\IspScope::class)->create([
            'nombre' => $nombre,
            'activo' => true,
            'moneda' => 'PEN',
            'simbolo_moneda' => 'S/.',
            'igv' => 18,
            'status' => 'active',
            'licencia_id' => $licencia?->id,
            'database_name' => 'temp_' . uniqid(),
        ]);

        $isp->update(['database_name' => TenantDatabaseService::generateDatabaseName($isp)]);
        $isp->refresh();

        $this->info('Creando base de datos tenant...');
        TenantDatabaseService::createDatabaseForIsp($isp);

        $this->info("ISP \"{$nombre}\" creado (ID: {$isp->id}, licencia: " . ($licencia ? $licencia->name : 'Sin licencia') . ').');

        return self::SUCCESS;
    }
}
