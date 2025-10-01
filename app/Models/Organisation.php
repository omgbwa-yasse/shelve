<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        return $this->belongsToMany(User::class, 'user_organisation_role', 'organisation_id', 'user_id');
    }
}




