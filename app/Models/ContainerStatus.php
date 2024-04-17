<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContainerStatus extends Model
{
    protected $fillable = [
        'name', 'description'
    ];

    public function containers()
    {
        return $this->hasMany(Container::class);
    }
}
