<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\floor;
use App\Models\shelf;

class Room extends Model
{
    use HasFactory;
    protected $fillable = ['code', 'name', 'description', 'visibility', 'type', 'floor_id', 'creator_id'];

    protected $casts = [
        'visibility' => 'string',
        'type' => 'string',
    ];

    public function floor()
    {
        return $this->belongsTo(Floor::class);
    }

    public function shelves()
    {
        return $this->hasMany(Shelf::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function organisations()
    {
        return $this->belongsToMany(Organisation::class, 'organisation_room', 'room_id', 'organisation_id');
    }

    // Méthodes pour la visibilité
    public function isPublic()
    {
        return $this->visibility === 'public';
    }

    public function isPrivate()
    {
        return $this->visibility === 'private';
    }

    public function inheritsVisibility()
    {
        return $this->visibility === 'inherit';
    }

    // Méthodes pour le type
    public function isArchives()
    {
        return $this->type === 'archives';
    }

    public function isProducer()
    {
        return $this->type === 'producer';
    }

    // Obtenir la visibilité effective (en tenant compte de l'héritage)
    public function getEffectiveVisibility()
    {
        if ($this->visibility === 'inherit' && $this->floor && $this->floor->building) {
            return $this->floor->building->visibility;
        }
        return $this->visibility;
    }
}
