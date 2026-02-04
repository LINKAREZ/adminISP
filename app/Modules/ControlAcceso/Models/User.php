<?php

namespace App\Modules\ControlAcceso\Models;

use App\Core\Traits\Auditable;
use App\Core\Traits\BelongsToIsp;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, Auditable, BelongsToIsp;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'isp_id',
        'is_default_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Relación uno a muchos con roles (un usuario tiene un solo rol)
     */
    public function role(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Verificar si es super admin (usuario sin isp_id o email específico)
     */
    public function isSuperAdmin(): bool
    {
        return $this->isp_id === null || $this->isRootUser();
    }

    /**
     * Verificar si es usuario root configurado
     */
    public function isRootUser(): bool
    {
        $email = trim(strtolower($this->email ?? ''));
        if ($email === '') {
            return false;
        }

        $rootEmail = trim(strtolower((string) config('security.root_email')));
        $extraRoots = array_filter(array_map(
            static fn($value) => trim(strtolower((string) $value)),
            (array) config('security.root_emails', [])
        ));

        $rootEmails = array_unique(array_filter(array_merge(
            $rootEmail !== '' ? [$rootEmail] : [],
            $extraRoots
        )));

        return in_array($email, $rootEmails, true);
    }

    /**
     * Verificar si el usuario tiene un rol específico
     */
    public function hasRole($role): bool
    {
        // Usuario root - tiene todos los roles siempre
        if ($this->isRootUser()) {
            return true;
        }

        // Cargar el rol si no está cargado
        if (!$this->relationLoaded('role')) {
            $this->load('role');
        }

        // Si no tiene rol, retornar false
        if (!$this->role) {
            return false;
        }

        // Comparar por nombre si es string
        if (is_string($role)) {
            return $this->role->name === $role;
        }

        // Comparar por instancia
        if ($role instanceof Role) {
            return $this->role->id === $role->id;
        }

        // Comparar por ID
        return $this->role->id === $role;
    }

    /**
     * Verificar si el usuario tiene alguno de los roles especificados
     */
    public function hasAnyRole(array $roles): bool
    {
        if (!$this->relationLoaded('role')) {
            $this->load('role');
        }

        if (!$this->role) {
            return false;
        }

        return in_array($this->role->name, $roles);
    }

    /**
     * Verificar si el usuario tiene un permiso específico
     */
    public function hasPermission(string $permission): bool
    {
        // Usuario root - tiene acceso completo siempre
        // Verificar con trim y lowercase para evitar problemas de formato
        if ($this->isRootUser()) {
            return true;
        }

        // Cargar el rol si no está cargado
        if (!$this->relationLoaded('role')) {
            $this->load('role');
        }

        // Administrador tiene acceso completo
        if ($this->role && $this->role->name === 'administrador') {
            return true;
        }

        // Si no tiene rol, no tiene permisos
        if (!$this->role) {
            return false;
        }

        // Cargar permisos si no están cargados
        if (!$this->role->relationLoaded('permissions')) {
            $this->role->load('permissions');
        }

        return $this->role->permissions->contains('name', $permission);
    }

    /**
     * Verificar si el usuario tiene alguno de los permisos especificados
     */
    public function hasAnyPermission(array $permissions): bool
    {
        // Usuario root - tiene acceso completo siempre
        if ($this->isRootUser()) {
            return true;
        }

        if (!$this->relationLoaded('role')) {
            $this->load(['role.permissions']);
        }

        if (!$this->role) {
            return false;
        }

        return $this->role->permissions->whereIn('name', $permissions)->isNotEmpty();
    }

    /**
     * Asignar un rol al usuario (reemplaza el rol anterior)
     */
    public function assignRole($role)
    {
        if (is_string($role)) {
            $role = Role::where('name', $role)->firstOrFail();
        }
        $this->role_id = $role->id;
        $this->save();
    }

    /**
     * Remover el rol del usuario
     */
    public function removeRole()
    {
        $this->role_id = null;
        $this->save();
    }

    /**
     * Obtener todos los permisos del usuario (heredados de su rol)
     */
    public function getAllPermissions()
    {
        if (!$this->relationLoaded('role')) {
            $this->load('role.permissions');
        }

        if (!$this->role) {
            return collect();
        }

        return $this->role->permissions;
    }
}
