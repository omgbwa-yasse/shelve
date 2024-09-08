<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomType extends Model
{
    use HasFactory;

    protected $table = 'room_types';

    protected $fillable = ['name', 'description'];


    protected $guarded = [];

    protected $dates = ['created_at', 'updated_at'];



    public function rooms()
    {
        return $this->belongsTo(Room::class, 'type_id');
    }


}
