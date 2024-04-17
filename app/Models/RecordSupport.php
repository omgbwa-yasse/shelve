<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecordSupport extends Model
{
    protected $fillable = [
        'name', 'observation'
    ];

    public function records()
    {
        return $this->hasMany(Record::class);
    }
}
