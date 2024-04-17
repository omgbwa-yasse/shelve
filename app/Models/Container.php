<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Container extends Model
{
    use HasFactory;
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
