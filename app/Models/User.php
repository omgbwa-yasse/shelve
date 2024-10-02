<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
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
        $permission = Permission::where('name', $permissionName)->first();

        if (!$permission) {
            return false;
        }

        $organisationId = $organisationId ?? $this->current_organisation_id;

        return $this->roles()
            ->wherePivot('organisation_id', $organisationId)
            ->whereHas('permissions', function ($query) use ($permission) {
                $query->where('permissions.id', $permission->id);
            })
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
