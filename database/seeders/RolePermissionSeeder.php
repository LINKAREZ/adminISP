<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Modules\ControlAcceso\Models\Role;
use App\Modules\ControlAcceso\Models\Permission;
use App\Modules\ControlAcceso\Models\User;

/**
 * 5 roles jerárquicos para ISP. Permisos por MÓDULO (CRUD).
 *
 * Módulos: dashboard, control-acceso, red, servicios, clientes, comprobantes, sistema, auditoria.
 * Solo ~26 permisos en total.
 */
class RolePermissionSeeder extends Seeder
{
    private const ROLES = [
        'administrador' => 'Máxima autoridad del ISP. Acceso total.',
        'supervisor' => 'Supervisión y reportes. Sin eliminar datos críticos.',
        'gerente-finanzas' => 'Gerente Finanzas. Dashboard, comprobantes, gastos, reportes y auditoría.',
        'cobrador' => 'Cobranza y caja. Clientes (lectura), comprobantes.',
        'tecnico' => 'Operaciones y campo. Red, servicios, clientes, comprobantes. Sin eliminaciones.',
        'soporte' => 'Soporte técnico. Tickets, clientes (consulta), comprobantes (consulta).',
        'ayudante' => 'Apoyo operativo. Consultas y registro básico.',
    ];

    /** Módulos con CRUD. dashboard y auditoria solo read. */
    private const MODULES = [
        'dashboard' => ['read'],
        'control-acceso' => ['create', 'read', 'update', 'delete'],
        'red' => ['create', 'read', 'update', 'delete'],
        'servicios' => ['create', 'read', 'update', 'delete'],
        'clientes' => ['create', 'read', 'update', 'delete'],
        'comprobantes' => ['create', 'read', 'update', 'delete'],
        'instalaciones' => ['create', 'read', 'update', 'delete'],
        'almacen' => ['create', 'read', 'update', 'delete'],
        'infraestructura' => ['create', 'read', 'update', 'delete'],
        'mapa-red' => ['read', 'edit', 'admin'],
        'sistema' => ['create', 'read', 'update', 'delete'],
        'auditoria' => ['read'],
        'tickets' => ['read', 'create'],
    ];

    private const MODULE_LABELS = [
        'dashboard' => 'Dashboard',
        'control-acceso' => 'Control de acceso',
        'red' => 'Red',
        'servicios' => 'Servicios',
        'clientes' => 'Clientes',
        'comprobantes' => 'Comprobantes',
        'instalaciones' => 'Instalaciones',
        'almacen' => 'Almacén',
        'infraestructura' => 'Infraestructura',
        'mapa-red' => 'Mapa de Red',
        'sistema' => 'Sistema',
        'auditoria' => 'Auditoría',
        'tickets' => 'Tickets',
    ];
    private const ACTION_LABELS = [
        'create' => 'Crear',
        'read' => 'Ver',
        'update' => 'Editar',
        'delete' => 'Eliminar',
        'edit' => 'Editar',
        'admin' => 'Administrar',
    ];

    public function run(): void
    {
        $this->createPermissions();
        $this->createRolesAndSyncPermissions();
        $this->assignRootToAdministrador();
        $this->pruneObsoletePermissions();
        $this->pruneObsoleteRoles();
        $this->clearCaches();
    }

    private function createPermissions(): void
    {
        $permissions = [];
        foreach (self::MODULES as $module => $actions) {
            foreach ($actions as $action) {
                $name = "{$module}.{$action}";
                $label = self::MODULE_LABELS[$module];
                $permissions[] = [
                    'name' => $name,
                    'display_name' => self::ACTION_LABELS[$action] . ' ' . $label,
                    'module' => $label,
                    'description' => '',
                ];
            }
        }

        foreach ($permissions as $p) {
            Permission::updateOrCreate(
                ['name' => $p['name']],
                array_merge($p, ['is_hidden' => true])
            );
        }

        $this->permissionNames = array_column($permissions, 'name');
    }

    /** @var array */
    private $permissionNames = [];

