<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\floor;
use App\Models\shelf;


class Room extends Model
{
    use HasFactory;
    protected $fillable = ['code', 'name', 'description', 'floor_id', 'creator_id'];

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
}
