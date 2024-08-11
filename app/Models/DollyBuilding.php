<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DollyBuilding extends Model
{
    use HasFactory;


    protected $table = 'dolly_buildings';

    protected $fillable = [
        'building_id',
        'dolly_id',
    ];

    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function dolly()
    {
        return $this->belongsTo(Dolly::class);
    }
}
