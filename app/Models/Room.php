<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = [
        'reference', 'name', 'description', 'floor_id'
    ];

    public function floor()
    {
        return $this->belongsTo(Floor::class);
    }

    public function shelves()
    {
        return $this->hasMany(Shelf::class);
    }
}
