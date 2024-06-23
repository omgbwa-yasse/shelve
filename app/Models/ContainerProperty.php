<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContainerProperty extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'width', 'length', 'depth', 'creator_id'];

    public function containers()
    {
        return $this->hasMany(Container::class, 'property_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }
}
