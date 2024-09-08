<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganisationRoom extends Model
{
    use HasFactory;

    protected $table = 'organisation_room';

    protected $fillable = ['room_id', 'organisation_id'];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }
}
