<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Organisation extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'parent_id',
    ];
    public function parent()
    {
        return $this->belongsTo(Organisation::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Organisation::class, 'parent_id');
    }

    // Méthode helper pour obtenir tous les enfants récursivement
    public function getAllChildren()
    {
        return $this->children()->with('getAllChildren');
    }

    // Méthode helper pour obtenir tous les parents récursivement
    public function getAllParents()
    {
        return $this->parent()->with('getAllParents');
    }

    // Méthode pour vérifier si l'organisation est une racine (direction)
    public function isRoot()
    {
        return is_null($this->parent_id);
    }

    // Méthode pour obtenir le niveau hiérarchique
    public function getLevel()
    {
        $level = 0;
        $parent = $this->parent;
        while ($parent) {
            $level++;
            $parent = $parent->parent;
        }
        return $level;
    }



    public function actives()
    {
        return $this->hasMany(OrganisationActive::class, 'organisation_id');
    }

    public function userSlips()
    {
        return $this->hasMany(Slip::class, 'user_organisation_id');
    }

    public function officerSlips()
    {
        return $this->hasMany(Slip::class, 'officer_organisation_id');
    }

    public function activities()
    {
        return $this->belongsToMany(Activity::class, 'organisation_activity', 'organisation_id', 'activity_id');
    }

    public function rooms()
    {
        return $this->belongsToMany(Room::class, 'organisation_room', 'organisation_id', 'room_id');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_organisation_role', 'organisation_id', 'role_id');
    }


    public function permissions()
    {
        return $this->hasManyThrough(
            Permission::class,
            Role::class,
            'organisation_id', // Foreign key on user_organisation_role table
            'role_id',         // Foreign key on role_permissions table
            'id',              // Local key on organisations table
            'id'               // Local key on roles table
        );
    }

}




