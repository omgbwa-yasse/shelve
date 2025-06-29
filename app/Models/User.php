<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use App\Models\Organisation;
use App\Models\Role;
use App\Models\Permission;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'surname',
        'birthday',
        'current_organisation_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function organisation()
    {
        return $this->belongsTo(Organisation::class, 'current_organisation_id');
    }

    /**
     * Alias for organisation relationship (for backward compatibility)
     */
    public function currentOrganisation()
    {
        return $this->organisation();
    }

    public function recordsCreated()
    {
        return $this->hasMany(Record::class, 'user_id');
    }

    public function communications()
    {
        return $this->hasMany(Communication::class, 'user_id');
    }

    public function operatorCommunications()
    {
        return $this->hasMany(Communication::class, 'operator_id');
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'user_id');
    }

    public function operatorReservations()
    {
        return $this->hasMany(Reservation::class, 'operator_id');
    }

    public function isSuperAdmin(): bool
    {
        return Cache::remember("user_{$this->id}_is_superadmin", 3600, function () {
            return $this->hasRole('superadmin');
        });
    }

    /**
     * Relations avec les rôles (système natif)
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    /**
     * Relations avec les permissions (système natif)
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'user_permissions');
    }

    /**
     * Vérifier si l'utilisateur a un rôle spécifique
     */
    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    /**
     * Vérifier si l'utilisateur a une permission spécifique
     */
    public function hasPermissionTo(string $permissionName): bool
    {
        // Vérifier les permissions directes
        if ($this->permissions()->where('name', $permissionName)->exists()) {
            return true;
        }

        // Vérifier les permissions via les rôles
        return $this->roles()->whereHas('permissions', function ($query) use ($permissionName) {
            $query->where('name', $permissionName);
        })->exists();
    }

    /**
     * Assigner un rôle à l'utilisateur
     */
    public function assignRole(string $roleName): void
    {
        $role = Role::where('name', $roleName)->first();
        if ($role && !$this->hasRole($roleName)) {
            $this->roles()->attach($role->id);
        }
    }

    /**
     * Retirer un rôle de l'utilisateur
     */
    public function removeRole(string $roleName): void
    {
        $role = Role::where('name', $roleName)->first();
        if ($role) {
            $this->roles()->detach($role->id);
        }
    }

    /**
     * Donner une permission directe à l'utilisateur
     */
    public function givePermissionTo(string $permissionName): void
    {
        $permission = Permission::where('name', $permissionName)->first();
        if ($permission && !$this->hasPermissionTo($permissionName)) {
            $this->permissions()->attach($permission->id);
        }
    }

    /**
     * Synchroniser les rôles de l'utilisateur
     */
    public function syncRoles(array $roleNames): void
    {
        $roleIds = Role::whereIn('name', $roleNames)->pluck('id');
        $this->roles()->sync($roleIds);
    }

    public function hasPermission(string $permissionName): bool
    {
        // Si c'est un superadmin, autoriser tout
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Utiliser le système natif pour vérifier la permission
        return $this->hasPermissionTo($permissionName);
    }
}
