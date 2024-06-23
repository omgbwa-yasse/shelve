<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Container extends Model
{
    use HasFactory;
    protected $fillable = ['code', 'shelve_id', 'status_id', 'property_id', 'creator_id'];

    public function shelf()
    {
        return $this->belongsTo(Shelf::class, 'shelve_id');
    }

    public function status()
    {
        return $this->belongsTo(ContainerStatus::class, 'status_id');
    }

    public function property()
    {
        return $this->belongsTo(ContainerProperty::class, 'property_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }
}
