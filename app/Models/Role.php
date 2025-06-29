<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;
    protected $table = 'roles';

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'guard_name',
    ];

    /**
     * Relations avec les utilisateurs (système natif)
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_roles');
    }

    /**
     * Relations avec les organisations (système existant)
     */
    public function organisations()
    {
        return $this->belongsToMany(Organisation::class, 'user_organisation_role');
    }

    /**
     * Relations avec les permissions (système natif)
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permissions', 'role_id', 'permission_id');
    }

    /**
     * Vérifier si le rôle a une permission spécifique
     */
    public function hasPermissionTo(string $permissionName): bool
    {
        return $this->permissions()->where('name', $permissionName)->exists();
    }

    /**
     * Donner une permission au rôle
     */
    public function givePermissionTo(string $permissionName): void
    {
        $permission = Permission::where('name', $permissionName)->first();
        if ($permission && !$this->hasPermissionTo($permissionName)) {
            $this->permissions()->attach($permission->id);
        }
    }

    /**
     * Retirer une permission du rôle
     */
    public function revokePermissionTo(string $permissionName): void
    {
        $permission = Permission::where('name', $permissionName)->first();
        if ($permission) {
            $this->permissions()->detach($permission->id);
        }
    }

    /**
     * Synchroniser les permissions du rôle
     */
    public function syncPermissions(array $permissionNames): void
    {
        $permissionIds = Permission::whereIn('name', $permissionNames)->pluck('id');
        $this->permissions()->sync($permissionIds);
    }
}