    private function createRolesAndSyncPermissions(): void
    {
        $allIds = Permission::whereIn('name', $this->permissionNames)->pluck('id')->toArray();

        foreach (self::ROLES as $name => $description) {
            $role = Role::firstOrCreate(
                ['name' => $name],
                ['description' => $description, 'is_active' => true]
            );
            $role->update(['description' => $description, 'is_active' => true]);

            $permIds = $name === 'administrador'
                ? $allIds
                : Permission::whereIn('name', $this->permissionsForRole($name))->pluck('id')->toArray();
            $role->permissions()->sync($permIds);
        }
    }

    private function permissionsForRole(string $roleName): array
    {
        return match ($roleName) {
            'supervisor' => [
                'dashboard.read',
                'control-acceso.read', 'control-acceso.create', 'control-acceso.update',
                'red.read', 'red.create', 'red.update',
                'servicios.read', 'servicios.create', 'servicios.update',
                'clientes.read', 'clientes.create', 'clientes.update',
                'comprobantes.read', 'comprobantes.create', 'comprobantes.update', 'comprobantes.delete',
                'instalaciones.read', 'instalaciones.create', 'instalaciones.update',
                'almacen.read', 'almacen.create', 'almacen.update', 'almacen.delete',
                'infraestructura.read', 'infraestructura.create', 'infraestructura.update',
                'mapa-red.read', 'mapa-red.edit',
                'sistema.read',
                'auditoria.read',
                'tickets.read', 'tickets.create',
            ],
            'gerente-finanzas' => [
                'dashboard.read',
                'clientes.read',
                'comprobantes.read', 'comprobantes.create', 'comprobantes.update', 'comprobantes.delete',
                'instalaciones.read',
                'auditoria.read',
            ],
            'cobrador' => [
                'dashboard.read',
                'clientes.read',
                'comprobantes.read', 'comprobantes.create', 'comprobantes.update',
            ],
            'tecnico' => [
                'dashboard.read',
                'red.read',
                'servicios.read', 'servicios.create', 'servicios.update',
                'clientes.read', 'clientes.create', 'clientes.update',
                'comprobantes.read', 'comprobantes.create', 'comprobantes.update',
                'instalaciones.read', 'instalaciones.create', 'instalaciones.update',
                'infraestructura.read', 'infraestructura.create', 'infraestructura.update',
                'mapa-red.read', 'mapa-red.edit',
                'tickets.read', 'tickets.create',
            ],
            'soporte' => [
                'dashboard.read',
                'clientes.read',
                'comprobantes.read',
                'tickets.read', 'tickets.create',
            ],
            'ayudante' => [
                'dashboard.read',
                'clientes.read',
                'comprobantes.read', 'comprobantes.create',
                'instalaciones.read',
                'infraestructura.read',
            ],
            default => [],
        };
    }

    private function assignRootToAdministrador(): void
    {
        $rootEmail = config('security.root_email');
        if (empty($rootEmail)) {
            return;
        }

        $root = User::where('email', $rootEmail)->first();
        $administrador = Role::where('name', 'administrador')->first();
        if ($root && $administrador) {
            $root->role_id = $administrador->id;
            $root->save();
        }
    }

    private function pruneObsoletePermissions(): void
    {
        $obsolete = Permission::whereNotIn('name', $this->permissionNames)->pluck('id')->toArray();
        if ($obsolete === []) {
            return;
        }
        DB::table('permission_role')->whereIn('permission_id', $obsolete)->delete();
        Permission::whereIn('id', $obsolete)->delete();
    }

    private function pruneObsoleteRoles(): void
    {
        $validNames = array_keys(self::ROLES);
        $administrador = Role::where('name', 'administrador')->first();
        if (!$administrador) {
            return;
        }

        $obsolete = Role::whereNotIn('name', $validNames)->get();
        foreach ($obsolete as $role) {
            User::where('role_id', $role->id)->update(['role_id' => $administrador->id]);
            $role->permissions()->detach();
            $role->delete();
        }
    }

    private function clearCaches(): void
    {
        Cache::forget('permissions.grouped.by.module');
        Cache::forget('permissions.modules');
        Cache::forget('roles.active');
    }
}
