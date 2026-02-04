<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class InstallReset extends Command
{
    protected $signature = 'install:reset {--force : No pedir confirmación}';
    protected $description = 'Desinstala la aplicación para poder usar el instalador web de nuevo (/install)';

    public function handle(): int
    {
        if (!$this->option('force')) {
            if (!$this->confirm('¿Desinstalar la aplicación? Esto borrará el archivo de instalación y reseteará la base de datos.')) {
                $this->info('Operación cancelada.');
                return self::SUCCESS;
            }
        }

        $flagPath = storage_path('installed');

        // 1. Eliminar archivo de instalación
        if (File::exists($flagPath)) {
            File::delete($flagPath);
            $this->info('✓ Archivo storage/installed eliminado.');
        } else {
            $this->line('  No existía storage/installed.');
        }

        // 2. Resetear base de datos (si está configurada)
        $dbReset = false;
        try {
            Artisan::call('migrate:fresh', ['--force' => true]);
            $this->info('✓ Base de datos reseteada (migrate:fresh).');
            $dbReset = true;
        } catch (\Throwable $e) {
            $this->warn('  migrate:fresh falló: ' . $e->getMessage());
            // Fallback: vaciar users para que isInstalled() devuelva false
            try {
                if (Schema::hasTable('users')) {
                    DB::statement('SET FOREIGN_KEY_CHECKS=0');
                    DB::table('users')->truncate();
                    DB::statement('SET FOREIGN_KEY_CHECKS=1');
                    $this->info('✓ Tabla users truncada. El instalador podrá ejecutarse.');
                    $dbReset = true;
                }
            } catch (\Throwable $e2) {
                $this->warn('  No se pudo truncar users: ' . $e2->getMessage());
            }
        }

        if (!$dbReset) {
            $this->line('  Si la app sigue "instalada", ejecuta manualmente: php artisan migrate:fresh --force');
        }

        // 3. Limpiar caché
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        $this->info('✓ Caché limpiada.');

        $this->newLine();
        $this->info('Listo. Visita /install para usar el instalador.');
        return self::SUCCESS;
    }
}
