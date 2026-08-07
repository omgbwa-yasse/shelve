<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organisation extends Model
{
    use HasFactory;

    // `description` ajouté au portage D09 : la colonne existe dans le schéma
    // (text NULL) mais le `$fillable` l'omettait, ce qui la faisait silencieusement
    // disparaître au `Organisation::create($request->all())` du Blade.
    protected $fillable = ['code', 'name', 'description', 'parent_id', 'email_module_enabled'];

    protected $casts = [
        'email_module_enabled' => 'boolean',
    ];

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
        return $this->belongsToMany(User::class, 'user_organisation_role', 'organisation_id', 'user_id');
    }

    /** Projets/OKR/KPI rattachés à cette unité administrative — voir App\Traits\HasAttachable. */
    public function projects()
    {
        return $this->morphMany(Project::class, 'attachable');
    }

    public function objectives()
    {
        return $this->morphMany(Objective::class, 'attachable');
    }

    public function kpis()
    {
        return $this->morphMany(Kpi::class, 'attachable');
    }
}




