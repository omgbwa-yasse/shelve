<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\floor;
use App\Models\shelf;


class Room extends Model
{
    use HasFactory;
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
