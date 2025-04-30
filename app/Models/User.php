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



    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'birthday' => 'date',
    ];



    public function currentOrganisationId()
    {
        return $this->current_organisation_id;
    }



    public function currentOrganisation()
    {
        return $this->belongsTo(Organisation::class, 'current_organisation_id');
    }



    public function organisations()
    {
        return $this->belongsToMany(Organisation::class, 'user_organisation_role', 'user_id', 'organisation_id')
                    ->withTimestamps();
    }


    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_organisation_role', 'user_id', 'role_id')
                    ->withPivot('organisation_id')
                    ->withTimestamps();
    }



    public function checkUserPermission($permissionName, $organisationId = null)
    {
        // Utiliser un cache pour les permissions fréquemment vérifiées
        $permission = Cache::remember('permission:'.$permissionName, 3600, function() use ($permissionName) {
            return Permission::where('name', $permissionName)->select('id')->first();
        });

        if (!$permission) {
            return false;
        }

        $organisationId = $organisationId ?? $this->current_organisation_id;

        // Requête optimisée avec jointure directe plutôt que whereHas
        return $this->roles()
            ->join('role_permission', 'roles.id', '=', 'role_permission.role_id')
            ->where('role_permission.permission_id', $permission->id)
            ->where('roles.organisation_id', $organisationId)
            ->exists();
    }



    public function hasPermissionTo($permissionName, $organisationId = null)
    {
        $organisationId = $organisationId ?? $this->current_organisation_id;

        return $this->roles()
            ->wherePivot('organisation_id', $organisationId)
            ->whereHas('permissions', function ($query) use ($permissionName) {
                $query->where('permissions.name', $permissionName);
            })
            ->exists();
    }



}
