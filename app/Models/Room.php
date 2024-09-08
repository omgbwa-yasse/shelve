<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\floor;
use App\Models\shelf;
use App\Models\RoomType;



class Room extends Model
{
    use HasFactory;
    protected $fillable = ['code', 'name', 'description', 'floor_id', 'creator_id', 'type_id'];

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


    public function type()
    {
        return $this->belongsTo(RoomType::class, 'type_id');
    }

    public function organisations()
    {
        return $this->belongsToMany(Organisation::class, 'organisation_room', 'room_id', 'organisation_id');
    }
}
