<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use App\Models\Organisation;
use App\Models\Role;
use App\Models\Permission;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

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

    /**
     * Vérifier si l'utilisateur est un superadmin
     *
     * @return bool
     */
    public function isSuperAdmin()
    {
        return $this->hasRole('superadmin') || $this->hasRole('super-admin');
    }

    /**
     * Méthode de compatibilité pour vérifier les permissions avec organisation
     * Compatible avec le système existant
     */
    public function checkUserPermission($permissionName, $organisationId = null)
    {
        // Si c'est un superadmin, autoriser tout
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Utiliser Spatie pour vérifier la permission
        return $this->hasPermissionTo($permissionName);
    }

    /**
     * Définir le guard par défaut pour Spatie Laravel Permission
     */
    public function getDefaultGuardName(): string
    {
        return 'web';
    }
}
