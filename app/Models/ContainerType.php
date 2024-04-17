<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ContainerType extends Model
{
    protected $fillable = [
        'name', 'description'
    ];

    public function mailContainers()
    {
        return $this->hasMany(MailContainer::class);
    }
}
