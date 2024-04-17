<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecordLevel extends Model
{
    protected $fillable = [
        'name', 'description', 'child'
    ];

    public function records()
    {
        return $this->hasMany(Record::class);
    }
}
