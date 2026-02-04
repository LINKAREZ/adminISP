<?php

namespace App\Console\Commands;

use App\Modules\ControlAcceso\Models\Permission;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DeleteNonStandardPermissions extends Command
{
    protected $signature = 'permissions:delete-non-standard';
    protected $description = 'Eliminar permisos no estándar (index, edit, store, destroy, etc.)';

    public function handle(): int
    {
        $this->info('Eliminando permisos no estándar...');
        $this->newLine();

        $standardActions = ['create', 'read', 'update', 'delete'];
        $actionMapping = [
            'index' => 'read',
            'show' => 'read',
            'store' => 'create',
            'edit' => 'update',
            'destroy' => 'delete',
        ];

        // Obtener todos los permisos
        $allPermissions = Permission::all();

        $deleted = 0;
        $migrated = 0;

        foreach ($allPermissions as $permission) {
            $parts = explode('.', $permission->name);
            if (count($parts) !== 2) {
                continue;
            }

            $resource = $parts[0];
            $action = $parts[1];

            // Si la acción es estándar, mantener
            if (in_array($action, $standardActions)) {
                continue;
            }

            // Si se puede mapear, migrar relaciones y eliminar
            if (isset($actionMapping[$action])) {
                $standardAction = $actionMapping[$action];
                $standardPermissionName = "{$resource}.{$standardAction}";
                $standardPermission = Permission::where('name', $standardPermissionName)->first();

                if ($standardPermission) {
                    // Migrar relaciones
                    $roleIds = DB::table('permission_role')
                        ->where('permission_id', $permission->id)
                        ->pluck('role_id')
                        ->toArray();

                    foreach ($roleIds as $roleId) {
                        $exists = DB::table('permission_role')
                            ->where('permission_id', $standardPermission->id)
                            ->where('role_id', $roleId)
                            ->exists();

                        if (!$exists) {
                            DB::table('permission_role')->insert([
                                'permission_id' => $standardPermission->id,
                                'role_id' => $roleId,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                            $migrated++;
                        }
                    }

                    // Eliminar relaciones del permiso no estándar
                    DB::table('permission_role')
                        ->where('permission_id', $permission->id)
                        ->delete();

                    $this->line("  ✗ Eliminado: {$permission->name} (relaciones migradas a {$standardPermissionName})");
                } else {
                    $this->warn("  ⚠ No se encontró permiso estándar: {$standardPermissionName} para migrar {$permission->name}");
                }
            } else {
                // No se puede mapear, eliminar directamente
                DB::table('permission_role')
                    ->where('permission_id', $permission->id)
                    ->delete();

                $this->line("  ✗ Eliminado: {$permission->name} (no se puede mapear)");
            }

            $permission->delete();
            $deleted++;
        }

        $this->newLine();
        $this->info("✓ Eliminados: {$deleted} permisos no estándar");
        $this->info("✓ Relaciones migradas: {$migrated}");

        return Command::SUCCESS;
    }
}
