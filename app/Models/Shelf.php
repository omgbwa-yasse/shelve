<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\room;


class Shelf extends Model
{
    use HasFactory;
    protected $fillable = [
        'reference', 'observation', 'ear', 'face', 'colonne', 'table', 'room_id'
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
