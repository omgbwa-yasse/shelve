<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Floor;

class Building extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'description', 'address', 'capacity', 'visibility', 'creator_id'];

    protected $casts = [
        'visibility' => 'string',
        'capacity' => 'integer',
    ];

    public function floors()
    {
        return $this->hasMany(Floor::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
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
}
