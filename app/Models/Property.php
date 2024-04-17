<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    protected $fillable = [
        'name', 'width', 'lengh', 'thinkness'
    ];

    public function containers()
    {
        return $this->hasMany(Container::class);
    }
}
