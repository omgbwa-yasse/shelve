<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Role et OrganisationInterim sont dans le même namespace App\Models.

class Organisation extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'parent_id'];

    public function parent()
    {
        return $this->belongsTo(Organisation::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Organisation::class, 'parent_id');
    }

    public function rooms()
    {
        return $this->belongsToMany(Room::class, 'organisation_room', 'organisation_id', 'room_id');
    }

    public function activities()
    {
        return $this->belongsToMany(Activity::class, 'organisation_activity', 'organisation_id', 'activity_id');
    }

    public function contacts()
    {
        return $this->belongsToMany(Contact::class, 'organisation_contact', 'organisation_id', 'contact_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_organisation_role', 'organisation_id', 'user_id')
            ->withPivot('role_id')
            ->withTimestamps();
    }

    /**
     * Remonte la chaîne des organisations parentes (du parent direct jusqu'à la racine).
     *
     * @return \Illuminate\Support\Collection<int, \App\Models\Organisation>
     */
    public function ancestors()
    {
        $ancestors = collect();
        $current = $this->parent;
        $guard = 0; // Garde-fou contre une hiérarchie corrompue (cycle)

        while ($current && $guard < 50) {
            $ancestors->push($current);
            $current = $current->parent;
            $guard++;
        }

        return $ancestors;
    }

    /**
     * Retourne l'utilisateur portant l'un des rôles donnés dans cette organisation.
     * Tient compte d'un éventuel intérim actif (voir OrganisationInterim).
     *
     * @param  array<int, string>  $roleNames
     */
    public function userWithRole(array $roleNames, ?int $activityId = null): ?User
    {
        $roleIds = Role::whereIn('name', $roleNames)->pluck('id');

        if ($roleIds->isEmpty()) {
            return null;
        }

        $titular = $this->users()
            ->wherePivotIn('role_id', $roleIds)
            ->first();

        if (!$titular) {
            return null;
        }

        // Si un intérim actif remplace le titulaire, on renvoie celui dont le volet
        // couvre l'activité concernée (à défaut, le volet global ou le principal).
        $interim = OrganisationInterim::activeForActivity($titular->id, $this->id, $activityId);

        return $interim ? $interim->interim : $titular;
    }

    /**
     * Le responsable attitré de cette organisation (responsable de service,
     * ou directeur, ou DG selon le niveau hiérarchique).
     *
     * @param  int|null  $activityId  activité du plan de classement concernée :
     *                                permet de router vers l'intérimaire du bon volet.
     */
    public function responsible(?int $activityId = null): ?User
    {
        return $this->userWithRole(['responsable', 'directeur', 'DG'], $activityId);
    }
}




