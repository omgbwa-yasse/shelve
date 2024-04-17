<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Communication extends Model
{
    protected $fillable = ['record_id', 'operator', 'user', 'date_creation', 'return', 'return_effective'];

    public function record()
    {
        return $this->belongsTo(Record::class);
    }

    public function operator()
    {
        return $this->belongsTo(User::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
