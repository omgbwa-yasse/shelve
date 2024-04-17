<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Floor extends Model
{
    protected $fillable = [
        'name', 'description', 'building_id'
    ];

    public function building()
    {
        return $this->belongsTo(Building::class);
    }
}
