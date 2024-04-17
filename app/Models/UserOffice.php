<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserOffice extends Model
{
    protected $fillable = ['user_id', 'office_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function office()
    {
        return $this->belongsTo(Office::class);
    }
}
