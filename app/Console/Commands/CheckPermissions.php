<?php

namespace App\Console\Commands;

use App\Modules\ControlAcceso\Models\Permission;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckPermissions extends Command
{
    protected $signature = 'permissions:check {resource?}';
    protected $description = 'Verificar permisos de un recurso o todos';

    public function handle(): int
    {
        $resource = $this->argument('resource');

        if ($resource) {
            $permissions = Permission::where('name', 'like', "{$resource}.%")->get();
            $this->info("Permisos de '{$resource}':");
        } else {
            $permissions = Permission::all();
            $this->info("Todos los permisos:");
        }

        $this->newLine();

        $standardActions = ['create', 'read', 'update', 'delete'];
        $nonStandard = [];

        foreach ($permissions as $perm) {
            $parts = explode('.', $perm->name);
            if (count($parts) === 2) {
                $action = $parts[1];
                if (!in_array($action, $standardActions)) {
                    $nonStandard[] = $perm->name;
                    $this->warn("  ⚠ NO ESTÁNDAR: {$perm->name}");
                } else {
                    $this->line("  ✓ {$perm->name}");
                }
            } else {
                $this->warn("  ⚠ Formato incorrecto: {$perm->name}");
            }
        }

        $this->newLine();
        if (empty($nonStandard)) {
            $this->info("✓ Todos los permisos están estandarizados");
        } else {
            $this->error("✗ Se encontraron " . count($nonStandard) . " permisos no estándar");
        }

        return Command::SUCCESS;
    }
}
