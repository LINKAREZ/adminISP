<?php

namespace App\Console\Commands;

use App\Modules\Sistema\Models\Isp;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class BackupDatabases extends Command
{
    protected $signature = 'backup:databases
                            {--path= : Directorio donde guardar los backups}
                            {--tenant= : Solo este ISP ID}';

    protected $description = 'Genera backup de la BD central y opcionalmente de las BDs tenant (requiere mysqldump en el sistema).';

    public function handle(): int
    {
        $path = $this->option('path') ?: storage_path('backups');
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }

        $connection = Config::get('database.default') ?: 'mysql';
        $dbName = Config::get("database.connections.{$connection}.database");
        $host = Config::get("database.connections.{$connection}.host");
        $user = Config::get("database.connections.{$connection}.username");
        $password = Config::get("database.connections.{$connection}.password");

        $date = now()->format('Y-m-d_His');
        $centralFile = $path . "/central_{$date}.sql";
        $cmd = sprintf(
            'mysqldump -h %s -u %s %s %s > %s 2>/dev/null',
            escapeshellarg($host),
            escapeshellarg($user),
            $password ? '-p' . escapeshellarg($password) : '',
            escapeshellarg($dbName),
            escapeshellarg($centralFile)
        );
        exec($cmd);
        if (file_exists($centralFile)) {
            $this->info("Central backup: {$centralFile}");
        } else {
            $this->warn('No se pudo crear backup central (¿mysqldump disponible?).');
        }

        $tenantId = $this->option('tenant');
        $isps = Isp::withoutGlobalScope(\App\Core\Scopes\IspScope::class)
            ->whereNotNull('database_name')
            ->when($tenantId, fn ($q) => $q->where('id', $tenantId))
            ->get();

        foreach ($isps as $isp) {
            $tenantFile = $path . "/tenant_{$isp->id}_{$date}.sql";
            $cmd = sprintf(
                'mysqldump -h %s -u %s %s %s > %s 2>/dev/null',
                escapeshellarg($host),
                escapeshellarg($user),
                $password ? '-p' . escapeshellarg($password) : '',
                escapeshellarg($isp->database_name),
                escapeshellarg($tenantFile)
            );
            exec($cmd);
            if (file_exists($tenantFile)) {
                $this->info("Tenant {$isp->id} ({$isp->nombre}): {$tenantFile}");
            }
        }

        return self::SUCCESS;
    }
}
