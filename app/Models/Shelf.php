<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\room;


class Shelf extends Model
{
    use HasFactory;
    protected $fillable = ['code', 'observation', 'face', 'ear', 'shelf', 'shelf_length', 'room_id', 'creator_id'];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function containers()
    {
        return $this->hasMany(Container::class, 'shelve_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    // Relation via room->organisations pour filtrer par organisation
    public function getOrganisationsAttribute()
    {
        return $this->room ? $this->room->organisations : collect();
    }
}
