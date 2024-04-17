<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Building;

class Floor extends Model
{
    use HasFactory;
    protected $fillable = [
        'name', 'description', 'building_id'
    ];

    public function building()
    {
        return $this->belongsTo(Building::class);
    }
}
