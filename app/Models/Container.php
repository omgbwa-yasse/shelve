<?php

namespace App\Models;

use App\Models\ContainerStatus;
use App\Models\Property;
use App\Models\Shelf;
use Illuminate\Database\Eloquent\Model;

class Container extends Model
{
    protected $fillable = [
        'reference', 'shelve_id', 'status_id', 'property_id'
    ];

    public function shelf()
    {
        return $this->belongsTo(Shelf::class);
    }

    public function status()
    {
        return $this->belongsTo(ContainerStatus::class);
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }
}
