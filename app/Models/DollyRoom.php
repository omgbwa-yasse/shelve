<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DollyRoom extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id',
        'dolly_id',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function dolly()
    {
        return $this->belongsTo(Dolly::class);
    }
}
