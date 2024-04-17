<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shelf extends Model
{
    protected $fillable = [
        'reference', 'observation', 'ear', 'face', 'colonne', 'table', 'room_id'
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
